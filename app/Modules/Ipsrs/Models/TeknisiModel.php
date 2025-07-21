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

    protected static function initSession()
    {
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }
    }

    /**
     * Menghitung jumlah tugas dengan status tertentu menggunakan Raw Query.
     * 
     * @param string $teknisi_id ID teknisi
     * @param string $status Status tugas
     * @return int Jumlah tugas
     */
    public function getCountTugasByStatus($teknisi_id, $status)
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = ? AND deleted_st = 0";

            $result = DbModel::rawData('row_array', $sql, [$teknisi_id, $status]);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            Log::error('Error getCountTugasByStatus: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mengambil daftar tugas dengan status tertentu menggunakan Raw Query.
     * 
     * @param string $teknisi_id ID teknisi
     * @param string $status Status tugas
     * @param int|null $limit Batasan jumlah data
     * @return array Daftar tugas
     */
    public function getListTugasByStatus($teknisi_id, $status, $limit = null)
    {
        try {
            $sql = "SELECT pt.penugasan_id, pt.catatan_penolakan, ok.order_kerja_id, a.asset_id, a.asset_nm, l.lokasi_nm, ok.jenis, ok.prioritas, ok.permintaan_id,
            ok.jadwal_pm_id, ok.tgl_dibuat,
                        COALESCE(pk.deskripsi, 'Pemeliharaan Rutin') as deskripsi
                    FROM penugasan_teknisi pt
                    JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                    LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                    LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                    LEFT JOIN asset a ON pk.asset_id = a.asset_id OR jp.asset_id = a.asset_id
                    LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
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

    public function getDetailTugas($penugasan_id)
    {
        $sql = "SELECT 
                    pt.*,
                    ok.order_kerja_id, 
                    a.asset_id, a.asset_nm, 
                    l.lokasi_nm, 
                    ok.jenis, 
                    ok.tgl_dibuat,
                    pk.deskripsi,
                    pk.anotasi_url,
                    pelapor.pegawai_nm as nama_pelapor,
                    jp.frekuensi,
                    jp.jenis as jenis_pemeliharaan,
                    jp.tgl_berikutnya as tgl_pemeliharaan,
                    jp.deskripsi as deskripsi_pemeliharaan
                FROM penugasan_teknisi pt
                JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                LEFT JOIN asset a ON pk.asset_id = a.asset_id OR jp.asset_id = a.asset_id
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                LEFT JOIN mst_pegawai pelapor ON pk.pegawai_id = pelapor.pegawai_id
                WHERE pt.penugasan_id = ? AND pt.deleted_st = 0";

        return DbModel::rawData('row_array', $sql, [$penugasan_id]);
    }

    /**
     * Update status penugasan teknisi
     */
    public static function updateStatusPenugasan($penugasan_id, $status, $alasan = null)
    {
        try {
            // Update status penugasan teknisi
            $update_data = [
                'status' => $status,
                'catatan_penolakan' => $alasan,
                'updated_at' => now(),
                'updated_by' => session('user_name')
            ];
            DbModel::updateData('penugasan_teknisi', $update_data, ['penugasan_id' => $penugasan_id]);

            // Ambil order_kerja_id terkait
            $penugasan = DbModel::getData('penugasan_teknisi', ['penugasan_id' => $penugasan_id]);
            $order_kerja_id = $penugasan['order_kerja_id'] ?? null;

            // Jika status sedang_dikerjakan, update order kerja ke diproses
            if ($order_kerja_id && $status === 'sedang_dikerjakan') {
                DbModel::updateData('order_kerja', [
                    'status' => 'diproses',
                    'updated_at' => now(),
                    'updated_by' => session('user_name')
                ], ['order_kerja_id' => $order_kerja_id]);
            }

            // Jika semua penugasan dibatalkan, update order kerja ke dibatalkan
            if ($order_kerja_id) {
                $count_aktif = DbModel::rawData('row_array',
                    "SELECT COUNT(*) as total FROM penugasan_teknisi WHERE order_kerja_id = ? AND status != 'dibatalkan' AND deleted_st = 0",
                    [$order_kerja_id]
                )['total'] ?? 0;

                if ($count_aktif == 0) {
                    DbModel::updateData('order_kerja', [
                        'status' => 'dibatalkan',
                        'catatan' => $alasan,
                        'updated_at' => now(),
                        'updated_by' => session('user_name')
                    ], ['order_kerja_id' => $order_kerja_id]);
                } else {
                    // Jika semua penugasan statusnya "ditugaskan", order kerja juga jadi "ditugaskan"
                    $count_ditugaskan = DbModel::rawData('row_array',
                        "SELECT COUNT(*) as total FROM penugasan_teknisi WHERE order_kerja_id = ? AND status = 'ditugaskan' AND deleted_st = 0",
                        [$order_kerja_id]
                    )['total'] ?? 0;

                    $count_total = DbModel::rawData('row_array',
                        "SELECT COUNT(*) as total FROM penugasan_teknisi WHERE order_kerja_id = ? AND deleted_st = 0",
                        [$order_kerja_id]
                    )['total'] ?? 0;

                    if ($count_ditugaskan == $count_total) {
                        DbModel::updateData('order_kerja', [
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

    /**
     * Menghitung jumlah tugas yang selesai dalam bulan ini
     */
    public function getCountCompletedTasksThisMonth($teknisi_id)
    {
        $start_date = date('Y-m-01 00:00:00'); // Awal bulan ini
        $end_date = date('Y-m-t 23:59:59');   // Akhir bulan ini

        $sql = "SELECT COUNT(*) as total 
                FROM penugasan_teknisi 
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

    /**
     * Menghitung jumlah tugas yang mendesak (prioritas tinggi dan darurat)
     */
    public function getCountUrgentTasks($teknisi_id)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM penugasan_teknisi pt
                JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                WHERE pt.pegawai_id = ? 
                AND pt.status IN ('ditugaskan', 'sedang_dikerjakan') 
                AND ok.prioritas IN ('tinggi', 'darurat')
                AND pt.deleted_st = 0";

        $result = DbModel::rawData('row_array', $sql, [$teknisi_id]);
        return $result['total'] ?? 0;
    }

    /**
     * Mengambil jadwal pemeliharaan yang akan datang
     */
    public function getUpcomingMaintenanceTasks($teknisi_id, $limit = 5)
    {
        $today = date('Y-m-d');
        $next_month = date('Y-m-d', strtotime('+30 days'));

        $sql = "SELECT jp.jadwal_pm_id, jp.tgl_berikutnya, a.asset_nm, l.lokasi_nm, jp.deskripsi
                FROM jadwal_pm jp
                JOIN asset a ON jp.asset_id = a.asset_id
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                WHERE jp.tgl_berikutnya BETWEEN ? AND ?
                AND jp.status = 'aktif'
                AND a.lokasi_id IN (
                    SELECT DISTINCT a2.lokasi_id 
                    FROM penugasan_teknisi pt
                    JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                    LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                    LEFT JOIN jadwal_pm jp2 ON ok.jadwal_pm_id = jp2.jadwal_pm_id
                    LEFT JOIN asset a2 ON pk.asset_id = a2.asset_id OR jp2.asset_id = a2.asset_id
                    WHERE pt.pegawai_id = ? AND pt.deleted_st = 0
                )
                ORDER BY jp.tgl_berikutnya ASC
                LIMIT ?";

        $result = DbModel::rawData('result_array', $sql, [$today, $next_month, $teknisi_id, $limit]);
        return is_array($result) ? $result : []; // Pastikan selalu mengembalikan array
    }

    /**
     * Mendapatkan data untuk grafik performa
     */
    public function getPerformanceChartData($teknisi_id)
    {
        $result = [
            'selesai' => [],
            'baru' => []
        ];

        // Mendapatkan 4 minggu terakhir
        $today = date('Y-m-d');
        for ($i = 4; $i > 0; $i--) {
            $week_start = date('Y-m-d', strtotime("-{$i} week", strtotime($today)));
            $week_end = date('Y-m-d', strtotime("-" . ($i - 1) . " week -1 day", strtotime($today)));

            // Tugas selesai
            $sql_selesai = "SELECT COUNT(*) as total 
                    FROM penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'selesai' 
                    AND tgl_selesai BETWEEN ? AND ?
                    AND deleted_st = 0";
            $data_selesai = DbModel::rawData('row_array', $sql_selesai, [$teknisi_id, $week_start, $week_end]);
            $result['selesai'][] = $data_selesai['total'] ?? 0;

            // Tugas baru
            $sql_baru = "SELECT COUNT(*) as total 
                    FROM penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'ditugaskan' 
                    AND tgl_penugasan BETWEEN ? AND ?
                    AND deleted_st = 0";
            $data_baru = DbModel::rawData('row_array', $sql_baru, [$teknisi_id, $week_start, $week_end]);
            $result['baru'][] = $data_baru['total'] ?? 0;
        }

        // Pastikan selalu mengembalikan array dengan struktur yang benar
        if (!isset($result) || !is_array($result)) {
            return ['selesai' => [0, 0, 0, 0], 'baru' => [0, 0, 0, 0]];
        }

        return $result;
    }

    /**
     * Mengambil sparepart yang paling sering digunakan oleh teknisi
     */
    public function getMostUsedSpareparts($teknisi_id, $limit = 5)
    {
        $sql = "SELECT s.sparepart_id, s.sparepart_nm, s.stok, s.stok_min, 
                COUNT(ps.penggunaan_id) as jumlah_pakai, SUM(ps.jumlah) as total_jumlah
                FROM mst_sparepart s
                JOIN penggunaan_sparepart ps ON s.sparepart_id = ps.sparepart_id
                JOIN log_kerja lk ON ps.log_kerja_id = lk.log_kerja_id
                WHERE lk.teknisi_pegawai_id = ?
                GROUP BY s.sparepart_id, s.sparepart_nm, s.stok, s.stok_min
                ORDER BY jumlah_pakai DESC
                LIMIT ?";

        $result = DbModel::rawData('result_array', $sql, [$teknisi_id, $limit]);
        return is_array($result) ? $result : []; // Pastikan selalu mengembalikan array
    }

    /**
     * Mendapatkan data untuk dashboard teknisi seperti pendekatan dashboard admin
     * 
     * @param string $teknisi_id ID teknisi
     * @return array Data dashboard
     */
    public function getDashboardData($teknisi_id)
    {
        try {
            $data = [];

            // Tugas baru (ditugaskan)
            $sql = "SELECT COUNT(*) as total FROM penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'ditugaskan' AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql, [$teknisi_id]);
            $data['tugas_baru_count'] = $result['total'] ?? 0;

            // Tugas aktif (sedang dikerjakan)
            $sql = "SELECT pt.penugasan_id, ok.order_kerja_id, a.asset_nm, l.lokasi_nm, ok.prioritas, 
                    ok.tgl_dibuat, COALESCE(pk.deskripsi, jp.deskripsi, 'Pemeliharaan Rutin') as deskripsi
                    FROM penugasan_teknisi pt
                    JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                    LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                    LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                    LEFT JOIN asset a ON (pk.asset_id = a.asset_id OR jp.asset_id = a.asset_id)
                    LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                    WHERE pt.pegawai_id = ? AND pt.status = 'sedang_dikerjakan' AND pt.deleted_st = 0
                    ORDER BY ok.tgl_dibuat DESC LIMIT 5";
            $data['tugas_aktif_list'] = DbModel::rawData('result_array', $sql, [$teknisi_id]) ?: [];

            // Tugas selesai bulan ini
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
            $sql = "SELECT COUNT(*) as total FROM penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'selesai' 
                    AND (
                        (tgl_selesai IS NOT NULL AND DATE(tgl_selesai) BETWEEN ? AND ?) 
                        OR 
                        (tgl_selesai IS NULL AND updated_at BETWEEN ? AND ? AND status = 'selesai')
                    )
                    AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql, [$teknisi_id, $start_date, $end_date, $start_date, $end_date]);
            $data['tugas_selesai_count'] = $result['total'] ?? 0;

            // Tugas mendesak
            $sql = "SELECT COUNT(*) as total FROM penugasan_teknisi pt
                    JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                    WHERE pt.pegawai_id = ? AND pt.status IN ('ditugaskan','sedang_dikerjakan') 
                    AND ok.prioritas IN ('tinggi','darurat') AND pt.deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql, [$teknisi_id]);
            $data['tugas_mendesak_count'] = $result['total'] ?? 0;

            // Tugas ditolak
            $sql = "SELECT COUNT(*) as total FROM penugasan_teknisi 
                    WHERE pegawai_id = ? AND status = 'dibatalkan' AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql, [$teknisi_id]);
            $data['tugas_ditolak_count'] = $result['total'] ?? 0;

            // Jadwal pemeliharaan mendatang
            $today = date('Y-m-d');
            $next_month = date('Y-m-d', strtotime('+30 days'));
            $sql = "SELECT jp.jadwal_pm_id, jp.tgl_berikutnya, a.asset_nm, l.lokasi_nm, 
                    COALESCE(jp.deskripsi, 'Pemeliharaan Rutin') as deskripsi
                    FROM jadwal_pm jp
                    JOIN asset a ON jp.asset_id = a.asset_id
                    LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                    WHERE jp.tgl_berikutnya BETWEEN ? AND ? AND jp.status = 'aktif'
                    AND jp.jadwal_pm_id IN (
                        SELECT pt.jadwal_pm_id
                        FROM penugasan_teknisi pt
                        WHERE pt.pegawai_id = ?
                    )
                    ))
                    ORDER BY jp.tgl_berikutnya ASC LIMIT 5";
            $data['jadwal_mendatang'] = DbModel::rawData('result_array', $sql, [$today, $next_month, $teknisi_id, $teknisi_id]) ?: [];

            // Data untuk chart kinerja
            $data['chart_kinerja'] = $this->getSimplePerformanceChartData($teknisi_id);

            // Sparepart yang sering digunakan
            $sql = "SELECT s.sparepart_id, s.sparepart_nm, s.stok, COALESCE(s.stok_min, 0) as stok_min,
                    COUNT(ps.penggunaan_id) as jumlah_pakai
                    FROM mst_sparepart s
                    JOIN penggunaan_sparepart ps ON s.sparepart_id = ps.sparepart_id
                    JOIN log_kerja lk ON ps.log_kerja_id = lk.log_kerja_id
                    WHERE lk.teknisi_pegawai_id = ? OR lk.teknisi_pegawai_id IN (
                        SELECT pegawai_id FROM penugasan_teknisi 
                        WHERE order_kerja_id IN (
                            SELECT order_kerja_id FROM penugasan_teknisi WHERE pegawai_id = ?
                        )
                    )
                    GROUP BY s.sparepart_id, s.sparepart_nm, s.stok, s.stok_min
                    ORDER BY jumlah_pakai DESC LIMIT 5";
            $data['top_spareparts'] = DbModel::rawData('result_array', $sql, [$teknisi_id, $teknisi_id]) ?: [];

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

    /**
     * Mendapatkan data chart kinerja dengan query lebih sederhana
     */
    public function getSimplePerformanceChartData($teknisi_id)
    {
        $result = [
            'selesai' => [0, 0, 0, 0],
            'baru' => [0, 0, 0, 0]
        ];

        // Mendapatkan tanggal awal bulan ini
        $first_day_of_month = date('Y-m-01');

        // Untuk tugas selesai per minggu dalam bulan ini
        $sql = "SELECT 
                    FLOOR(DATEDIFF(tgl_selesai, '$first_day_of_month') / 7) as week_idx,
                    COUNT(*) as total
                FROM penugasan_teknisi
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

        // Untuk tugas baru per minggu dalam bulan ini
        $sql = "SELECT 
                    FLOOR(DATEDIFF(tgl_mulai, '$first_day_of_month') / 7) as week_idx,
                    COUNT(*) as total
                FROM penugasan_teknisi
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

    /**
     * Mendapatkan data penugasan berdasarkan ID
     * 
     * @param string|int $penugasan_id ID Penugasan
     * @return array|null Data penugasan atau null jika tidak ditemukan
     */
    public function getPenugasanById($penugasan_id)
    {
        try {
            $sql = "SELECT pt.*, ok.order_kerja_id, ok.jenis, ok.prioritas, ok.tgl_dibuat,
                      COALESCE(pk.deskripsi, jp.deskripsi, 'Pemeliharaan Rutin') as deskripsi
                   FROM penugasan_teknisi pt
                   JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                   LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                   LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                   WHERE pt.penugasan_id = ? AND pt.deleted_st = 0";

            $result = DbModel::rawData('row_array', $sql, [$penugasan_id]);
            return $result ?: null;
        } catch (Exception $e) {
            // Log error jika diperlukan
            return null;
        }
    }

    /**
     * Verifikasi barcode aset dengan order kerja
     * 
     * @param string|int $order_kerja_id ID Order Kerja
     * @param string $barcode Barcode yang di-scan
     * @return array Status verifikasi dan pesan
     */
    public function verifyAssetBarcode($order_kerja_id, $barcode)
    {
        try {
            // Cari asset_id berdasarkan barcode
            $assetResult = DbModel::rawData(
                'row_array',
                "SELECT asset_id, asset_nm FROM asset WHERE barcode = ? AND deleted_st = 0",
                [$barcode]
            );

            if (!$assetResult) {
                return [
                    'success' => false,
                    'msg' => 'Barcode tidak terdaftar dalam sistem.'
                ];
            }

            // Ambil asset_id dari order kerja (baik dari permintaan maupun jadwal PM)
            $orderAssetResult = DbModel::rawData(
                'row_array',
                "SELECT 
                    COALESCE(pk.asset_id, jp.asset_id) as asset_id 
                 FROM order_kerja ok
                 LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                 LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                 WHERE ok.order_kerja_id = ?",
                [$order_kerja_id]
            );

            if (!$orderAssetResult || !$orderAssetResult['asset_id']) {
                return [
                    'success' => false,
                    'msg' => 'Tidak dapat menemukan aset terkait order kerja ini.'
                ];
            }

            // Verifikasi apakah asset_id dari barcode sama dengan asset_id dari order kerja
            if ($assetResult['asset_id'] == $orderAssetResult['asset_id']) {
                return [
                    'success' => true,
                    'asset_id' => $assetResult['asset_id'],
                    'asset_nm' => $assetResult['asset_nm'],
                    'msg' => 'Barcode terverifikasi dengan benar.'
                ];
            }

            return [
                'success' => false,
                'msg' => 'Barcode yang di-scan tidak sesuai dengan aset yang tercatat pada order kerja ini.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
