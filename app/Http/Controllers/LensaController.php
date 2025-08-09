<?php

namespace App\Http\Controllers;

use App\Models\Lensa;
use Illuminate\Http\Request;
use App\Imports\LensaImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LensaExport;
use Illuminate\Support\Facades\Log;

class LensaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        $sales = \App\Models\Sales::where('keterangan', 'like', '%lensa%')->pluck('nama_sales', 'id_sales');
        return view('lensa.index', compact('branches', 'sales'));
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $query = Lensa::with(['branch', 'sales'])->accessibleByUser($user)->orderBy('id', 'desc');
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('stock_type')) {
            if ($request->stock_type === 'ready') {
                $query->readyStock();
            } elseif ($request->stock_type === 'custom') {
                $query->customOrder();
            }
        }
        $lensa = $query->get();
        return datatables()->of($lensa)
            ->addColumn('select_all', function ($lensa) {
                return '<input type="checkbox" name="selected_lensa[]" value="' . $lensa->id . '">';
            })
            ->addColumn('kode_lensa', function ($lensa) {
                return $lensa->kode_lensa;
            })
            ->addColumn('merk_lensa', function ($lensa) {
                return $lensa->merk_lensa;
            })
            ->addColumn('branch_name', function ($lensa) {
                return $lensa->branch ? $lensa->branch->name : '-';
            })
            ->addColumn('type', function ($lensa) {
                return $lensa->type ?? '-';
            })
            ->addColumn('index', function ($lensa) {
                return $lensa->index ?? '-';
            })
            ->addColumn('coating', function ($lensa) {
                return $lensa->coating ?? '-';
            })
            ->addColumn('harga_beli_lensa', function ($lensa) {
                return format_uang($lensa->harga_beli_lensa);
            })
            ->addColumn('harga_jual_lensa', function ($lensa) {
                return format_uang($lensa->harga_jual_lensa);
            })
            ->addColumn('stok', function ($lensa) {
                return format_uang($lensa->stok);
            })
            ->addColumn('stock_status', function ($lensa) {
                $badge = $lensa->is_custom_order ? 'badge-warning' : 'badge-success';
                return '<span class="badge ' . $badge . '">' . $lensa->stock_status . '</span>';
            })
            ->addColumn('sales_name', function ($lensa) {
                return $lensa->sales ? $lensa->sales->nama_sales : '-';
            })
            ->addColumn('add', function ($lensa) {
                return $lensa->add ? substr($lensa->add, 0, 50) . (strlen($lensa->add) > 50 ? '...' : '') : '-';
            })
            ->addIndexColumn()
            ->addColumn('aksi', function ($lensa) {
                return '<div class="btn-group">
                    <button onclick="editform(\'' . route('lensa.update', $lensa->id) . '\')" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button onclick="deleteData(\'' . route('lensa.destroy', $lensa->id) . '\')" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['aksi', 'select_all', 'stock_status'])
            ->make(true);
    }

    public function create()
    {
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        $sales = \App\Models\Sales::where('keterangan', 'like', '%lensa%')->pluck('nama_sales', 'id_sales');
        return view('lensa.form', compact('branches', 'sales'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $lensa = Lensa::latest()->first() ?? new Lensa();
        $id_baru = (int)$lensa->id + 1;
        
        $data = $request->all();
        $data['kode_lensa'] = 'L' . tambah_nol_didepan($id_baru, 5);
        
        // CATATAN: Sistem mengizinkan duplikasi merk_lensa dan kode_lensa
        // Tidak ada validasi unique pada field ini
        
        // Logika baru yang membedakan peran Admin dan Kasir
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Untuk Admin, branch_id diambil dari form input
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
            $data['branch_id'] = $request->branch_id;
        } else {
            // Untuk Kasir, branch_id dipaksa dari profil user
            $data['branch_id'] = $user->branch_id;
        }
        
        Lensa::create($data);
        return response()->json(['message' => 'Lensa berhasil ditambahkan']);
    }

    public function show($id)
    {
        $lensa = Lensa::find($id);
        return response()->json($lensa);
    }

    public function edit($id)
    {
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        $sales = \App\Models\Sales::where('keterangan', 'like', '%lensa%')->pluck('nama_sales', 'id_sales');
        $lensa = Lensa::find($id);
        return view('lensa.form', compact('lensa', 'branches', 'sales'));
    }

    public function update(Request $request, $id)
    {
        $lensa = Lensa::find($id);
        $user = auth()->user();
        $data = $request->all();

        // Admin/Super Admin bisa mengubah semua data termasuk cabang
        // Kasir tidak bisa mengubah data cabang
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            unset($data['branch_id']);
        } else {
             $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
             $data['branch_id'] = $request->branch_id;
        }
        
        $lensa->update($data);
        return response()->json(['message' => 'Lensa berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $lensa = Lensa::find($id);
        $lensa->delete();
        return response(null, 204);
    }

    public function getData()
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $lensa = Lensa::all();
        } else {
            $lensa = Lensa::accessibleByUser($user)->get();
        }
        return response()->json($lensa);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls'
            ]);

            // Debug: Log file info
            \Log::info('Lensa import file info:', [
                'filename' => $request->file('file')->getClientOriginalName(),
                'size' => $request->file('file')->getSize(),
                'mime' => $request->file('file')->getMimeType()
            ]);

            // Count records before import
            $beforeCount = \App\Models\Lensa::count();
            \Log::info('Lensa count before import: ' . $beforeCount);

            $import = new LensaImport;
            Excel::import($import, $request->file('file'));
            
            // Count records after import
            $afterCount = \App\Models\Lensa::count();
            $importedCount = $afterCount - $beforeCount;
            \Log::info('Lensa count after import: ' . $afterCount . ' (imported: ' . $importedCount . ')');
            
            // Log some sample imported records to check stock values
            $recentLensa = \App\Models\Lensa::latest()->take(5)->get();
            \Log::info('Sample imported lensa with stock values:', $recentLensa->map(function($lensa) {
                return [
                    'id' => $lensa->id,
                    'merk_lensa' => $lensa->merk_lensa,
                    'stok' => $lensa->stok,
                    'stok_type' => gettype($lensa->stok)
                ];
            })->toArray());
            
            return response()->json([
                'success' => true,
                'message' => "Data lensa berhasil diimport! ($importedCount record ditambahkan)"
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            \Log::error('Lensa import validation error:', $failures);
            return response()->json([
                'success' => false,
                'message' => 'Error validasi: ' . $e->getMessage(),
                'failures' => $failures
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Lensa import error: ' . $e->getMessage());
            \Log::error('Lensa import error trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal import data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testImport(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls'
            ]);

            // Read the Excel file without importing
            $data = Excel::toArray([], $request->file('file'));
            
            if (empty($data) || empty($data[0])) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel kosong atau tidak dapat dibaca',
                    'data' => []
                ]);
            }

            $rows = $data[0];
            $headers = array_shift($rows); // Remove header row
            
            \Log::info('Test import - Headers found:', $headers);
            
            $processedData = [];
            $import = new LensaImport;
            
            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue; // Skip empty rows
                
                $rowData = array_combine($headers, $row);
                \Log::info('Test import - Processing row ' . ($index + 2) . ':', $rowData);
                
                $processedRow = $import->model($rowData);
                
                if ($processedRow) {
                    $processedData[] = [
                        'row' => $index + 2, // +2 because we removed header and arrays are 0-indexed
                        'data' => [
                            'kode_lensa' => $processedRow->kode_lensa,
                            'merk_lensa' => $processedRow->merk_lensa,
                            'type' => $processedRow->type,
                            'index' => $processedRow->index,
                            'coating' => $processedRow->coating,
                            'harga_beli_lensa' => $processedRow->harga_beli_lensa,
                            'harga_jual_lensa' => $processedRow->harga_jual_lensa,
                            'stok' => $processedRow->stok,
                            'stok_type' => gettype($processedRow->stok),
                            'branch_id' => $processedRow->branch_id,
                            'sales_id' => $processedRow->sales_id,
                            'is_custom_order' => $processedRow->is_custom_order,
                            'add' => $processedRow->add,
                        ],
                        'original_row' => $rowData
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data akan diimport dengan format berikut:',
                'data' => [
                    'total_rows' => count($processedData),
                    'headers' => $headers,
                    'processed_data' => $processedData
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Test import error: ' . $e->getMessage());
            \Log::error('Test import error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function export()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                Log::warning('Lensa export: No authenticated user');
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            Log::info('Lensa export started by user: ' . $user->id);
            
            // Create export instance
            $export = new LensaExport();
            
            // Test if export data is valid
            $data = $export->collection();
            if ($data->isEmpty()) {
                Log::warning('Lensa export: No data to export');
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data untuk diexport'
                ], 404);
            }
            
            $filename = 'lensa_' . date('Y-m-d_H-i-s') . '.xlsx';
            Log::info('Lensa export: Generating file ' . $filename . ' with ' . $data->count() . ' records');
            
            // Use simple download like test route
            return Excel::download($export, $filename);
        } catch (\Exception $e) {
            Log::error('Lensa export error: ' . $e->getMessage());
            Log::error('Lensa export trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportFull()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                Log::warning('Lensa export full: No authenticated user');
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            Log::info('Lensa export full started by user: ' . $user->id);
            
            // Create export instance
            $export = new LensaExport();
            
            // Test if export data is valid
            $data = $export->collection();
            if ($data->isEmpty()) {
                Log::warning('Lensa export full: No data to export');
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data untuk diexport'
                ], 404);
            }
            
            $filename = 'lensa_lengkap_' . date('Y-m-d_H-i-s') . '.xlsx';
            Log::info('Lensa export full: Generating file ' . $filename . ' with ' . $data->count() . ' records');
            
            // Use simple download like test route
            return Excel::download($export, $filename);
        } catch (\Exception $e) {
            Log::error('Lensa export full error: ' . $e->getMessage());
            Log::error('Lensa export full trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template Excel for lensa import
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate()
    {
        try {
            \Log::info('Starting template download for lensa');
            
            // Alternative approach: Create template directly without separate export class
            $headers = [
                'Kode Lensa',
                'Merk Lensa',
                'Type',
                'Index',
                'Coating',
                'Harga Beli',
                'Harga Jual',
                'Stok',
                'Cabang',
                'Sales',
                'Tipe Stok',
                'Catatan'
            ];
            
            $sampleData = [
                [
                    'L00001',
                    'Essilor',
                    'Single Vision',
                    '1.56',
                    'Anti-Reflective',
                    200000,
                    300000,
                    20,
                    'Cabang Utama',
                    'John Sales',
                    'Ready Stock',
                    'Lensa premium kualitas tinggi'
                ],
                [
                    'L00002',
                    'Hoya',
                    'Progressive',
                    '1.67',
                    'Blue Cut',
                    400000,
                    600000,
                    15,
                    'Cabang Utama',
                    'Jane Sales',
                    'Custom Order',
                    'Lensa progresif untuk presbyopia'
                ],
                array_fill(0, 12, '') // Empty row
            ];
            
            // Create array for export
            $data = array_merge([$headers], $sampleData);
            
            // Create simple array export
            $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                private $data;
                
                public function __construct($data) {
                    $this->data = $data;
                }
                
                public function array(): array {
                    return $this->data;
                }
            };
            
            \Log::info('Export instance created successfully');
            
            return Excel::download(
                $export, 
                'template_lensa.xlsx'
            );
            
        } catch (\Exception $e) {
            \Log::error('Template download error: ' . $e->getMessage());
            \Log::error('Template download trace: ' . $e->getTraceAsString());
            
            // Return as file download error instead of JSON for better UX
            return response('Error downloading template: ' . $e->getMessage(), 500)
                   ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Bulk delete multiple lensa
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:lensa,id'
        ]);

        try {
            $ids = $request->input('ids');
            $deletedCount = 0;

            foreach ($ids as $id) {
                $lensa = Lensa::find($id);
                if ($lensa) {
                    $lensa->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'message' => "Berhasil menghapus {$deletedCount} data lensa."
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testManualImport()
    {
        try {
            // Create sample data that matches the user's Excel format
            $sampleData = [
                'Kode Lensa' => 'L00001',
                'Merk Lensa' => 'KRYP CR MC HIJAU',
                'Type' => 'Bifokal',
                'Index' => 'PLANO',
                'Coating' => '',
                'Harga Beli' => '',
                'Harga Jual' => '8000',
                'Stok' => '13', // This should be the actual stock value
                'Cabang' => 'Optik Melati Cabang 1',
                'Sales' => 'KRYP CR MC HIJAU',
                'Tipe Stok' => 'Ready Stock', // This should be parsed as false (not custom order)
                'Catatan' => '100'
            ];

            \Log::info('Testing manual import with sample data:', $sampleData);

            $import = new LensaImport;
            $result = $import->model($sampleData);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test import berhasil',
                    'data' => [
                        'kode_lensa' => $result->kode_lensa,
                        'merk_lensa' => $result->merk_lensa,
                        'stok' => $result->stok,
                        'stok_type' => gettype($result->stok),
                        'is_custom_order' => $result->is_custom_order
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Test import gagal - tidak ada data yang diproses'
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Manual test import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
