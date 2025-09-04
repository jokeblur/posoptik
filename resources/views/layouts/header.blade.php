

<header class="main-header">
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
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
                        <select id="branch-selector" class="form-control" style="width: 160px; display: inline-block;">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach(\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}" @if(session('active_branch_id', auth()->user()->branch_id) == $branch->id) selected @endif>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </li>
                @endif
                
                <!-- Stock Transfer Notifications -->
                @include('partials.stock-transfer-notifications')
                
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" onclick="event.preventDefault(); $(this).next('.dropdown-menu').toggleClass('show'); $(this).parent().toggleClass('open');">
                        <div class="user-image-initials" data-role="{{ auth()->user()->role }}">
                            {{ \App\Helpers\UserHelper::getInitials(auth()->user()->name) }}
                        </div>
                        <div class="hidden-xs user-info-vertical">
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-role">{{ \App\Helpers\UserHelper::getRoleDisplayName(auth()->user()->role) }}</div>
                            @if(auth()->user()->branch)
                                <div class="user-branch">{{ auth()->user()->branch->name }}</div>
                            @endif
                        </div>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <div class="user-image-initials-large" data-role="{{ auth()->user()->role }}">
                                {{ \App\Helpers\UserHelper::getInitials(auth()->user()->name) }}
                            </div>
                            <div class="user-details">
                                <h4 class="user-name-large">{{ auth()->user()->name }}</h4>
                                <p class="user-email">{{ auth()->user()->email }}</p>
                                <p class="user-role-large">
                                    <i class="fa fa-user-circle"></i> {{ \App\Helpers\UserHelper::getRoleDisplayName(auth()->user()->role) }}
                                </p>
                                @if(auth()->user()->branch)
                                    <p class="user-branch-large">
                                        <i class="fa fa-building"></i> {{ auth()->user()->branch->name }}
                                    </p>
                                @endif
                            </div>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="#" class="btn btn-default btn-flat" onclick="confirmLogout(); return false;">Keluar</a>
                                <!-- Alternative logout button -->
                                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-default btn-flat" style="background: none; border: none; color: inherit; padding: 6px 12px;">
                                        Logout Direct
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
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

<!-- Fix untuk navbar dropdown di VPS -->
<script>
$(document).ready(function() {
    console.log('Navbar fix script loaded');
    
    // Fix dropdown toggle jika Bootstrap tidak ter-load
    $('.dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $dropdown = $(this).next('.dropdown-menu');
        var $parent = $(this).parent();
        
        // Close other dropdowns
        $('.dropdown-menu').not($dropdown).removeClass('show');
        $('.dropdown').not($parent).removeClass('open');
        
        // Toggle current dropdown
        $dropdown.toggleClass('show');
        $parent.toggleClass('open');
        
        console.log('Dropdown toggled:', $dropdown.hasClass('show'));
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
            $('.dropdown').removeClass('open');
        }
    });
    
    // Fix logout function jika SweetAlert tidak ter-load
    window.confirmLogout = function() {
        console.log('confirmLogout called');
        
        if (typeof Swal !== 'undefined') {
            // SweetAlert tersedia, gunakan konfirmasi
            Swal.fire({
                title: "Keluar dari aplikasi?",
                text: "Anda akan keluar dari sesi saat ini.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, keluar!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('SweetAlert confirmed, submitting logout form');
                    document.getElementById("logout-form").submit();
                }
            });
        } else {
            // SweetAlert tidak tersedia, gunakan confirm native
            if (confirm("Apakah Anda yakin ingin keluar dari aplikasi?")) {
                console.log('Native confirm confirmed, submitting logout form');
                document.getElementById("logout-form").submit();
            }
        }
    };
    
    // Alternative logout function - direct submit
    window.directLogout = function() {
        console.log('Direct logout called');
        document.getElementById("logout-form").submit();
    };
    
    // Debug: Check if logout form exists
    if (document.getElementById("logout-form")) {
        console.log('Logout form found');
    } else {
        console.log('Logout form NOT found');
    }
});
</script>