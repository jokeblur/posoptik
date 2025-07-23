<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $roles = ['admin', 'kasir', 'passet bantu'];
        return view('user.index', compact('branches', 'roles'));
    }

    public function data()
    {
        $users = User::with('branch')->where('role', '!=', 'super admin')->latest()->get();

        return datatables()
            ->of($users)
            ->addIndexColumn()
            ->addColumn('branch_name', function ($user) {
                return $user->branch->name ?? 'N/A';
            })
            ->addColumn('aksi', function ($user) {
                return '
                <div class="btn-group">
                    <button onclick="editForm(`'. route('user.show', $user->id) .'`, `'. route('user.update', $user->id) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-edit"></i> Edit</button>
                    <button onclick="deleteData(`'. route('user.destroy', $user->id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i> Hapus</button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'kasir', 'passet bantu'])],
            'branch_id' => 'required|exists:branches,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'branch_id' => $request->branch_id,
        ]);

        return response()->json(['message' => 'User berhasil ditambahkan.', 'data' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::in(['admin', 'kasir', 'passet bantu'])],
            'branch_id' => 'required|exists:branches,id',
        ]);

        $userData = $request->except('password');
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json(['message' => 'User berhasil diperbarui.', 'data' => $user]);
    }

    public function destroy(User $user)
    {
        // Prevent deleting super admin
        if ($user->role === 'super admin') {
            return response()->json(['message' => 'Super Admin tidak dapat dihapus.'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
