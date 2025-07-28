<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeknisiModel extends Model
{
    protected static $nav_sess;

    public function __construct()
    {
        parent::__construct();
        self::initSession();
    }

    // Inisialisasi session navigasi
    protected static function initSession()
    {
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }
    }

    // Menghitung jumlah tugas dengan status tertentu
    public function getCountTugasByStatus($teknisi_id, $status)
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM trx_penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = ? AND deleted_st = 0";

            $result = DbModel::rawData('row_array', $sql, [$teknisi_id, $status]);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            Log::error('Error getCountTugasByStatus: ' . $e->getMessage());
            return 0;
        }
    }

    // Mengambil daftar tugas dengan status tertentu
    public function getListTugasByStatus($teknisi_id, $status, $limit = null)
    {
        try {
            $sql = "SELECT pt.penugasan_id, pt.catatan_penolakan, ok.order_kerja_id, 
                        COALESCE(a_pk.asset_id, a_jp.asset_id) as asset_id, 
                        COALESCE(a_pk.asset_nm, a_jp.asset_nm) as asset_nm, 
                        COALESCE(l.lokasi_nm, l2.lokasi_nm) as lokasi_nm, 
                        ok.jenis, ok.prioritas, ok.permintaan_id,
                        ok.jadwal_pm_id, ok.tgl_dibuat,
                        COALESCE(pk.deskripsi, jp.deskripsi, 'Pemeliharaan Rutin') as deskripsi
                    FROM trx_penugasan_teknisi pt
                    JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                    LEFT JOIN trx_permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                    LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                    LEFT JOIN mst_asset a_pk ON pk.asset_id = a_pk.asset_id
                    LEFT JOIN mst_asset a_jp ON jp.asset_id = a_jp.asset_id
                    LEFT JOIN mst_lokasi l ON a_pk.lokasi_id = l.lokasi_id
                    LEFT JOIN mst_lokasi l2 ON a_jp.lokasi_id = l2.lokasi_id
                    WHERE pt.pegawai_id = ? AND pt.status = ? AND pt.deleted_st = 0
                    ORDER BY ok.tgl_dibuat DESC";

            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }

            $result = DbModel::rawData('result_array', $sql, [$teknisi_id, $status]);
            return is_array($result) ? $result : [];
        } catch (\Exception $e) {
            Log::error('Error getListTugasByStatus: ' . $e->getMessage());
            return [];
        }
    }

    // Mengambil detail tugas berdasarkan penugasan_id
    public function getDetailTugas($penugasan_id)
    {
        $sql = "SELECT 
                    pt.*,
                    ok.order_kerja_id, 
                    -- Pilih asset dari pk jika ada, jika tidak dari jp
                    CASE 
                        WHEN pk.asset_id IS NOT NULL THEN pk.asset_id
                        ELSE jp.asset_id
                    END AS asset_id,
                    CASE 
                        WHEN pk.asset_id IS NOT NULL THEN a_pk.asset_nm
                        ELSE a_jp.asset_nm
                    END AS asset_nm,
                    -- Pilih lokasi dari pk jika ada, jika tidak dari jp
                    CASE 
                        WHEN pk.asset_id IS NOT NULL THEN l.lokasi_nm
                        ELSE l2.lokasi_nm
                    END AS lokasi_nm,
                    ok.jenis, 
                    ok.tgl_dibuat,
                    pk.deskripsi,
                    pk.anotasi_url,
                    pelapor.pegawai_nm as nama_pelapor,
                    jp.frekuensi,
                    jp.jenis as jenis_pemeliharaan,
                    jp.tgl_berikutnya as tgl_pemeliharaan,
                    jp.deskripsi as deskripsi_pemeliharaan
                FROM trx_penugasan_teknisi pt
                JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                LEFT JOIN trx_permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                LEFT JOIN mst_asset a_pk ON pk.asset_id = a_pk.asset_id
                LEFT JOIN mst_asset a_jp ON jp.asset_id = a_jp.asset_id
                LEFT JOIN mst_lokasi l ON a_pk.lokasi_id = l.lokasi_id
                LEFT JOIN mst_lokasi l2 ON a_jp.lokasi_id = l2.lokasi_id
                LEFT JOIN mst_pegawai pelapor ON pk.pegawai_id = pelapor.pegawai_id
                WHERE pt.penugasan_id = ? AND pt.deleted_st = 0";

        return DbModel::rawData('row_array', $sql, [$penugasan_id]);
    }

    // Update status penugasan teknisi
    public static function updateStatusPenugasan($penugasan_id, $status, $alasan = null)
    {
        try {
            $update_data = [
                'status' => $status,
                'updated_at' => now(),
                'updated_by' => session('user_name')
            ];
            DbModel::updateData('trx_penugasan_teknisi', $update_data, ['penugasan_id' => $penugasan_id]);

            $penugasan = DbModel::getData('trx_penugasan_teknisi', ['penugasan_id' => $penugasan_id]);
            $order_kerja_id = $penugasan['order_kerja_id'] ?? null;

            if ($order_kerja_id && $status === 'sedang_dikerjakan') {
                DbModel::updateData('trx_order_kerja', [
                    'status' => 'diproses',
                    'updated_at' => now(),
                    'updated_by' => session('user_name')
                ], ['order_kerja_id' => $order_kerja_id]);
            }

            if ($order_kerja_id) {
                $count_aktif = DbModel::rawData(
                    'row_array',
                    "SELECT COUNT(*) as total FROM trx_penugasan_teknisi WHERE order_kerja_id = ? AND status != 'dibatalkan' AND deleted_st = 0",
                    [$order_kerja_id]
                )['total'] ?? 0;

                if ($count_aktif == 0) {
                    DbModel::updateData('trx_order_kerja', [
                        'status' => 'dibatalkan',
                        'updated_at' => now(),
                        'updated_by' => session('user_name')
                    ], ['order_kerja_id' => $order_kerja_id]);
                } else {
                    $count_ditugaskan = DbModel::rawData(
                        'row_array',
                        "SELECT COUNT(*) as total FROM trx_penugasan_teknisi WHERE order_kerja_id = ? AND status = 'ditugaskan' AND deleted_st = 0",
                        [$order_kerja_id]
                    )['total'] ?? 0;

                    $count_total = DbModel::rawData(
                        'row_array',
                        "SELECT COUNT(*) as total FROM trx_penugasan_teknisi WHERE order_kerja_id = ? AND deleted_st = 0",
                        [$order_kerja_id]
                    )['total'] ?? 0;

                    if ($count_ditugaskan == $count_total) {
                        DbModel::updateData('trx_order_kerja', [
                            'status' => 'ditugaskan',
                            'updated_at' => now(),
                            'updated_by' => session('user_name')
                        ], ['order_kerja_id' => $order_kerja_id]);
                    }
                }
            }
            return ['success' => true];
        } catch (\Exception $e) {
            \Log::error('Error updateStatusPenugasan: ' . $e->getMessage());
            return ['success' => false, 'msg' => $e->getMessage()];
        }
    }

    // Menghitung jumlah tugas yang selesai dalam bulan ini
    public function getCountCompletedTasksThisMonth($teknisi_id)
    {
        $start_date = date('Y-m-01 00:00:00');
        $end_date = date('Y-m-t 23:59:59');

        $sql = "SELECT COUNT(*) as total 
                FROM trx_penugasan_teknisi 
                WHERE pegawai_id = ? AND status = 'selesai' 
                AND (
                    (tgl_selesai BETWEEN ? AND ?) 
                    OR 
                    (tgl_selesai IS NULL AND updated_at BETWEEN ? AND ?)
                )
                AND deleted_st = 0";

        $result = DbModel::rawData('row_array', $sql, [
            $teknisi_id,
            $start_date,
            $end_date,
            $start_date,
            $end_date
        ]);
        return $result['total'] ?? 0;
    }

    // Menghitung jumlah tugas yang mendesak (prioritas tinggi dan darurat)
    public function getCountUrgentTasks($teknisi_id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM trx_penugasan_teknisi pt
                JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                WHERE pt.pegawai_id = ? 
                AND pt.status IN ('ditugaskan', 'sedang_dikerjakan') 
                AND ok.prioritas IN ('tinggi', 'darurat')
                AND pt.deleted_st = 0";

        $result = DbModel::rawData('row_array', $sql, [$teknisi_id]);
        return $result['total'] ?? 0;
    }

    // Mengambil jadwal pemeliharaan yang akan datang
    public function getUpcomingMaintenanceTasks($teknisi_id, $limit = 5)
    {
        $today = date('Y-m-d');
        $next_month = date('Y-m-d', strtotime('+30 days'));

        $sql = "SELECT 
                    jp.jadwal_pm_id, 
                    jp.tgl_berikutnya, 
                    COALESCE(a.asset_id, NULL) AS asset_id,
                    COALESCE(a.asset_nm, NULL) AS asset_nm,
                    COALESCE(l.lokasi_nm, NULL) AS lokasi_nm,
                    COALESCE(jp.deskripsi, 'Pemeliharaan Rutin') as deskripsi
                FROM trx_jadwal_pm jp
                LEFT JOIN mst_asset a ON jp.asset_id = a.asset_id
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                WHERE jp.tgl_berikutnya BETWEEN ? AND ?
                  AND jp.status = 'aktif'
                  AND jp.jadwal_pm_id IN (
                      SELECT pt.jadwal_pm_id
                      FROM trx_penugasan_teknisi pt
                      WHERE pt.pegawai_id = ?
                  )
                ORDER BY jp.tgl_berikutnya ASC
                LIMIT ?";

        $result = DbModel::rawData('result_array', $sql, [$today, $next_month, $teknisi_id, $limit]);
        return is_array($result) ? $result : [];
    }

    // Mendapatkan data untuk grafik performa
    public function getPerformanceChartData($teknisi_id)
    {
        $result = [
            'selesai' => [],
            'baru' => []
        ];

        $today = date('Y-m-d');
        for ($i = 4; $i > 0; $i--) {
            $week_start = date('Y-m-d', strtotime("-{$i} week", strtotime($today)));
            $week_end = date('Y-m-d', strtotime("-" . ($i - 1) . " week -1 day", strtotime($today)));

            $sql_selesai = "SELECT COUNT(*) as total 
                    FROM trx_penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'selesai' 
                    AND tgl_selesai BETWEEN ? AND ?
                    AND deleted_st = 0";
            $data_selesai = DbModel::rawData('row_array', $sql_selesai, [$teknisi_id, $week_start, $week_end]);
            $result['selesai'][] = $data_selesai['total'] ?? 0;

            $sql_baru = "SELECT COUNT(*) as total 
                    FROM trx_penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'ditugaskan' 
                    AND tgl_penugasan BETWEEN ? AND ?
                    AND deleted_st = 0";
            $data_baru = DbModel::rawData('row_array', $sql_baru, [$teknisi_id, $week_start, $week_end]);
            $result['baru'][] = $data_baru['total'] ?? 0;
        }

        if (!isset($result) || !is_array($result)) {
            return ['selesai' => [0, 0, 0, 0], 'baru' => [0, 0, 0, 0]];
        }

        return $result;
    }

    // Mengambil sparepart yang paling sering digunakan oleh teknisi
    public function getMostUsedSpareparts($teknisi_id, $limit = 5)
    {
        $sql = "SELECT s.sparepart_id, s.sparepart_nm, s.stok, s.stok_min, 
                COUNT(ps.penggunaan_id) as jumlah_pakai, SUM(ps.jumlah) as total_jumlah
                FROM mst_sparepart s
                JOIN trx_penggunaan_sparepart ps ON s.sparepart_id = ps.sparepart_id
                JOIN trx_log_kerja lk ON ps.log_kerja_id = lk.log_kerja_id
                WHERE lk.teknisi_pegawai_id = ?
                GROUP BY s.sparepart_id, s.sparepart_nm, s.stok, s.stok_min
                ORDER BY jumlah_pakai DESC
                LIMIT ?";

        $result = DbModel::rawData('result_array', $sql, [$teknisi_id, $limit]);
        return is_array($result) ? $result : [];
    }

    // Mendapatkan data untuk dashboard teknisi
    public function getDashboardData($teknisi_id)
    {
        try {
            $data = [];

            // Jumlah tugas baru
            $sql = "SELECT COUNT(*) as total FROM trx_penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'ditugaskan' AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql, [$teknisi_id]);
            $data['tugas_baru_count'] = $result['total'] ?? 0;

            // Daftar tugas aktif (sedang dikerjakan), gunakan COALESCE dan alias untuk asset/lokasi
            $sql = "SELECT pt.penugasan_id, ok.order_kerja_id, 
                        COALESCE(a_pk.asset_id, a_jp.asset_id) AS asset_id,
                        COALESCE(a_pk.asset_nm, a_jp.asset_nm) AS asset_nm,
                        COALESCE(l.lokasi_nm, l2.lokasi_nm) AS lokasi_nm,
                        ok.prioritas, ok.tgl_dibuat, 
                        COALESCE(pk.deskripsi, jp.deskripsi, 'Pemeliharaan Rutin') AS deskripsi
                    FROM trx_penugasan_teknisi pt
                    JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                    LEFT JOIN trx_permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                    LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                    LEFT JOIN mst_asset a_pk ON pk.asset_id = a_pk.asset_id
                    LEFT JOIN mst_asset a_jp ON jp.asset_id = a_jp.asset_id
                    LEFT JOIN mst_lokasi l ON a_pk.lokasi_id = l.lokasi_id
                    LEFT JOIN mst_lokasi l2 ON a_jp.lokasi_id = l2.lokasi_id
                    WHERE pt.pegawai_id = ? AND pt.status = 'sedang_dikerjakan' AND pt.deleted_st = 0
                    ORDER BY ok.tgl_dibuat DESC LIMIT 5";
            $data['tugas_aktif_list'] = DbModel::rawData('result_array', $sql, [$teknisi_id]) ?: [];

            // Jumlah tugas selesai bulan ini
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
            $sql = "SELECT COUNT(*) as total FROM trx_penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'selesai' 
                    AND (
                        (tgl_selesai IS NOT NULL AND DATE(tgl_selesai) BETWEEN ? AND ?) 
                        OR 
                        (tgl_selesai IS NULL AND updated_at BETWEEN ? AND ? AND status = 'selesai')
                    )
                    AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql, [$teknisi_id, $start_date, $end_date, $start_date, $end_date]);
            $data['tugas_selesai_count'] = $result['total'] ?? 0;

            // Jumlah tugas mendesak
            $sql = "SELECT COUNT(*) as total FROM trx_penugasan_teknisi pt
                    JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                    WHERE pt.pegawai_id = ? AND pt.status IN ('ditugaskan','sedang_dikerjakan') 
                    AND ok.prioritas IN ('tinggi','darurat') AND pt.deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql, [$teknisi_id]);
            $data['tugas_mendesak_count'] = $result['total'] ?? 0;

            // Jumlah tugas ditolak/dibatalkan
            $sql = "SELECT COUNT(*) as total FROM trx_penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'dibatalkan' AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql, [$teknisi_id]);
            $data['tugas_ditolak_count'] = $result['total'] ?? 0;

            // Jadwal pemeliharaan mendatang, gunakan COALESCE dan alias
            $today = date('Y-m-d');
            $next_month = date('Y-m-d', strtotime('+30 days'));
            $sql = "SELECT jp.jadwal_pm_id, jp.tgl_berikutnya, 
                        COALESCE(a.asset_id, NULL) AS asset_id,
                        COALESCE(a.asset_nm, NULL) AS asset_nm,
                        COALESCE(l.lokasi_nm, NULL) AS lokasi_nm,
                        COALESCE(jp.deskripsi, 'Pemeliharaan Rutin') AS deskripsi
                    FROM trx_jadwal_pm jp
                    LEFT JOIN mst_asset a ON jp.asset_id = a.asset_id
                    LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                    WHERE jp.tgl_berikutnya BETWEEN ? AND ? AND jp.status = 'aktif'
                    AND jp.jadwal_pm_id IN (
                        SELECT pt.jadwal_pm_id
                        FROM trx_penugasan_teknisi pt
                        WHERE pt.pegawai_id = ?
                    )
                    ORDER BY jp.tgl_berikutnya ASC LIMIT 5";
            $data['jadwal_mendatang'] = DbModel::rawData('result_array', $sql, [$today, $next_month, $teknisi_id]) ?: [];

            // Chart kinerja
            $data['chart_kinerja'] = $this->getSimplePerformanceChartData($teknisi_id);

            // Sparepart paling sering dipakai, gunakan COALESCE dan alias
            $sql = "SELECT s.sparepart_id, s.sparepart_nm, s.stok, COALESCE(s.stok_min, 0) AS stok_min,
                        COUNT(ps.penggunaan_id) AS jumlah_pakai
                    FROM mst_sparepart s
                    JOIN trx_penggunaan_sparepart ps ON s.sparepart_id = ps.sparepart_id
                    JOIN trx_log_kerja lk ON ps.log_kerja_id = lk.log_kerja_id
                    WHERE lk.teknisi_pegawai_id = ?
                    GROUP BY s.sparepart_id, s.sparepart_nm, s.stok, s.stok_min
                    ORDER BY jumlah_pakai DESC LIMIT 5";
            $data['top_spareparts'] = DbModel::rawData('result_array', $sql, [$teknisi_id]) ?: [];

            return $data;
        } catch (\Exception $e) {
            Log::error('Error getDashboardData: ' . $e->getMessage());
            return [
                'tugas_baru_count' => 0,
                'tugas_aktif_list' => [],
                'tugas_selesai_count' => 0,
                'tugas_mendesak_count' => 0,
                'tugas_ditolak_count' => 0,
                'jadwal_mendatang' => [],
                'chart_kinerja' => [
                    'selesai' => [0, 0, 0, 0],
                    'baru' => [0, 0, 0, 0]
                ],
                'top_spareparts' => []
            ];
        }
    }

    // Mendapatkan data chart kinerja dengan query sederhana
    public function getSimplePerformanceChartData($teknisi_id)
    {
        $result = [
            'selesai' => [0, 0, 0, 0],
            'baru' => [0, 0, 0, 0]
        ];

        $first_day_of_month = date('Y-m-01');

        $sql = "SELECT 
                    FLOOR(DATEDIFF(tgl_selesai, '$first_day_of_month') / 7) as week_idx,
                    COUNT(*) as total
                FROM trx_penugasan_teknisi
                WHERE pegawai_id = ? 
                    AND status = 'selesai'
                    AND tgl_selesai IS NOT NULL
                    AND MONTH(tgl_selesai) = MONTH(CURRENT_DATE()) 
                    AND YEAR(tgl_selesai) = YEAR(CURRENT_DATE())
                GROUP BY week_idx";

        $data_selesai = DbModel::rawData('result_array', $sql, [$teknisi_id]) ?: [];

        foreach ($data_selesai as $row) {
            $week_idx = min(max(intval($row['week_idx']), 0), 3);
            $result['selesai'][$week_idx] = intval($row['total']);
        }

        $sql = "SELECT 
                    FLOOR(DATEDIFF(tgl_mulai, '$first_day_of_month') / 7) as week_idx,
                    COUNT(*) as total
                FROM trx_penugasan_teknisi
                WHERE pegawai_id = ?
                    AND MONTH(tgl_mulai) = MONTH(CURRENT_DATE()) 
                    AND YEAR(tgl_mulai) = YEAR(CURRENT_DATE())
                GROUP BY week_idx";

        $data_baru = DbModel::rawData('result_array', $sql, [$teknisi_id]) ?: [];

        foreach ($data_baru as $row) {
            $week_idx = min(max(intval($row['week_idx']), 0), 3);
            $result['baru'][$week_idx] = intval($row['total']);
        }

        return $result;
    }

    // Mendapatkan data penugasan berdasarkan ID
    public function getPenugasanById($penugasan_id)
    {
        try {
            $sql = "SELECT pt.*, ok.order_kerja_id, ok.jenis, ok.prioritas, ok.tgl_dibuat,
                      COALESCE(pk.deskripsi, jp.deskripsi, 'Pemeliharaan Rutin') as deskripsi
                   FROM trx_penugasan_teknisi pt
                   JOIN trx_order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                   LEFT JOIN trx_permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                   LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                   WHERE pt.penugasan_id = ? AND pt.deleted_st = 0";

            $result = DbModel::rawData('row_array', $sql, [$penugasan_id]);
            return $result ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    // Mengambil data order kerja berdasarkan ID
    public function getOrderKerja($order_kerja_id)
    {
        $sql = "SELECT * FROM trx_order_kerja WHERE order_kerja_id = ? AND deleted_st = 0";
        return DbModel::rawData('row_array', $sql, [$order_kerja_id]);
    }

    /**
     * Mendapatkan asset_id dari barcode/no_seri atau dari order_kerja_id
     */
    public function getAssetIdByBarcodeOrOrderKerja($barcode, $order_kerja_id)
    {
        // Cari berdasarkan barcode/no_seri
        $asset = DbModel::getData('mst_asset', ['no_seri' => $barcode]);
        if ($asset) {
            return $asset['asset_id'];
        }

        // Alternatif: Cari dari order_kerja
        $order_kerja = DbModel::getData('trx_order_kerja', ['order_kerja_id' => $order_kerja_id]);
        if ($order_kerja) {
            if (!empty($order_kerja['permintaan_id'])) {
                $permintaan = DbModel::getData('trx_permintaan_komplain', ['permintaan_id' => $order_kerja['permintaan_id']]);
                return $permintaan['asset_id'] ?? null;
            } elseif (!empty($order_kerja['jadwal_pm_id'])) {
                $jadwalPm = DbModel::getData('trx_jadwal_pm', ['jadwal_pm_id' => $order_kerja['jadwal_pm_id']]);
                return $jadwalPm['asset_id'] ?? null;
            }
        }
        return null;
    }

    // Mengambil data permintaan komplain berdasarkan ID
    public function getPermintaanKomplain($permintaan_id)
    {
        $sql = "SELECT * FROM trx_permintaan_komplain WHERE permintaan_id = ? AND deleted_st = 0";
        return DbModel::rawData('row_array', $sql, [$permintaan_id]);
    }

    // Mengambil data jadwal PM berdasarkan ID
    public function getJadwalPm($jadwal_pm_id)
    {
        $sql = "SELECT * FROM trx_jadwal_pm WHERE jadwal_pm_id = ? AND deleted_st = 0";
        return DbModel::rawData('row_array', $sql, [$jadwal_pm_id]);
    }

    // Mengambil data log kerja berdasarkan order kerja
    public function getLogKerja($order_kerja_id)
    {
        $sql = "SELECT * FROM trx_log_kerja WHERE order_kerja_id = ? AND deleted_st = 0";
        return DbModel::rawData('row_array', $sql, [$order_kerja_id]);
    }

    // Mengambil semua data sparepart
    public function getAllSparepart()
    {
        $sql = "SELECT * FROM mst_sparepart WHERE deleted_st = 0";
        return DbModel::rawData('result_array', $sql, []);
    }

    // Menyelesaikan order kerja
    public function selesaiPenugasanByOrderKerja($order_kerja_id, $pegawai_id)
    {
        $order = DbModel::getData('trx_order_kerja', ['order_kerja_id' => $order_kerja_id]);
        if ($order) {
            $penugasan = DbModel::getData('trx_penugasan_teknisi', [
                'order_kerja_id' => $order_kerja_id,
                'status' => 'sedang_dikerjakan'
            ]);
            if ($penugasan) {
                $this->updateStatusPenugasan($penugasan['penugasan_id'], 'selesai');
                // Update tgl_selesai dan pegawai_id
                DbModel::updateData('trx_penugasan_teknisi', [
                    'status' => 'selesai',
                    'tgl_selesai' => now(),
                    'updated_at' => now(),
                    'updated_by' => session('user_name'),
                    'pegawai_id' => $pegawai_id
                ], [
                    'order_kerja_id' => $order_kerja_id,
                    'pegawai_id' => $pegawai_id
                ]);
            }
        }
    }

    /**
     * Update status trx_order_kerja ke 'menunggu_sparepart'
     */
    public function setOrderKerjaMenungguSparepart($order_kerja_id)
    {
        return DbModel::updateData(
            'trx_order_kerja',
            [
                'status' => 'menunggu_sparepart',
                'updated_at' => date('Y-m-d H:i:s')
            ],
            ['order_kerja_id' => $order_kerja_id]
        );
    }

    // Mengambil data asset berdasarkan ID
    public function getAsset($asset_id)
    {
        $sql = "SELECT * FROM mst_asset WHERE asset_id = ? AND deleted_st = 0";
        return DbModel::rawData('row_array', $sql, [$asset_id]);
    }

    // Mengambil daftar log kerja berdasarkan asset
    public function getLogKerjaList($asset_id)
    {
        $sql = "SELECT * FROM trx_log_kerja WHERE asset_id = ? AND deleted_st = 0 ORDER BY tgl_mulai DESC";
        return DbModel::rawData('result_array', $sql, [$asset_id]);
    }

    // Mengambil daftar order kerja berdasarkan asset
    public function getOrderKerjaListByAsset($asset_id)
    {
        $sql = "SELECT * FROM trx_order_kerja WHERE (permintaan_id IN (SELECT permintaan_id FROM trx_permintaan_komplain WHERE asset_id = ?) OR jadwal_pm_id IN (SELECT jadwal_pm_id FROM trx_jadwal_pm WHERE asset_id = ?)) AND deleted_st = 0";
        return DbModel::rawData('result_array', $sql, [$asset_id, $asset_id]);
    }

    /**
     * Ambil daftar trx_order_kerja beserta nama teknisi dan deskripsi berdasarkan asset_id
     */
    public function getOrderKerjaByAssetId($asset_id)
    {
        $sql = "SELECT ok.*, p.pegawai_nm as teknisi_nama, 
                   COALESCE(pk.deskripsi, j.deskripsi) as deskripsi
            FROM trx_order_kerja ok
            LEFT JOIN trx_penugasan_teknisi pt ON ok.order_kerja_id = pt.order_kerja_id
            LEFT JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id
            LEFT JOIN trx_permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
            LEFT JOIN trx_jadwal_pm j ON ok.jadwal_pm_id = j.jadwal_pm_id
            WHERE ok.deleted_st = 0
              AND ( (pk.asset_id = ? AND (pk.deleted_st = 0 OR pk.deleted_st IS NULL)) 
                  OR (j.asset_id = ? AND (j.deleted_st = 0 OR j.deleted_st IS NULL)) )
              AND (pt.deleted_st = 0 OR pt.deleted_st IS NULL)
            ORDER BY ok.tgl_dibuat DESC";
        return \App\Modules\App\Models\DbModel::rawData('result_array', $sql, [$asset_id, $asset_id]);
    }
    // ...existing code...

    // Mengambil detail asset berdasarkan ID
    public function getAssetDetail($asset_id)
    {
        $sql = "SELECT a.*, l.lokasi_nm, k.kategori_asset_nm
        FROM mst_asset a
        LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
        LEFT JOIN mst_kategori_asset k ON a.kategori_asset_id = k.kategori_asset_id
        WHERE a.asset_id = ? AND a.deleted_st = 0";

        return DbModel::rawData('row_array', $sql, [$asset_id]);
    }

    // Mengambil data penugasan berdasarkan order kerja
    public function getPenugasanByOrderKerja($order_kerja_id)
    {
        $sql = "SELECT * FROM trx_penugasan_teknisi WHERE order_kerja_id = ? AND deleted_st = 0";
        return DbModel::rawData('row_array', $sql, [$order_kerja_id]);
    }

    // Mengambil data sparepart berdasarkan log kerja
    public function getSparepartByLogKerjaId($log_kerja_id)
    {
        $sql = "SELECT ps.*, s.sparepart_nm 
            FROM trx_penggunaan_sparepart ps
            JOIN mst_sparepart s ON ps.sparepart_id = s.sparepart_id
            WHERE ps.log_kerja_id = ?";
        return \App\Modules\App\Models\DbModel::rawData('result_array', $sql, [$log_kerja_id]);
    }
}
