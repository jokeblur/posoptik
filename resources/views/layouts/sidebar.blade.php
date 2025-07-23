<aside class="main-sidebar skin-red-light">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ asset('AdminLTE2/dist/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ auth()->user()->name }}</p>
                <!-- <a href="#"><i class="fa fa-circle text-success"></i> {{ auth()->user()->email }}</a> -->
               <div class="pull-left info">
               @if(auth()->user()->branch)
                    <small><i class="fa fa-building"></i> {{ auth()->user()->branch->name }}</small>
                @endif

               </div>
                
            </div>
        </div>
        
        <!-- Branch Selector for Super Admin -->
        @if(auth()->user()->canAccessAllBranches())
        <div class="user-panel" style="border-bottom: 1px solid #444;">
            <div class="pull-left info" style="width: 100%;">
                <select id="branch-selector" class="form-control" style="margin: 10px 0;">
                    <option value="">Pilih Cabang</option>
                    <!-- Options will be populated via AJAX -->
                </select>
            </div>
        </div>
        @endif
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">
           
            @if(auth()->user()->isSuperAdmin())
            <li class="header"><b>Data</b></li>
            <li>
                <a href="{{ route('branch.index') }}">
                    <i class="fa fa-building"></i> <span>Data Cabang</span>
                </a>
            </li>
            @endif
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
              <li>
                <a href="{{ route('pasien.index') }}">
                    <i class="fa fa-user-secret"></i> <span>Data Pasien</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dokter.index') }}">
                    <i class="fa fa-stethoscope"></i> <span>Data Dokter</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sales.index') }}">
                    <i class="fa fa-user-secret"></i> <span>Data Sales</span>
                </a>
            </li>
          
            @endif
         
           
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
            <li class="header"><b>MAIN MENU</b></li>
            <li>
                <a href="{{ route('frame.index') }}">
                    <i class="fa fa-glasses"></i> <span>Frame</span>
                </a>
            </li>
            <li>
                <a href="{{ route('lensa.index') }}">
                    <i class="fa fa-tablets"></i> <span>Lensa</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isKasir())
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-laptop"></i> <span>Transaksi</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('penjualan.index') }}"><i class="fa fa-circle-o"></i> Daftar Transaksi</a></li>
                    <li><a href="{{ route('penjualan.create') }}"><i class="fa fa-circle-o"></i> Transaksi Baru</a></li>
                </ul>
            </li>
            @endif
           
        
           
            @if(auth()->user()->role == 'super admin')
            <li><a href="{{ route('user.index') }}"><i class="fa fa-user-plus"></i> <span>Manajemen User</span></a></li>
            <li><a href="{{ route('branch.index') }}"><i class="fa fa-building"></i> <span>Manajemen Cabang</span></a></li>
            <li><a href="{{ route('passet.index') }}"><i class="fa fa-hourglass-half"></i> <span>Pengerjaan Passet</span></a></li>
            @endif

             @if(auth()->user()->role == 'admin')
             <li class="header">MASTER</li>
             <!-- <li><a href="{{ route('lensa.index') }}"><i class="fa fa-cubes"></i> <span>Stok Lensa</span></a></li>
             <li><a href="{{ route('dokter.index') }}"><i class="fa fa-user-md"></i> <span>Dokter</span></a></li>
             <li><a href="{{ route('sales.index') }}"><i class="fa fa-users"></i> <span>Sales</span></a></li> -->
             <li><a href="{{ route('user.index') }}"><i class="fa fa-user-plus"></i> <span>Manajemen User</span></a></li>
             <li><a href="{{ route('passet.index') }}"><i class="fa fa-hourglass-half"></i> <span>Pengerjaan Passet</span></a></li>
             @endif

             @if(auth()->user()->role == 'kasir')
             <li class="header">TRANSAKSI</li>
             <li><a href="{{ route('pasien.index') }}"><i class="fa fa-user-plus"></i> <span>Data Pasien</span></a></li>
             <li><a href="{{ route('penjualan.index') }}"><i class="fa fa-upload"></i> <span>Data Penjualan</span></a></li>
             @endif

             @if(auth()->user()->role == 'passet bantu')
             <li class="header">PROSES PENGERJAAN</li>
             <li><a href="{{ route('passet.index') }}"><i class="fa fa-hourglass-half"></i> <span>Pengerjaan</span></a></li>
             @endif
             
             <li><a href="{{ url('/inventory') }}"><i class="fa fa-database"></i> <span>Laporan Inventory</span></a></li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>