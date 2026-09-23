<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#a4193d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mobileTitle ?? 'Kasir' }} - Optik Melati</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE2/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <style>
        :root {
            --brand: #a4193d;
            --brand-dark: #7d1230;
        }
        * { -webkit-tap-highlight-color: transparent; }
        html, body { height: 100%; background: #f2f3f7; }
        body {
            margin: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            padding-top: calc(52px + env(safe-area-inset-top));
            padding-bottom: calc(66px + env(safe-area-inset-bottom));
        }
        .m-header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1030;
            height: calc(52px + env(safe-area-inset-top));
            padding-top: env(safe-area-inset-top);
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: #fff; display: flex; align-items: center;
            padding-left: 14px; padding-right: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        .m-header .m-title { font-size: 17px; font-weight: 700; flex: 1; }
        .m-header .m-branch { font-size: 11px; opacity: .9; display: block; }
        .m-header a, .m-header button { color: #fff; }
        .m-header .btn-logout {
            border: 1px solid rgba(255,255,255,.5); background: transparent;
            border-radius: 20px; font-size: 11px; padding: 4px 10px;
        }
        .m-content { padding: 12px; }
        .m-card {
            background: #fff; border-radius: 12px; padding: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 12px;
        }
        .m-card h4 { margin-top: 0; font-weight: 700; font-size: 15px; }
        .m-nav {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 1030;
            height: calc(60px + env(safe-area-inset-bottom));
            padding-bottom: env(safe-area-inset-bottom);
            background: #fff; display: flex;
            box-shadow: 0 -2px 10px rgba(0,0,0,.12);
        }
        .m-nav a {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; color: #888; text-decoration: none !important;
            font-size: 10px; padding-top: 6px;
        }
        .m-nav a i { font-size: 20px; margin-bottom: 2px; }
        .m-nav a.active { color: var(--brand); font-weight: 700; }
        .m-btn {
            display: block; width: 100%; border: none; border-radius: 12px;
            padding: 14px; font-size: 16px; font-weight: 700; text-align: center;
        }
        .m-btn-primary { background: var(--brand); color: #fff; }
        .m-btn-primary:active { background: var(--brand-dark); }
        .m-btn-outline {
            background: #fff; color: var(--brand); border: 2px solid var(--brand);
        }
        .m-input, .m-select {
            width: 100%; border: 1.5px solid #ddd; border-radius: 10px;
            padding: 12px; font-size: 15px; background: #fff;
        }
        .m-input:focus, .m-select:focus { border-color: var(--brand); outline: none; }
        .m-label { font-size: 12px; font-weight: 700; color: #555; margin-bottom: 4px; display: block; }
        .badge-cabang { background: var(--brand); }
        .text-brand { color: var(--brand); }
        .m-empty { text-align: center; color: #999; padding: 30px 10px; }
        .m-empty i { font-size: 40px; margin-bottom: 8px; display: block; }
        /* full-screen modal look di mobile */
        .modal-dialog { margin: 0; width: 100%; height: 100%; }
        .modal-content { height: 100%; border-radius: 0; display: flex; flex-direction: column; }
        .modal-body { flex: 1; overflow-y: auto; }
        @media (min-width: 768px) {
            .modal-dialog { margin: 30px auto; width: 640px; height: auto; }
            .modal-content { height: auto; border-radius: 8px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="m-header">
        <div style="flex:1;">
            <span class="m-title"><i class="fa fa-shopping-cart"></i> {{ $mobileTitle ?? 'Kasir' }}</span>
            <span class="m-branch"><i class="fa fa-map-marker"></i> {{ $branchName ?? '' }} · {{ auth()->user()->name }}</span>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-logout" title="Versi Desktop" style="margin-right:8px; text-decoration:none;">
            <i class="fa fa-desktop"></i>
        </a>
        <a href="{{ route('logout') }}" class="btn-logout" style="text-decoration:none;"
           onclick="event.preventDefault(); document.getElementById('m-logout-form').submit();">
            <i class="fa fa-sign-out"></i>
        </a>
        <form id="m-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
    </header>

    <main class="m-content">
        @yield('content')
    </main>

    <nav class="m-nav">
        <a href="{{ route('kasir-mobile.index') }}" class="{{ ($activeTab ?? '') === 'pos' ? 'active' : '' }}">
            <i class="fa fa-shopping-cart"></i><span>Transaksi</span>
        </a>
        <a href="{{ route('kasir-mobile.riwayat') }}" class="{{ ($activeTab ?? '') === 'riwayat' ? 'active' : '' }}">
            <i class="fa fa-history"></i><span>Riwayat</span>
        </a>
        <a href="{{ route('kasir-mobile.stok') }}" class="{{ ($activeTab ?? '') === 'stok' ? 'active' : '' }}">
            <i class="fa fa-cubes"></i><span>Stok</span>
        </a>
        <a href="{{ route('kasir-mobile.transfer') }}" class="{{ ($activeTab ?? '') === 'transfer' ? 'active' : '' }}">
            <i class="fa fa-exchange"></i><span>Transfer</span>
        </a>
    </nav>

    <script src="{{ asset('AdminLTE2/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('AdminLTE2/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        function formatRupiah(n) { return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID'); }
        function toast(msg, ok) {
            const t = $('<div>').css({
                position: 'fixed', top: '60px', left: '50%', transform: 'translateX(-50%)',
                background: ok === false ? '#c0392b' : '#27ae60', color: '#fff',
                padding: '10px 18px', borderRadius: '24px', 'z-index': 9999, 'font-size': '13px',
                'box-shadow': '0 2px 10px rgba(0,0,0,.3)'
            }).text(msg);
            $('body').append(t);
            setTimeout(() => t.fadeOut(300, () => t.remove()), 2500);
        }
    </script>
    @stack('scripts')
</body>
</html>
