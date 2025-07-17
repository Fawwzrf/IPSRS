<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DashboardModel extends Model
{
    protected static $nav_sess;

    public function __construct()
    {
        parent::__construct();
        $this->initSession();
    }

    protected function initSession()
    {
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }
    }

    public function getCountKomplainBaru()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM permintaan_komplain WHERE status = 'baru' AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            Log::error('Error in getCountKomplainBaru: ' . $e->getMessage());
            return 0;
        }
    }

    public function getCountOrderKerjaAktif()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM order_kerja WHERE status IN ('ditugaskan', 'diproses', 'menunggu_sparepart') AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            Log::error('Error in getCountOrderKerjaAktif: ' . $e->getMessage());
            return 0;
        }
    }

    public function getCountAsetPerbaikan()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM asset WHERE status = 'perbaikan' AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            Log::error('Error in getCountAsetPerbaikan: ' . $e->getMessage());
            return 0;
        }
    }

    public function getCountSparepartKritis()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM mst_sparepart WHERE stok <= stok_min AND deleted_st = 0";
            $result = DbModel::rawData('row_array', $sql);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            Log::error('Error in getCountSparepartKritis: ' . $e->getMessage());
            return 0;
        }
    }

    public function getCountJadwalBelumDibuatOK()
    {
        try {
            // Menggunakan subquery untuk konsistensi format dengan modul acuan
            $sql = "SELECT COUNT(*) as total FROM (
                    SELECT jp.jadwal_pm_id
                    FROM jadwal_pm jp
                    WHERE jp.deleted_st = 0 
                    AND jp.jadwal_pm_id NOT IN (
                        SELECT jadwal_pm_id FROM order_kerja 
                        WHERE jadwal_pm_id IS NOT NULL AND deleted_st = 0
                    )
                ) x";
            $result = DbModel::rawData('row_array', $sql);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            Log::error('Error in getCountJadwalBelumDibuatOK: ' . $e->getMessage());
            return 0;
        }
    }

    public function getChartKomplainHarian()
    {
        try {
            // Menggunakan subquery untuk konsistensi format
            $sql = "SELECT * FROM (
                    SELECT DATE(tgl) as tanggal, count(*) as jumlah 
                    FROM permintaan_komplain
                    WHERE tgl >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(tgl)
                    ORDER BY DATE(tgl) ASC
                ) x";
            return DbModel::rawData('result_array', $sql);
        } catch (\Exception $e) {
            Log::error('Error in getChartKomplainHarian: ' . $e->getMessage());
            return [];
        }
    }

    public function getUrgentJobs()
    {
        try {
            // Menggunakan subquery untuk konsistensi format
            $sql = "SELECT * FROM (
                    SELECT 
                        ok.order_kerja_id, 
                        COALESCE(pk.deskripsi, 'Pemeliharaan Rutin') as deskripsi, 
                        a.asset_nm 
                    FROM order_kerja ok
                    LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                    LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                    LEFT JOIN asset a ON (pk.asset_id = a.asset_id OR jp.asset_id = a.asset_id)
                    WHERE ok.status IN ('baru', 'ditugaskan') 
                    AND ok.prioritas = 'darurat'
                    AND ok.deleted_st = 0
                    ORDER BY ok.tgl_dibuat ASC
                    LIMIT 5
                ) x";
            return DbModel::rawData('result_array', $sql);
        } catch (\Exception $e) {
            Log::error('Error in getUrgentJobs: ' . $e->getMessage());
            return [];
        }
    }
}
