<?php

namespace App\Http\Controllers;

use \App\Models\Dokter;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        return view('dokter.index');

    }

    public function data()
    {
        $dokter = Dokter::orderBy('id_dokter','desc')->get();

       
        return datatables()
        ->of($dokter)        
        ->addIndexColumn()
        ->addColumn('aksi', function ($dokter) {
            return '
            <div class="btn-group">
                <button onclick="editform(`'. route('dokter.update', $dokter->id_dokter) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                <button onclick="deleteData(`'. route('dokter.destroy', $dokter->id_dokter) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
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
    
        $dokter = Dokter::create($request->all());       
        
        return view('dokter.index');

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
        $dokter = Dokter::find($id);
        
        return response()->json($dokter);
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
        $dokter = Dokter::find($id);
        $dokter->update($request->all());
        
        return view('dokter.index');
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
        $dokter = Dokter::find($id);
        $dokter->delete();

        return response(null, 204);
    }
}
