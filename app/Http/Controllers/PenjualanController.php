<?php
namespace App\Http\Controllers;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Aksesoris;
use App\Models\Frame;
use App\Models\Lensa;
use App\Models\User;
use App\Models\Pasien;
use App\Services\BpjsPricingService;
use App\Helpers\WhatsAppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\OpenDay;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    protected $bpjsPricingService;
    private const BPJS_SERVICE_TYPES = ['BPJS I', 'BPJS II', 'BPJS III'];
    private const WORK_STATUS_MENUNGGU_PENGERJAAN = 'Menunggu Pengerjaan';
    private const WORK_STATUS_SEDANG_MENGERJAKAN = 'Sedang Mengerjakan';
    private const WORK_STATUS_LENSA_DI_PESAN = 'Lensa Di Pesan';
    private const WORK_STATUS_LENSA_DATANG = 'Lensa Datang';
    private const WORK_STATUS_SUDAH_DI_KERJAKAN = 'Sudah Di Kerjakan';
    private const WORK_STATUS_KIRIM_WA = 'Kirim WA';
    private const WORK_STATUS_SUDAH_DI_AMBIL = 'Sudah Di Ambil';
    private const WORK_STATUS_ALLOWED = [
        self::WORK_STATUS_MENUNGGU_PENGERJAAN,
        self::WORK_STATUS_SEDANG_MENGERJAKAN,
        self::WORK_STATUS_LENSA_DI_PESAN,
        self::WORK_STATUS_LENSA_DATANG,
        self::WORK_STATUS_SUDAH_DI_KERJAKAN,
        self::WORK_STATUS_KIRIM_WA,
        self::WORK_STATUS_SUDAH_DI_AMBIL,
    ];
    private array $tableColumnsCache = [];

    public function __construct(BpjsPricingService $bpjsPricingService)
    {
        $this->bpjsPricingService = $bpjsPricingService;
    }

    private function isBpjsServiceType(?string $serviceType): bool
    {
        return in_array($serviceType, self::BPJS_SERVICE_TYPES, true);
    }

    private function resolveBpjsTransactionStatus(float $frameAdditionalCost, float $manualAdditionalCost): string
    {
        return ($frameAdditionalCost > 0 || $manualAdditionalCost > 0) ? 'Naik Kelas' : 'Normal';
    }

    private function calculateBpjsPatientPayableTotal(float $bpjsAdditionalCost, float $aksesoriTotal, float $diskon = 0): float
    {
        $discount = max(0, $diskon);
        $netAdditionalCost = max(0, $bpjsAdditionalCost - $discount);
        $remainingDiscount = max(0, $discount - $bpjsAdditionalCost);

        return max(0, $netAdditionalCost + $aksesoriTotal - $remainingDiscount);
    }

    private function storeBase64ImageToPublicDisk(string $base64Image, string $directory = 'photos_bpjs'): ?string
    {
        if (!preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $base64Image, $matches)) {
            return null;
        }

        $base64Payload = substr($base64Image, strpos($base64Image, ',') + 1);
        $binary = base64_decode($base64Payload, true);

        if ($binary === false) {
            return null;
        }

        if (strlen($binary) > (3 * 1024 * 1024)) {
            throw new \RuntimeException('Ukuran foto BPJS melebihi batas 3MB.');
        }

        $extension = strtolower($matches[1]);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $relativePath = trim($directory, '/') . '/' . now()->format('YmdHis') . '-' . Str::random(10) . '.' . $extension;
        Storage::disk('public')->put($relativePath, $binary);

        return $relativePath;
    }

    /**
     * Resolve branch id for transaction context with safe fallback for admin roles.
     */
    private function resolveBranchIdForUser($user): ?int
    {
        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            return $user->branch_id ? (int) $user->branch_id : null;
        }

        $sessionBranchId = session('active_branch_id');
        if (!empty($sessionBranchId)) {
            return (int) $sessionBranchId;
        }

        if (!empty($user->branch_id)) {
            session(['active_branch_id' => (int) $user->branch_id]);
            return (int) $user->branch_id;
        }

        $firstBranchId = \App\Models\Branch::query()->value('id');
        if (!empty($firstBranchId)) {
            session(['active_branch_id' => (int) $firstBranchId]);
            return (int) $firstBranchId;
        }

        return null;
    }

    private function findFreeCleanerAksesoris(int $branchId): ?Aksesoris
    {
        return Aksesoris::query()
            ->where('branch_id', $branchId)
            ->where('stok', '>', 0)
            ->whereRaw('LOWER(nama_produk) LIKE ?', ['%cleaner%'])
            ->orderByDesc('stok')
            ->first();
    }

    private function applyJenisTransaksiFilter($query, ?string $jenisTransaksi, bool $hasJenisTransaksiColumn)
    {
        if (!$jenisTransaksi) {
            return $query;
        }

        if ($hasJenisTransaksiColumn) {
            if ($jenisTransaksi === 'Gosok') {
                return $query->where('jenis_transaksi', 'Gosok');
            }

            return $query->where(function ($q) {
                $q->where('jenis_transaksi', 'Stock')
                    ->orWhereNull('jenis_transaksi');
            });
        }

        if (!$this->hasTableColumn('lensa', 'is_custom_order')) {
            return $query;
        }

        $gosokLensIds = Lensa::query()
            ->where('is_custom_order', true)
            ->select('id');

        if ($jenisTransaksi === 'Gosok') {
            return $query->whereHas('details', function ($detailQuery) use ($gosokLensIds) {
                $detailQuery->where('itemable_type', Lensa::class)
                    ->whereIn('itemable_id', $gosokLensIds);
            });
        }

        return $query->whereDoesntHave('details', function ($detailQuery) use ($gosokLensIds) {
            $detailQuery->where('itemable_type', Lensa::class)
                ->whereIn('itemable_id', $gosokLensIds);
        });
    }

    private function normalizeWhatsappNumber(?string $phone): ?string
    {
        // Delegate to centralized WhatsAppHelper
        return WhatsAppHelper::normalizePhoneNumber($phone);
    }

    private function filterDataByExistingColumns(string $table, array $data): array
    {
        if (!isset($this->tableColumnsCache[$table])) {
            $this->tableColumnsCache[$table] = Schema::getColumnListing($table);
        }

        $available = array_flip($this->tableColumnsCache[$table]);
        return array_filter(
            $data,
            static fn ($key) => isset($available[$key]),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Cache-aware check for column existence to prevent repeated DB hits
     */
    private function hasTableColumn(string $table, string $column): bool
    {
        if (!isset($this->tableColumnsCache[$table])) {
            $this->tableColumnsCache[$table] = Schema::getColumnListing($table);
        }

        return in_array($column, $this->tableColumnsCache[$table], true);
    }

    private function buildReadyPickupMessage(Penjualan $penjualan): string
    {
        $namaPasien = $penjualan->nama_pasien ?: 'Pelanggan';
        $kode = $penjualan->kode_penjualan ?: '-';
        $cabang = $penjualan->branch->name ?? 'Optik Melati';
        $normalizedCabang = Str::lower($cabang);

        $jamOperasional = "*Jam Operasional*\n"
            . "Senin - Sabtu: 08.00 - 16.30 WIB\n"
            . "Istirahat: 12.30 - 13.30 WIB\n"
            . "Minggu: Tutup";

        if (Str::contains($normalizedCabang, ['optik melati cabang 2', 'optik melati 2'])) {
            $jamOperasional = "*Jam Operasional*\n"
                . "Senin - Jumat: 11.00 - 19.30 WIB ISTIRAHAT 15.00-16.00\n"
                . "Sabtu: 09.00 - 17.30 WIB ISTIRAHAT 12.00-13.00\n"
                . "Minggu dan tanggal merah: Tutup";
        }

        return "Halo Bapak/Ibu {$namaPasien},\n\n"
            . "Kami informasikan bahwa kacamata Anda dengan nomor nota *{$kode}* telah selesai dikerjakan dan sudah dapat diambil di *{$cabang}*.\n\n"
            . $jamOperasional . "\n\n"
            . "Mohon melakukan pengambilan pada jam operasional. Kami tidak melayani pengambilan di luar jam kerja.\n\n"
            . "Terima kasih atas kepercayaan Anda kepada Optik Melati. Kami tunggu kedatangannya.";
    }

    private function notifyWhatsappReadyPickup(Penjualan $penjualan): array
    {
        $phone = WhatsAppHelper::normalizePhoneNumber($penjualan->pasien?->nohp ?? null);
        if (!$phone) {
            return [
                'success' => false,
                'channel' => 'none',
                'message' => 'Nomor WhatsApp pasien belum tersedia.',
            ];
        }

        $message = $this->buildReadyPickupMessage($penjualan);
        $waLink = WhatsAppHelper::buildShareLink($phone, $message);

        // Try to send via gateway first
        $gatewayResult = WhatsAppHelper::sendViaGateway($phone, $message);
        if ($gatewayResult['success']) {
            return [
                'success' => true,
                'channel' => 'gateway',
                'message' => $gatewayResult['message'],
            ];
        }

        // Fallback to manual link
        return [
            'success' => true,
            'channel' => 'wa_link',
            'message' => ($gatewayResult['message'] ?? 'WhatsApp gateway tidak aktif.') . ' Kirim pesan manual melalui link berikut.',
            'open_link' => true,
            'link' => $waLink,
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $branches = collect(); // Default to empty collection
        $selectedBranchId = $user->branch_id;

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $branches = \App\Models\Branch::all();
            // If an active branch is set in session, use it, otherwise use user's default
            $selectedBranchId = session('active_branch_id', $user->branch_id);
        }

        return view('penjualan.index', compact('branches', 'selectedBranchId'));
    }

    public function statistics()
    {
        $user = auth()->user();
        $query = Penjualan::query();
        $hasJenisTransaksiColumn = $this->hasTableColumn('penjualan', 'jenis_transaksi');
        $bulan = (int) request()->input('bulan', now()->format('m'));
        $tahun = (int) request()->input('tahun', now()->format('Y'));

        // Jika user super admin atau admin, gunakan branch_id dari request jika ada
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            if (request()->has('branch_id') && request()->branch_id !== '') {
                $query->where('branch_id', request()->branch_id);
            } else {
                // Jika tidak ada branch_id di request, default ke branch user atau semua jika super admin
                // Note: Jika memilih 'Tampilkan Semua Cabang', request()->branch_id akan menjadi empty string
                // Jadi hanya terapkan filter jika ada branch_id yang valid
                if (!$user->isSuperAdmin()) { // Hanya terapkan default jika bukan super admin
                    $query->where('branch_id', $user->branch_id);
                }
            }
        } else {
            // User biasa hanya bisa melihat cabang mereka sendiri
            $query->where('branch_id', $user->branch_id);
        }

        $this->applyJenisTransaksiFilter($query, request()->jenis_transaksi, $hasJenisTransaksiColumn);

        $query->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);

        $statistics = $query->selectRaw('
            SUM(CASE WHEN status_pengerjaan = "Menunggu Pengerjaan" THEN 1 ELSE 0 END) as menunggu,
            SUM(CASE WHEN status_pengerjaan = "Lensa Di Pesan" THEN 1 ELSE 0 END) as lensa_dipesan,
            SUM(CASE WHEN status_pengerjaan = "Lensa Datang" THEN 1 ELSE 0 END) as lensa_datang,
            SUM(CASE WHEN status_pengerjaan = "Sedang Mengerjakan" THEN 1 ELSE 0 END) as sedang,
            SUM(CASE WHEN status_pengerjaan = "Sudah Di Kerjakan" THEN 1 ELSE 0 END) as selesai,
            SUM(CASE WHEN status_pengerjaan = "Kirim WA" THEN 1 ELSE 0 END) as kirim_wa,
            SUM(CASE WHEN status_pengerjaan = "Sudah Di Ambil" THEN 1 ELSE 0 END) as diambil
        ')->first();

        return response()->json([
            'menunggu' => (int) $statistics->menunggu,
            'lensa_dipesan' => (int) $statistics->lensa_dipesan,
            'lensa_datang' => (int) $statistics->lensa_datang,
            'sedang' => (int) $statistics->sedang,
            'selesai' => (int) $statistics->selesai,
            'kirim_wa' => (int) $statistics->kirim_wa,
            'diambil' => (int) $statistics->diambil
        ]);
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $query = Penjualan::query()
            ->with(['user', 'branch', 'passetByUser', 'dokter', 'pasien'])
            ->latest();
        $hasJenisTransaksiColumn = $this->hasTableColumn('penjualan', 'jenis_transaksi');
        $bulan = (int) $request->input('bulan', now()->format('m'));
        $tahun = (int) $request->input('tahun', now()->format('Y'));

        // Jika user super admin atau admin, gunakan branch_id dari request jika ada
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            if ($request->has('branch_id') && $request->branch_id !== '') {
                $query->where('branch_id', $request->branch_id);
            } else {
                // Jika tidak ada branch_id di request, default ke branch user atau semua jika super admin
                // Note: Jika memilih 'Tampilkan Semua Cabang', request()->branch_id akan menjadi empty string
                // Jadi hanya terapkan filter jika ada branch_id yang valid
                if (!$user->isSuperAdmin()) { // Hanya terapkan default jika bukan super admin
                    $query->where('branch_id', $user->branch_id);
                }
            }
        } else {
            // User biasa hanya bisa melihat cabang mereka sendiri
            $query->where('branch_id', $user->branch_id);
        }

        // Filter berdasarkan status pengerjaan jika ada
        if ($request->has('status_filter') && $request->status_filter) {
            $query->where('status_pengerjaan', $request->status_filter);
        }

        // Filter berdasarkan jenis transaksi jika ada
        $this->applyJenisTransaksiFilter($query, $request->jenis_transaksi, $hasJenisTransaksiColumn);

        // DataTables memakai tanggal transaksi yang dapat diubah admin, bukan waktu record dibuat.
        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();
        $query->whereBetween('tanggal', [$awalBulan->toDateString(), $akhirBulan->toDateString()]);

        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->filterColumn('nama_pasien', function ($query, $keyword) {
                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->whereHas('pasien', function ($pasienQuery) use ($keyword) {
                        $pasienQuery->where('nama_pasien', 'like', "%{$keyword}%");
                    })->orWhere('nama_pasien_manual', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('nama_dokter', function ($query, $keyword) {
                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->whereHas('dokter', function ($dokterQuery) use ($keyword) {
                        $dokterQuery->where('nama_dokter', 'like', "%{$keyword}%");
                    })->orWhere('dokter_manual', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('jenis_layanan', function ($query, $keyword) {
                $query->where(function ($searchQuery) use ($keyword) {
                    $searchQuery->where('pasien_service_type', 'like', "%{$keyword}%")
                        ->orWhereHas('pasien', function ($pasienQuery) use ($keyword) {
                            $pasienQuery->where('service_type', 'like', "%{$keyword}%");
                        });
                });
            })
            ->filterColumn('passet_by', function ($query, $keyword) {
                $query->whereHas('passetByUser', function ($userQuery) use ($keyword) {
                    $userQuery->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->tanggal ?? $penjualan->created_at, false);
            })
            ->editColumn('kode_penjualan', function ($penjualan) {
                return '<span class="label label-success">'. $penjualan->kode_penjualan .'</span>';
            })
            ->addColumn('total_harga', function ($penjualan) {
                // Jika transaksi BPJS, tampilkan harga BPJS default
                if ($penjualan->pasien && in_array($penjualan->pasien->service_type, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                    $bpjsPrice = 0;
                    if ($penjualan->bpjs_default_price > 0) {
                        $bpjsPrice = $penjualan->bpjs_default_price;
                    } else {
                        // Fallback jika bpjs_default_price tidak tersimpan
                        $bpjsPricingService = new \App\Services\BpjsPricingService();
                        $bpjsPrice = $bpjsPricingService->getDefaultPrice($penjualan->pasien->service_type);
                    }

                    $manualAdditional = max(0, (float) ($penjualan->bpjs_manual_additional_cost ?? 0));
                    $finalBpjsAmount = $bpjsPrice + $manualAdditional;

                    if ($manualAdditional > 0) {
                        return '<span class="label label-info" title="BPJS: ' . $penjualan->pasien->service_type . '">Rp. '. format_uang($finalBpjsAmount) . '</span>'
                            . '<br><small class="text-warning">Tambahan: Rp. ' . format_uang($manualAdditional) . '</small>';
                    }

                    return '<span class="label label-info" title="BPJS: ' . $penjualan->pasien->service_type . '">Rp. '. format_uang($finalBpjsAmount) . '</span>';
                }
                // Untuk transaksi umum, tampilkan total normal
                return '<span class="text-success">Rp. '. format_uang($penjualan->total) . '</span>';
            })

            ->addColumn('passet_by', function ($penjualan) {
                return $penjualan->passetByUser?->name ?? '-';
            })
            ->addColumn('nama_pasien', function ($penjualan) {
                return $penjualan->pasien?->nama_pasien
                    ?? ($penjualan->nama_pasien_manual ?: '-');
            })
            ->addColumn('nama_dokter', function ($penjualan) {
                if ($penjualan->dokter && !empty($penjualan->dokter->nama_dokter)) {
                    return $penjualan->dokter->nama_dokter;
                }
                if (!empty($penjualan->dokter_manual)) {
                    return $penjualan->dokter_manual;
                }
                return '-';
            })
            ->addColumn('jenis_layanan', function ($penjualan) {
                if ($penjualan->pasien && !empty($penjualan->pasien->service_type)) {
                    $serviceType = $penjualan->pasien->service_type;
                    // Berikan warna berbeda untuk jenis layanan
                    if (in_array($serviceType, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                        return '<span class="label label-info">' . $serviceType . '</span>';
                    } else {
                        return '<span class="label label-default">' . $serviceType . '</span>';
                    }
                }
                return '<span class="label label-default">UMUM</span>';
            })
            ->addColumn('status_transaksi', function ($penjualan) {
                if ($penjualan->transaction_status == 'Naik Kelas') {
                    return '<span class="label label-warning">Naik Kelas</span>';
                } else {
                    return '<span class="label label-success">' . ($penjualan->transaction_status ?? 'Normal') . '</span>';
                }
            })
            ->addColumn('metode_pembayaran', function ($penjualan) {
                $metode = strtolower((string) ($penjualan->metode_pembayaran ?? ''));
                $bank = strtoupper((string) ($penjualan->bank_transfer ?? ''));

                if ($metode === 'transfer') {
                    $label = 'Transfer' . ($bank !== '' ? ' - ' . $bank : '');
                    return '<span class="label label-info">' . e($label) . '</span>';
                }

                if ($metode === 'qris') {
                    return '<span class="label label-primary">QRIS</span>';
                }

                if ($metode === 'cash') {
                    return '<span class="label label-success">Cash</span>';
                }

                return '<span class="label label-default">-</span>';
            })
            ->addColumn('status_pembayaran', function ($penjualan) {
                $serviceType = strtoupper((string) ($penjualan->pasien_service_type ?? ($penjualan->pasien->service_type ?? '')));
                $isBpjs = in_array($serviceType, self::BPJS_SERVICE_TYPES, true);

                if ($isBpjs) {
                    return '<span class="label label-info">Claim BPJS</span>';
                }

                if (($penjualan->status ?? '') === 'Lunas') {
                    return '<span class="label label-success">Lunas</span>';
                }

                return '<span class="label label-warning">Belum Lunas</span>';
            })
            ->addColumn('jenis_transaksi', function ($penjualan) {
                $jenis = $penjualan->jenis_transaksi ?? 'Stock';
                $labelClass = $jenis === 'Gosok' ? 'label-warning' : 'label-info';

                return '<span class="label ' . $labelClass . '">' . e($jenis) . '</span>';
            })
            ->addColumn('status_pengerjaan', function ($penjualan) {
                $statusClass = 'label-default';
                $statusText = $penjualan->status_pengerjaan;
                $timeText = '';

                if ($penjualan->status_pengerjaan == self::WORK_STATUS_SUDAH_DI_KERJAKAN) {
                    $statusClass = 'label-success';
                    if ($penjualan->waktu_selesai_dikerjakan) {
                        $timeText = '<br><small>'. tanggal_indonesia($penjualan->waktu_selesai_dikerjakan, true) .'</small>';
                    }
                } elseif ($penjualan->status_pengerjaan == self::WORK_STATUS_LENSA_DI_PESAN) {
                    $statusClass = 'label-warning';
                } elseif ($penjualan->status_pengerjaan == self::WORK_STATUS_MENUNGGU_PENGERJAAN) {
                    $statusClass = 'label-warning';
                } elseif ($penjualan->status_pengerjaan == self::WORK_STATUS_SEDANG_MENGERJAKAN) {
                    $statusClass = 'label-info';
                } elseif ($penjualan->status_pengerjaan == self::WORK_STATUS_LENSA_DATANG) {
                    $statusClass = 'label-primary';
                } elseif ($penjualan->status_pengerjaan == self::WORK_STATUS_KIRIM_WA) {
                    $statusClass = 'label-default';
                } elseif ($penjualan->status_pengerjaan == self::WORK_STATUS_SUDAH_DI_AMBIL) {
                    $statusClass = 'label-primary';
                    if ($penjualan->waktu_sudah_diambil) {
                        $timeText = '<br><small>'. tanggal_indonesia($penjualan->waktu_sudah_diambil, true) .'</small>';
                    }
                }

                return '<span class="label '. $statusClass .'">'. $statusText .'</span>' . $timeText;
            })
            ->addColumn('aksi', function ($penjualan) {
                $user = auth()->user();
                $detailButton = '<a href="'. route('penjualan.show', $penjualan->id) .'" class="btn btn-xs btn-info btn-flat" title="Detail"><i class="fa fa-eye"></i></a>';
                $editButton = '<a href="'. route('penjualan.edit', $penjualan->id) .'" class="btn btn-xs btn-warning btn-flat" title="Edit"><i class="fa fa-edit"></i></a>';
                $lunasButton = '';
                $statusButton = '';
                $ambilButton = '';
                $deleteButton = '';

                if (($penjualan->status ?? '') !== 'Lunas') {
                    $lunasButton = '<button onclick="lunasTransaksi(`'. route('penjualan.lunas', $penjualan->id) .'`, '. (float) ($penjualan->total ?? 0) .', '. (float) ($penjualan->bayar ?? 0) .', '. (float) ($penjualan->kekurangan ?? 0) .')" class="btn btn-xs btn-success btn-flat" title="Pelunasan"><i class="fa fa-money"></i></button>';
                }
                
                // Tombol update status pengerjaan
                if ($penjualan->status_pengerjaan != self::WORK_STATUS_SUDAH_DI_AMBIL) {
                    $statusButton = '<button onclick="updateStatusPengerjaan('.$penjualan->id.')" class="btn btn-xs btn-primary btn-flat" title="Update Status"><i class="fa fa-cogs"></i></button>';
                }
                
                if (in_array($penjualan->status_pengerjaan, [self::WORK_STATUS_SUDAH_DI_KERJAKAN, self::WORK_STATUS_KIRIM_WA], true)) {
                    $ambilButton = '<button onclick="tandaiDiambil(`'. route('penjualan.diambil', $penjualan->id) .'`)" class="btn btn-xs btn-success btn-flat" title="Tandai Diambil"><i class="fa fa-check-square"></i></button>';
                }
                
                // Hanya super admin dan admin yang bisa menghapus transaksi
                if ($user->isSuperAdmin() || $user->isAdmin()) {
                    $deleteButton = '<button onclick="hapusTransaksi(`'. route('penjualan.destroy', $penjualan->id) .'`)" class="btn btn-xs btn-danger btn-flat" title="Hapus"><i class="fa fa-trash"></i></button>';
                }

                return '
                <div class="btn-group">
                    '. $detailButton .'
                    '. $editButton .'
                    '. $lunasButton .'
                    '. $statusButton .'
                    '. $ambilButton .'
                    '. $deleteButton .'
                </div>
                ';
            })
            ->addColumn('barcode', function ($penjualan) {
                return $penjualan->barcode ?? null;
            })
            ->rawColumns(['aksi', 'kode_penjualan', 'status_pengerjaan', 'status_transaksi', 'status_pembayaran', 'metode_pembayaran', 'jenis_transaksi', 'total_harga', 'jenis_layanan'])
            ->make(true);
    }
    public function searchProduct(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $user = auth()->user();

        if ($user && ($user->isSuperAdmin() || $user->isAdmin())) {
            $branchId = session('active_branch_id', $user->branch_id);
        } else {
            $branchId = $user->branch_id ?? null;
        }

        if ($query === '') {
            return response()->json([]);
        }
        
        $frames = \App\Models\Frame::when($branchId, function ($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })
            ->where(function ($q) use ($query) {
                $q->where('merk_frame', 'LIKE', "{$query}%")
                  ->orWhere('kode_frame', 'LIKE', "{$query}%");
            })
            ->select('id', 'merk_frame as name', 'harga_jual_frame as price', \DB::raw("'frame' as type"))
            ->limit(5)
            ->get();
            
        $lenses = \App\Models\Lensa::when($branchId, function ($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })
            ->where(function ($q) use ($query) {
                $q->where('merk_lensa', 'LIKE', "{$query}%")
                  ->orWhere('kode_lensa', 'LIKE', "{$query}%");
            })
            ->select('id', 'merk_lensa as name', 'harga_jual_lensa as price', 'index', 'cly', 'add', \DB::raw("'lensa' as type"))
            ->limit(5)
            ->get();

        $aksesoris = Aksesoris::when($branchId, function ($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })
            ->where('nama_produk', 'LIKE', "{$query}%")
            ->select('id', 'nama_produk as name', 'harga_jual as price', \DB::raw("'aksesoris' as type"))
            ->limit(5)
            ->get();

        $products = $frames->concat($lenses)->concat($aksesoris);

        return response()->json($products);
    }

    public function getLensaStok(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $includeOutOfStock = filter_var(
                $request->input('include_out_of_stock', true),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            if ($includeOutOfStock === null) {
                $includeOutOfStock = true;
            }

            $query = \App\Models\Lensa::with(['branch', 'sales']);
            if (!$includeOutOfStock) {
                $query->where('stok', '>', 0);
            }

            // Filter by branch if not admin/super admin
            if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                $query->where('branch_id', $user->branch_id);
            }

            // Apply search filter. Prefix LIKE can use indexes and is much faster than %term% scans.
            if ($request->has('search') && !empty($request->search)) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('kode_lensa', 'LIKE', "{$search}%")
                      ->orWhere('merk_lensa', 'LIKE', "{$search}%")
                      ->orWhere('type', 'LIKE', "{$search}%")
                      ->orWhere('index', 'LIKE', "{$search}%")
                      ->orWhere('coating', 'LIKE', "{$search}%");
                });
            }

            $lensas = $query->orderBy('merk_lensa', 'asc')->limit(1000)->get();

            $normalizePrescriptionValue = static function ($value, $field = null) {
                $value = strtoupper(trim((string) $value));
                if ($value === '' || $value === '-') {
                    return $field === 'cyl' ? '0' : '';
                }

                if (in_array($value, ['PLANO', 'PL'], true)) {
                    return '0';
                }

                $value = str_replace(',', '.', $value);

                if (is_numeric($value)) {
                    $numericValue = (float) $value;
                    // Ukuran resep sering ditulis +050 atau -050 untuk 0.50.
                    if (in_array($field, ['sph', 'cyl'], true)
                        && preg_match('/^[+-]?\d{3}$/', trim((string) $value))) {
                        $numericValue /= 100;
                    }

                    // ADD stok sering tersimpan sebagai 300, sedangkan resep menulis 3.00.
                    if ($field === 'add' && abs($numericValue) >= 100) {
                        $numericValue /= 100;
                    }

                    return rtrim(rtrim(number_format($numericValue, 2, '.', ''), '0'), '.');
                }

                return preg_replace('/\s+/', '', $value);
            };

            $rightPrescription = [
                'sph' => $normalizePrescriptionValue($request->input('od_sph'), 'sph'),
                'cyl' => $normalizePrescriptionValue($request->input('od_cyl'), 'cyl'),
                'axis' => $normalizePrescriptionValue($request->input('od_axis'), 'axis'),
                'add' => $normalizePrescriptionValue($request->input('add_kanan', $request->input('add')), 'add'),
            ];
            $leftPrescription = [
                'sph' => $normalizePrescriptionValue($request->input('os_sph'), 'sph'),
                'cyl' => $normalizePrescriptionValue($request->input('os_cyl'), 'cyl'),
                'axis' => $normalizePrescriptionValue($request->input('os_axis'), 'axis'),
                'add' => $normalizePrescriptionValue($request->input('add_kiri', $request->input('add')), 'add'),
            ];
            $hasPrescription = collect($rightPrescription)->some(fn ($value) => $value !== '')
                || collect($leftPrescription)->some(fn ($value) => $value !== '');

            if ($hasPrescription && !$request->boolean('show_all_lens_sizes')) {
                $lensas = $lensas->filter(function ($lensa) use ($rightPrescription, $leftPrescription, $normalizePrescriptionValue) {
                    $lensValues = [
                        'sph' => $normalizePrescriptionValue($lensa->index, 'sph'),
                        'cyl' => $normalizePrescriptionValue($lensa->cly, 'cyl'),
                        'axis' => $normalizePrescriptionValue($lensa->axis, 'axis'),
                        'add' => $normalizePrescriptionValue($lensa->add, 'add'),
                    ];

                    $matchesPrescription = static function (array $prescription) use ($lensValues) {
                        foreach (['sph', 'cyl', 'add'] as $field) {
                            if ($lensValues[$field] !== $prescription[$field]) {
                                return false;
                            }
                        }

                        return $prescription['sph'] !== ''
                            || $prescription['cyl'] !== ''
                            || $prescription['add'] !== '';
                    };

                    return $matchesPrescription($rightPrescription) || $matchesPrescription($leftPrescription);
                })->values();
            }

            $data = $lensas->map(function($lensa) {
                return [
                    'id' => $lensa->id,
                    'kode_lensa' => $lensa->kode_lensa,
                    'merk_lensa' => $lensa->merk_lensa,
                    'type' => $lensa->type ?? '-',
                    'index' => $lensa->index ?? '-',
                    'coating' => $lensa->coating ?? '-',
                    'cly' => $lensa->cly ?? '-',
                    'axis' => $lensa->axis ?? '-',
                    'add' => $lensa->add ?? '-',
                    'stok' => $lensa->stok,
                    'harga_jual_lensa' => $lensa->harga_jual_lensa,
                    'harga_formatted' => format_uang($lensa->harga_jual_lensa),
                    'branch_name' => $lensa->branch ? $lensa->branch->name : '-',
                    'sales_name' => $lensa->sales ? $lensa->sales->nama_sales : '-'
                ];
            });

            return response()->json([
                'data' => $data,
                'total' => $data->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get lensa stok error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load lensa data'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $canBackdateTransaction = $user->isAdmin() || $user->isSuperAdmin();
        $defaultTransactionDate = now()->toDateString();

        $branch_id = $this->resolveBranchIdForUser($user);
        if (!$branch_id) {
            return view('penjualan.create', [
                'error_message' => 'Data cabang belum tersedia. Silakan buat cabang terlebih dahulu.',
                'branches' => \App\Models\Branch::all(),
                'pasiens' => collect(),
                'dokters' => collect(),
                'frames' => collect(),
                'lenses' => collect(),
                'aksesoris' => collect(),
                'selected_pasien' => null,
                'canBackdateTransaction' => $canBackdateTransaction,
                'defaultTransactionDate' => $defaultTransactionDate,
            ]);
        }

        if (!$canBackdateTransaction) {
            $today = now()->toDateString();
            $openDay = OpenDay::where('branch_id', $branch_id)->where('tanggal', $today)->first();

            if (!$openDay || !$openDay->is_open) {
                $branchName = \App\Models\Branch::find($branch_id)->name ?? 'Cabang ini';
                return redirect()->route('penjualan.index')
                    ->with('error', "{$branchName} belum dibuka hari ini. Silakan hubungi admin untuk melakukan Open Day terlebih dahulu.");
            }
        }
        
        $pasiens = \App\Models\Pasien::all();
        $dokters = \App\Models\Dokter::all();
        $frames = \App\Models\Frame::where('branch_id', $branch_id)->get();
        // Tampilkan semua lensa, termasuk yang stok 0
        $lenses = \App\Models\Lensa::where('branch_id', $branch_id)->get();
        $aksesoris = \App\Models\Aksesoris::where('branch_id', $branch_id)
            ->orderBy('nama_produk')
            ->get();
        
        // Cek apakah ada pasien_id yang dikirim dari form pasien
        $selected_pasien = null;
        if ($request->has('pasien_id')) {
            $selected_pasien = \App\Models\Pasien::with('prescriptions.dokter')->find($request->pasien_id);
        }
        
        // Conditional debug log
        if (config('app.debug')) {
            \Log::debug('PenjualanController@create - Data loaded', [
                'branch_id' => $branch_id,
                'lenses_count' => $lenses->count(),
                'frames_count' => $frames->count(),
                'aksesoris_count' => $aksesoris->count(),
                'selected_pasien_id' => $request->pasien_id ?? null
            ]);
        }
        
        return view('penjualan.create', compact(
            'pasiens',
            'dokters',
            'frames',
            'lenses',
            'aksesoris',
            'selected_pasien',
            'canBackdateTransaction',
            'defaultTransactionDate'
        ));
    }
    
    public function store(Request $request)
    {
        $user = auth()->user();
        $canBackdateTransaction = $user->isAdmin() || $user->isSuperAdmin();

        $branch_id = $this->resolveBranchIdForUser($user);
        if (!$branch_id) {
            return response()->json(['message' => 'Cabang tidak ditemukan. Silakan konfigurasi cabang terlebih dahulu.'], 422);
        }

        $today = now()->toDateString();
        if (!$canBackdateTransaction) {
            $openDay = OpenDay::where('branch_id', $branch_id)->where('tanggal', $today)->first();
            if (!$openDay || !$openDay->is_open) {
                return response()->json(['message' => 'Transaksi tidak dapat dilakukan. Kasir cabang ini sudah tutup atau belum dibuka.'], 403);
            }
        }
        // Validasi dasar
            $hasJenisTransaksiColumn = $this->hasTableColumn('penjualan', 'jenis_transaksi');
            $hasMetodePembayaranColumn = $this->hasTableColumn('penjualan', 'metode_pembayaran');
            $hasBankTransferColumn = $this->hasTableColumn('penjualan', 'bank_transfer');
            $hasBpjsManualAdditionalColumn = $this->hasTableColumn('penjualan', 'bpjs_manual_additional_cost');

            $rules = [
            'kode_penjualan' => 'required|unique:penjualan,kode_penjualan',
            'tanggal' => $canBackdateTransaction ? 'required|date|before_or_equal:today' : 'required|date|in:' . $today,
            'items' => 'required|json',
            'total' => 'required|numeric',
            'diskon' => 'required|numeric|min:0',
            'bayar' => 'required|numeric|min:0',
            'kekurangan' => 'required|numeric',
            'bpjs_manual_additional_cost' => 'nullable|numeric|min:0',
            'photo_bpjs' => 'nullable|image|max:3072',
            'photo_bpjs_webcam' => 'nullable|string',
        ];

        if ($hasMetodePembayaranColumn) {
            $rules['metode_pembayaran'] = 'required|in:cash,transfer,qris';
        }

        if ($hasBankTransferColumn) {
            $rules['bank_transfer'] = 'nullable|required_if:metode_pembayaran,transfer|in:BNI,BRI,MANDIRI,BSI,BCA';
        }

        if ($hasJenisTransaksiColumn) {
            $rules['jenis_transaksi'] = 'required|in:Stock,Gosok';
        }

        // Validasi kondisional untuk pasien
        if ($request->filled('pasien_id')) {
            $rules['pasien_id'] = 'exists:pasien,id_pasien';
        } else {
            $rules['pasien_name'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $transactionDate = $canBackdateTransaction
            ? Carbon::parse((string) $request->tanggal)->toDateString()
            : $today;
        $transactionDateTime = Carbon::parse($transactionDate . ' ' . now()->format('H:i:s'));

        DB::beginTransaction();
        try {
            
            $kekurangan = $request->kekurangan;
            $status = $kekurangan <= 0 ? 'Lunas' : 'Belum Lunas';
            $transactionStatus = 'Normal'; // Default status
            $jenisTransaksi = $hasJenisTransaksiColumn ? ($request->jenis_transaksi ?: 'Stock') : null;
            $bpjsDefaultPrice = 0;
            $totalAdditionalCost = 0;
            $bpjsManualAdditionalCost = 0;
            $pasienServiceType = null;
            $bpjsFrameSaleTotal = 0;
            $bpjsLensSaleTotal = 0;
            $bpjsAksesorisSaleTotal = 0;
            $firstFrameDetailId = null;
            $calculatedTotal = 0;

            $items = json_decode($request->items, true);
            $hanyaAksesoris = !empty($items) && collect($items)->every(function($item) {
                return $item['type'] === 'aksesoris';
            });
            $mengandungLensaGosok = !empty($items) && collect($items)->contains(function ($item) {
                return ($item['type'] ?? null) === 'lensa_gosok';
            });

            if ($mengandungLensaGosok) {
                $jenisTransaksi = 'Gosok';
            }

            $tanggalSiap = $mengandungLensaGosok
                ? now()->addDays(15)->toDateString()
                : $request->tanggal_siap;

            // Jika ada pasien, gunakan BPJS pricing service
            $pasien = null;
            if ($request->filled('pasien_id')) {
                $pasien = Pasien::find($request->pasien_id);
                if ($pasien && $this->isBpjsServiceType($pasien->service_type)) {
                    $pasienServiceType = $pasien->service_type;
                    $bpjsDefaultPrice = $this->bpjsPricingService->getDefaultPrice($pasien->service_type);
                    $bpjsManualAdditionalCost = $hasBpjsManualAdditionalColumn
                        ? max(0, (float) $request->input('bpjs_manual_additional_cost', 0))
                        : 0;
                    
                    // Conditional debug logging untuk BPJS pricing
                    if (config('app.debug')) {
                        \Log::debug('BPJS Pricing in Store Method:', [
                            'pasien_id' => $pasien->id_pasien,
                            'service_type' => $pasien->service_type,
                            'bpjs_default_price' => $bpjsDefaultPrice,
                            'pasien_service_type' => $pasienServiceType
                        ]);
                    }
                }
            }

            // Generate barcode
            $barcode = 'TRX' . date('Ymd') . str_pad(Penjualan::max('id') + 1, 6, '0', STR_PAD_LEFT);
            
            $penjualanData = [
                'kode_penjualan' => $request->kode_penjualan,
                'barcode' => $barcode,
                'tanggal' => $transactionDate,
                'tanggal_siap' => $tanggalSiap,
                'pasien_id' => $request->filled('pasien_id') ? $request->pasien_id : null,
                'nama_pasien_manual' => $request->filled('pasien_id') ? null : $request->pasien_name,
                'dokter_id' => $request->filled('dokter_id') ? $request->dokter_id : null,
                'dokter_manual' => $request->filled('dokter_manual') ? $request->dokter_manual : null,
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'total' => $request->total,
                'diskon' => $request->diskon,
                'bayar' => $request->bayar,
                'kekurangan' => $kekurangan,
                'status' => $status,
                'transaction_status' => $transactionStatus, // Status transaksi (Normal/Naik Kelas)
                'bpjs_default_price' => $bpjsDefaultPrice,
                'total_additional_cost' => $totalAdditionalCost,
                'pasien_service_type' => $pasienServiceType,
                'status_pengerjaan' => self::WORK_STATUS_MENUNGGU_PENGERJAAN,
                'waktu_sudah_diambil' => null,
                'created_at' => $transactionDateTime,
                'updated_at' => $transactionDateTime,
            ];

            if ($hasBpjsManualAdditionalColumn) {
                $penjualanData['bpjs_manual_additional_cost'] = $bpjsManualAdditionalCost;
            }

            if ($hasMetodePembayaranColumn) {
                $penjualanData['metode_pembayaran'] = strtolower((string) $request->metode_pembayaran);
            }

            if ($hasBankTransferColumn) {
                $penjualanData['bank_transfer'] = strtolower((string) $request->metode_pembayaran) === 'transfer'
                    ? strtoupper((string) $request->bank_transfer)
                    : null;
            }

            if ($hasJenisTransaksiColumn) {
                $penjualanData['jenis_transaksi'] = $jenisTransaksi ?? 'Stock';
            }

            // Handle file upload
            if ($request->hasFile('photo_bpjs')) {
                $path = $request->file('photo_bpjs')->store('photos_bpjs', 'public');
                $penjualanData['photo_bpjs'] = $path;
            } elseif ($request->filled('photo_bpjs_webcam')) {
                $path = $this->storeBase64ImageToPublicDisk((string) $request->photo_bpjs_webcam, 'photos_bpjs');
                if (!empty($path)) {
                    $penjualanData['photo_bpjs'] = $path;
                }
            }

            // Handle signature for BPJS patients
            if ($request->filled('signature_bpjs') && $pasien && in_array($pasien->service_type, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                $penjualanData['signature_bpjs'] = $request->signature_bpjs;
                $penjualanData['signature_date'] = now();
            }

            $penjualan = Penjualan::create($penjualanData);
            
            // Debug logging untuk memastikan data tersimpan
            \Log::info('Penjualan Created with BPJS Data:', [
                'penjualan_id' => $penjualan->id,
                'bpjs_default_price' => $penjualan->bpjs_default_price,
                'pasien_service_type' => $penjualan->pasien_service_type,
                'total_additional_cost' => $penjualan->total_additional_cost,
                'transaction_status' => $penjualan->transaction_status
            ]);

            $containsCleanerItem = false;

            foreach ($items as $itemData) {
                $itemModel = null;
                $price = $itemData['price']; // Default price dari frontend
                $additionalCost = 0;
                $normalUnitPrice = $itemData['price'] ?? 0;
                
                if ($itemData['type'] === 'frame') {
                    $itemModel = \App\Models\Frame::find($itemData['id']);
                    if ($itemModel) {
                        $normalUnitPrice = $itemModel->harga_jual_frame ?? $normalUnitPrice;
                    }
                    // Jika ada pasien dengan service_type BPJS, gunakan pricing service
                    if ($itemModel && $pasien && $this->isBpjsServiceType($pasien->service_type)) {
                        $pricing = $this->bpjsPricingService->calculateFramePrice($pasien, $itemModel);
                        $price = $pricing['price'];
                        $additionalCost = $pricing['additional_cost'];
                        
                        // Update status transaksi jika ada naik kelas
                        if ($pricing['status'] === 'Naik Kelas') {
                            $transactionStatus = 'Naik Kelas';
                        }
                        
                        // Akumulasi total biaya tambahan
                        $totalAdditionalCost += $additionalCost * $itemData['quantity'];
                    }
                } elseif ($itemData['type'] === 'lensa') {
                    $itemModel = \App\Models\Lensa::find($itemData['id']);
                    if ($itemModel) {
                        $normalUnitPrice = $itemModel->harga_jual_lensa ?? $normalUnitPrice;
                    }
                } elseif ($itemData['type'] === 'lensa_gosok') {
                    $kodeLensaGosok = 'GSK-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 4));
                    $normalUnitPrice = $itemData['price'] ?? 0;

                    $lensaData = [
                        'kode_lensa' => $kodeLensaGosok,
                        'merk_lensa' => $itemData['merk'] ?? 'Lensa Gosok',
                        'type' => $itemData['lensaType'] ?? null,
                        'index' => $itemData['index'] ?? null,
                        'coating' => $itemData['coating'] ?? null,
                        'cly' => $itemData['cly'] ?? null,
                        'axis' => $itemData['axis'] ?? null,
                        'add' => $itemData['add'] ?? null,
                        'harga_beli_lensa' => 0,
                        'harga_jual_lensa' => $itemData['price'] ?? 0,
                        'stok' => 0,
                        'is_custom_order' => true,
                        'sales_id' => null,
                        'branch_id' => $branch_id,
                    ];

                    $itemModel = \App\Models\Lensa::create(
                        $this->filterDataByExistingColumns('lensa', $lensaData)
                    );
                } elseif ($itemData['type'] === 'aksesoris') {
                    $itemModel = Aksesoris::find($itemData['id']);
                    if ($itemModel) {
                        $normalUnitPrice = $itemModel->harga_jual ?? $normalUnitPrice;
                        $containsCleanerItem = $containsCleanerItem || str_contains(strtolower($itemModel->nama_produk ?? ''), 'cleaner');
                    }
                }

                if ($pasien && $this->isBpjsServiceType($pasien->service_type ?? null)) {
                    $quantity = (int) ($itemData['quantity'] ?? 0);
                    if ($itemData['type'] === 'frame') {
                        $bpjsFrameSaleTotal += ($normalUnitPrice * $quantity);
                    }
                    if (in_array($itemData['type'], ['lensa', 'lensa_gosok'])) {
                        $bpjsLensSaleTotal += ($normalUnitPrice * $quantity);
                    }
                }

                $totalHargaJualProduk = ($totalHargaJualProduk ?? 0) + ($normalUnitPrice * ($itemData['quantity'] ?? 0));

                if ($itemModel) {
                    $detailSubtotal = $price * $itemData['quantity'];
                    $detail = $penjualan->details()->create([
                        'itemable_id' => $itemModel->id,
                        'itemable_type' => get_class($itemModel),
                        'quantity' => $itemData['quantity'],
                        'price' => $price,
                        'subtotal' => $detailSubtotal,
                        'additional_cost' => $additionalCost, // Simpan biaya tambahan
                    ]);

                    $calculatedTotal += $detailSubtotal;

                    if ($pasien && $this->isBpjsServiceType($pasien->service_type ?? null) && ($itemData['type'] ?? null) === 'aksesoris') {
                        $bpjsAksesorisSaleTotal += $detailSubtotal;
                    }

                    if (($itemData['type'] ?? null) === 'frame' && $firstFrameDetailId === null) {
                        $firstFrameDetailId = $detail->id;
                    }

                    // Update stok untuk item inventori (bukan lensa gosok manual/custom)
                    if ($itemData['type'] !== 'lensa_gosok') {
                        $itemModel->decrement('stok', $itemData['quantity']);
                    }
                }
            }

            $isUmumTransaction = !$pasien || !$this->isBpjsServiceType($pasien->service_type ?? null);

            if ($isUmumTransaction && !$containsCleanerItem) {
                $cleanerItem = $this->findFreeCleanerAksesoris($branch_id);

                if ($cleanerItem) {
                    $penjualan->details()->create([
                        'itemable_id' => $cleanerItem->id,
                        'itemable_type' => get_class($cleanerItem),
                        'quantity' => 1,
                        'price' => 0,
                        'subtotal' => 0,
                        'additional_cost' => 0,
                    ]);

                    $cleanerItem->decrement('stok', 1);

                    Log::info('Free cleaner added to umum transaction.', [
                        'penjualan_id' => $penjualan->id,
                        'aksesoris_id' => $cleanerItem->id,
                        'branch_id' => $branch_id,
                    ]);
                } else {
                    Log::warning('Cleaner aksesoris not found or out of stock for umum transaction.', [
                        'penjualan_id' => $penjualan->id,
                        'branch_id' => $branch_id,
                    ]);
                }
            }

            // Untuk BPJS, status naik kelas ditentukan dari hasil kalkulasi frame.
            // Jangan override dengan total seluruh produk agar lensa/aksesoris tidak salah memicu naik kelas.
            if ($pasien && $this->isBpjsServiceType($pasien->service_type)) {
                if ($totalAdditionalCost > 0) {
                    // Naik kelas: biaya tambahan = (harga frame + harga lensa) - default BPJS.
                    $totalAdditionalCost = max(0, ($bpjsFrameSaleTotal + $bpjsLensSaleTotal) - $bpjsDefaultPrice);

                    // Simpan biaya tambahan total pada satu baris frame agar total detail tetap konsisten.
                    $penjualan->details()->update(['additional_cost' => 0]);
                    if ($firstFrameDetailId) {
                        $penjualan->details()->where('id', $firstFrameDetailId)->update(['additional_cost' => $totalAdditionalCost]);
                    }
                }

                \Log::info('BPJS Additional Cost (Store):', [
                    'penjualan_id' => $penjualan->id,
                    'service_type' => $pasien->service_type,
                    'bpjs_default_price' => $bpjsDefaultPrice,
                    'total_harga_jual_produk' => $totalHargaJualProduk ?? 0,
                    'total_additional_cost' => $totalAdditionalCost,
                    'transaction_status' => $transactionStatus,
                    'bpjs_frame_sale_total' => $bpjsFrameSaleTotal,
                    'bpjs_lens_sale_total' => $bpjsLensSaleTotal,
                ]);

                $transactionStatus = $this->resolveBpjsTransactionStatus($totalAdditionalCost, $bpjsManualAdditionalCost);
            }

            $diskon = max(0, (float) $request->diskon);
            $discountOnAdditionalCost = min($diskon, $totalAdditionalCost);
            $totalAdditionalCost = max(0, $totalAdditionalCost - $discountOnAdditionalCost);
            $remainingDiscount = max(0, $diskon - $discountOnAdditionalCost);
            $discountOnManualCost = min($remainingDiscount, $bpjsManualAdditionalCost);
            $bpjsManualAdditionalCost = max(0, $bpjsManualAdditionalCost - $discountOnManualCost);
            $remainingDiscount = max(0, $remainingDiscount - $discountOnManualCost);
            if ($pasien && $this->isBpjsServiceType($pasien->service_type ?? null)) {
                $penjualan->details()->update(['additional_cost' => 0]);
                if ($firstFrameDetailId && $totalAdditionalCost > 0) {
                    $penjualan->details()->where('id', $firstFrameDetailId)->update(['additional_cost' => $totalAdditionalCost]);
                }
            }
            $transactionStatus = $this->resolveBpjsTransactionStatus($totalAdditionalCost, $bpjsManualAdditionalCost);
            $finalTotal = ($pasien && $this->isBpjsServiceType($pasien->service_type ?? null))
                ? $this->calculateBpjsPatientPayableTotal($totalAdditionalCost + $bpjsManualAdditionalCost, $bpjsAksesorisSaleTotal)
                : max(0, $calculatedTotal - $diskon);
            $bayar = max(0, (float) $request->bayar);
            $kekurangan = $finalTotal - $bayar;
            $status = $kekurangan <= 0 ? 'Lunas' : 'Belum Lunas';
            
            // Update status transaksi dan informasi BPJS jika ada perubahan
            $updateData = [
                'total' => $finalTotal,
                'diskon' => $diskon,
                'bayar' => $bayar,
                'kekurangan' => $kekurangan,
                'status' => $status,
                'transaction_status' => $transactionStatus,
                'total_additional_cost' => $totalAdditionalCost + $bpjsManualAdditionalCost
            ];
            if ($hasBpjsManualAdditionalColumn) {
                $updateData['bpjs_manual_additional_cost'] = $bpjsManualAdditionalCost;
            }
            $penjualan->update($updateData);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'redirect_url' => route('penjualan.show', $penjualan->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        $penjualan = Penjualan::with('details.itemable', 'user', 'branch', 'pasien', 'dokter')->findOrFail($id);
        return view('penjualan.show', compact('penjualan'));
    }

    public function bpjsPhoto($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        if (empty($penjualan->photo_bpjs)) {
            abort(404, 'Foto bukti BPJS tidak tersedia.');
        }

        $relativePath = ltrim($penjualan->photo_bpjs, '/');
        if (strpos($relativePath, 'storage/') === 0) {
            $relativePath = substr($relativePath, strlen('storage/'));
        }

        // Prioritas 1: disk public (storage/app/public/*)
        if (Storage::disk('public')->exists($relativePath)) {
            return response()->file(Storage::disk('public')->path($relativePath));
        }

        // Prioritas 2: direct path di storage/app/* (fallback data lama)
        $legacyStoragePath = storage_path('app/' . $relativePath);
        if (file_exists($legacyStoragePath)) {
            return response()->file($legacyStoragePath);
        }

        // Prioritas 3: direct path di public/* (fallback paling akhir)
        $publicPath = public_path($relativePath);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }

        abort(404, 'File foto bukti BPJS tidak ditemukan di storage.');
    }

    public function bpjsPhotoUpdateIndex(Request $request)
    {
        $user = auth()->user();

        $isScanMode = (bool) $request->boolean('scan_mode');
        $branchName = Str::lower((string) optional($user->branch)->name ?? '');
        $isKasirOptikMelati1 = $user->isKasir()
            && (Str::contains($branchName, 'optik melati cabang 1') || Str::contains($branchName, 'optik melati 1'));

        if ($isScanMode && $user->isKasir() && !$isKasirOptikMelati1) {
            abort(403, 'Menu scan foto BPJS hanya untuk kasir Optik Melati 1.');
        }

        $query = Penjualan::query()
            ->with(['pasien', 'branch', 'user'])
            ->where(function ($q) {
                $q->whereIn('pasien_service_type', self::BPJS_SERVICE_TYPES)
                    ->orWhereHas('pasien', function ($pasienQuery) {
                        $pasienQuery->whereIn('service_type', self::BPJS_SERVICE_TYPES);
                    });
            })
            ->orderByDesc('id');

        if ($isScanMode) {
            $query->where('status_pengerjaan', self::WORK_STATUS_SUDAH_DI_AMBIL);
        }

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->q);
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('kode_penjualan', 'like', "{$keyword}%")
                    ->orWhere('nama_pasien_manual', 'like', "{$keyword}%")
                    ->orWhere('pasien_id', 'like', "{$keyword}%")
                    ->orWhereHas('pasien', function ($pasienQuery) use ($keyword) {
                        $pasienQuery->where('nama_pasien', 'like', "{$keyword}%")
                            ->orWhere('id_pasien', 'like', "{$keyword}%")
                            ->orWhere('no_bpjs', 'like', "{$keyword}%");
                    });
            });
        }

        $penjualans = $query->paginate(20)->appends($request->query());

        return view('penjualan.update_bpjs_photo', compact('penjualans', 'isScanMode', 'isKasirOptikMelati1'));
    }

    public function bpjsPhotoUpdateStore(Request $request, $id)
    {
        $user = auth()->user();
        $penjualan = Penjualan::with('pasien')->findOrFail($id);

        if (!$user->isSuperAdmin() && !$user->isAdmin() && (int) $penjualan->branch_id !== (int) $user->branch_id) {
            return back()->with('error', 'Anda tidak memiliki akses ke transaksi ini.');
        }

        $isBpjsTransaction = in_array((string) ($penjualan->pasien_service_type ?? ''), self::BPJS_SERVICE_TYPES, true)
            || in_array((string) ($penjualan->pasien->service_type ?? ''), self::BPJS_SERVICE_TYPES, true);

        if (!$isBpjsTransaction) {
            return back()->with('error', 'Transaksi ini bukan transaksi BPJS.');
        }

        $request->validate([
            'photo_bpjs' => 'nullable|image|max:3072',
            'photo_bpjs_webcam' => 'nullable|string',
        ]);

        if (!$request->hasFile('photo_bpjs') && !$request->filled('photo_bpjs_webcam')) {
            return back()->with('error', 'Silakan ambil/upload foto BPJS terlebih dahulu.');
        }

        try {
            $path = null;

            if ($request->hasFile('photo_bpjs')) {
                $path = $request->file('photo_bpjs')->store('photos_bpjs', 'public');
            } elseif ($request->filled('photo_bpjs_webcam')) {
                $path = $this->storeBase64ImageToPublicDisk((string) $request->photo_bpjs_webcam, 'photos_bpjs');
            }

            if (empty($path)) {
                return back()->with('error', 'Gagal menyimpan foto BPJS.');
            }

            $penjualan->update([
                'photo_bpjs' => $path,
            ]);

            Log::info('BPJS photo updated by cashier menu', [
                'penjualan_id' => $penjualan->id,
                'kode_penjualan' => $penjualan->kode_penjualan,
                'updated_by_user_id' => $user->id,
                'updated_by_user_name' => $user->name,
                'updated_by_user_role' => $user->role,
                'branch_id' => $penjualan->branch_id,
            ]);

            return back()->with('success', 'Foto BPJS berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Failed to update BPJS photo from cashier menu', [
                'penjualan_id' => $penjualan->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal memperbarui foto BPJS: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = auth()->user();
        $canEditTransactionDate = $user && ($user->isAdmin() || $user->isSuperAdmin());

        $penjualan = Penjualan::with([
            'details.itemable', 
            'user', 
            'branch', 
            'pasien.prescriptions' => function($query) {
                $query->orderBy('tanggal', 'desc')->limit(1);
            }, 
            'dokter'
        ])->findOrFail($id);
        
        $dokters = \App\Models\Dokter::all();
        $pasiens = \App\Models\Pasien::all();

        $branchId = $penjualan->branch_id;
        $frames = \App\Models\Frame::where('branch_id', $branchId)->get();
        $lenses = \App\Models\Lensa::where('branch_id', $branchId)->where('stok', '>', 0)->get();
        $aksesoris = \App\Models\Aksesoris::where('branch_id', $branchId)->where('stok', '>', 0)->orderBy('nama_produk')->get();
        
        // Get latest prescription for the patient
        $latestPrescription = null;
        if ($penjualan->pasien) {
            $latestPrescription = $penjualan->pasien->prescriptions->first();
        }
        
        // Debug logging
        \Log::info('Edit Penjualan Data:', [
            'penjualan_id' => $penjualan->id,
            'details_count' => $penjualan->details ? $penjualan->details->count() : 0,
            'pasien_id' => $penjualan->pasien_id,
            'latest_prescription' => $latestPrescription ? $latestPrescription->toArray() : null,
            'details_data' => $penjualan->details ? $penjualan->details->toArray() : null
        ]);
        
        return view('penjualan.edit', compact('penjualan', 'dokters', 'pasiens', 'latestPrescription', 'frames', 'lenses', 'aksesoris', 'canEditTransactionDate'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $canEditTransactionDate = $user && ($user->isAdmin() || $user->isSuperAdmin());

        $hasMetodePembayaranColumn = $this->hasTableColumn('penjualan', 'metode_pembayaran');
        $hasBankTransferColumn = $this->hasTableColumn('penjualan', 'bank_transfer');
        $hasBpjsManualAdditionalColumn = $this->hasTableColumn('penjualan', 'bpjs_manual_additional_cost');

        $today = now()->toDateString();

        $request->validate([
            'pasien_id' => 'required',
            'tanggal' => $canEditTransactionDate ? 'required|date|before_or_equal:today' : 'required|date',
            'items' => 'required|json',
            'diskon' => 'required|numeric|min:0',
            'bayar' => 'required|numeric|min:0',
            'bpjs_manual_additional_cost' => 'nullable|numeric|min:0',
            'status_pengerjaan' => 'required|in:' . implode(',', self::WORK_STATUS_ALLOWED),
            'photo_bpjs' => 'nullable|image|max:3072',
            'signature_bpjs' => 'nullable|string',
        ]);

        if ($hasMetodePembayaranColumn) {
            $request->validate([
                'metode_pembayaran' => 'required|in:cash,transfer,qris',
            ]);
        }

        if ($hasBankTransferColumn) {
            $request->validate([
                'bank_transfer' => 'nullable|required_if:metode_pembayaran,transfer|in:BNI,BRI,MANDIRI,BSI,BCA',
            ]);
        }

        $hasJenisTransaksiColumn = Schema::hasColumn('penjualan', 'jenis_transaksi');

        if ($hasJenisTransaksiColumn) {
            $request->validate([
                'jenis_transaksi' => 'required|in:Stock,Gosok',
            ]);
        }

        try {
            DB::beginTransaction();

            $penjualan = Penjualan::with('details.itemable')->findOrFail($id);

            $selectedDate = $canEditTransactionDate
                ? Carbon::parse((string) $request->tanggal)->toDateString()
                : optional($penjualan->created_at)->toDateString();

            if (!$canEditTransactionDate && $selectedDate !== (string) $request->tanggal) {
                throw new \Exception('Tanggal transaksi tidak dapat diubah untuk role ini.');
            }

            $currentCreatedAt = $penjualan->created_at ? Carbon::parse($penjualan->created_at) : now();
            $newCreatedAt = Carbon::parse($selectedDate . ' ' . $currentCreatedAt->format('H:i:s'));

            $items = json_decode($request->items, true);

            if (!is_array($items) || empty($items)) {
                throw new \Exception('Keranjang transaksi tidak valid atau kosong.');
            }

            $pasien = Pasien::find($request->pasien_id);
            $isBpjs = $pasien && $this->isBpjsServiceType($pasien->service_type);
            $bpjsDefaultPrice = $isBpjs ? $this->bpjsPricingService->getDefaultPrice($pasien->service_type) : 0;
            $transactionStatus = 'Normal';
            $totalAdditionalCost = 0;
            $bpjsManualAdditionalCost = $isBpjs
                ? max(0, (float) $request->input('bpjs_manual_additional_cost', (float) ($penjualan->bpjs_manual_additional_cost ?? 0)))
                : 0;
            $calculatedTotal = 0;
            $bpjsFrameSaleTotal = 0;
            $bpjsLensSaleTotal = 0;
            $bpjsAksesorisSaleTotal = 0;
            $firstFrameDetailId = null;
            $containsCleanerItem = false;

            // Kembalikan stok item lama sebelum mengganti detail transaksi.
            foreach ($penjualan->details as $oldDetail) {
                if (!$oldDetail->itemable) {
                    continue;
                }

                $isCustomLensa = $oldDetail->itemable_type === Lensa::class
                    && $this->hasTableColumn('lensa', 'is_custom_order')
                    && (bool) ($oldDetail->itemable->is_custom_order ?? false);

                if (!$isCustomLensa) {
                    $oldDetail->itemable->increment('stok', (int) $oldDetail->quantity);
                }
            }

            // Hapus detail lama, lalu isi ulang dari keranjang baru.
            $penjualan->details()->delete();

            foreach ($items as $itemData) {
                $itemType = $itemData['type'] ?? null;
                $itemId = $itemData['id'] ?? null;
                $quantity = max(1, (int) ($itemData['quantity'] ?? 1));

                if (!$itemType || !$itemId) {
                    continue;
                }

                $itemModel = null;
                $price = (float) ($itemData['price'] ?? 0);
                $additionalCost = 0;
                $normalUnitPrice = $price;

                if ($itemType === 'frame') {
                    $itemModel = Frame::find($itemId);
                    if ($itemModel && $isBpjs) {
                        $normalUnitPrice = (float) ($itemModel->harga_jual_frame ?? $price);
                        $pricing = $this->bpjsPricingService->calculateFramePrice($pasien, $itemModel);
                        $price = (float) $pricing['price'];
                        $additionalCost = (float) $pricing['additional_cost'];
                        $totalAdditionalCost += ($additionalCost * $quantity);

                        if ($pricing['status'] === 'Naik Kelas') {
                            $transactionStatus = 'Naik Kelas';
                        }
                    } elseif ($itemModel) {
                        $price = (float) ($itemModel->harga_jual_frame ?? $price);
                        $normalUnitPrice = $price;
                    }
                } elseif ($itemType === 'lensa') {
                    $itemModel = Lensa::find($itemId);
                    if ($itemModel) {
                        $price = (float) ($itemModel->harga_jual_lensa ?? $price);
                        $normalUnitPrice = $price;
                    }
                } elseif ($itemType === 'lensa_gosok') {
                    $kodeLensaGosok = 'GSK-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 4));

                    $normalUnitPrice = $price;

                    $lensaData = [
                        'kode_lensa' => $kodeLensaGosok,
                        'merk_lensa' => $itemData['merk'] ?? ($itemData['name'] ?? 'Lensa Gosok'),
                        'type' => $itemData['lensaType'] ?? null,
                        'index' => $itemData['index'] ?? null,
                        'coating' => $itemData['coating'] ?? null,
                        'cly' => $itemData['cly'] ?? null,
                        'axis' => $itemData['axis'] ?? null,
                        'add' => $itemData['add'] ?? null,
                        'harga_beli_lensa' => 0,
                        'harga_jual_lensa' => $price,
                        'stok' => 0,
                        'is_custom_order' => true,
                        'sales_id' => null,
                        'branch_id' => $penjualan->branch_id,
                    ];

                    $itemModel = Lensa::create(
                        $this->filterDataByExistingColumns('lensa', $lensaData)
                    );
                } elseif ($itemType === 'aksesoris') {
                    $itemModel = Aksesoris::find($itemId);
                    if ($itemModel) {
                        $price = (float) ($itemModel->harga_jual ?? $price);
                        $normalUnitPrice = $price;
                        $containsCleanerItem = $containsCleanerItem || str_contains(strtolower($itemModel->nama_produk ?? ''), 'cleaner');
                    }
                }

                if (!$itemModel) {
                    continue;
                }

                $subtotal = $price * $quantity;
                $calculatedTotal += $subtotal;

                $detail = $penjualan->details()->create([
                    'itemable_id' => $itemModel->id,
                    'itemable_type' => get_class($itemModel),
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'additional_cost' => $additionalCost,
                ]);

                if ($isBpjs) {
                    if ($itemType === 'frame') {
                        $bpjsFrameSaleTotal += ($normalUnitPrice * $quantity);
                        if ($firstFrameDetailId === null) {
                            $firstFrameDetailId = $detail->id;
                        }
                    } elseif (in_array($itemType, ['lensa', 'lensa_gosok'], true)) {
                        $bpjsLensSaleTotal += ($normalUnitPrice * $quantity);
                    } elseif ($itemType === 'aksesoris') {
                        $bpjsAksesorisSaleTotal += ($normalUnitPrice * $quantity);
                    }
                }

                $isCustomLensa = $itemModel instanceof Lensa
                    && $this->hasTableColumn('lensa', 'is_custom_order')
                    && (bool) ($itemModel->is_custom_order ?? false);

                if (!$isCustomLensa) {
                    $itemModel->decrement('stok', $quantity);
                }
            }

            if (!($isBpjs) && !$containsCleanerItem) {
                $cleanerItem = $this->findFreeCleanerAksesoris((int) $penjualan->branch_id);
                if ($cleanerItem) {
                    $penjualan->details()->create([
                        'itemable_id' => $cleanerItem->id,
                        'itemable_type' => get_class($cleanerItem),
                        'quantity' => 1,
                        'price' => 0,
                        'subtotal' => 0,
                        'additional_cost' => 0,
                    ]);

                    $cleanerItem->decrement('stok', 1);
                }
            }

            $diskon = (float) $request->diskon;

            if ($isBpjs && $totalAdditionalCost > 0) {
                $totalAdditionalCost = max(0, ($bpjsFrameSaleTotal + $bpjsLensSaleTotal) - $bpjsDefaultPrice);
                $penjualan->details()->update(['additional_cost' => 0]);
                if ($firstFrameDetailId) {
                    $penjualan->details()->where('id', $firstFrameDetailId)->update(['additional_cost' => $totalAdditionalCost]);
                }
            }

            $discountOnAdditionalCost = min($diskon, $totalAdditionalCost);
            $totalAdditionalCost = max(0, $totalAdditionalCost - $discountOnAdditionalCost);
            $remainingDiscount = max(0, $diskon - $discountOnAdditionalCost);
            $discountOnManualCost = min($remainingDiscount, $bpjsManualAdditionalCost);
            $bpjsManualAdditionalCost = max(0, $bpjsManualAdditionalCost - $discountOnManualCost);
            $remainingDiscount = max(0, $remainingDiscount - $discountOnManualCost);
            if ($isBpjs) {
                $penjualan->details()->update(['additional_cost' => 0]);
                if ($firstFrameDetailId && $totalAdditionalCost > 0) {
                    $penjualan->details()->where('id', $firstFrameDetailId)->update(['additional_cost' => $totalAdditionalCost]);
                }
            }

            $finalTotal = $isBpjs
                ? $this->calculateBpjsPatientPayableTotal($totalAdditionalCost + $bpjsManualAdditionalCost, $bpjsAksesorisSaleTotal)
                : max(0, $calculatedTotal - $diskon);
            $bayar = (float) $request->bayar;
            $kekurangan = $finalTotal - $bayar;
            $statusPembayaran = $kekurangan <= 0 ? 'Lunas' : 'Belum Lunas';

            if ($isBpjs) {
                $transactionStatus = $this->resolveBpjsTransactionStatus($totalAdditionalCost, $bpjsManualAdditionalCost);
            } else {
                $totalAdditionalCost = 0;
                $bpjsManualAdditionalCost = 0;
                $transactionStatus = 'Normal';
                $bpjsDefaultPrice = 0;
            }
            
            // Update basic information
            $penjualan->tanggal = $selectedDate;
            $penjualan->created_at = $newCreatedAt;
            $penjualan->pasien_id = $request->pasien_id;
            $penjualan->dokter_id = $request->dokter_id ?: null;
            $penjualan->dokter_manual = $request->dokter_manual;
            $penjualan->tanggal_siap = $request->tanggal_siap;
            $penjualan->diskon = $diskon;
            $penjualan->total = $finalTotal;
            $penjualan->bayar = $bayar;
            $penjualan->status = $statusPembayaran;
            $penjualan->status_pengerjaan = $request->status_pengerjaan;
            $penjualan->kekurangan = $kekurangan;
            $penjualan->pasien_service_type = $isBpjs ? $pasien->service_type : null;
            $penjualan->bpjs_default_price = $bpjsDefaultPrice;
            $penjualan->total_additional_cost = $totalAdditionalCost + $bpjsManualAdditionalCost;
            if ($hasBpjsManualAdditionalColumn) {
                $penjualan->bpjs_manual_additional_cost = $bpjsManualAdditionalCost;
            }
            $penjualan->transaction_status = $transactionStatus;

            if ($hasMetodePembayaranColumn) {
                $penjualan->metode_pembayaran = strtolower((string) $request->metode_pembayaran);
            }

            if ($hasBankTransferColumn) {
                $penjualan->bank_transfer = strtolower((string) $request->metode_pembayaran) === 'transfer'
                    ? strtoupper((string) $request->bank_transfer)
                    : null;
            }

            if ($hasJenisTransaksiColumn) {
                $penjualan->jenis_transaksi = $request->jenis_transaksi;
            }

            // Update bukti BPJS jika diunggah ulang
            if ($request->hasFile('photo_bpjs')) {
                $path = $request->file('photo_bpjs')->store('photos_bpjs', 'public');
                $penjualan->photo_bpjs = $path;
            }

            // Update tanda tangan BPJS jika diisi dari form edit
            if ($request->filled('signature_bpjs')) {
                $penjualan->signature_bpjs = $request->signature_bpjs;
                $penjualan->signature_date = now();
            }
            
            $penjualan->save();

            DB::commit();

            return redirect()->route('penjualan.index')
                ->with('success', 'Transaksi berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Gagal mengupdate transaksi: ' . $e->getMessage());
        }
    }

    public function cetak($id)
    {
        $penjualan = Penjualan::with('details.itemable', 'user', 'branch', 'pasien', 'dokter')->findOrFail($id);
        return view('penjualan.cetak', compact('penjualan'));
    }

    public function cetakHalf($id)
    {
        $penjualan = Penjualan::with('details.itemable', 'user', 'branch', 'pasien', 'dokter')->findOrFail($id);
        return view('penjualan.cetak_half', compact('penjualan'));
    }

    public function cetakBarcodeWa($id)
    {
        $penjualan = Penjualan::findOrFail($id);
        return view('penjualan.cetak_barcode_wa', compact('penjualan'));
    }

    /**
     * Generate barcode image and return URL for WhatsApp sharing
     */
    public function generateBarcodeImage(Request $request, $id)
    {
        try {
            $penjualan = Penjualan::findOrFail($id);
            $user = auth()->user();

            if (!$user->isSuperAdmin() && !$user->isAdmin() && (int) $penjualan->branch_id !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke transaksi ini.'
                ], 403);
            }

            if (!$penjualan->barcode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode belum tersedia untuk transaksi ini.'
                ], 400);
            }

            // Ensure directory exists
            $dir = storage_path('app/wa_barcode');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            // Pasien diarahkan ke halaman profil perusahaan; scanner internal tetap bisa ambil barcode dari URL.
            $barcodeValue = (string) ($penjualan->barcode ?: $penjualan->kode_penjualan);

            if ($barcodeValue === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Data barcode transaksi tidak valid.'
                ], 400);
            }

            $qrPayload = route('public.company-profile.barcode', [
                'barcode' => $barcodeValue,
            ]);

            // Generate QR lokal terlebih dulu; jika backend server belum mendukung (mis. Imagick), fallback ke API.
            try {
                $qrCodeData = QrCode::format('png')
                    ->size(1024)
                    ->margin(4)
                    ->errorCorrection('H')
                    ->generate($qrPayload);
            } catch (\Throwable $qrError) {
                Log::warning('Local QR generation failed, fallback to API.', [
                    'penjualan_id' => $penjualan->id,
                    'error' => $qrError->getMessage(),
                ]);

                $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&ecc=H&qzone=4&format=png&data=' . urlencode($qrPayload);
                $apiResponse = Http::timeout(15)->get($apiUrl);

                if (!$apiResponse->successful()) {
                    throw new \Exception('Failed to generate QR code image from fallback API: ' . $apiResponse->status());
                }

                $qrCodeData = $apiResponse->body();
            }

            if (empty($qrCodeData)) {
                throw new \Exception('Failed to generate QR code image data');
            }

            // Store with unique filename
            $filename = 'barcode-' . $penjualan->id . '-' . now()->format('YmdHis') . '.png';
            $fullPath = storage_path('app/wa_barcode/' . $filename);
            
            // Save file
            if (!file_put_contents($fullPath, $qrCodeData)) {
                throw new \Exception('Failed to write barcode file to disk');
            }

            // Gunakan URL token berbasis filename agar tidak bergantung pada
            // APP_URL/proxy/APP_KEY VPS dan tidak kadaluarsa sebelum diambil.
            $imageUrl = route('penjualan.share-barcode-image', ['token' => $filename]);

            Log::info('Barcode generated successfully', [
                'penjualan_id' => $penjualan->id,
                'filename' => $filename,
                'url' => $imageUrl
            ]);

            $whatsapp = null;
            if ($request->boolean('auto_send')) {
                $rawPhone = (string) $request->input('phone', '');
                $normalizedPhone = WhatsAppHelper::normalizePhoneNumber($rawPhone);

                if (!$normalizedPhone) {
                    $whatsapp = [
                        'success' => false,
                        'channel' => 'none',
                        'message' => 'Nomor WhatsApp tidak valid untuk pengiriman otomatis.',
                    ];
                } else {
                    $pasienName = $penjualan->nama_pasien ?: 'Pelanggan';
                    $waMessage = "Terima kasih Bapak/Ibu {$pasienName} telah bertransaksi di Optik Melati. "
                        . "Berikut kami lampirkan link QR code transaksi Anda: {$imageUrl}. "
                        . "Pada saat pengambilan, Anda bisa menunjukkan QR code ini kepada kami.";

                    $gatewayResult = WhatsAppHelper::sendViaGateway($normalizedPhone, $waMessage);
                    if ($gatewayResult['success']) {
                        $whatsapp = [
                            'success' => true,
                            'channel' => 'gateway',
                            'message' => 'QR code berhasil dikirim otomatis ke WhatsApp pasien.',
                        ];
                    } else {
                        $manualLink = WhatsAppHelper::buildShareLink($normalizedPhone, $waMessage);
                        $whatsapp = [
                            'success' => true,
                            'channel' => 'wa_link',
                            'message' => 'Gateway WhatsApp tidak aktif/menolak request. Dialihkan ke WhatsApp Web/App.',
                            'open_link' => true,
                            'link' => $manualLink,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'image_url' => $imageUrl,
                'message' => 'Barcode berhasil dibuat.',
                'whatsapp' => $whatsapp,
            ]);
        } catch (\Exception $e) {
            Log::error('Generate barcode image error: ' . $e->getMessage(), [
                'penjualan_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat barcode gambar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add Optik Melati logo to center of QR code
     */
    private function addLogoToQRCode($qrCodeData)
    {
        // Create QR code image from data
        $qrImage = imagecreatefromstring($qrCodeData);
        if (!$qrImage) {
            throw new \Exception('Failed to create image from QR code data');
        }

        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);

        // Load logo
        $logoPath = public_path('image/optik-melati.png');
        if (!file_exists($logoPath)) {
            imagedestroy($qrImage);
            throw new \Exception('Logo file not found: ' . $logoPath);
        }

        $logoImage = imagecreatefrompng($logoPath);
        if (!$logoImage) {
            imagedestroy($qrImage);
            throw new \Exception('Failed to load logo image');
        }

        $logoWidth = imagesx($logoImage);
        $logoHeight = imagesy($logoImage);

        // Calculate logo size (25% of QR code size, max 80px)
        $maxLogoSize = min(($qrWidth * 0.25), 80);
        $logoScaleRatio = $maxLogoSize / max($logoWidth, $logoHeight);
        
        $newLogoWidth = (int)($logoWidth * $logoScaleRatio);
        $newLogoHeight = (int)($logoHeight * $logoScaleRatio);

        // Create white background for logo (so it's visible over QR code)
        $bgSize = $newLogoWidth + 10;
        $bgImage = imagecreatetruecolor($bgSize, $bgSize);
        $white = imagecolorallocate($bgImage, 255, 255, 255);
        imagefilledrectangle($bgImage, 0, 0, $bgSize, $bgSize, $white);

        // Resize and copy logo onto white background
        imagecopyresampled($bgImage, $logoImage, 5, 5, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);

        // Calculate position (center of QR code)
        $x = ($qrWidth - $bgSize) / 2;
        $y = ($qrHeight - $bgSize) / 2;

        // Copy background with logo onto QR code (center position)
        imagecopy($qrImage, $bgImage, (int)$x, (int)$y, 0, 0, $bgSize, $bgSize);

        // Convert to PNG
        ob_start();
        imagepng($qrImage);
        $result = ob_get_clean();

        // Clean up
        imagedestroy($qrImage);
        imagedestroy($logoImage);
        imagedestroy($bgImage);

        return $result;
    }

    /**
     * Share barcode image via legacy token or signed filename URL
     */
    public function shareBarcodeImage(Request $request, $token)
    {
        $filename = null;

        // Backward compatibility: link lama berbasis token UUID + cache.
        if (preg_match('/^[a-f0-9\-]{36}$/', (string) $token)) {
            $cacheKey = 'barcode_' . $token;
            $filename = Cache::get($cacheKey);

            if (!$filename) {
                abort(404, 'Link telah expired atau tidak ditemukan.');
            }
        } else {
            // Link baru berbasis filename: divalidasi di bawah dan tidak memakai
            // signature agar tetap dapat dibuka dari WhatsApp di VPS.
            $filename = (string) $token;
        }

        // Validate filename format
        if (!preg_match('/^barcode-\d+-\d{14}\.png$/', $filename)) {
            abort(404, 'Invalid filename.');
        }

        preg_match('/^barcode-(\d+)-/', $filename, $matches);
        $penjualan = Penjualan::select('id', 'status_pengerjaan')->find((int) ($matches[1] ?? 0));
        if (!$penjualan) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        if ($penjualan->status_pengerjaan === self::WORK_STATUS_SUDAH_DI_AMBIL) {
            abort(410, 'QR code sudah tidak berlaku karena transaksi telah diambil.');
        }

        $path = storage_path('app/wa_barcode/' . $filename);
        if (!file_exists($path)) {
            abort(404, 'Barcode gambar tidak ditemukan.');
        }

        // Display image in browser (not download)
        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=86400, no-transform'
        ]);
    }

    public function cetakHalfShare($id)
    {
        $penjualan = Penjualan::with('details.itemable', 'user', 'branch', 'pasien', 'dokter')->findOrFail($id);
        return view('penjualan.cetak_half', compact('penjualan'));
    }

    public function uploadWhatsappReceiptImage(Request $request, $id)
    {
        $penjualan = Penjualan::findOrFail($id);
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && (int) $penjualan->branch_id !== (int) $user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke transaksi ini.'
            ], 403);
        }

        $request->validate([
            'image_data' => 'required|string',
        ]);

        $imageData = $request->input('image_data');
        if (!preg_match('/^data:image\/png;base64,/', $imageData)) {
            return response()->json([
                'success' => false,
                'message' => 'Format gambar tidak valid. Harus PNG base64.'
            ], 422);
        }

        $base64 = substr($imageData, strpos($imageData, ',') + 1);
        $binary = base64_decode($base64, true);

        if ($binary === false) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca data gambar.'
            ], 422);
        }

        if (strlen($binary) > (5 * 1024 * 1024)) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran gambar terlalu besar (maksimal 5MB).'
            ], 422);
        }

        $filename = 'nota-' . $penjualan->id . '-' . now()->format('YmdHis') . '-' . Str::random(8) . '.png';
        $relativePath = 'wa_nota/' . $filename;
        Storage::disk('local')->put($relativePath, $binary);

        $signedRelativePath = URL::temporarySignedRoute(
            'penjualan.share-nota-image',
            now()->addDays(15),
            ['file' => $filename],
            false
        );

        $signedImageUrl = url($signedRelativePath);

        return response()->json([
            'success' => true,
            'image_url' => $signedImageUrl,
            'message' => 'Gambar nota berhasil dibuat.'
        ]);
    }

    public function shareNotaImage(Request $request, $file)
    {
        if (!URL::hasValidSignature($request, false)) {
            abort(403, 'Invalid signature.');
        }

        if (!preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
            abort(404);
        }

        $path = storage_path('app/wa_nota/' . $file);
        if (!file_exists($path)) {
            abort(404, 'Gambar nota tidak ditemukan.');
        }

        $downloadName = 'nota-OPTIKMELATI.png';
        if (preg_match('/^nota-(\d+)-/', $file, $matches)) {
            $penjualanId = (int) $matches[1];
            $penjualan = Penjualan::select('kode_penjualan')->find($penjualanId);
            if ($penjualan && !empty($penjualan->kode_penjualan)) {
                $safeKode = preg_replace('/[^A-Za-z0-9\-_]/', '', $penjualan->kode_penjualan);
                $downloadName = 'nota-OPTIKMELATI-' . $safeKode . '.png';
            }
        }

        return response()->download($path, $downloadName, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function lunas($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        if ($penjualan->status == 'Lunas') {
            return response()->json(['message' => 'Transaksi ini sudah lunas.'], 422);
        }

        $penjualan->bayar = $penjualan->total;
        $penjualan->kekurangan = 0;
        $penjualan->status = 'Lunas';
        $penjualan->save();

        return response()->json(['message' => 'Transaksi berhasil dilunasi.']);
    }

    public function diambil($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        // Cek apakah transaksi sudah lunas
        if ($penjualan->status !== 'Lunas') {
            return response()->json(['message' => 'Transaksi belum lunas! Mohon selesaikan pembayaran terlebih dahulu.'], 422);
        }

        $penjualan->status_pengerjaan = self::WORK_STATUS_SUDAH_DI_AMBIL;
        $penjualan->waktu_sudah_diambil = now(); // Catat waktu diambil
        $penjualan->save();

        return response()->json(['message' => 'Status berhasil diubah menjadi Sudah Di Ambil.']);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        
        // Hanya super admin dan admin yang bisa menghapus transaksi
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            Log::warning('Unauthorized delete attempt on penjualan', [
                'penjualan_id' => $id,
                'user_id' => $user->id ?? null,
                'user_role' => $user->role ?? null,
            ]);
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus transaksi.'], 403);
        }

        $penjualan = Penjualan::with('details.itemable')->findOrFail($id);

        try {
            DB::beginTransaction();
            $restoredItems = [];

            // Kembalikan stok item sebelum detail transaksi dihapus.
            foreach ($penjualan->details as $detail) {
                if (!$detail->itemable) {
                    continue;
                }

                $isCustomLensa = $detail->itemable_type === Lensa::class
                    && $this->hasTableColumn('lensa', 'is_custom_order')
                    && (bool) ($detail->itemable->is_custom_order ?? false);

                if (!$isCustomLensa) {
                    $detail->itemable->increment('stok', (int) $detail->quantity);
                    $restoredItems[] = [
                        'detail_id' => $detail->id,
                        'itemable_type' => class_basename($detail->itemable_type),
                        'itemable_id' => $detail->itemable_id,
                        'qty_restored' => (int) $detail->quantity,
                    ];
                }
            }

            // Hapus detail transaksi terlebih dahulu
            $penjualan->details()->delete();
            
            // Hapus transaksi
            $penjualan->delete();

            DB::commit();

            Log::info('Penjualan deleted and stock restored', [
                'penjualan_id' => $penjualan->id,
                'kode_penjualan' => $penjualan->kode_penjualan,
                'deleted_by_user_id' => $user->id,
                'deleted_by_user_name' => $user->name,
                'deleted_by_user_role' => $user->role,
                'branch_id' => $penjualan->branch_id,
                'restored_items' => $restoredItems,
            ]);

            return response()->json(['message' => 'Transaksi berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete penjualan', [
                'penjualan_id' => $penjualan->id ?? $id,
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Gagal menghapus transaksi. Silakan coba lagi.'], 500);
        }
    }

    /**
     * Replace damaged lensa with new lensa from stock
     */
    public function replaceLensaDamaged(Request $request, $penjualanId)
    {
        $user = auth()->user();
        
        $request->validate([
            'detail_id' => 'required|integer|exists:penjualan_detail,id',
            'new_lensa_id' => 'required|integer|exists:lensa,id',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $penjualan = Penjualan::findOrFail($penjualanId);
            
            // Verify user access
            if (!$user->isSuperAdmin() && !$user->isAdmin() && (int) $penjualan->branch_id !== (int) $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke transaksi ini.'
                ], 403);
            }

            // Get old lensa detail
            $detail = PenjualanDetail::findOrFail($request->detail_id);
            
            // Verify it's a lensa
            if ($detail->itemable_type !== 'App\\Models\\Lensa') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item bukan lensa.'
                ], 422);
            }

            // Get old lensa
            $oldLensa = Lensa::findOrFail($detail->itemable_id);
            
            // Get new lensa
            $newLensa = Lensa::findOrFail($request->new_lensa_id);
            
            // Check new lensa stock
            if ($newLensa->stok < $detail->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok lensa pengganti tidak cukup. Tersedia: ' . $newLensa->stok . ' unit.'
                ], 422);
            }

            // Restore old lensa stock
            $oldLensa->increment('stok', $detail->quantity);
            
            // Reduce new lensa stock
            $newLensa->decrement('stok', $detail->quantity);
            
            // Update detail with new lensa
            $detail->itemable_id = $newLensa->id;
            $detail->price = $newLensa->harga_jual_lensa;
            $detail->subtotal = $newLensa->harga_jual_lensa * $detail->quantity;
            $detail->save();

            // Log the replacement
            Log::info('Lensa Damage Replacement', [
                'penjualan_id' => $penjualanId,
                'detail_id' => $detail->id,
                'old_lensa_id' => $oldLensa->id,
                'old_lensa_merk' => $oldLensa->merk_lensa,
                'new_lensa_id' => $newLensa->id,
                'new_lensa_merk' => $newLensa->merk_lensa,
                'quantity' => $detail->quantity,
                'reason' => $request->reason,
                'replaced_by_user_id' => $user->id,
                'replaced_by_user_name' => $user->name,
                'timestamp' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lensa berhasil diganti. Stok sudah diupdate.',
                'old_lensa' => [
                    'merk' => $oldLensa->merk_lensa,
                    'kode' => $oldLensa->kode_lensa,
                ],
                'new_lensa' => [
                    'merk' => $newLensa->merk_lensa,
                    'kode' => $newLensa->kode_lensa,
                    'harga' => $newLensa->harga_jual_lensa,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Replace lensa damaged error: ' . $e->getMessage(), [
                'penjualan_id' => $penjualanId,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengganti lensa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLensa()
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $branch_id = session('active_branch_id', $user->branch_id);
            $lensa = \App\Models\Lensa::where('branch_id', $branch_id)->get();
        } else {
            $lensa = \App\Models\Lensa::where('branch_id', $user->branch_id)->get();
        }
        return response()->json($lensa);
    }

    public function getFrame()
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $branch_id = session('active_branch_id', $user->branch_id);
            $frame = \App\Models\Frame::where('branch_id', $branch_id)->get();
        } else {
            $frame = \App\Models\Frame::where('branch_id', $user->branch_id)->get();
        }
        return response()->json($frame);
    }

    /**
     * Rekap omset harian kasir (total penjualan hari ini, jumlah transaksi)
     */
    public function omsetHarian(Request $request)
    {
        $user = auth()->user();
        $branch_id = $user->branch_id;
        
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $branch_id = session('active_branch_id', $user->branch_id);
        }
        
        $today = now()->toDateString();
        $omset = Penjualan::where('branch_id', $branch_id)
                          ->whereDate('created_at', $today)
                          ->sum('total');
        
        return response()->json(['omset' => $omset]);
    }

    /**
     * Calculate BPJS pricing for frame selection
     */
    public function calculateBpjsPrice(Request $request)
    {
        try {
            $request->validate([
                'pasien_id' => 'required|exists:pasien,id_pasien',
                'frame_id' => 'required|exists:frames,id'
            ]);

            $pasien = Pasien::findOrFail($request->pasien_id);
            $frame = Frame::findOrFail($request->frame_id);

            // Hanya proses jika pasien memiliki service_type BPJS
            if (!in_array($pasien->service_type, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak memiliki layanan BPJS'
                ]);
            }

            $pricing = $this->bpjsPricingService->calculateFramePrice($pasien, $frame);

            return response()->json([
                'success' => true,
                'data' => [
                    'pasien_service_type' => $pasien->service_type,
                    'status' => $pricing['status'],
                    'frame_type' => $frame->jenis_frame,
                    'original_price' => $frame->harga_jual_frame,
                    'calculated_price' => $pricing['price'],
                    'additional_cost' => $pricing['additional_cost'],
                    'reason' => $pricing['reason']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung harga: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix existing transactions that don't have BPJS default price set
     */
    public function fixBpjsPrices()
    {
        try {
            $transactions = Penjualan::whereNotNull('pasien_id')
                ->whereHas('pasien', function($query) {
                    $query->whereIn('service_type', ['BPJS I', 'BPJS II', 'BPJS III']);
                })
                ->where(function($query) {
                    $query->whereNull('bpjs_default_price')
                          ->orWhere('bpjs_default_price', 0);
                })
                ->with('pasien')
                ->get();

            $fixed = 0;
            foreach ($transactions as $transaction) {
                if ($transaction->pasien && in_array($transaction->pasien->service_type, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                    $bpjsDefaultPrice = $this->bpjsPricingService->getDefaultPrice($transaction->pasien->service_type);
                    
                    $transaction->update([
                        'bpjs_default_price' => $bpjsDefaultPrice,
                        'pasien_service_type' => $transaction->pasien->service_type
                    ]);
                    
                    $fixed++;
                    
                    \Log::info('Fixed BPJS transaction:', [
                        'transaction_id' => $transaction->id,
                        'pasien_service_type' => $transaction->pasien->service_type,
                        'bpjs_default_price' => $bpjsDefaultPrice
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbaiki {$fixed} transaksi BPJS",
                'fixed_count' => $fixed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbaiki data BPJS: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatusPengerjaan(Request $request, $id)
    {
        try {
            $request->validate([
                'status_pengerjaan' => 'required|in:' . implode(',', self::WORK_STATUS_ALLOWED),
                'nohp' => 'nullable|string|max:30',
            ]);

            $penjualan = Penjualan::with(['pasien', 'branch'])->findOrFail($id);
            $oldStatus = $penjualan->status_pengerjaan;

            if ($request->status_pengerjaan === self::WORK_STATUS_KIRIM_WA) {
                if (!$penjualan->pasien) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data pasien tidak ditemukan pada transaksi ini. Tidak bisa kirim WA.',
                    ], 422);
                }

                $inputNoHp = trim((string) $request->input('nohp', ''));
                if ($inputNoHp !== '') {
                    $normalizedNoHp = WhatsAppHelper::normalizePhoneNumber($inputNoHp);
                    $penjualan->pasien->nohp = $normalizedNoHp ?: $inputNoHp;
                    $penjualan->pasien->save();
                    $penjualan->setRelation('pasien', $penjualan->pasien->fresh());
                }

                $existingNoHp = trim((string) ($penjualan->pasien->nohp ?? ''));
                if ($existingNoHp === '') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nomor HP pasien belum ada. Isi nomor HP untuk status Kirim WA.',
                    ], 422);
                }
            }
            
            $updateData = [
                'status_pengerjaan' => $request->status_pengerjaan,
                'passet_by_user_id' => auth()->id()
            ];

            // Set waktu selesai dikerjakan jika status berubah ke "Sudah Di Kerjakan"
            if ($request->status_pengerjaan == self::WORK_STATUS_SUDAH_DI_KERJAKAN && $penjualan->status_pengerjaan != self::WORK_STATUS_SUDAH_DI_KERJAKAN) {
                $updateData['waktu_selesai_dikerjakan'] = now();
            }

            if ($request->status_pengerjaan == self::WORK_STATUS_SUDAH_DI_AMBIL && ($penjualan->status ?? 'Belum Lunas') !== 'Lunas') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi belum lunas. Status Sudah Di Ambil hanya untuk transaksi yang sudah Lunas.'
                ], 422);
            }

            if ($request->status_pengerjaan == self::WORK_STATUS_SUDAH_DI_AMBIL && $penjualan->status_pengerjaan != self::WORK_STATUS_SUDAH_DI_AMBIL) {
                $updateData['waktu_sudah_diambil'] = now();
            }

            $penjualan->update($updateData);

            $whatsappNotification = null;
            if ($request->status_pengerjaan === self::WORK_STATUS_KIRIM_WA && $oldStatus !== self::WORK_STATUS_KIRIM_WA) {
                $penjualan->refresh();
                $penjualan->loadMissing(['pasien', 'branch']);
                $whatsappNotification = $this->notifyWhatsappReadyPickup($penjualan);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status pengerjaan berhasil diperbarui',
                'data' => [
                    'status_pengerjaan' => $penjualan->status_pengerjaan,
                    'passet_by_user_id' => $penjualan->passet_by_user_id,
                    'waktu_selesai_dikerjakan' => $penjualan->waktu_selesai_dikerjakan
                ],
                'whatsapp' => $whatsappNotification,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating status pengerjaan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui status pengerjaan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUsersList()
    {
        try {
            \Log::info('getUsersList method called');
            
            $users = \App\Models\User::select('id', 'name', 'role', 'branch_id')
                ->where('role', 'passet bantu')
                ->orderBy('name')
                ->get();
            
            \Log::info('Users found:', ['count' => $users->count(), 'users' => $users->toArray()]);
            
            // Jika tidak ada user dengan role passet bantu, tambahkan user yang sedang login jika role-nya passet bantu
            if ($users->isEmpty() && auth()->user()->role === 'passet bantu') {
                $currentUser = auth()->user();
                $users->push((object)[
                    'id' => $currentUser->id,
                    'name' => $currentUser->name,
                    'role' => $currentUser->role,
                    'branch_id' => $currentUser->branch_id
                ]);
                \Log::info('Added current user as fallback');
            }
            
            return response()->json($users);
        } catch (\Exception $e) {
            \Log::error('Error getting users list: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get users'], 500);
        }
    }

    /**
     * Test BPJS pricing logic for debugging
     */
    public function testBpjsPricing(Request $request)
    {
        try {
            $request->validate([
                'pasien_id' => 'required|exists:pasien,id_pasien',
                'frame_id' => 'required|exists:frames,id'
            ]);

            $pasien = Pasien::findOrFail($request->pasien_id);
            $frame = Frame::findOrFail($request->frame_id);

            // Log untuk debugging
            \Log::info('Test BPJS Pricing:', [
                'pasien_id' => $pasien->id_pasien,
                'pasien_service_type' => $pasien->service_type,
                'frame_id' => $frame->id,
                'frame_jenis' => $frame->jenis_frame,
                'frame_harga_asli' => $frame->harga_jual_frame
            ]);

            $pricing = $this->bpjsPricingService->calculateFramePrice($pasien, $frame);

            \Log::info('BPJS Pricing Result:', $pricing);

            return response()->json([
                'success' => true,
                'data' => [
                    'pasien_service_type' => $pasien->service_type,
                    'frame_type' => $frame->jenis_frame,
                    'original_price' => $frame->harga_jual_frame,
                    'calculated_price' => $pricing['price'],
                    'additional_cost' => $pricing['additional_cost'],
                    'reason' => $pricing['reason'],
                    'debug_info' => [
                        'is_frame_umum' => $frame->jenis_frame === 'Umum',
                        'default_bpjs_price' => $this->bpjsPricingService->getDefaultPrice($pasien->service_type)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Test BPJS Pricing Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal test pricing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug frame data for BPJS pricing
     */
    public function debugFrameData(Request $request)
    {
        try {
            $request->validate([
                'frame_id' => 'required|exists:frames,id'
            ]);

            $frame = Frame::findOrFail($request->frame_id);
            
            \Log::info('Frame Data Debug:', [
                'frame_id' => $frame->id,
                'frame_name' => $frame->merk_frame,
                'frame_jenis' => $frame->jenis_frame,
                'frame_harga' => $frame->harga_jual_frame,
                'frame_jenis_type' => gettype($frame->jenis_frame),
                'frame_jenis_length' => strlen($frame->jenis_frame ?? ''),
                'is_umum' => $frame->jenis_frame === 'Umum',
                'is_umum_trimmed' => trim($frame->jenis_frame ?? '') === 'Umum'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'frame_id' => $frame->id,
                    'frame_name' => $frame->merk_frame,
                    'frame_jenis' => $frame->jenis_frame,
                    'frame_harga' => $frame->harga_jual_frame,
                    'is_umum' => $frame->jenis_frame === 'Umum',
                    'debug_info' => [
                        'type' => gettype($frame->jenis_frame),
                        'length' => strlen($frame->jenis_frame ?? ''),
                        'trimmed' => trim($frame->jenis_frame ?? ''),
                        'trimmed_is_umum' => trim($frame->jenis_frame ?? '') === 'Umum'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Debug Frame Data Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal debug frame data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAuditReport()
    {
        return view('penjualan.audit_delete');
    }

    public function deleteAuditReportData(Request $request)
    {
        $entries = $this->readPenjualanDeleteAuditLogEntries(1500);

        if ($request->filled('start_date')) {
            $startDate = (string) $request->start_date;
            $entries = $entries->filter(function ($entry) use ($startDate) {
                return substr((string) $entry['deleted_at'], 0, 10) >= $startDate;
            })->values();
        }

        if ($request->filled('end_date')) {
            $endDate = (string) $request->end_date;
            $entries = $entries->filter(function ($entry) use ($endDate) {
                return substr((string) $entry['deleted_at'], 0, 10) <= $endDate;
            })->values();
        }

        return datatables()->of($entries)
            ->addIndexColumn()
            ->addColumn('deleted_by', function ($row) {
                return ($row['deleted_by_user_name'] ?? '-') . ' (ID: ' . ($row['deleted_by_user_id'] ?? '-') . ')';
            })
            ->addColumn('restored_summary', function ($row) {
                return (int) ($row['restored_items_count'] ?? 0) . ' item';
            })
            ->addColumn('aksi', function ($row) {
                $payload = htmlspecialchars(json_encode($row['restored_items'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                return '<button type="button" class="btn btn-xs btn-info" onclick="showRestoredItems(\'' . $payload . '\')"><i class="fa fa-list"></i> Detail</button>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    private function readPenjualanDeleteAuditLogEntries(int $maxEntries = 1000)
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return collect();
        }

        $lines = @file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines) || empty($lines)) {
            return collect();
        }

        $entries = [];

        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (count($entries) >= $maxEntries) {
                break;
            }

            $line = $lines[$i];
            if (!str_contains($line, 'Penjualan deleted and stock restored')) {
                continue;
            }

            $matches = [];
            $matched = preg_match('/^\[(?<timestamp>[^\]]+)\]\s+\w+\.\w+:\s+Penjualan deleted and stock restored\s+(?<context>\{.*\})$/', $line, $matches);
            if (!$matched) {
                continue;
            }

            $context = json_decode($matches['context'] ?? '{}', true);
            if (!is_array($context)) {
                continue;
            }

            $restoredItems = $context['restored_items'] ?? [];
            if (!is_array($restoredItems)) {
                $restoredItems = [];
            }

            $entries[] = [
                'deleted_at' => $matches['timestamp'] ?? '-',
                'penjualan_id' => $context['penjualan_id'] ?? '-',
                'kode_penjualan' => $context['kode_penjualan'] ?? '-',
                'deleted_by_user_id' => $context['deleted_by_user_id'] ?? '-',
                'deleted_by_user_name' => $context['deleted_by_user_name'] ?? '-',
                'deleted_by_user_role' => $context['deleted_by_user_role'] ?? '-',
                'branch_id' => $context['branch_id'] ?? '-',
                'restored_items_count' => count($restoredItems),
                'restored_items' => $restoredItems,
            ];
        }

        return collect($entries);
    }
    
    /**
     * Laporan tanda tangan BPJS
     */
    public function signatureReport()
    {
        return view('penjualan.signature-report');
    }
    
    /**
     * Data untuk laporan tanda tangan BPJS
     */
    public function signatureReportData(Request $request)
    {
        $query = Penjualan::with(['pasien', 'user', 'branch'])
            ->whereNotNull('signature_bpjs')
            ->whereHas('pasien', function($q) {
                $q->whereIn('service_type', ['BPJS I', 'BPJS II', 'BPJS III']);
            });
            
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }
        
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // Filter by service type
        if ($request->filled('service_type')) {
            $query->whereHas('pasien', function($q) use ($request) {
                $q->where('service_type', $request->service_type);
            });
        }
        
        $penjualan = $query->orderBy('tanggal', 'desc')->get();
        
        return DataTables::of($penjualan)
            ->addColumn('tanggal', function($p) {
                return $p->tanggal ? $p->tanggal->format('d/m/Y') : '-';
            })
            ->addColumn('nama_pasien', function($p) {
                return $p->pasien->nama_pasien ?? $p->nama_pasien_manual ?? '-';
            })
            ->addColumn('service_type', function($p) {
                return $p->pasien->service_type ?? '-';
            })
            ->addColumn('kasir', function($p) {
                return $p->user->name ?? '-';
            })
            ->addColumn('cabang', function($p) {
                return $p->branch->name ?? '-';
            })
            ->addColumn('signature_date', function($p) {
                return $p->signature_date ? $p->signature_date->format('d/m/Y H:i') : '-';
            })
            ->addColumn('actions', function($p) {
                $buttons = '<a href="' . route('penjualan.show', $p->id) . '" class="btn btn-xs btn-info" title="Lihat Detail"><i class="fa fa-eye"></i></a>';
                if ($p->signature_bpjs) {
                    $buttons .= ' <button class="btn btn-xs btn-success" onclick="viewSignature(\'' . $p->signature_bpjs . '\', \'' . $p->pasien->nama_pasien . '\')" title="Lihat Tanda Tangan"><i class="fa fa-signature"></i></button>';
                }
                return $buttons;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
