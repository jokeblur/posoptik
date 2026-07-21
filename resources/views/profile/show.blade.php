@extends('layouts.master')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Profil Pengguna</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Profil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Validasi Gagal!</strong>
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Profile Card -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Profil</h3>
                    </div>
                    
                    <!-- User Info Display -->
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 text-center">
                                <div class="user-profile-avatar">
                                    <div class="user-image-initials-large" data-role="{{ auth()->user()->role }}" style="font-size: 48px; width: 120px; height: 120px; margin: 0 auto;">
                                        {{ \App\Helpers\UserHelper::getInitials(auth()->user()->name) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nama:</strong></td>
                                        <td>{{ auth()->user()->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ auth()->user()->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Role:</strong></td>
                                        <td>
                                            <span class="badge badge-primary">
                                                {{ \App\Helpers\UserHelper::getRoleDisplayName(auth()->user()->role) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if(auth()->user()->branch)
                                    <tr>
                                        <td><strong>Cabang:</strong></td>
                                        <td>{{ auth()->user()->branch->name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Terdaftar:</strong></td>
                                        <td>{{ auth()->user()->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Terakhir Diperbarui:</strong></td>
                                        <td>{{ auth()->user()->updated_at->format('d M Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="card card-warning mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Edit Profil</h3>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name">Nama</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Current Password (for verification) -->
                            <div class="form-group">
                                <label for="current_password">Kata Sandi Saat Ini <small>(Wajib diisi jika ingin mengubah kata sandi)</small></label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password" placeholder="Isi jika ingin mengubah kata sandi">
                                @error('current_password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <hr>

                            <!-- New Password -->
                            <div class="form-group">
                                <label for="password">Kata Sandi Baru <small>(Kosongkan jika tidak ingin mengubah)</small></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Masukkan kata sandi baru">
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group">
                                <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi kata sandi baru">
                                @error('password_confirmation')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .user-profile-avatar {
        margin-bottom: 20px;
    }
</style>
@endsection
