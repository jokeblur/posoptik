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
        $user = auth()->user();
        
        // Super admin bisa add super admin, admin hanya bisa add kasir dan passet bantu
        if ($user->isSuperAdmin()) {
            $roles = ['super admin', 'admin', 'kasir', 'passet bantu'];
        } else {
            $roles = ['admin', 'kasir', 'passet bantu'];
        }
        
        return view('user.index', compact('branches', 'roles'));
    }

    public function data()
    {
        $user = auth()->user();
        
        // Super admin bisa lihat semua user, admin hanya bisa lihat yang bukan super admin
        if ($user->isSuperAdmin()) {
            $users = User::with('branch')->latest()->get();
        } else {
            $users = User::with('branch')->where('role', '!=', 'super admin')->latest()->get();
        }

        return datatables()
            ->of($users)
            ->addIndexColumn()
            ->addColumn('branch_name', function ($user) {
                return $user->branch->name ?? 'N/A';
            })
            ->addColumn('role_badge', function ($user) {
                $color = [
                    'super admin' => 'badge-danger',
                    'admin' => 'badge-warning',
                    'kasir' => 'badge-info',
                    'passet bantu' => 'badge-success',
                ];
                return '<span class="badge ' . ($color[$user->role] ?? 'badge-secondary') . '">' . ucfirst($user->role) . '</span>';
            })
            ->addColumn('aksi', function ($user) {
                $currentUser = auth()->user();
                $canDelete = $currentUser->isSuperAdmin() && $user->role !== 'super admin';
                
                $editBtn = '<button onclick="editForm(`'. route('user.show', $user->id) .'`, `'. route('user.update', $user->id) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-edit"></i> Edit</button>';
                $deleteBtn = $canDelete ? '<button onclick="deleteData(`'. route('user.destroy', $user->id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i> Hapus</button>' : '';
                
                return '
                <div class="btn-group">
                    '. $editBtn .'
                    '. $deleteBtn .'
                </div>
                ';
            })
            ->rawColumns(['aksi', 'role_badge'])
            ->make(true);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        // Validasi role berdasarkan user yang login
        $allowedRoles = ['admin', 'kasir', 'passet bantu'];
        if ($currentUser->isSuperAdmin()) {
            $allowedRoles[] = 'super admin';
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in($allowedRoles)],
            'branch_id' => 'required|exists:branches,id',
        ]);

        // Jika super admin akan dibuat, hanya super admin yang bisa
        if ($request->role === 'super admin' && !$currentUser->isSuperAdmin()) {
            return response()->json(['message' => 'Hanya Super Admin yang bisa membuat Super Admin baru.'], 403);
        }

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'branch_id' => $request->branch_id,
        ]);

        return response()->json(['message' => 'User berhasil ditambahkan.', 'data' => $newUser]);
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Validasi role berdasarkan user yang login
        $allowedRoles = ['admin', 'kasir', 'passet bantu'];
        if ($currentUser->isSuperAdmin()) {
            $allowedRoles[] = 'super admin';
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::in($allowedRoles)],
            'branch_id' => 'required|exists:branches,id',
        ]);

        // Jika akan di-ubah ke super admin, hanya super admin yang bisa
        if ($request->role === 'super admin' && !$currentUser->isSuperAdmin()) {
            return response()->json(['message' => 'Hanya Super Admin yang bisa mengubah user menjadi Super Admin.'], 403);
        }

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
