<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderKerjaModel extends Model
{
    protected static $nav_sess;

    /**
     * Konstruktor
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
     * Load data untuk datatables
     */
    static function loadDatatables()
    {
        self::initSession();
        $where = "1 = 1 ";

        if (@self::$nav_sess['search']['data']['jenis'] != '') {
            $where .= " AND ok.jenis = '" . @self::$nav_sess['search']['data']['jenis'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $where .= " AND ok.status = '" . @self::$nav_sess['search']['data']['status'] . "' ";
        }
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $term = @strtolower(self::$nav_sess['search']['data']['term']);
            $where .= " AND (
                LOWER(ok.order_kerja_id) LIKE '%" . $term . "%' 
                OR LOWER(COALESCE(a1.asset_nm, a2.asset_nm)) LIKE '%" . $term . "%'
                OR LOWER(COALESCE(pk.deskripsi, jp.jenis)) LIKE '%" . $term . "%'
            ) ";
        }

        $query = "SELECT * FROM (
                    SELECT 
                      ok.order_kerja_id, 
                      ok.tgl_dibuat, 
                      ok.status, 
                      ok.jenis, 
                      ok.prioritas,
                      COALESCE(a1.asset_nm, a2.asset_nm) as asset_nm,
                      COALESCE(pk.deskripsi, jp.jenis) as deskripsi_sumber
                    FROM 
                      trx_order_kerja ok
                      LEFT JOIN trx_permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                      LEFT JOIN trx_jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                      LEFT JOIN mst_asset a1 ON pk.asset_id = a1.asset_id
                      LEFT JOIN mst_asset a2 ON jp.asset_id = a2.asset_id
                    WHERE $where AND ok.deleted_st = 0
                ) x";

        $search = ['order_kerja_id', 'asset_nm', 'deskripsi_sumber', 'jenis', 'status', 'prioritas'];
        $where = null;
        $isWhere = null;

        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);

        if (!empty($result['data'])) {
            foreach ($result['data'] as $key => $row) {
                $order_kerja_id = $row['order_kerja_id'];
                $teknisiQuery = "SELECT GROUP_CONCAT(p.pegawai_nm SEPARATOR ', ') as tim_teknisi 
                                FROM trx_penugasan_teknisi pt 
                                JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id 
                                WHERE pt.order_kerja_id = ? AND pt.deleted_st = 0";
                $teknisiData = DbModel::rawData('row_array', $teknisiQuery, [$order_kerja_id]);
                $result['data'][$key]['tim_teknisi'] = $teknisiData['tim_teknisi'] ?? 'Belum ditugaskan';
            }
        }

        return response()->json($result);
    }

    /**
     * Ambil data order kerja berdasarkan ID
     */
    public function getById($id)
    {
        if (!$id) return null;
        return DbModel::getData('trx_order_kerja', ['order_kerja_id' => $id]);
    }

    /**
     * Simpan data order kerja (insert/update)
     */
    public static function saveData($id = null, $d = [])
    {
        try {
            \Log::info('OrderKerjaModel::saveData input', [
                'id' => $id,
                'data' => array_keys($d)
            ]);

            $result = ['status' => false, 'message' => '', 'mode' => ''];
            $mode = !empty($id) ? 'update' : 'insert';
            $result['mode'] = $mode;

            DB::beginTransaction();

            if ($mode == 'insert') {
                if (empty($d['order_kerja_id'])) {
                    $d['order_kerja_id'] = self::generateOrderKerjaId();
                }
                $existingOrder = DB::table('trx_order_kerja')
                    ->where('order_kerja_id', $d['order_kerja_id'])
                    ->first();
                if ($existingOrder) {
                    \Log::info('OrderKerjaModel: Order already exists', ['order_kerja_id' => $d['order_kerja_id']]);
                    DB::commit();
                    $result['status'] = true;
                    $result['order_kerja_id'] = $d['order_kerja_id'];
                    $result['message'] = 'Order kerja sudah ada';
                    return $result;
                }
                if (empty($d['jadwal_pm_id']) && empty($d['permintaan_id'])) {
                    throw new \Exception("Order kerja harus memiliki jadwal PM atau permintaan");
                }
                $validFields = [
                    'order_kerja_id', 'jadwal_pm_id', 'permintaan_id', 'jenis', 'tgl_dibuat',
                    'tgl_target_selesai', 'tgl_selesai', 'prioritas', 'estimasi_biaya', 'catatan',
                    'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_st', 'active_st'
                ];
                $dataToInsert = [];
                foreach ($validFields as $field) {
                    if (isset($d[$field])) {
                        if (in_array($field, ['tgl_dibuat', 'tgl_target_selesai', 'tgl_selesai']) && !empty($d[$field])) {
                            $dataToInsert[$field] = to_date($d[$field], '-', 'date');
                        } else {
                            $dataToInsert[$field] = $d[$field];
                        }
                    }
                }
                if (isset($d['deskripsi']) && !isset($dataToInsert['catatan'])) {
                    $dataToInsert['catatan'] = $d['deskripsi'];
                }
                $dataToInsert['tgl_dibuat'] = $dataToInsert['tgl_dibuat'] ?? date('Y-m-d');
                $dataToInsert['status'] = $dataToInsert['status'] ?? 'baru';
                $dataToInsert['created_at'] = date('Y-m-d H:i:s');
                $dataToInsert['created_by'] = session('nama_user') ?? session('nama_pegawai') ?? 'system';
                if (isset($d['jadwal_pm_id']) && !isset($dataToInsert['jadwal_pm_id'])) {
                    $dataToInsert['jadwal_pm_id'] = $d['jadwal_pm_id'];
                }
                if (!isset($dataToInsert['jenis'])) {
                    if (!empty($dataToInsert['jadwal_pm_id'])) {
                        $dataToInsert['jenis'] = 'pemeliharaan';
                    } elseif (!empty($dataToInsert['permintaan_id'])) {
                        $dataToInsert['jenis'] = 'perbaikan';
                    }
                }
                \Log::info('OrderKerjaModel: Inserting filtered data', ['data' => $dataToInsert]);
                $insert = DbModel::insertData('trx_order_kerja', $dataToInsert);
                if (!$insert) {
                    throw new \Exception("Gagal menyimpan order kerja");
                }
                $order_kerja_id = $dataToInsert['order_kerja_id'];
                if (!empty($d['teknisi']) && is_array($d['teknisi'])) {
                    foreach ($d['teknisi'] as $pegawai_id) {
                        $penugasan_id = self::generatePenugasanId();
                        $penugasan = [
                            'penugasan_id' => $penugasan_id,
                            'order_kerja_id' => $order_kerja_id,
                            'pegawai_id' => $pegawai_id,
                            'status' => 'ditugaskan',
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => session('nama_user') ?? session('nama_pegawai') ?? 'system'
                        ];
                        \Log::info('OrderKerjaModel: Inserting penugasan', ['pegawai_id' => $pegawai_id]);
                        $insertPenugasan = DbModel::insertData('trx_penugasan_teknisi', $penugasan);
                        if (!$insertPenugasan) {
                            throw new \Exception("Gagal menyimpan penugasan teknisi");
                        }
                    }
                    DbModel::updateData(
                        'trx_order_kerja',
                        ['status' => 'ditugaskan'],
                        ['order_kerja_id' => $order_kerja_id]
                    );
                }
                DB::commit();
                \Log::info('OrderKerjaModel: Insert berhasil', ['order_kerja_id' => $order_kerja_id]);
                $result['status'] = true;
                $result['order_kerja_id'] = $order_kerja_id;
                $result['message'] = 'Order kerja berhasil dibuat';
            } else {
                $validFields = [
                    'jadwal_pm_id', 'permintaan_id', 'jenis', 'tgl_dibuat', 'tgl_target_selesai',
                    'tgl_selesai', 'prioritas', 'estimasi_biaya', 'catatan', 'status',
                    'updated_at', 'updated_by', 'deleted_st', 'active_st'
                ];
                $dataToUpdate = [];
                foreach ($validFields as $field) {
                    if (isset($d[$field])) {
                        if (in_array($field, ['tgl_dibuat', 'tgl_target_selesai', 'tgl_selesai']) && !empty($d[$field])) {
                            $dataToUpdate[$field] = to_date($d[$field], '-', 'date');
                        } else {
                            $dataToUpdate[$field] = $d[$field];
                        }
                    }
                }
                if (isset($d['deskripsi']) && !isset($dataToUpdate['catatan'])) {
                    $dataToUpdate['catatan'] = $d['deskripsi'];
                }
                $dataToUpdate['updated_at'] = date('Y-m-d H:i:s');
                $dataToUpdate['updated_by'] = session('nama_user') ?? session('nama_pegawai') ?? 'system';
                $update = DbModel::updateData('trx_order_kerja', $dataToUpdate, ['order_kerja_id' => $id]);
                if (!$update) {
                    throw new \Exception("Gagal mengupdate order kerja");
                }
                if (isset($d['teknisi']) && is_array($d['teknisi'])) {
                    DbModel::updateData('trx_penugasan_teknisi', [
                        'deleted_st' => 1,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => session('nama_user') ?? session('nama_pegawai') ?? 'system'
                    ], [
                        'order_kerja_id' => $id
                    ]);
                    foreach ($d['teknisi'] as $pegawai_id) {
                        $penugasan_id = self::generatePenugasanId();
                        $penugasan = [
                            'penugasan_id' => $penugasan_id,
                            'order_kerja_id' => $id,
                            'pegawai_id' => $pegawai_id,
                            'status' => 'ditugaskan',
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => session('nama_user') ?? session('nama_pegawai') ?? 'system'
                        ];
                        $insertPenugasan = DbModel::insertData('trx_penugasan_teknisi', $penugasan);
                        if (!$insertPenugasan) {
                            throw new \Exception("Gagal menyimpan penugasan teknisi");
                        }
                    }
                    DbModel::updateData(
                        'trx_order_kerja',
                        ['status' => 'ditugaskan'],
                        ['order_kerja_id' => $id]
                    );
                }
                DB::commit();
                $result['status'] = true;
                $result['order_kerja_id'] = $id;
                $result['message'] = 'Order kerja berhasil diupdate';
            }
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in OrderKerjaModel::saveData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $result['status'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
    }

    /**
     * Soft delete order kerja dan penugasan teknisi
     */
    public function deleteData($id)
    {
        try {
            DB::beginTransaction();
            DB::table('trx_penugasan_teknisi')
                ->where('order_kerja_id', $id)
                ->update(['deleted_st' => 1, 'updated_by' => session('user_name'), 'updated_at' => now()]);
            DB::table('trx_order_kerja')
                ->where('order_kerja_id', $id)
                ->update(['deleted_st' => 1, 'updated_by' => session('user_name'), 'updated_at' => now()]);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting order kerja: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil semua teknisi aktif
     */
    public function getAllActiveTeknisi()
    {
        return DbModel::allData('mst_pegawai', [
            'deleted_st' => '0',
            'active_st'  => '1',
            'jabatan_id' => '90'
        ]);
    }

    /**
     * Ambil semua order kerja
     */
    public function getAllOrderKerja()
    {
        $data = DbModel::allData('trx_order_kerja');
        foreach ($data as &$row) {
            $row['tgl_dibuat']   = to_date($row['tgl_dibuat'] ?? '');
            $row['total_biaya']  = numId($row['total_biaya'] ?? 0);
        }
        return $data;
    }

    /**
     * Ambil jadwal PM yang tersedia
     */
    public function getAvailableJadwalPm()
    {
        $sql = "SELECT jp.*, a.asset_nm, 
                COALESCE(jp.frekuensi, 'N/A') as frekuensi, 
                COALESCE(jp.jenis, 'N/A') as jenis
            FROM trx_jadwal_pm jp 
            JOIN mst_asset a ON jp.asset_id = a.asset_id 
            WHERE jp.deleted_st = 0 
            AND jp.active_st = 1
            AND jp.status != 'dibatalkan'
            AND jp.jadwal_pm_id NOT IN (
                SELECT DISTINCT jadwal_pm_id FROM trx_order_kerja 
                WHERE jadwal_pm_id IS NOT NULL 
                AND deleted_st = 0
                AND status NOT IN ('selesai', 'dibatalkan')
            )";
        return DbModel::rawData('result_array', $sql);
    }

    /**
     * Ambil komplain yang tersedia
     */
    public function getAvailableKomplain()
    {
        $sql = "SELECT pk.*, a.asset_nm 
            FROM trx_permintaan_komplain pk 
            JOIN mst_asset a ON pk.asset_id = a.asset_id 
            WHERE pk.deleted_st = 0 
            AND pk.status IN ('diverifikasi', 'baru', 'dikirim', 'diterima')
            AND pk.permintaan_id NOT IN (
                SELECT DISTINCT permintaan_id FROM trx_order_kerja 
                WHERE permintaan_id IS NOT NULL 
                AND deleted_st = 0
                AND status NOT IN ('selesai', 'dibatalkan')
            )";
        return DbModel::rawData('result_array', $sql);
    }

    /**
     * Ambil jadwal PM berdasarkan ID
     */
    public function getJadwalPmById($jadwal_pm_id)
    {
        $sql = "SELECT jp.*, a.asset_nm, 
                COALESCE(jp.frekuensi, 'N/A') as frekuensi, 
                COALESCE(jp.jenis, 'N/A') as jenis
            FROM trx_jadwal_pm jp 
            JOIN mst_asset a ON jp.asset_id = a.asset_id 
            WHERE jp.jadwal_pm_id = ?";
        return DbModel::rawData('row_array', $sql, [$jadwal_pm_id]);
    }

    /**
     * Ambil komplain berdasarkan ID
     */
    public function getKomplainById($permintaan_id)
    {
        $sql = "SELECT pk.*, a.asset_nm 
            FROM trx_permintaan_komplain pk 
            JOIN mst_asset a ON pk.asset_id = a.asset_id 
            WHERE pk.permintaan_id = ?";
        return DbModel::rawData('row_array', $sql, [$permintaan_id]);
    }

    /**
     * Ambil teknisi yang ditugaskan pada order kerja
     */
    public function getAssignedTeknisi($order_kerja_id)
    {
        return array_column(
            DbModel::allData('trx_penugasan_teknisi', [
                'order_kerja_id' => $order_kerja_id,
                'deleted_st'     => 0
            ]),
            'pegawai_id'
        );
    }

    /**
     * Ambil order kerja berdasarkan ID
     */
    public function getOrderKerjaById($order_kerja_id)
    {
        return DbModel::getData('trx_order_kerja', [
            'order_kerja_id' => $order_kerja_id
        ]);
    }

    /**
     * Ambil data hasil teknisi untuk modal
     */
    public function getHasilTeknisiModalData($order_kerja_id)
    {
        $penugasan = DbModel::rawData(
            'result_array',
            "SELECT pt.*, p.pegawai_nm
         FROM trx_penugasan_teknisi pt
         LEFT JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id
         WHERE pt.order_kerja_id = ? AND pt.deleted_st = 0",
            [$order_kerja_id]
        );

        $log_kerja = DbModel::rawData(
            'result_array',
            "SELECT lk.*, p.pegawai_nm
         FROM trx_log_kerja lk
         LEFT JOIN mst_pegawai p ON lk.teknisi_pegawai_id = p.pegawai_id
         WHERE lk.order_kerja_id = ? AND lk.deleted_st = 0",
            [$order_kerja_id]
        );

        foreach ($log_kerja as &$log) {
            $log['fotos'] = DbModel::rawData(
                'result_array',
                "SELECT foto_url, deskripsi FROM trx_log_kerja_foto WHERE log_kerja_id = ?",
                [$log['log_kerja_id']]
            );
            $log['sparepart'] = DbModel::rawData(
                'result_array',
                "SELECT ps.jumlah, ms.sparepart_nm
             FROM trx_penggunaan_sparepart ps
             LEFT JOIN mst_sparepart ms ON ps.sparepart_id = ms.sparepart_id
             WHERE ps.log_kerja_id = ? AND ps.deleted_st = 0",
                [$log['log_kerja_id']]
            );
        }

        return [
            'penugasan' => $penugasan,
            'log_kerja' => $log_kerja,
        ];
    }

    /**
     * Generate ID unik untuk order kerja baru (12 digit angka)
     */
    public static function generateOrderKerjaId()
    {
        try {
            $lastId = DB::table('trx_order_kerja')
                ->orderBy('order_kerja_id', 'desc')
                ->value('order_kerja_id');
            \Log::info('Last order_kerja_id', ['id' => $lastId]);
            if (!$lastId) {
                $nextId = '000000000001';
            } else {
                $lastNumber = intval($lastId);
                $nextNumber = $lastNumber + 1;
                $nextId = str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
            }
            \Log::info('Generated new order_kerja_id', ['id' => $nextId]);
            return $nextId;
        } catch (\Exception $e) {
            \Log::error('Error generating order_kerja_id: ' . $e->getMessage());
            return date('YmdHis') . rand(100, 999);
        }
    }

    /**
     * Generate penugasan_id dengan format 12 digit angka
     */
    public static function generatePenugasanId()
    {
        try {
            $lastId = DB::table('trx_penugasan_teknisi')
                ->orderBy('penugasan_id', 'desc')
                ->value('penugasan_id');
            \Log::info('Last penugasan_id', ['id' => $lastId]);
            if (!$lastId) {
                $nextId = '000000000001';
            } else {
                $lastNumber = intval($lastId);
                $nextNumber = $lastNumber + 1;
                $nextId = str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
            }
            \Log::info('Generated new penugasan_id', ['id' => $nextId]);
            return $nextId;
        } catch (\Exception $e) {
            \Log::error('Error generating penugasan_id: ' . $e->getMessage());
            return '000000' . time() % 1000000;
        }
    }
    public static function getStatusById($order_kerja_id)
    {
        $row = DbModel::getData('trx_order_kerja', ['order_kerja_id' => $order_kerja_id]);
        return $row ? $row['status'] : null;
    }
}
