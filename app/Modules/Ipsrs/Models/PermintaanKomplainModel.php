<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        
        $where = "1 = 1 ";

        // Filter berdasarkan aset
        if (@self::$nav_sess['search']['data']['asset_id'] != '') {
            $where .= " AND k.asset_id = '" . @self::$nav_sess['search']['data']['asset_id'] . "' ";
        }
        
        // Filter berdasarkan pegawai
        if (@self::$nav_sess['search']['data']['pegawai_id'] != '') {
            $where .= " AND k.pegawai_id = '" . @self::$nav_sess['search']['data']['pegawai_id'] . "' ";
        }
        
        // Filter berdasarkan status
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $where .= " AND k.status = '" . @self::$nav_sess['search']['data']['status'] . "' ";
        }
        
        // Filter berdasarkan pencarian
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $where .= " AND (LOWER(a.asset_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' 
                      OR LOWER(p.pegawai_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                      OR LOWER(k.deskripsi) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%') ";
        }

        // Gunakan subquery dengan alias seperti pada modul acuan
        $query = "SELECT * FROM (
                    SELECT 
                      k.permintaan_id, k.tgl, k.deskripsi, k.status, k.active_st,
                      k.anotasi_url,
                      a.asset_nm,
                      p.pegawai_nm
                    FROM 
                      permintaan_komplain k
                      LEFT JOIN asset a ON k.asset_id = a.asset_id
                      LEFT JOIN mst_pegawai p ON k.pegawai_id = p.pegawai_id
                    WHERE $where AND k.deleted_st = 0
                ) x ";
                
        $search = ['permintaan_id', 'asset_nm', 'pegawai_nm', 'deskripsi', 'status'];
        $where = null;
        $isWhere = null;
        
        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }

    public function getById($id)
    {
        if (!$id) return null;
        return DbModel::getData('permintaan_komplain', ['permintaan_id' => $id]);
    }

    public function saveData($id, $data)
    {
        DB::beginTransaction();
        
        try {
            // Hapus lokasi_id dari data jika ada
            if (isset($data['lokasi_id'])) {
                unset($data['lokasi_id']);
            }
            
            $saveData = [
                'tgl' => to_date($data['tgl'], '-', 'date'),
                'asset_id' => $data['asset_id'],
                'pegawai_id' => $data['pegawai_id'],
                'deskripsi' => $data['deskripsi'],
                'status' => $data['status'],
                'anotasi_url' => $data['anotasi_url'] ?? null,
                'active_st' => isset($data['active_st']) ? $data['active_st'] : 1,
            ];

            if ($id == null) {
                // Mode tambah baru
                $saveData['permintaan_id'] = DbModel::getId('permintaan_komplain', 2, 12);
                $saveData['created_by'] = session('user_name');
                $saveData['created_at'] = now();
                
                $result = DbModel::insertData('permintaan_komplain', $saveData);
                DB::commit();
                
                return ['status' => $result, 'mode' => 'insert'];
            } else {
                // Mode update
                $saveData['updated_by'] = session('user_name');
                $saveData['updated_at'] = now();
                
                $result = DbModel::updateData('permintaan_komplain', $saveData, ['permintaan_id' => $id]);
                DB::commit();
                
                return ['status' => $result, 'mode' => 'update'];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving permintaan komplain: ' . $e->getMessage());
            return ['status' => false, 'mode' => $id ? 'update' : 'insert'];
        }
    }

    public function deleteData($id)
    {
        try {
            DB::beginTransaction();
            
            // Soft delete
            $result = DbModel::updateData('permintaan_komplain', 
                ['deleted_st' => 1, 'updated_by' => session('user_name'), 'updated_at' => now()], 
                ['permintaan_id' => $id]
            );
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting permintaan komplain: ' . $e->getMessage());
            return false;
        }
    }
}
