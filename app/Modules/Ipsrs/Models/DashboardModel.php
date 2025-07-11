<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class DashboardModel extends Model
{
    public function getCountKomplainBaru()
    {
        $sql = "SELECT COUNT(*) as total FROM permintaan_komplain WHERE status = 'baru' AND deleted_st = 0";
        $result = DbModel::rawData('row_array', $sql);
        return $result['total'] ?? 0;
    }

    public function getCountOrderKerjaAktif()
    {
        $sql = "SELECT COUNT(*) as total FROM order_kerja WHERE status IN ('ditugaskan', 'diproses', 'menunggu_sparepart') AND deleted_st = 0";
        $result = DbModel::rawData('row_array', $sql);
        return $result['total'] ?? 0;
    }

    public function getCountAsetPerbaikan()
    {
        $sql = "SELECT COUNT(*) as total FROM asset WHERE status = 'perbaikan' AND deleted_st = 0";
        $result = DbModel::rawData('row_array', $sql);
        return $result['total'] ?? 0;
    }

    public function getCountSparepartKritis()
    {
        $sql = "SELECT COUNT(*) as total FROM mst_sparepart WHERE stok <= stok_min AND deleted_st = 0";
        $result = DbModel::rawData('row_array', $sql);
        return $result['total'] ?? 0;
    }

    public function getChartKomplainHarian()
    {
        // Mengambil data jumlah komplain per hari untuk 7 hari terakhir
        $sql = "SELECT DATE(tgl) as tanggal, count(*) as jumlah 
                FROM permintaan_komplain
                WHERE tgl >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY tanggal
                ORDER BY tanggal ASC";
        return DbModel::rawData('result_array', $sql);
    }

    public function getUrgentJobs()
    {
        // Mengambil 5 pekerjaan paling darurat
        $sql = "SELECT ok.order_kerja_id, COALESCE(pk.deskripsi, 'Pemeliharaan Rutin') as deskripsi, a.asset_nm 
                FROM order_kerja ok
                LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                LEFT JOIN asset a ON pk.asset_id = a.asset_id OR jp.asset_id = a.asset_id
                WHERE ok.status IN ('baru', 'ditugaskan') AND ok.prioritas = 'darurat'
                ORDER BY ok.tgl_dibuat ASC
                LIMIT 5";
        return DbModel::rawData('result_array', $sql);
    }
}
