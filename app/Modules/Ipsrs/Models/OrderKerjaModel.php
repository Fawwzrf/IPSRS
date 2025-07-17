<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderKerjaModel extends Model
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
        
        // Filter berdasarkan jenis
        if (@self::$nav_sess['search']['data']['jenis'] != '') {
            $where .= " AND ok.jenis = '" . @self::$nav_sess['search']['data']['jenis'] . "' ";
        }
        
        // Filter berdasarkan status
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $where .= " AND ok.status = '" . @self::$nav_sess['search']['data']['status'] . "' ";
        } else {
            // Filter default: status bukan dibatalkan jika tidak ada filter status
            $where .= " AND ok.status != 'dibatalkan' ";
        }
        
        // Filter berdasarkan pencarian
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $term = @strtolower(self::$nav_sess['search']['data']['term']);
            $where .= " AND (
                LOWER(ok.order_kerja_id) LIKE '%" . $term . "%' 
                OR LOWER(COALESCE(a1.asset_nm, a2.asset_nm)) LIKE '%" . $term . "%'
                OR LOWER(COALESCE(pk.deskripsi, jp.jenis)) LIKE '%" . $term . "%'
            ) ";
        }

        // Gunakan subquery dengan alias seperti pada modul acuan
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
                      order_kerja ok
                      LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                      LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                      LEFT JOIN asset a1 ON pk.asset_id = a1.asset_id
                      LEFT JOIN asset a2 ON jp.asset_id = a2.asset_id
                    WHERE $where AND ok.deleted_st = 0
                ) x";
                
        $search = ['order_kerja_id', 'asset_nm', 'deskripsi_sumber', 'jenis', 'status', 'prioritas'];
        $where = null;
        $isWhere = null;
        
        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        
        // Tambahkan data Tim Teknisi secara manual setelah mendapatkan data utama
        if (!empty($result['data'])) {
            foreach ($result['data'] as $key => $row) {
                $order_kerja_id = $row['order_kerja_id'];

                // Query terpisah untuk mengambil nama teknisi
                $teknisiQuery = "SELECT GROUP_CONCAT(p.pegawai_nm SEPARATOR ', ') as tim_teknisi 
                                FROM penugasan_teknisi pt 
                                JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id 
                                WHERE pt.order_kerja_id = ? AND pt.deleted_st = 0";

                $teknisiData = DbModel::rawData('row_array', $teknisiQuery, [$order_kerja_id]);

                // Tambahkan data tim teknisi ke dalam array hasil
                $result['data'][$key]['tim_teknisi'] = $teknisiData['tim_teknisi'] ?? 'Belum ditugaskan';
            }
        }
        
        return response()->json($result);
    }

    public function getById($id)
    {
        if (!$id) return null;
        return DbModel::getData('order_kerja', ['order_kerja_id' => $id]);
    }

    public static function saveData($id, $post_data)
    {
        // Validasi Input Utama
        if (empty($post_data['jadwal_pm_id']) && empty($post_data['permintaan_id'])) {
            return [
                'status' => false,
                'message' => 'Pilih salah satu sumber pekerjaan (Jadwal PM atau Komplain).',
                'mode' => 'validation'
            ];
        }
        
        if (empty($post_data['pegawai_ids'])) {
            return [
                'status' => false,
                'message' => 'Pilih minimal satu teknisi untuk ditugaskan.',
                'mode' => 'validation'
            ];
        }

        // Memulai Transaksi Database untuk keamanan data
        DB::beginTransaction();

        try {
            // Mengambil ID order kerja atau membuat yang baru
            $is_insert = ($id == null);
            $order_kerja_id = $id ?? DbModel::getId('order_kerja', 2, 12);

            // Menyiapkan data untuk tabel 'order_kerja'
            $data_ok = [
                'order_kerja_id' => $order_kerja_id,
                'permintaan_id'  => $post_data['permintaan_id'] ?? null,
                'jadwal_pm_id'   => $post_data['jadwal_pm_id'] ?? null,
                'tgl_dibuat'     => to_date($post_data['tgl_dibuat'], '-', 'date'),
                'tgl_target_selesai' => !empty($post_data['tgl_target_selesai']) ? to_date($post_data['tgl_target_selesai'], '-', 'date') : null,
                'prioritas'      => $post_data['prioritas'],
                'status'         => $post_data['status'],
                'estimasi_biaya' => $post_data['estimasi_biaya'] ?? 0,
                'catatan'        => $post_data['catatan'] ?? null,
                'jenis'          => !empty($post_data['jadwal_pm_id']) ? 'pemeliharaan' : 'perbaikan',
            ];

            // Menggunakan helper DbModel untuk insert atau update
            if ($is_insert) {
                $data_ok['created_by'] = session('user_name');
                $data_ok['created_at'] = now();
                DbModel::insertData('order_kerja', $data_ok);
            } else {
                $data_ok['updated_by'] = session('user_name');
                $data_ok['updated_at'] = now();
                DbModel::updateData('order_kerja', $data_ok, ['order_kerja_id' => $id]);
            }

            // Proses penugasan teknisi
            // Hapus penugasan lama terlebih dahulu untuk menghindari duplikat saat edit
            DB::table('penugasan_teknisi')->where('order_kerja_id', $order_kerja_id)->update(['deleted_st' => 1]);

            // Loop untuk membuat penugasan baru
            foreach ($post_data['pegawai_ids'] as $pegawai_id) {
                $penugasan_data = [
                    'penugasan_id'    => DbModel::getId('penugasan_teknisi', 2, 12),
                    'order_kerja_id'  => $order_kerja_id,
                    'pegawai_id'      => $pegawai_id,
                    'status'          => 'ditugaskan', // Status default untuk penugasan baru
                    'created_by'      => session('user_name'),
                    'created_at'      => now(),
                ];
                DB::table('penugasan_teknisi')->insert($penugasan_data);
            }

            // Update status sumber pekerjaan
            if (!empty($post_data['jadwal_pm_id'])) {
                DB::table('jadwal_pm')
                    ->where('jadwal_pm_id', $post_data['jadwal_pm_id'])
                    ->update(['status' => 'diproses']);
            }
            if (!empty($post_data['permintaan_id'])) {
                DB::table('permintaan_komplain')
                    ->where('permintaan_id', $post_data['permintaan_id'])
                    ->update(['status' => 'diproses']);
            }

            // Jika semua proses berhasil, simpan perubahan secara permanen
            DB::commit();
            return [
                'status' => true,
                'message' => 'Order Kerja berhasil disimpan.',
                'mode' => $is_insert ? 'insert' : 'update'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'mode' => $id ? 'update' : 'insert'
            ];
        }
    }

    public function deleteData($id)
    {
        try {
            DB::beginTransaction();

            // Soft delete penugasan teknisi
            DB::table('penugasan_teknisi')
                ->where('order_kerja_id', $id)
                ->update(['deleted_st' => 1, 'updated_by' => session('user_name'), 'updated_at' => now()]);
            
            // Soft delete order kerja
            DB::table('order_kerja')
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
    
    // Tambahkan method untuk mengambil jadwal PM yang tersedia (tidak dibatalkan)
    public static function getAvailableJadwalPM()
    {
        $sql = "SELECT * FROM (
                  SELECT 
                    jp.jadwal_pm_id,
                    jp.jenis,
                    a.asset_nm,
                    a.asset_id,
                    jp.tgl_berikutnya,
                    jp.status
                  FROM 
                    jadwal_pm jp
                    LEFT JOIN asset a ON jp.asset_id = a.asset_id
                  WHERE 
                    jp.deleted_st = 0 
                    AND jp.active_st = 1
                    AND jp.status != 'dibatalkan'  /* Filter status dibatalkan */
                    AND jp.jadwal_pm_id NOT IN (
                        SELECT DISTINCT jadwal_pm_id FROM order_kerja 
                        WHERE jadwal_pm_id IS NOT NULL 
                        AND deleted_st = 0
                        AND status NOT IN ('selesai', 'dibatalkan')
                    )
                ) x
                ORDER BY x.tgl_berikutnya ASC";
        
        return DbModel::rawData('result_array', $sql);
    }
}