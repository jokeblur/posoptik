<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Print Agent - {{ optional($branch)->name ?? 'Optik Melati' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 20px; background: #f3f4f6; color: #1f2937; font-family: Arial, Helvetica, sans-serif; }
        .wrap { max-width: 980px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        h1 { margin: 0 0 4px; font-size: 22px; color: #a4193d; }
        .muted { color: #6b7280; font-size: 13px; }
        .status { display: flex; align-items: center; gap: 12px; font-size: 18px; font-weight: 700; }
        .dot { width: 16px; height: 16px; border-radius: 50%; background: #9ca3af; flex: 0 0 16px; }
        .dot.on { background: #16a34a; box-shadow: 0 0 0 4px rgba(22,163,74,.2); }
        .dot.busy { background: #f59e0b; box-shadow: 0 0 0 4px rgba(245,158,11,.25); }
        .dot.err { background: #dc2626; box-shadow: 0 0 0 4px rgba(220,38,38,.2); }
        .btns { margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap; }
        button { border: 0; border-radius: 5px; padding: 9px 16px; font-weight: 700; cursor: pointer; color: #fff; background: #2563eb; }
        button.grey { background: #6b7280; }
        button.green { background: #16a34a; }
        button.small { padding: 4px 10px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        th { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: 700; color: #fff; }
        .b-menunggu { background: #6b7280; } .b-proses { background: #f59e0b; } .b-selesai { background: #16a34a; } .b-gagal { background: #dc2626; }
        ol { margin: 6px 0 0; padding-left: 20px; line-height: 1.6; font-size: 14px; }
        code { background: #f3f4f6; padding: 2px 5px; border-radius: 3px; font-size: 12px; word-break: break-all; }
        #printFrame { position: fixed; left: -10000px; top: 0; width: 900px; height: 1200px; border: 0; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Print Agent</h1>
        <div class="muted">
            Cabang: <strong>{{ optional($branch)->name ?? '-' }}</strong> &middot; Login: {{ auth()->user()->name }}.
            Biarkan halaman ini tetap terbuka di PC yang tersambung ke printer. Nota yang dikirim dari tablet akan langsung dicetak.
        </div>
        <div class="status" style="margin-top:14px;">
            <span class="dot" id="statusDot"></span>
            <span id="statusText">Memulai...</span>
        </div>
        <div class="muted" id="statusDetail" style="margin-top:4px;"></div>
        <div class="btns">
            <button type="button" id="toggleBtn" class="grey">Jeda</button>
            <button type="button" id="testBtn" class="green">Test Print</button>
        </div>
    </div>

    <div class="card">
        <strong>Riwayat print (15 terakhir)</strong>
        <table style="margin-top:8px;">
            <thead>
                <tr><th>Waktu</th><th>Dokumen</th><th>Dari</th><th>Status</th><th></th></tr>
            </thead>
            <tbody id="riwayatBody">
                <tr><td colspan="5" class="muted">Belum ada.</td></tr>
            </tbody>
        </table>
    </div>

    <div class="card" style="border-left: 5px solid #dc2626; background: #fff7ed;">
        <strong style="color:#b45309;">PENTING: Tanpa Chrome kiosk, dialog print tetap muncul</strong>
        <p style="margin: 8px 0 0; color: #92400e; line-height: 1.5;">
            Browser web tidak bisa memaksa print tanpa dialog di mode normal. Untuk <strong>tanpa dialog</strong>, Chrome PC harus dibuka dengan flag <code>--kiosk-printing</code>.
            Jika masih muncul dialog, biasanya ada proses Chrome lain yang masih berjalan sehingga flag diabaikan.
            Solusi paling pasti: jalankan <code>print-agent-chrome.bat</code> (di folder utama aplikasi), yang memakai profil Chrome terpisah.
        </p>
    </div>

    <div class="card">
        <strong>Cara pasang di PC (sekali saja)</strong>
        <ol>
            <li>Jadikan printer nota sebagai <strong>printer default</strong> Windows (Settings &rarr; Printers, matikan "Let Windows manage my default printer").</li>
            <li>Buat shortcut Chrome di desktop, klik kanan &rarr; Properties, pada kolom <em>Target</em> tambahkan di belakangnya:<br>
                <code>--kiosk-printing "{{ route('print-agent') }}"</code></li>
            <li>Tutup semua jendela Chrome, lalu buka Chrome lewat shortcut itu dan login. Halaman ini terbuka dan print berjalan tanpa dialog.</li>
            <li>Klik <strong>Test Print</strong>. Bila masih muncul dialog print, berarti Chrome belum dibuka lewat shortcut (tutup semua Chrome dulu, lalu buka dari shortcut baru).</li>
            <li>Jangan tutup / minimize jendela ini selama toko buka. Boleh ditaruh di belakang jendela lain.</li>
        </ol>
    </div>
</div>

<iframe id="printFrame" title="Print frame"></iframe>

<script>
(function () {
    const ambilUrl = @json(route('print-agent.ambil'));
    const selesaiUrl = @json(url('/print-agent')) + '/';
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const frame = document.getElementById('printFrame');
    const dot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');
    const statusDetail = document.getElementById('statusDetail');
    const toggleBtn = document.getElementById('toggleBtn');
    const INTERVAL_MS = 3000;

    let aktif = true;
    let sibuk = false;
    let timer = null;

    function setStatus(kelas, teks, detail) {
        dot.className = 'dot ' + kelas;
        statusText.textContent = teks;
        statusDetail.textContent = detail || '';
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body || {})
        }).then(function (res) {
            if (res.status === 401 || res.status === 419) {
                throw new Error('Sesi login habis. Muat ulang halaman ini lalu login lagi.');
            }
            if (!res.ok) {
                throw new Error('Server error ' + res.status);
            }
            return res.json();
        });
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderRiwayat(list) {
        const body = document.getElementById('riwayatBody');
        if (!list || !list.length) {
            body.innerHTML = '<tr><td colspan="5" class="muted">Belum ada.</td></tr>';
            return;
        }
        body.innerHTML = list.map(function (j) {
            const ulang = (j.status === 'gagal' || j.status === 'selesai')
                ? '<button type="button" class="small grey" data-ulang="' + j.id + '">Print ulang</button>' : '';
            return '<tr><td>' + esc(j.dibuat_at) + '</td><td>' + esc(j.judul) + '</td><td>' + esc(j.dibuat_oleh || '-') + '</td>'
                + '<td><span class="badge b-' + esc(j.status) + '">' + esc(j.status_label) + '</span>'
                + (j.pesan ? '<div class="muted">' + esc(j.pesan) + '</div>' : '') + '</td><td>' + ulang + '</td></tr>';
        }).join('');
    }

    function bunyi() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            osc.frequency.value = 880;
            osc.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch (e) { /* tanpa suara */ }
    }

    // Muat halaman ke iframe tersembunyi lalu print. Dengan Chrome --kiosk-printing, print() langsung ke printer default.
    function cetakDiFrame(muat) {
        return new Promise(function (resolve, reject) {
            const batas = setTimeout(function () { reject(new Error('Halaman nota tidak termuat (timeout).')); }, 30000);
            frame.onload = function () {
                clearTimeout(batas);
                setTimeout(function () {
                    try {
                        const doc = frame.contentDocument;
                        if (doc && /\/login/.test(frame.contentWindow.location.pathname)) {
                            throw new Error('Sesi login habis, nota tidak bisa dibuka.');
                        }
                        frame.contentWindow.focus();
                        frame.contentWindow.print();
                        resolve();
                    } catch (e) {
                        reject(e);
                    }
                }, 1200); // beri waktu font & gambar termuat
            };
            muat();
        });
    }

    function prosesJob(job) {
        sibuk = true;
        bunyi();
        setStatus('busy', 'Mencetak: ' + job.judul, 'Dikirim oleh ' + (job.dibuat_oleh || '-') + ' pukul ' + job.dibuat_at);

        return cetakDiFrame(function () { frame.src = job.url; })
            .then(function () {
                return post(selesaiUrl + job.id + '/selesai', { berhasil: true });
            })
            .catch(function (e) {
                return post(selesaiUrl + job.id + '/selesai', { berhasil: false, pesan: String(e.message || e).slice(0, 250) });
            })
            .finally(function () {
                sibuk = false;
            });
    }

    function cek() {
        if (!aktif || sibuk) {
            return;
        }
        post(ambilUrl)
            .then(function (res) {
                renderRiwayat(res.riwayat);
                if (res.job) {
                    return prosesJob(res.job).then(cek);
                }
                setStatus('on', 'Siap - menunggu kiriman dari tablet', 'Cek terakhir: ' + new Date().toLocaleTimeString('id-ID'));
            })
            .catch(function (e) {
                setStatus('err', 'Tidak tersambung ke server', e.message);
            });
    }

    function mulai() {
        clearInterval(timer);
        timer = setInterval(cek, INTERVAL_MS);
        cek();
    }

    toggleBtn.addEventListener('click', function () {
        aktif = !aktif;
        toggleBtn.textContent = aktif ? 'Jeda' : 'Lanjutkan';
        toggleBtn.className = aktif ? 'grey' : '';
        if (aktif) {
            cek();
        } else {
            setStatus('', 'Dijeda - tidak mengambil kiriman', 'Klik Lanjutkan untuk mulai lagi.');
        }
    });

    document.getElementById('testBtn').addEventListener('click', function () {
        if (sibuk) {
            return;
        }
        sibuk = true;
        cetakDiFrame(function () {
            frame.removeAttribute('src');
            frame.srcdoc = '<html><head><style>@page{size:80mm auto;margin:0}body{font-family:Arial;padding:6mm;font-size:12px}</style></head>'
                + '<body><h3 style="margin:0 0 4px">TEST PRINT</h3>Print Agent {{ e(optional($branch)->name ?? '') }}<br>'
                + new Date().toLocaleString('id-ID') + '<br>Printer siap dipakai dari tablet.</body></html>';
        }).catch(function (e) {
            alert('Test print gagal: ' + e.message);
        }).finally(function () {
            frame.removeAttribute('srcdoc');
            sibuk = false;
        });
    });

    document.getElementById('riwayatBody').addEventListener('click', function (e) {
        const id = e.target.getAttribute('data-ulang');
        if (id) {
            post(selesaiUrl + id + '/ulang').then(cek);
        }
    });

    // Cegah layar/PC tidur selama agent berjalan (bila browser mendukung).
    if (navigator.wakeLock) {
        const kunci = function () { navigator.wakeLock.request('screen').catch(function () {}); };
        kunci();
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                kunci();
            }
        });
    }

    mulai();
})();
</script>
</body>
</html>
