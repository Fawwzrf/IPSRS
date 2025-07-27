<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class PelaporModel extends Model
{
    // Konstanta status
    const STATUS_BARU = 'baru';
    const STATUS_DIVERIFIKASI = 'diverifikasi';
    const STATUS_DIPROSES = 'diproses';
    const STATUS_SELESAI = 'selesai';
    const STATUS_DITOLAK = 'ditolak';
    const STATUS_DIBATALKAN = 'dibatalkan';

    protected static $status_list = [
        self::STATUS_BARU => 'Baru',
        self::STATUS_DIVERIFIKASI => 'Diverifikasi',
        self::STATUS_DIPROSES => 'Diproses',
        self::STATUS_SELESAI => 'Selesai',
        self::STATUS_DITOLAK => 'Ditolak',
        self::STATUS_DIBATALKAN => 'Dibatalkan'
    ];

    /**
     * Mengambil riwayat komplain untuk pegawai tertentu.
     */
    public function getHistoryByPegawai($pegawai_id, $limit = 15, $offset = 0)
    {
        $sql = "SELECT 
                    pk.permintaan_id, 
                    pk.tgl, 
                    pk.deskripsi, 
                    pk.status,
                    a.asset_nm,
                    l.lokasi_nm
                FROM trx_permintaan_komplain pk
                LEFT JOIN mst_asset a ON pk.asset_id = a.asset_id
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                WHERE pk.pegawai_id = ? AND pk.deleted_st = 0
                ORDER BY pk.tgl DESC
                LIMIT ?, ?";

        return DbModel::rawData('result_array', $sql, [$pegawai_id, $offset, $limit]);
    }

    /**
     * Menghitung total laporan untuk pegawai tertentu.
     */
    public function countHistoryByPegawai($pegawai_id)
    {
        $sql = "SELECT COUNT(*) as total
                FROM trx_permintaan_komplain
                WHERE pegawai_id = ? AND deleted_st = 0";

        $result = DbModel::rawData('row_array', $sql, [$pegawai_id]);
        return $result['total'] ?? 0;
    }

    /**
     * Mengambil daftar status yang digunakan dalam sistem.
     */
    public static function getStatusList()
    {
        return self::$status_list;
    }

    /**
     * Mengambil class badge untuk status tertentu.
     */
    public static function getStatusBadgeClass($status)
    {
        $status = strtolower($status);
        $badgeClass = 'bg-secondary';

        if ($status == 'baru') $badgeClass = 'bg-info';
        elseif ($status == 'diverifikasi') $badgeClass = 'bg-primary';
        elseif ($status == 'diproses') $badgeClass = 'bg-warning';
        elseif ($status == 'selesai') $badgeClass = 'bg-success';
        elseif ($status == 'ditolak') $badgeClass = 'bg-danger';
        elseif ($status == 'dibatalkan') $badgeClass = 'bg-secondary';

        return $badgeClass;
    }
}
