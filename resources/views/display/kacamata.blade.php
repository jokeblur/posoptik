<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Display Optik Melati</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --ink: #14202b; --muted: #61707d; --red: #a4193d; --cream: #fff8ef; --line: #e8ded4; --green: #16856b; }
        * { box-sizing: border-box; }
        html, body { width: 100%; height: 100%; overflow: hidden; }
        body { margin: 0; min-height: 100vh; color: var(--ink); background: #f4eee8; font-family: 'Poppins', sans-serif; }
        .display { width: 100%; height: 100vh; min-height: 0; display: grid; grid-template-rows: auto 1fr auto; overflow: hidden; background: radial-gradient(circle at 100% 0, #f7d8d0 0, transparent 32%), linear-gradient(135deg, #fbf5ee, #e9f1ed); }
        header { display: flex; align-items: center; justify-content: space-between; padding: 18px 4vw; border-bottom: 1px solid rgba(20,32,43,.12); }
        .brand { display: flex; gap: 13px; align-items: center; }
        .brand img { width: 48px; height: 48px; object-fit: contain; }
        .brand strong { display: block; font-size: clamp(19px, 2.1vw, 30px); font-weight: 700; letter-spacing: .06em; }
        .brand small { color: var(--muted); font: 600 11px Arial, sans-serif; letter-spacing: .14em; text-transform: uppercase; }
        .clock { text-align: right; color: var(--red); font: 700 clamp(22px, 2.7vw, 38px) 'Poppins', sans-serif; }
        .clock small { display: block; color: var(--muted); font: 12px Arial, sans-serif; letter-spacing: .08em; }
        .clock-wrap { display: flex; align-items: center; }
        .display-tools { display: flex; align-items: center; gap: 6px; margin-right: 18px; }
        .display-tools button { min-width: 32px; height: 30px; border: 1px solid rgba(164,25,61,.28); border-radius: 3px; color: var(--red); background: white; cursor: pointer; font: 600 13px 'Poppins', sans-serif; }
        .display-tools button:hover { color: white; background: var(--red); }
        .display-tools span { min-width: 46px; color: var(--muted); text-align: center; font: 11px 'Poppins', sans-serif; }
        main { display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(340px, .82fr); gap: 24px; width: min(1500px, 92vw); height: clamp(300px, 65vh, 560px); min-height: 0; margin: 24px auto; }
        .promo, .monitor { min-height: 0; border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 16px 40px rgba(86, 57, 39, .09); overflow: hidden; }
        .promo { position: relative; display: flex; align-items: flex-end; padding: clamp(24px, 4vw, 58px); color: white; background: #8d2945; height: clamp(300px, 65vh, 560px); min-height: 0; }
        .promo:after { content: ''; position: absolute; inset: 0; background: linear-gradient(0deg, rgba(16,22,31,.72), transparent 70%); }
        .promo-content { position: absolute; inset: 0; z-index: 1; display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; padding: clamp(28px, 5vw, 72px); }
        .promo-copy { max-width: 570px; animation: rise .7s ease both; }
        #promo-slides { position: absolute; inset: 0; width: 100%; height: 100%; max-width: none; }
        .promo-media { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .promo-media.video { object-fit: cover; }
        .promo-slide { display: none; position: absolute; inset: 0; }
        .promo-slide.active { display: block; }
        .promo-empty { display: block; }
        .eyebrow { font: 700 12px Arial, sans-serif; letter-spacing: .2em; text-transform: uppercase; color: #ffd9a8; }
        .promo h1 { margin: 14px 0 12px; font-size: clamp(38px, 5.2vw, 76px); line-height: .96; font-weight: 600; }
        .promo p { margin: 0; font-size: clamp(17px, 1.8vw, 25px); line-height: 1.35; color: #fff4ea; }
        .dots { position: absolute; right: 28px; bottom: 28px; z-index: 2; font: 12px Arial, sans-serif; letter-spacing: .2em; color: #ffd9a8; }
        .monitor { display: flex; min-width: 0; min-height: 0; height: 100%; flex-direction: column; padding: clamp(16px, 2vw, 24px); background: rgba(255,255,255,.84); overflow: hidden; }
        .monitor-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 18px; }
        .monitor h2 { margin: 0 0 4px; font-size: clamp(20px, 2.2vw, 30px); font-weight: 600; }
        .monitor-sub { color: var(--muted); font: 10px Arial, sans-serif; }
        .live { display: inline-flex; align-items: center; gap: 6px; color: var(--green); font: 700 11px Arial, sans-serif; text-transform: uppercase; letter-spacing: .1em; }
        .live:before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 0 5px rgba(22,133,107,.12); }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 12px; }
        .stat { padding: 8px; background: var(--cream); border-left: 2px solid var(--red); }
        .stat b { display: block; color: var(--red); font: 700 19px Arial, sans-serif; }
        .stat span { color: var(--muted); font: 9px Arial, sans-serif; }
        .orders { display: grid; flex: 1; min-height: 0; align-content: start; gap: 9px; overflow-x: hidden; overflow-y: scroll; scrollbar-gutter: stable; padding-right: 4px; }
        .order { display: grid; grid-template-columns: 1fr auto; gap: 7px; align-items: center; padding: 9px 10px; border: 1px solid var(--line); background: white; }
        .order strong { display: block; font-size: 14px; font-weight: 500; }
        .order code { color: var(--muted); font: 8px Arial, sans-serif; }
        .order-actions { display: flex; flex-wrap: wrap; gap: 4px; grid-column: 1 / -1; padding-top: 1px; }
        .order-actions button { border: 0; border-radius: 3px; padding: 5px 7px; cursor: pointer; color: white; font: 600 8px 'Poppins', sans-serif; letter-spacing: .02em; box-shadow: 0 1px 2px rgba(0,0,0,.15); }
        .action-wa { background: #16856b; }
        .action-take { background: #a4193d; }
        .action-pay { background: #c17b16; }
        .status { padding: 5px 7px; color: #754b00; background: #fff0c9; font: 700 8px Arial, sans-serif; text-align: right; text-transform: uppercase; letter-spacing: .04em; }
        .status.ready { color: #12644f; background: #d8f2e9; }
        .empty { padding: 45px 18px; color: var(--muted); text-align: center; font-size: 18px; }
        footer { display: flex; justify-content: space-between; padding: 12px 4vw; color: var(--muted); font: 11px Arial, sans-serif; letter-spacing: .04em; }
        .media-fullscreen { position: fixed; z-index: 20; inset: 0; display: none; align-items: center; justify-content: center; padding: 0; background: #111; cursor: pointer; }
        .media-fullscreen.active { display: flex; animation: appear .35s ease both; }
        .media-fullscreen img, .media-fullscreen video { display: block; width: 100%; height: 100%; object-fit: cover; }
        .media-title { position: absolute; left: 4vw; bottom: 4vh; max-width: 70%; padding: 10px 16px; color: white; background: rgba(0,0,0,.55); font: 600 clamp(16px, 2vw, 28px) Arial, sans-serif; }
        .media-hint { position: absolute; right: 4vw; bottom: 4vh; color: rgba(255,255,255,.75); font: 12px Arial, sans-serif; }
        @keyframes appear { from { opacity: 0; } to { opacity: 1; } }
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 900px) { main { grid-template-columns: 1fr; height: auto; } .promo { height: clamp(300px, 58vh, 460px); } .monitor { height: clamp(340px, 58vh, 460px); } .orders { max-height: none; } }
        @media (max-width: 520px) { header { padding: 14px 5vw; } .brand img { width: 38px; height: 38px; } .clock small { font-size: 9px; } .display-tools { margin-right: 8px; } .display-tools button { min-width: 28px; height: 28px; } .display-tools span { min-width: 38px; font-size: 10px; } main { width: 90vw; margin: 15px auto; gap: 15px; } .promo { height: 62vh; max-height: 390px; } .monitor { height: 62vh; max-height: 440px; padding: 17px; } .order { grid-template-columns: 1fr; } .status { text-align: left; width: fit-content; } footer { padding: 10px 5vw; } }
    </style>
</head>
<body>
<div class="display">
    <header>
        <div class="brand"><img src="{{ asset('image/optik-melati.png') }}" alt="Optik Melati"><div><strong>OPTIK MELATI</strong><small>{{ $branchName }}</small></div></div>
        <div class="clock-wrap"><div class="display-tools" aria-label="Ukuran display"><button id="zoom-out" type="button" title="Perkecil display">-</button><span id="zoom-level">100%</span><button id="zoom-in" type="button" title="Perbesar display">+</button></div><div class="clock" id="clock"><small>WAKTU TOKO</small>--:--</div></div>
    </header>
    <main>
        <section class="promo" aria-label="Media promosi">
            <div class="promo-copy" id="promo-slides"><div class="promo-slide active promo-empty"><span class="eyebrow">Media promosi</span><h1>Promosi Optik Melati</h1><p>Upload gambar atau video promosi dari menu Media Promosi Display.</p></div></div>
            <div class="dots" id="dots">PROMO</div>
        </section>
        <section class="monitor" aria-label="Monitoring kacamata pasien">
            <div class="monitor-head"><div><h2>Status Kacamata</h2><div class="monitor-sub">Pesanan aktif di {{ $branchName }}</div></div><span class="live">Live</span></div>
            <div class="summary"><div class="stat"><b id="total">0</b><span>Siap ditampilkan</span></div><div class="stat"><b id="ready-done">0</b><span>Sudah dikerjakan</span></div><div class="stat"><b id="ready-wa">0</b><span>Sudah kirim WA</span></div></div>
            <div class="orders" id="orders"><div class="empty">Memuat status pesanan...</div></div>
        </section>
    </main>
    <footer><span>Informasi diperbarui otomatis</span><span id="last-update">Menghubungkan...</span></footer>
</div>
<div class="media-fullscreen" id="media-fullscreen" role="button" tabindex="0" aria-label="Tutup media promosi">
    <div id="media-stage"></div><span class="media-title" id="media-title"></span><span class="media-hint">Klik untuk kembali ke monitoring</span>
</div>
<script>
    const dataUrl = @json(route('display.kacamata.data'));
    const mediaUrl = @json(route('display.kacamata.media'));
    let mediaItems = [];
    let mediaIndex = 0;
    let idleTimer;
    const displayZoomKey = 'optik-melati-display-zoom';
    let displayZoom = Number(localStorage.getItem(displayZoomKey) || 1);
    function applyDisplayZoom() {
        displayZoom = Math.min(1.4, Math.max(0.8, displayZoom));
        document.documentElement.style.zoom = displayZoom;
        document.getElementById('zoom-level').textContent = Math.round(displayZoom * 100) + '%';
        localStorage.setItem(displayZoomKey, displayZoom.toFixed(2));
    }
    const statusClass = status => status === 'Sudah Di Kerjakan' || status === 'Kirim WA' ? 'ready' : '';
    const statusLabel = status => status === 'Sudah Di Kerjakan' ? 'Siap Diambil' : status;
    async function refreshDisplay() {
        try {
            const response = await fetch(dataUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            const data = await response.json();
            document.getElementById('total').textContent = data.total;
            document.getElementById('ready-done').textContent = data.counts['Sudah Di Kerjakan'] || 0;
            document.getElementById('ready-wa').textContent = data.counts['Kirim WA'] || 0;
            document.getElementById('last-update').textContent = 'Update ' + data.updated_at;
            document.getElementById('orders').innerHTML = data.orders.length ? data.orders.map(order => '<article class="order"><div><strong>' + order.patient + '</strong><code>' + order.code + ' · update ' + order.updated_at + '</code></div><span class="status ' + statusClass(order.status) + '">' + statusLabel(order.status) + '</span><div class="order-actions">' + (order.status === 'Sudah Di Kerjakan' ? '<button class="action-wa" onclick="sendAction(\'' + order.urls.send_wa + '\', { status_pengerjaan: \'Kirim WA\' }, \'Kirim notifikasi WhatsApp untuk pasien ini?\')"><i class="fa fa-whatsapp"></i> Kirim WA</button>' : '') + (order.status === 'Kirim WA' ? '<button class="action-take" onclick="sendAction(\'' + order.urls.take + '\', {}, \'Tandai kacamata sudah diambil?\')"><i class="fa fa-check"></i> Sudah Diambil</button>' : '') + (order.payment_status !== 'Lunas' ? '<button class="action-pay" onclick="sendAction(\'' + order.urls.pay + '\', {}, \'Konfirmasi pelunasan transaksi ini?\')"><i class="fa fa-money"></i> Pelunasan</button>' : '') + '</div></article>').join('') : '<div class="empty">Belum ada pesanan aktif.</div>';
        } catch (error) {
            document.getElementById('last-update').textContent = 'Koneksi terputus, mencoba lagi...';
        }
    }
    async function sendAction(url, payload, confirmation) {
        const confirmationResult = await Swal.fire({ title: 'Konfirmasi aksi', text: confirmation, icon: 'warning', showCancelButton: true, confirmButtonColor: '#a4193d', cancelButtonColor: '#777', confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Batal' });
        if (!confirmationResult.isConfirmed) return;
        try {
            const response = await fetch(url, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: JSON.stringify(payload) });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Aksi gagal diproses.');
            await Swal.fire({ title: 'Berhasil', text: result.message || 'Aksi berhasil diproses.', icon: 'success', confirmButtonColor: '#a4193d', confirmButtonText: 'OK' });
            await refreshDisplay();
        } catch (error) {
            await Swal.fire({ title: 'Aksi gagal', text: error.message, icon: 'error', confirmButtonColor: '#a4193d', confirmButtonText: 'Tutup' });
        }
    }
    async function refreshMedia() {
        try {
            const response = await fetch(mediaUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            mediaItems = await response.json();
            const slides = document.getElementById('promo-slides');
            if (mediaItems.length) {
                slides.innerHTML = mediaItems.map((item, index) => '<div class="promo-slide ' + (index === 0 ? 'active' : '') + '">' + (item.tipe === 'video' ? '<video class="promo-media video" src="' + escapeHtml(item.url) + '" autoplay loop playsinline></video>' : '<img class="promo-media" src="' + escapeHtml(item.url) + '" alt="' + escapeHtml(item.judul) + '">') + '<div class="promo-content"><div class="promo-copy"><span class="eyebrow">Promosi Optik Melati</span><h1>' + escapeHtml(item.judul) + '</h1></div></div></div>').join('');
                document.getElementById('dots').textContent = '01 / ' + String(mediaItems.length).padStart(2, '0');
                enableVideoSound(slides.querySelectorAll('video'));
            }
        } catch (error) {
            mediaItems = [];
        }
    }
    function enableVideoSound(videos) {
        videos.forEach(video => {
            video.muted = false;
            video.play().catch(() => {
                video.muted = true;
                video.play().catch(() => {});
                soundBlocked = true;
            });
        });
    }
    let soundBlocked = false;
    document.addEventListener('click', () => {
        if (!soundBlocked) return;
        soundBlocked = false;
        document.querySelectorAll('video').forEach(video => { video.muted = false; video.play().catch(() => {}); });
    });
    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
    }
    document.getElementById('zoom-out').addEventListener('click', () => { displayZoom -= .1; applyDisplayZoom(); });
    document.getElementById('zoom-in').addEventListener('click', () => { displayZoom += .1; applyDisplayZoom(); });
    applyDisplayZoom();
    let fullscreenTimer;
    function showFullscreenMedia() {
        if (!mediaItems.length) return;
        clearTimeout(fullscreenTimer);
        const item = mediaItems[mediaIndex % mediaItems.length];
        const stage = document.getElementById('media-stage');
        stage.innerHTML = item.tipe === 'video'
            ? '<video src="' + escapeHtml(item.url) + '" autoplay playsinline></video>'
            : '<img src="' + escapeHtml(item.url) + '" alt="' + escapeHtml(item.judul) + '">';
        document.getElementById('media-title').textContent = item.judul;
        document.getElementById('media-fullscreen').classList.add('active');
        enableVideoSound(stage.querySelectorAll('video'));
        mediaIndex = (mediaIndex + 1) % mediaItems.length;

        const video = stage.querySelector('video');
        if (video) {
            video.addEventListener('ended', showFullscreenMedia, { once: true });
        } else {
            fullscreenTimer = setTimeout(showFullscreenMedia, 8000);
        }
    }
    function hideFullscreenMedia() {
        clearTimeout(fullscreenTimer);
        document.getElementById('media-fullscreen').classList.remove('active');
        document.getElementById('media-stage').innerHTML = '';
        resetIdleTimer();
    }
    function resetIdleTimer() {
        clearTimeout(idleTimer);
        if (!document.getElementById('media-fullscreen').classList.contains('active')) {
            idleTimer = setTimeout(showFullscreenMedia, 8000);
        }
    }
    let slide = 0;
    setInterval(() => { const slides = document.querySelectorAll('#promo-slides .promo-slide'); if (slides.length < 2) return; slides[slide].classList.remove('active'); slide = (slide + 1) % slides.length; slides[slide].classList.add('active'); document.getElementById('dots').textContent = String(slide + 1).padStart(2, '0') + ' / ' + String(slides.length).padStart(2, '0'); }, 8000);
    document.getElementById('media-fullscreen').addEventListener('click', hideFullscreenMedia);
    document.addEventListener('mousemove', resetIdleTimer, { passive: true });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && document.getElementById('media-fullscreen').classList.contains('active')) hideFullscreenMedia(); });
    setInterval(refreshDisplay, 15000); refreshDisplay();
    setInterval(refreshMedia, 30000); refreshMedia(); resetIdleTimer();
    setInterval(() => { document.getElementById('clock').innerHTML = '<small>WAKTU TOKO</small>' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }, 1000);
</script>
</body>
</html>