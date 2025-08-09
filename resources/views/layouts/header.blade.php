<header class="main-header">
    <!-- Logo -->
    <a href="{{ url('/') }}" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>O</b>M</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">{{ config('app.name') }}</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                @if(isset($openDay) && auth()->user()->isKasir())
                    <li style="display:flex;align-items:center;">
                        @if($openDay && $openDay->is_open)
                            <span class="label label-success" style="margin-right:8px;"><i class="fa fa-unlock"></i> Kasir Buka: {{ $openDay->updated_at->format('H:i') }}</span>
                        @else
                            <span class="label label-danger" style="margin-right:8px;"><i class="fa fa-lock"></i> Kasir Tutup</span>
                        @endif
                    </li>
                @endif
                
                <!-- Branch Selector for Admin/Super Admin -->
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <!-- Hanya tampilkan native select branch -->
                    <li style="padding: 10px 15px;">
                        <select id="branch-selector" class="form-control" style="width: 180px; display: inline-block;">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach(\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}" @if(session('active_branch_id', auth()->user()->branch_id) == $branch->id) selected @endif>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </li>
                @endif
                
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ asset('AdminLTE2/dist/img/user2-160x160.jpg') }}" class="user-image" alt="User Image">
                        <span class="hidden-xs">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="{{ asset('AdminLTE2/dist/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
                            <p>
                                {{ auth()->user()->name }} - {{ auth()->user()->email }}
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="#" class="btn btn-default btn-flat" onclick="confirmLogout()">Keluar</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<form action="{{ route('logout') }}" method="post" id="logout-form" style="display: none;">
    @csrf
</form>

@if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
<script>
$(document).ready(function() {
    // Handle branch selection
    // This script block is no longer needed as the branch dropdown is removed.
    // The native select #branch-selector will handle the branch change.
});
</script>
@endif