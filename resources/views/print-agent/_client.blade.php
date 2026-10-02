{{-- Kirim nota ke printer PC (Print Agent). Pakai: kirimPrintPc(penjualanId, 'half' | 'struk') --}}
<script>
(function () {
    if (window.kirimPrintPc) {
        return;
    }

    const storeUrl = @json(route('print-jobs.store'));
    const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || @json(csrf_token());

    function toast(teks, warna, lama) {
        let box = document.getElementById('printPcToast');
        if (!box) {
            box = document.createElement('div');
            box.id = 'printPcToast';
            box.style.cssText = 'position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:99999;max-width:92%;'
                + 'padding:12px 18px;border-radius:8px;color:#fff;font:600 14px Arial,sans-serif;box-shadow:0 4px 14px rgba(0,0,0,.25);text-align:center;';
            document.body.appendChild(box);
        }
        box.style.background = warna;
        box.textContent = teks;
        box.style.display = 'block';
        clearTimeout(box._t);
        if (lama) {
            box._t = setTimeout(function () { box.style.display = 'none'; }, lama);
        }
    }

    function pantau(statusUrl, percobaan, pesanTunggu) {
        if (percobaan > 20) {
            toast('Nota masih antre. Pastikan halaman Print Agent di PC terbuka.', '#d97706', 6000);
            return;
        }
        setTimeout(function () {
            fetch(statusUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    const job = res.job || {};
                    if (job.status === 'selesai') {
                        toast('Nota sudah dicetak di PC.', '#16a34a', 4000);
                    } else if (job.status === 'gagal') {
                        toast('Gagal print di PC: ' + (job.pesan || 'tidak diketahui'), '#dc2626', 7000);
                    } else {
                        toast(job.status === 'proses' ? 'Sedang dicetak di PC...' : (pesanTunggu || 'Menunggu PC mengambil nota...'), job.status === 'proses' || !pesanTunggu ? '#2563eb' : '#d97706');
                        pantau(statusUrl, percobaan + 1, pesanTunggu);
                    }
                })
                .catch(function () { pantau(statusUrl, percobaan + 1, pesanTunggu); });
        }, 1500);
    }

    window.kirimPrintPc = function (penjualanId, jenis) {
        toast('Mengirim nota ke printer PC...', '#2563eb');

        return fetch(storeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ penjualan_id: penjualanId, jenis: jenis || 'half' })
        }).then(function (r) {
            return r.json().then(function (body) {
                if (!r.ok) {
                    throw new Error(body.message || ('Error ' + r.status));
                }
                return body;
            });
        }).then(function (res) {
            const pesanTunggu = res.agent_aktif ? null : 'Nota masuk antrean, tapi Print Agent di PC belum aktif. Buka halaman Print Agent di PC.';
            toast(pesanTunggu || 'Menunggu PC mengambil nota...', pesanTunggu ? '#d97706' : '#2563eb');
            pantau(res.status_url, 0, pesanTunggu);
        }).catch(function (e) {
            toast('Gagal mengirim ke printer PC: ' + e.message, '#dc2626', 7000);
        });
    };
})();
</script>
