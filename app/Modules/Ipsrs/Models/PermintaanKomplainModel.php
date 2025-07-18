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

    public static function saveData($id = null, $d = [])
    {
        try {
            // Log input data untuk debugging
            \Log::info('PermintaanKomplainModel::saveData input', [
                'id' => $id,
                'data' => array_keys($d)
            ]);
            
            $result = ['status' => false, 'message' => '', 'mode' => ''];
            $mode = !empty($id) ? 'update' : 'insert';
            $result['mode'] = $mode;
            
            DB::beginTransaction();
            
            if ($mode == 'insert') {
                // Generate ID jika tidak ada
                if (empty($d['permintaan_id'])) {
                    $d['permintaan_id'] = self::generatePermintaanId();
                }
                
                // PENTING: Format tanggal tgl ke format MySQL (YYYY-MM-DD)
                if (isset($d['tgl']) && !empty($d['tgl'])) {
                    // Jika format DD-MM-YYYY
                    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $d['tgl'])) {
                        $dateParts = explode('-', $d['tgl']);
                        $d['tgl'] = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                    }
                } else {
                    // Default ke tanggal hari ini jika tidak ada
                    $d['tgl'] = date('Y-m-d');
                }
                
                // Validasi dan persiapan data lain
                
                // Daftar field yang valid untuk insert
                $validFields = [
                    'permintaan_id', 'tgl', 'asset_id', 'pegawai_id',
                    'deskripsi', 'status', 'foto_url', 'anotasi_url',
                    'created_at', 'created_by', 'updated_at', 'updated_by',
                    'deleted_st', 'active_st'
                ];
                
                // Buat array data baru yang hanya berisi field valid
                $dataToInsert = [];
                foreach ($validFields as $field) {
                    if (isset($d[$field])) {
                        $dataToInsert[$field] = $d[$field];
                    }
                }
                
                // Set nilai default untuk kolom wajib
                $dataToInsert['active_st'] = $dataToInsert['active_st'] ?? 1;
                $dataToInsert['created_at'] = date('Y-m-d H:i:s');
                $dataToInsert['created_by'] = session('nama_user') ?? session('nama_pegawai') ?? 'system';
                
                // Untuk debugging, log data yang akan dimasukkan
                \Log::info('PermintaanKomplainModel: Inserting filtered data', ['data' => $dataToInsert]);
                
                $insert = DbModel::insertData('permintaan_komplain', $dataToInsert);
                if (!$insert) {
                    throw new \Exception("Gagal menyimpan permintaan");
                }
                
                // Commit jika berhasil
                DB::commit();
                
                // Set status hasil
                $result['status'] = true;
                $result['permintaan_id'] = $dataToInsert['permintaan_id'];
                $result['message'] = 'Permintaan berhasil disimpan';
                
            } else {
                // Kode untuk update
                // ...
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
    
    /**
     * Generate ID unik untuk permintaan komplain baru
     * @return string Format: 12-digit numeric (000000000001)
     */
    public static function generatePermintaanId()
    {
        // Ambil ID terakhir
        $sql = "SELECT permintaan_id FROM permintaan_komplain 
                ORDER BY permintaan_id DESC LIMIT 1";
        $last = DbModel::rawData('row_array', $sql);
        $lastId = isset($last['permintaan_id']) ? $last['permintaan_id'] : '000000000000';
        
        // Log untuk debugging
        \Log::info('Last permintaan_id', ['id' => $lastId]);
        
        // Konversi string ID ke integer dan tambahkan 1
        $nextIdInt = (int)$lastId + 1;
        
        // Format kembali ke string dengan leading zeros (12 digit)
        $nextId = str_pad($nextIdInt, 12, '0', STR_PAD_LEFT);
        
        // Log ID baru yang dihasilkan
        \Log::info('Generated new permintaan_id', ['id' => $nextId]);
        
        return $nextId;
    }
}
