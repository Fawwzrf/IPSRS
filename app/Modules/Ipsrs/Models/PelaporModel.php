<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class PelaporModel extends Model
{
    /**
     * Mengambil riwayat komplain untuk pegawai tertentu.
     */
    public function getHistoryByPegawai($pegawai_id)
    {
        $sql = "SELECT 
                    pk.permintaan_id, 
                    pk.tgl, 
                    pk.deskripsi, 
                    pk.status,
                    a.asset_nm,
                    l.lokasi_nm
                FROM permintaan_komplain pk
                LEFT JOIN asset a ON pk.asset_id = a.asset_id
                LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id
                WHERE pk.pegawai_id = ? AND pk.deleted_st = 0
                ORDER BY pk.tgl DESC";

        return DbModel::rawData('result_array', $sql, [$pegawai_id]);
    }
}
