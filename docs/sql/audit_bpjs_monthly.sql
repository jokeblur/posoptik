-- Audit BPJS Bulanan per Cabang
-- Tujuan:
-- 1) Bandingkan jumlah pasien BPJS versi dashboard kasir
-- 2) Bandingkan pasien BPJS unik yang membeli frame
-- 3) Tampilkan qty/transaksi frame BPJS
--
-- Kompatibel untuk MySQL 8+ (menggunakan CTE).
-- Jika Anda memakai rentang panjang, sesuaikan @start_date/@end_date.

SET @start_date = '2026-01-01';
SET @end_date   = '2026-12-31';

WITH base_tx AS (
    SELECT
        p.id AS penjualan_id,
        p.branch_id,
        p.pasien_id,
        DATE_FORMAT(p.created_at, '%Y-%m') AS periode_bulan,
        UPPER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF(pa.service_type, ''), 'UMUM'))) AS service_type_norm
    FROM penjualan p
    LEFT JOIN pasien pa ON pa.id_pasien = p.pasien_id
    WHERE p.created_at >= @start_date
      AND p.created_at < DATE_ADD(@end_date, INTERVAL 1 DAY)
),
branch_months AS (
    SELECT DISTINCT
        branch_id,
        periode_bulan
    FROM base_tx
),
dashboard_bpjs AS (
    -- Meniru logika dashboard: transaksi BPJS exact list, pasien harus ada, hitung unik pasien
    SELECT
        bt.branch_id,
        bt.periode_bulan,
        COUNT(DISTINCT bt.pasien_id) AS dashboard_bpjs_pasien
    FROM base_tx bt
    WHERE bt.service_type_norm IN ('BPJS I', 'BPJS II', 'BPJS III')
      AND bt.pasien_id IS NOT NULL
    GROUP BY bt.branch_id, bt.periode_bulan
),
frame_bpjs_pasien AS (
    -- Pasien BPJS unik yang membeli item frame
    SELECT
        bt.branch_id,
        bt.periode_bulan,
        COUNT(DISTINCT bt.pasien_id) AS frame_bpjs_pasien_unik
    FROM base_tx bt
    INNER JOIN penjualan_detail pd
        ON pd.penjualan_id = bt.penjualan_id
       AND pd.itemable_type = 'App\\Models\\Frame'
    WHERE bt.service_type_norm IN ('BPJS I', 'BPJS II', 'BPJS III')
      AND bt.pasien_id IS NOT NULL
    GROUP BY bt.branch_id, bt.periode_bulan
),
frame_bpjs_summary AS (
    -- Qty/transaksi frame BPJS
    SELECT
        bt.branch_id,
        bt.periode_bulan,
        COALESCE(SUM(pd.quantity), 0) AS frame_bpjs_qty,
        COUNT(DISTINCT bt.penjualan_id) AS frame_bpjs_transaksi
    FROM base_tx bt
    INNER JOIN penjualan_detail pd
        ON pd.penjualan_id = bt.penjualan_id
       AND pd.itemable_type = 'App\\Models\\Frame'
    WHERE bt.service_type_norm IN ('BPJS I', 'BPJS II', 'BPJS III')
    GROUP BY bt.branch_id, bt.periode_bulan
)
SELECT
    b.id AS branch_id,
    b.name AS branch_name,
    bm.periode_bulan,
    COALESCE(d.dashboard_bpjs_pasien, 0) AS dashboard_bpjs_pasien,
    COALESCE(fp.frame_bpjs_pasien_unik, 0) AS frame_bpjs_pasien_unik,
    COALESCE(fs.frame_bpjs_qty, 0) AS frame_bpjs_qty,
    COALESCE(fs.frame_bpjs_transaksi, 0) AS frame_bpjs_transaksi,
    COALESCE(d.dashboard_bpjs_pasien, 0) - COALESCE(fp.frame_bpjs_pasien_unik, 0) AS selisih_pasien,
    COALESCE(fs.frame_bpjs_qty, 0) - COALESCE(fs.frame_bpjs_transaksi, 0) AS selisih_qty_vs_transaksi
FROM branch_months bm
INNER JOIN branches b
    ON b.id = bm.branch_id
LEFT JOIN dashboard_bpjs d
    ON d.branch_id = bm.branch_id
   AND d.periode_bulan = bm.periode_bulan
LEFT JOIN frame_bpjs_pasien fp
    ON fp.branch_id = bm.branch_id
   AND fp.periode_bulan = bm.periode_bulan
LEFT JOIN frame_bpjs_summary fs
    ON fs.branch_id = bm.branch_id
   AND fs.periode_bulan = bm.periode_bulan
ORDER BY bm.periode_bulan DESC, b.name ASC;

-- Opsional: filter 1 bulan tertentu
-- Tambahkan di query paling akhir sebelum ORDER BY:
-- WHERE bm.periode_bulan = '2026-08';
