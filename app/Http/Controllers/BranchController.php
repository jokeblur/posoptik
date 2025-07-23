<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('branch.index');
    }

    public function data()
    {
        $branches = Branch::with('manager')->orderBy('id', 'desc')->get();
        return datatables()
            ->of($branches)
            ->addColumn('manager_name', function ($branch) {
                return $branch->manager ? $branch->manager->name : '-';
            })
            ->addColumn('status', function ($branch) {
                return $branch->is_active ? 
                    '<span class="label label-success">Aktif</span>' : 
                    '<span class="label label-danger">Tidak Aktif</span>';
            })
            ->addIndexColumn()
            ->addColumn('aksi', function ($branch) {
                return '<div class="btn-group">
                    <button onclick="editform(`' . route('branch.update', $branch->id) . '` )" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button onclick="deleteData(`' . route('branch.destroy', $branch->id) . '` )" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['aksi', 'status'])
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
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
        ]);

        Branch::create($request->all());
        return response()->json(['message' => 'Cabang berhasil ditambahkan']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $branch = Branch::with('manager')->find($id);
        return response()->json($branch);
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
        $branch = Branch::find($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code,' . $id,
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
        ]);

        $branch->update($request->all());
        return response()->json(['message' => 'Cabang berhasil diupdate']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branch.index');
    }

    public function list()
    {
        $branches = Branch::all(['id', 'name']);
        return response()->json($branches);
    }

    public function setActive(Request $request)
    {
        $request->validate(['branch_id' => 'required|exists:branches,id']);
        session(['active_branch_id' => $request->branch_id]);
        return response()->json(['success' => true]);
    }

    /**
     * Switch user's active branch
     */
    public function switchBranch(Request $request)
    {
        $user = auth()->user();
        $branchId = $request->branch_id;
        
        // Check if user can access this branch
        if (!$user->canAccessAllBranches()) {
            if ($user->branch_id != $branchId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        session(['active_branch_id' => $branchId]);
        $branch = Branch::find($branchId);
        
        return response()->json([
            'message' => 'Berhasil beralih ke ' . $branch->name,
            'branch' => $branch
        ]);
    }

    /**
     * Get user's accessible branches
     */
    public function getUserBranches()
    {
        $user = auth()->user();
        $branches = $user->getAccessibleBranches();
        
        return response()->json($branches);
    }
}
