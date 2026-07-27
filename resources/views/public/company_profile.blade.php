<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Perusahaan - Optik Melati</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('AdminLTE2/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <style>
        :root {
            --brand: #a4193d;
            --brand-dark: #7f1230;
            --brand-soft: #f9e9ee;
            --surface: #ffffff;
            --surface-muted: #f6f7fb;
            --line: #e4e8f0;
            --text: #263445;
            --muted: #637286;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Poppins", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at 0% 0%, #f5d9e2 0%, transparent 42%),
                linear-gradient(180deg, #f7f8fc 0%, #f2f4f8 100%);
            color: var(--text);
        }

        .wrap {
            max-width: 920px;
            margin: 0 auto;
            padding: 22px 16px 52px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 14px;
            box-shadow: 0 6px 16px rgba(31, 46, 85, 0.08);
        }

        .brand-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-inline img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.14);
        }

        .brand-inline strong {
            font-size: 14px;
            letter-spacing: 0.4px;
        }

        .badge-pos {
            background: var(--brand-soft);
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .hero {
            background: linear-gradient(120deg, var(--brand) 0%, var(--brand-dark) 80%);
            color: #fff;
            border-radius: 16px;
            padding: 24px 22px;
            box-shadow: 0 16px 34px rgba(127, 18, 48, 0.3);
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 30px;
            line-height: 1.2;
            letter-spacing: 0.6px;
        }

        .hero p {
            margin: 0;
            font-size: 15px;
            opacity: 0.98;
            max-width: 640px;
        }

        .hero-tags {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .hero-tags span {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 500;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 8px 22px rgba(31, 46, 85, 0.08);
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 17px;
            color: var(--brand-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card p,
        .card li {
            margin: 0;
            font-size: 14px;
            color: var(--muted);
            line-height: 1.7;
        }

        .card ul {
            margin: 0;
            padding-left: 18px;
        }

        .note {
            margin-top: 14px;
            background: var(--surface-muted);
            border: 1px dashed #cbd4e3;
            border-radius: 12px;
            padding: 12px 14px;
            color: var(--muted);
            font-size: 13px;
        }

        .note strong {
            color: var(--brand-dark);
        }

        .actions {
            margin-top: 14px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .cta {
            display: inline-block;
            text-decoration: none;
            background: var(--brand);
            color: #fff;
            border: 1px solid var(--brand);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
        }

        .cta:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .cta.alt {
            background: #fff;
            color: var(--brand-dark);
            border-color: #d3d9e6;
        }

        .cta.alt:hover {
            background: #f4f6fb;
            color: var(--brand-dark);
        }

        @media (max-width: 480px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .hero h1 {
                font-size: 25px;
            }

            .hero p {
                font-size: 14px;
            }

            .cta {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <div class="brand-inline">
                <img src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati">
                <strong>OPTIK MELATI</strong>
            </div>
            <span class="badge-pos">POS OPTIK MELATI</span>
        </div>

        <section class="hero">
            <h1>Optik Melati</h1>
            <p>Pusat layanan kacamata, lensa, dan pemeriksaan mata dengan pelayanan ramah dan cepat.</p>
            <div class="hero-tags">
                <span>Pelayanan Ramah</span>
                <span>Pengerjaan Presisi</span>
                <span>Kualitas Terjamin</span>
            </div>
        </section>

        <section class="grid">
            <article class="card">
                <h2><i class="fa fa-check-circle"></i> Layanan Kami</h2>
                <ul>
                    <li>Pemeriksaan mata dasar</li>
                    <li>Frame dan lensa berbagai kebutuhan</li>
                    <li>Layanan BPJS sesuai ketentuan</li>
                    <li>Perbaikan dan penyesuaian kacamata</li>
                </ul>
            </article>

            <article class="card">
                <h2><i class="fa fa-heart"></i> Komitmen</h2>
                <p>Kami mengutamakan kualitas produk, ketelitian pengerjaan, dan kenyamanan pelanggan di setiap kunjungan.</p>
            </article>

            <article class="card">
                <h2><i class="fa fa-phone"></i> Kontak</h2>
                <p>Untuk info jadwal dan cabang aktif, silakan hubungi admin toko Optik Melati terdekat.</p>
            </article>
        </section>

        <div class="note">
            <strong>Info QR:</strong> QR ini mengarah ke halaman profil perusahaan.
            @if(!empty($barcode))
                Referensi kunjungan: {{ $barcode }}.
            @endif
            Untuk melihat detail transaksi, gunakan fitur scanner di aplikasi internal Optik Melati.
        </div>

        <div class="actions">
            <a href="{{ url('/') }}" class="cta">Masuk ke Aplikasi</a>
            <a href="https://wa.me" class="cta alt" rel="noopener noreferrer" target="_blank">Buka WhatsApp</a>
        </div>
    </div>
</body>
</html>
