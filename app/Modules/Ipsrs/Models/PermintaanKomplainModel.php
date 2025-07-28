<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PermintaanKomplainModel extends Model
{
    protected static $nav_sess;

    /**
     * Konstruktor: Inisialisasi session
     */
    public function __construct()
    {
        parent::__construct();
        self::initSession();
    }

    /**
     * Inisialisasi session navigasi
     */
    protected static function initSession()
    {
        if (is_null(self::$nav_sess)) {
            self::$nav_sess = session(request('n'));
        }
    }

    /**
     * Load data untuk datatables dengan filter
     */
    static function loadDatatables()
    {
        self::initSession();

        $where = "1 = 1 ";

        if (@self::$nav_sess['search']['data']['asset_id'] != '') {
            $where .= " AND k.asset_id = '" . @self::$nav_sess['search']['data']['asset_id'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['pegawai_id'] != '') {
            $where .= " AND k.pegawai_id = '" . @self::$nav_sess['search']['data']['pegawai_id'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $where .= " AND k.status = '" . @self::$nav_sess['search']['data']['status'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $where .= " AND (LOWER(a.asset_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' 
                      OR LOWER(p.pegawai_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                      OR LOWER(k.deskripsi) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%') ";
        }

        $query = "SELECT * FROM (
                    SELECT 
                      k.permintaan_id, k.tgl, k.deskripsi, k.status, k.active_st,
                      k.anotasi_url,
                      a.asset_nm,
                      p.pegawai_nm
                    FROM 
                      trx_permintaan_komplain k
                      LEFT JOIN mst_asset a ON k.asset_id = a.asset_id
                      LEFT JOIN mst_pegawai p ON k.pegawai_id = p.pegawai_id
                    WHERE $where AND k.deleted_st = 0
                ) x ";

        $search = ['permintaan_id', 'asset_nm', 'pegawai_nm', 'deskripsi', 'status'];
        $where = null;
        $isWhere = null;

        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }

    /**
     * Ambil data permintaan komplain berdasarkan ID
     */
    public function getById($id)
    {
        if (!$id) return null;
        return DbModel::getData('trx_permintaan_komplain', ['permintaan_id' => $id]);
    }

    /**
     * Simpan data permintaan komplain (insert/update)
     */
    public static function saveData($id = null, $d = [])
    {
        try {
            $result = ['status' => false, 'message' => '', 'mode' => ''];
            $mode = !empty($id) ? 'update' : 'insert';
            $result['mode'] = $mode;

            DB::beginTransaction();

            if ($mode == 'insert') {
                if (empty($d['permintaan_id'])) {
                    $d['permintaan_id'] = self::generatePermintaanId();
                }
                if (isset($d['tgl']) && !empty($d['tgl'])) {
                    $d['tgl'] = to_date($d['tgl'], '-', 'date');
                } else {
                    $d['tgl'] = date('Y-m-d');
                }

                $validFields = [
                    'permintaan_id', 'tgl', 'asset_id', 'pegawai_id', 'deskripsi', 'status',
                    'foto_url', 'anotasi_url', 'created_at', 'created_by', 'updated_at',
                    'updated_by', 'deleted_st', 'active_st'
                ];

                $dataToInsert = [];
                foreach ($validFields as $field) {
                    if (isset($d[$field])) {
                        $dataToInsert[$field] = $d[$field];
                    }
                }

                $dataToInsert['active_st'] = $dataToInsert['active_st'] ?? 1;
                $dataToInsert['created_at'] = date('Y-m-d H:i:s');
                $dataToInsert['created_by'] = session('nama_user') ?? session('nama_pegawai') ?? 'system';

                $insert = DbModel::insertData('trx_permintaan_komplain', $dataToInsert);
                if (!$insert) {
                    throw new \Exception("Gagal menyimpan permintaan");
                }

                DB::commit();
                $result['status'] = true;
                $result['permintaan_id'] = $dataToInsert['permintaan_id'];
                $result['message'] = 'Permintaan berhasil disimpan';
            } else {
                if (isset($d['tgl']) && !empty($d['tgl'])) {
                    $d['tgl'] = to_date($d['tgl'], '-', 'date');
                } else {
                    $d['tgl'] = date('Y-m-d');
                }

                $validFields = [
                    'tgl', 'asset_id', 'pegawai_id', 'deskripsi', 'status',
                    'foto_url', 'anotasi_url', 'updated_at', 'updated_by', 'active_st'
                ];

                $dataToUpdate = [];
                foreach ($validFields as $field) {
                    if (isset($d[$field])) {
                        $dataToUpdate[$field] = $d[$field];
                    }
                }

                $dataToUpdate['updated_at'] = date('Y-m-d H:i:s');
                $dataToUpdate['updated_by'] = session('nama_user') ?? session('nama_pegawai') ?? 'system';

                $update = DbModel::updateData('trx_permintaan_komplain', $dataToUpdate, ['permintaan_id' => $id]);
                if (!$update) {
                    throw new \Exception("Gagal mengupdate permintaan");
                }

                DB::commit();
                $result['status'] = true;
                $result['permintaan_id'] = $id;
                $result['message'] = 'Permintaan berhasil diupdate';
            }

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in PermintaanKomplainModel::saveData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $result['status'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
    }

    /**
     * Soft delete data permintaan komplain
     */
    public function deleteData($id)
    {
        try {
            DB::beginTransaction();
            $result = DbModel::updateData(
                'trx_permintaan_komplain',
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

    /**
     * Ambil semua data aset aktif
     */
    public function getAllActiveAsset()
    {
        return DbModel::allData('mst_asset', ['deleted_st' => 0, 'active_st' => 1]);
    }

    /**
     * Ambil semua data pegawai aktif
     */
    public function getAllActivePegawai()
    {
        return DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);
    }

    /**
     * Ambil semua data lokasi aktif
     */
    public function getAllActiveLokasi()
    {
        return DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1]);
    }

    /**
     * Cek apakah order kerja sudah ada berdasarkan permintaan_id
     */
    public function isOrderKerjaExistByPermintaanId($permintaan_id)
    {
        return DbModel::getData('trx_order_kerja', ['permintaan_id' => $permintaan_id, 'deleted_st' => 0]) ? true : false;
    }

    /**
     * Generate ID unik untuk permintaan komplain baru
     * @return string Format: 12-digit numeric (000000000001)
     */
    public static function generatePermintaanId()
    {
        $sql = "SELECT permintaan_id FROM trx_permintaan_komplain 
                ORDER BY permintaan_id DESC LIMIT 1";
        $last = DbModel::rawData('row_array', $sql);
        $lastId = isset($last['permintaan_id']) ? $last['permintaan_id'] : '000000000000';
        $nextIdInt = (int)$lastId + 1;
        $nextId = str_pad($nextIdInt, 12, '0', STR_PAD_LEFT);
        return $nextId;
    }
}
