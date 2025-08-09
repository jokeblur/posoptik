<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use Illuminate\Http\Request;
use App\Imports\SalesImport;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        return view('sales.index');

    }

    public function data()
    {
        $sales = Sales::orderBy('id_sales','desc')->get();

       
        return datatables()
        ->of($sales)        
        ->addIndexColumn()
        ->addColumn('aksi', function ($sales) {
            return '
            <div class="btn-group">
                <button onclick="editform(`'. route('sales.update', $sales->id_sales) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                <button onclick="deleteData(`'. route('sales.destroy', $sales->id_sales) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
            </div>
            ';
        })
        ->rawColumns(['aksi'])
        ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    
        $sales = Sales::create($request->all());       
        
        return view('sales.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $sales = Sales::find($id);
        
        return response()->json($sales);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $sales = Sales::find($id);
        $sales->update($request->all());
        
        return view('sales.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $sales = Sales::find($id);
        $sales->delete();

        return response(null, 204);
    }

    /**
     * Import sales data from Excel file
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new SalesImport, $request->file('file'));
            
            return response()->json([
                'success' => true,
                'message' => 'Data sales berhasil diimport!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal import data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export sales data to Excel file
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        return Excel::download(new SalesExport, 'sales.xlsx');
    }

    /**
     * Export all sales data to Excel file
     *
     * @return \Illuminate\Http\Response
     */
    public function exportFull()
    {
        return Excel::download(new SalesExport, 'sales_lengkap.xlsx');
    }
}
