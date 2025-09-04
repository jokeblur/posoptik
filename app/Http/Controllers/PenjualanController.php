<?php
namespace App\Http\Controllers;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Frame;
use App\Models\Lensa;
use App\Models\User;
use App\Models\Pasien;
use App\Services\BpjsPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OpenDay;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    protected $bpjsPricingService;

    public function __construct(BpjsPricingService $bpjsPricingService)
    {
        $this->bpjsPricingService = $bpjsPricingService;
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

        $statistics = $query->selectRaw('
            SUM(CASE WHEN status_pengerjaan = "Menunggu Pengerjaan" THEN 1 ELSE 0 END) as menunggu,
            SUM(CASE WHEN status_pengerjaan = "Sedang Dikerjakan" THEN 1 ELSE 0 END) as sedang,
            SUM(CASE WHEN status_pengerjaan = "Selesai Dikerjakan" THEN 1 ELSE 0 END) as selesai,
            SUM(CASE WHEN status_pengerjaan = "Sudah Diambil" THEN 1 ELSE 0 END) as diambil
        ')->first();

        return response()->json([
            'menunggu' => (int) $statistics->menunggu,
            'sedang' => (int) $statistics->sedang,
            'selesai' => (int) $statistics->selesai,
            'diambil' => (int) $statistics->diambil
        ]);
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $query = Penjualan::with('user', 'branch', 'passetByUser', 'dokter', 'pasien')->latest();

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

        $penjualan = $query->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
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
                    
                    return '<span class="label label-info" title="BPJS: ' . $penjualan->pasien->service_type . '">Rp. '. format_uang($bpjsPrice) . '</span>';
                }
                // Untuk transaksi umum, tampilkan total normal
                return '<span class="text-success">Rp. '. format_uang($penjualan->total) . '</span>';
            })

            ->addColumn('passet_by', function ($penjualan) {
                return $penjualan->passetByUser->name ?? '-';
            })
            ->addColumn('nama_pasien', function ($penjualan) {
                return $penjualan->pasien->nama_pasien ?? '-';
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
            ->addColumn('status_pengerjaan', function ($penjualan) {
                $statusClass = 'label-default';
                $statusText = $penjualan->status_pengerjaan;
                $timeText = '';

                if ($penjualan->status_pengerjaan == 'Selesai Dikerjakan') {
                    $statusClass = 'label-success';
                    if ($penjualan->waktu_selesai_dikerjakan) {
                        $timeText = '<br><small>'. tanggal_indonesia($penjualan->waktu_selesai_dikerjakan, true) .'</small>';
                    }
                } elseif ($penjualan->status_pengerjaan == 'Menunggu Pengerjaan') {
                    $statusClass = 'label-warning';
                } elseif ($penjualan->status_pengerjaan == 'Sudah Diambil') {
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
                $statusButton = '';
                $ambilButton = '';
                $deleteButton = '';
                
                // Tombol update status pengerjaan
                if ($penjualan->status_pengerjaan != 'Sudah Diambil') {
                    $statusButton = '<button onclick="updateStatusPengerjaan('.$penjualan->id.')" class="btn btn-xs btn-primary btn-flat" title="Update Status"><i class="fa fa-cogs"></i></button>';
                }
                
                if ($penjualan->status_pengerjaan == 'Selesai Dikerjakan') {
                    $ambilButton = '<button onclick="tandaiDiambil(`'. route('penjualan.diambil', $penjualan->id) .'`)" class="btn btn-xs btn-success btn-flat" title="Tandai Diambil"><i class="fa fa-check-square"></i></button>';
                }
                
                // Hanya super admin dan admin yang bisa menghapus transaksi
                if (($user->isSuperAdmin() || $user->isAdmin()) && 
                    ($user->role === 'super admin' || $penjualan->branch_id === $user->branch_id)) {
                    $deleteButton = '<button onclick="hapusTransaksi(`'. route('penjualan.destroy', $penjualan->id) .'`)" class="btn btn-xs btn-danger btn-flat" title="Hapus"><i class="fa fa-trash"></i></button>';
                }

                return '
                <div class="btn-group">
                    '. $detailButton .'
                    '. $editButton .'
                    '. $statusButton .'
                    '. $ambilButton .'
                    '. $deleteButton .'
                </div>
                ';
            })
            ->addColumn('barcode', function ($penjualan) {
                return $penjualan->barcode ?? null;
            })
            ->rawColumns(['aksi', 'kode_penjualan', 'status_pengerjaan', 'status_transaksi', 'total_harga', 'jenis_layanan'])
            ->make(true);
    }
    public function searchProduct(Request $request)
    {
        $query = $request->get('q');
        
        $frames = \App\Models\Frame::where('merk_frame', 'LIKE', "%{$query}%")
            ->orWhere('kode_frame', 'LIKE', "%{$query}%")
            ->select('id', 'merk_frame as name', 'harga_jual_frame as price', \DB::raw("'frame' as type"))
            ->limit(5)
            ->get();
            
        $lenses = \App\Models\Lensa::where('merk_lensa', 'LIKE', "%{$query}%")
            ->orWhere('kode_lensa', 'LIKE', "%{$query}%")
            ->select('id', 'merk_lensa as name', 'harga_jual_lensa as price', \DB::raw("'lensa' as type"))
            ->limit(5)
            ->get();

        $products = $frames->concat($lenses);

        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        // Tentukan branch_id berdasarkan role
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $branch_id = session('active_branch_id', $user->branch_id);
            
            // Jika admin/super admin belum memilih cabang aktif
            if (!$branch_id || $branch_id == $user->branch_id) {
                $branches = \App\Models\Branch::all();
                return view('penjualan.create', [
                    'error_message' => 'Silakan pilih cabang aktif terlebih dahulu di dropdown navbar sebelum melakukan transaksi.',
                    'branches' => $branches,
                    'pasiens' => collect(),
                    'dokters' => collect(),
                    'frames' => collect(),
                    'lenses' => collect(),
                    'aksesoris' => collect(),
                    'selected_pasien' => null
                ]);
            }
        } else {
            $branch_id = $user->branch_id;
        }
        
        $today = now()->toDateString();
        $openDay = OpenDay::where('branch_id', $branch_id)->where('tanggal', $today)->first();
        
        if (!$openDay || !$openDay->is_open) {
            $branches = \App\Models\Branch::all();
            return view('penjualan.create', [
                'error_message' => 'Transaksi tidak dapat dilakukan. Kasir cabang ini sudah tutup atau belum dibuka. Silakan hubungi admin untuk open day.',
                'branches' => $branches,
                'pasiens' => collect(),
                'dokters' => collect(),
                'frames' => collect(),
                'lenses' => collect(),
                'aksesoris' => collect(),
                'selected_pasien' => null
            ]);
        }
        
        $pasiens = \App\Models\Pasien::all();
        $dokters = \App\Models\Dokter::all();
        $frames = \App\Models\Frame::where('branch_id', $branch_id)->where('stok', '>', 0)->get();
        // Tampilkan semua lensa, termasuk yang stok 0
        $lenses = \App\Models\Lensa::where('branch_id', $branch_id)->get();
        $aksesoris = \App\Models\Aksesoris::where('branch_id', $branch_id)->where('stok', '>', 0)->get();
        
        // Cek apakah ada pasien_id yang dikirim dari form pasien
        $selected_pasien = null;
        if ($request->has('pasien_id')) {
            $selected_pasien = \App\Models\Pasien::with('prescriptions.dokter')->find($request->pasien_id);
        }
        
        // Debug log
        \Log::info('PenjualanController@create - Data loaded', [
            'branch_id' => $branch_id,
            'lenses_count' => $lenses->count(),
            'frames_count' => $frames->count(),
            'aksesoris_count' => $aksesoris->count(),
            'selected_pasien_id' => $request->pasien_id ?? null
        ]);
        
        return view('penjualan.create', compact('pasiens', 'dokters', 'frames', 'lenses', 'aksesoris', 'selected_pasien'));
    }
    
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Tentukan branch_id berdasarkan role
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $branch_id = session('active_branch_id', $user->branch_id);
        } else {
            $branch_id = $user->branch_id;
        }
        
        $today = now()->toDateString();
        $openDay = OpenDay::where('branch_id', $branch_id)->where('tanggal', $today)->first();
        if (!$openDay || !$openDay->is_open) {
            return response()->json(['message' => 'Transaksi tidak dapat dilakukan. Kasir cabang ini sudah tutup atau belum dibuka.'], 403);
        }
        // Validasi dasar
        $rules = [
            'kode_penjualan' => 'required|unique:penjualan,kode_penjualan',
            'items' => 'required|json',
            'total' => 'required|numeric',
            'diskon' => 'required|numeric|min:0',
            'bayar' => 'required|numeric|min:0',
            'kekurangan' => 'required|numeric',
        ];

        // Validasi kondisional untuk pasien
        if ($request->filled('pasien_id')) {
            $rules['pasien_id'] = 'exists:pasien,id_pasien';
        } else {
            $rules['pasien_name'] = 'required|string|max:255';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            
            $kekurangan = $request->kekurangan;
            $status = $kekurangan <= 0 ? 'Lunas' : 'Belum Lunas';
            $transactionStatus = 'Normal'; // Default status
            $bpjsDefaultPrice = 0;
            $totalAdditionalCost = 0;
            $pasienServiceType = null;

            $items = json_decode($request->items, true);
            $hanyaAksesoris = !empty($items) && collect($items)->every(function($item) {
                return $item['type'] === 'aksesoris';
            });

            // Jika ada pasien, gunakan BPJS pricing service
            $pasien = null;
            if ($request->filled('pasien_id')) {
                $pasien = Pasien::find($request->pasien_id);
                if ($pasien && in_array($pasien->service_type, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                    $pasienServiceType = $pasien->service_type;
                    $bpjsDefaultPrice = $this->bpjsPricingService->getDefaultPrice($pasien->service_type);
                    
                    // Debug logging untuk BPJS pricing
                    \Log::info('BPJS Pricing in Store Method:', [
                        'pasien_id' => $pasien->id_pasien,
                        'service_type' => $pasien->service_type,
                        'bpjs_default_price' => $bpjsDefaultPrice,
                        'pasien_service_type' => $pasienServiceType
                    ]);
                }
            }

            // Generate barcode
            $barcode = 'TRX' . date('Ymd') . str_pad(Penjualan::max('id') + 1, 6, '0', STR_PAD_LEFT);
            
            $penjualanData = [
                'kode_penjualan' => $request->kode_penjualan,
                'barcode' => $barcode,
                'tanggal' => now(),
                'tanggal_siap' => $request->tanggal_siap,
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
                'status_pengerjaan' => $hanyaAksesoris ? 'Sudah Diambil' : 'Menunggu Pengerjaan',
                'waktu_sudah_diambil' => $hanyaAksesoris ? now() : null,
            ];

            // Handle file upload
            if ($request->hasFile('photo_bpjs')) {
                $path = $request->file('photo_bpjs')->store('photos_bpjs', 'public');
                $penjualanData['photo_bpjs'] = $path;
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

            foreach ($items as $itemData) {
                $itemModel = null;
                $price = $itemData['price']; // Default price dari frontend
                $additionalCost = 0;
                
                if ($itemData['type'] === 'frame') {
                    $itemModel = \App\Models\Frame::find($itemData['id']);
                    // Jika ada pasien dengan service_type BPJS, gunakan pricing service
                    if ($pasien && in_array($pasien->service_type, ['BPJS I', 'BPJS II', 'BPJS III'])) {
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
                } elseif ($itemData['type'] === 'aksesoris') {
                    $itemModel = \App\Models\Aksesoris::find($itemData['id']);
                }

                if ($itemModel) {
                    $penjualan->details()->create([
                        'itemable_id' => $itemModel->id,
                        'itemable_type' => get_class($itemModel),
                        'quantity' => $itemData['quantity'],
                        'price' => $price,
                        'subtotal' => $price * $itemData['quantity'],
                        'additional_cost' => $additionalCost, // Simpan biaya tambahan
                    ]);

                    // Update stok
                    $itemModel->decrement('stok', $itemData['quantity']);
                }
            }
            
            // Update status transaksi dan informasi BPJS jika ada perubahan
            $updateData = [
                'transaction_status' => $transactionStatus,
                'total_additional_cost' => $totalAdditionalCost
            ];
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

    public function edit($id)
    {
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
        
        return view('penjualan.edit', compact('penjualan', 'dokters', 'pasiens', 'latestPrescription'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pasien_id' => 'required',
            'bayar' => 'required|numeric|min:0',
            'status' => 'required|in:Belum Lunas,Lunas',
            'status_pengerjaan' => 'required|in:Menunggu Pengerjaan,Sedang Dikerjakan,Selesai Dikerjakan,Sudah Diambil',
        ]);

        try {
            DB::beginTransaction();

            $penjualan = Penjualan::findOrFail($id);
            
            // Update basic information
            $penjualan->pasien_id = $request->pasien_id;
            $penjualan->dokter_id = $request->dokter_id ?: null;
            $penjualan->dokter_manual = $request->dokter_manual;
            $penjualan->tanggal_siap = $request->tanggal_siap;
            $penjualan->bayar = $request->bayar;
            $penjualan->status = $request->status;
            $penjualan->status_pengerjaan = $request->status_pengerjaan;
            
            // Calculate kekurangan
            $penjualan->kekurangan = $penjualan->total - $request->bayar;
            
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

        $penjualan->status_pengerjaan = 'Sudah Diambil';
        $penjualan->waktu_sudah_diambil = now(); // Catat waktu diambil
        $penjualan->save();

        return response()->json(['message' => 'Status berhasil diubah menjadi Sudah Diambil.']);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        
        // Hanya super admin dan admin yang bisa menghapus transaksi
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus transaksi.'], 403);
        }

        $penjualan = Penjualan::findOrFail($id);

        // Cek apakah user memiliki akses ke cabang transaksi ini
        if ($user->role !== 'super admin' && $penjualan->branch_id !== $user->branch_id) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus transaksi dari cabang lain.'], 403);
        }

        try {
            // Hapus detail transaksi terlebih dahulu
            $penjualan->details()->delete();
            
            // Hapus transaksi
            $penjualan->delete();

            return response()->json(['message' => 'Transaksi berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus transaksi. Silakan coba lagi.'], 500);
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
                'status_pengerjaan' => 'required|in:Menunggu Pengerjaan,Sedang Dikerjakan,Selesai Dikerjakan,Sudah Diambil',
                'passet_by' => 'required|string|max:255'
            ]);

            $penjualan = Penjualan::findOrFail($id);
            
            $updateData = [
                'status_pengerjaan' => $request->status_pengerjaan,
                'passet_by' => $request->passet_by
            ];

            // Set waktu selesai dikerjakan jika status berubah ke "Selesai Dikerjakan"
            if ($request->status_pengerjaan == 'Selesai Dikerjakan' && $penjualan->status_pengerjaan != 'Selesai Dikerjakan') {
                $updateData['waktu_selesai_dikerjakan'] = now();
            }

            $penjualan->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Status pengerjaan berhasil diperbarui',
                'data' => [
                    'status_pengerjaan' => $penjualan->status_pengerjaan,
                    'passet_by' => $penjualan->passet_by,
                    'waktu_selesai_dikerjakan' => $penjualan->waktu_selesai_dikerjakan
                ]
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
