<?php
namespace App\Http\Controllers;
use App\Models\Transaksi;
use App\Models\PenjualanDetail;
use App\Models\Frame;
use App\Models\Lensa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('penjualan.index');
    }

    public function data()
    {
        $user = auth()->user();
        $query = Transaksi::with('user', 'branch', 'passetByUser')->latest();

        // Jika user bukan super admin, filter berdasarkan cabang mereka
        if ($user->role !== 'super admin') {
            $query->where('branch_id', $user->branch_id);
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
                return 'Rp. '. format_uang($penjualan->total);
            })
            ->addColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? 'N/A';
            })
             ->addColumn('cabang', function ($penjualan) {
                return $penjualan->branch->name ?? 'N/A';
            })
            ->addColumn('passet_by', function ($penjualan) {
                return $penjualan->passetByUser->name ?? '-';
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
                $detailButton = '<a href="'. route('penjualan.show', $penjualan->id) .'" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i> Detail</a>';
                $ambilButton = '';
                if ($penjualan->status_pengerjaan == 'Selesai Dikerjakan') {
                    $ambilButton = '<button onclick="tandaiDiambil(`'. route('penjualan.diambil', $penjualan->id) .'`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-check-square"></i> Tandai Diambil</button>';
                }

                return '
                <div class="btn-group">
                    '. $detailButton .'
                    '. $ambilButton .'
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_penjualan', 'status_pengerjaan'])
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
    public function create()
    {
        $pasiens = \App\Models\Pasien::all();
        $dokters = \App\Models\Dokter::all();
        $frames = \App\Models\Frame::where('stok', '>', 0)->get();
        $lenses = \App\Models\Lensa::where('stok', '>', 0)->get();
        
        return view('penjualan.create', compact('pasiens', 'dokters', 'frames', 'lenses'));
    }
    public function store(Request $request)
    {
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
            $user = auth()->user();
            
            // Prioritaskan branch_id dari sesi jika ada (untuk admin/super admin)
            // Jika tidak, gunakan branch_id yang melekat pada user (untuk kasir, dll)
            $branch_id = session('active_branch_id', $user->branch_id);

            if (!$branch_id) {
                // Jika setelah semua pengecekan branch_id tetap tidak ada, lempar error
                throw new \Exception('ID Cabang tidak dapat ditentukan untuk pengguna ini.');
            }

            $kekurangan = $request->kekurangan;
            $status = $kekurangan <= 0 ? 'Lunas' : 'Belum Lunas';

            $penjualanData = [
                'kode_penjualan' => $request->kode_penjualan,
                'tanggal' => now(),
                'tanggal_siap' => $request->tanggal_siap,
                'pasien_id' => $request->filled('pasien_id') ? $request->pasien_id : null,
                'nama_pasien_manual' => $request->filled('pasien_id') ? null : $request->pasien_name,
                'dokter_id' => $request->dokter_id,
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'total' => $request->total,
                'diskon' => $request->diskon,
                'bayar' => $request->bayar,
                'kekurangan' => $kekurangan,
                'status' => $status,
                'status_pengerjaan' => 'Menunggu Pengerjaan', // Tambahkan ini
            ];

            // Handle file upload
            if ($request->hasFile('photo_bpjs')) {
                $path = $request->file('photo_bpjs')->store('photos_bpjs', 'public');
                $penjualanData['photo_bpjs'] = $path;
            }

            $penjualan = Transaksi::create($penjualanData);

            $items = json_decode($request->items, true);

            foreach ($items as $itemData) {
                $itemModel = null;
                if ($itemData['type'] === 'frame') {
                    $itemModel = \App\Models\Frame::find($itemData['id']);
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
                        'price' => $itemData['price'],
                        'subtotal' => $itemData['price'] * $itemData['quantity'],
                    ]);
                    $itemModel->decrement('stok', $itemData['quantity']);
                }
            }

            DB::commit();
            // Return a JSON success response
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'redirect_url' => route('penjualan.show', ['penjualan' => $penjualan->id])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Return a JSON error response
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        $penjualan = Transaksi::with('details.itemable', 'user', 'branch', 'pasien', 'dokter')->findOrFail($id);
        return view('penjualan.show', compact('penjualan'));
    }

    public function cetak($id)
    {
        $penjualan = Transaksi::with('details.itemable', 'user', 'branch', 'pasien', 'dokter')->findOrFail($id);
        return view('penjualan.cetak', compact('penjualan'));
    }

    public function lunas($id)
    {
        $penjualan = Transaksi::findOrFail($id);

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
        $penjualan = Transaksi::findOrFail($id);

        // Cek apakah transaksi sudah lunas
        if ($penjualan->status !== 'Lunas') {
            return response()->json(['message' => 'Transaksi belum lunas! Mohon selesaikan pembayaran terlebih dahulu.'], 422);
        }

        $penjualan->status_pengerjaan = 'Sudah Diambil';
        $penjualan->waktu_sudah_diambil = now(); // Catat waktu diambil
        $penjualan->save();

        return response()->json(['message' => 'Status berhasil diubah menjadi Sudah Diambil.']);
    }

    public function getLensa()
    {
        $lensa = Lensa::all();
        return response()->json($lensa);
    }

    public function getFrame()
    {
        $frame = Frame::all();
        return response()->json($frame);
    }
}
