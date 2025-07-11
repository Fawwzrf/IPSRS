<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class PermintaanKomplainModel extends Model
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

    static function loadDatatables()
    {
        self::initSession();

        $query = "SELECT 
                    k.permintaan_id, k.tgl, k.deskripsi, k.status, k.active_st,
                    k.anotasi_url,
                    a.asset_nm,
                    p.pegawai_nm
                  FROM permintaan_komplain k
                  LEFT JOIN asset a ON k.asset_id = a.asset_id
                  LEFT JOIN mst_pegawai p ON k.pegawai_id = p.pegawai_id";

        $searchableColumns = ['a.asset_nm', 'p.pegawai_nm', 'k.deskripsi', 'k.status'];

        $whereConditions = ['k.deleted_st' => 0];
        $search_data = self::$nav_sess['search']['data'] ?? [];

        if (!empty($search_data['status'])) $whereConditions['k.status'] = $search_data['status'];
        if (!empty($search_data['asset_id'])) $whereConditions['k.asset_id'] = $search_data['asset_id'];
        if (!empty($search_data['pegawai_id'])) $whereConditions['k.pegawai_id'] = $search_data['pegawai_id'];

        $whereString = '';
        if (!empty($search_data['term'])) {
            $searchTerm = strtolower(addslashes($search_data['term']));
            $whereString = " (LOWER(a.asset_nm) LIKE '%{$searchTerm}%' OR LOWER(p.pegawai_nm) LIKE '%{$searchTerm}%' OR LOWER(k.deskripsi) LIKE '%{$searchTerm}%') ";
        }

        $result = DbModel::datatablesQuery($query, $searchableColumns, $whereConditions, $whereString);
        return response()->json($result);
    }

    public function getById($id)
    {
        if (!$id) return null;
        return DbModel::getData('permintaan_komplain', ['permintaan_id' => $id]);
    }

    public function saveData($id, $data)
    {
        $saveData = [
            'tgl' => to_date($data['tgl'], '-', 'date'),
            'asset_id' => $data['asset_id'],
            'pegawai_id' => $data['pegawai_id'],
            'deskripsi' => $data['deskripsi'],
            'status' => $data['status'],
            'anotasi_url' => $data['anotasi_url'] ?? null,
        ];

        if ($id == null) {
            $saveData['permintaan_id'] = DbModel::getId('permintaan_komplain', 2, 12);
            $result = DbModel::insertData('permintaan_komplain', $saveData);
            return ['status' => $result, 'mode' => 'insert'];
        } else {
            $result = DbModel::updateData('permintaan_komplain', $saveData, ['permintaan_id' => $id]);
            return ['status' => $result, 'mode' => 'update'];
        }
    }

    public function deleteData($id)
    {
        return DbModel::updateData('permintaan_komplain', ['deleted_st' => 1], ['permintaan_id' => $id]);
    }
}
