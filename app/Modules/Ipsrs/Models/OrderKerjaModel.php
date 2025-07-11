<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

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
        if (is_null(self::$nav_sess)) self::$nav_sess = session(request('n'));
    }

    static function loadDatatables()
    {
        self::initSession();

        // 1. Query utama disederhanakan, TANPA subquery GROUP_CONCAT
        $query = "SELECT 
                    ok.order_kerja_id, ok.tgl_dibuat, ok.status, ok.jenis, ok.prioritas,
                    COALESCE(a1.asset_nm, a2.asset_nm) as asset_nm,
                    COALESCE(pk.deskripsi, jp.jenis) as deskripsi_sumber
                  FROM order_kerja ok
                  LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                  LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                  LEFT JOIN asset a1 ON pk.asset_id = a1.asset_id
                  LEFT JOIN asset a2 ON jp.asset_id = a2.asset_id";

        $searchableColumns = ['ok.order_kerja_id', 'a1.asset_nm', 'a2.asset_nm', 'pk.deskripsi', 'jp.jenis'];
        $whereConditions = ['ok.deleted_st' => 0];
        $search_data = self::$nav_sess['search']['data'] ?? [];

        if (!empty($search_data['status'])) $whereConditions['ok.status'] = $search_data['status'];
        if (!empty($search_data['jenis'])) $whereConditions['ok.jenis'] = $search_data['jenis'];

        $whereString = '';
        if (!empty($search_data['term'])) {
            $searchTerm = strtolower(addslashes($search_data['term']));
            $whereString = " (LOWER(COALESCE(a1.asset_nm, a2.asset_nm)) LIKE '%{$searchTerm}%' OR LOWER(COALESCE(pk.deskripsi, jp.jenis)) LIKE '%{$searchTerm}%' OR ok.order_kerja_id LIKE '%{$searchTerm}%') ";
        }

        // 2. Panggil helper datatables dengan query yang sederhana
        $result = DbModel::datatablesQuery($query, $searchableColumns, $whereConditions, $whereString);

        // 3. Tambahkan data Tim Teknisi secara manual setelah mendapatkan data utama
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

    // File: Modules/Ipsrs/Models/OrderKerjaModel.php

    public static function saveData($id, $post_data) // Menambahkan parameter $id untuk konsistensi
    {
        // 1. Validasi Input Utama
        if (empty($post_data['jadwal_pm_id']) && empty($post_data['permintaan_id'])) {
            // PERBAIKAN: Mengembalikan 'status'
            return ['status' => false, 'message' => 'Pilih salah satu sumber pekerjaan (Jadwal PM atau Komplain).', 'mode' => 'validation'];
        }
        // Menggunakan nama yang benar dari form: 'pegawai_ids'
        if (empty($post_data['pegawai_ids'])) {
            // PERBAIKAN: Mengembalikan 'status'
            return ['status' => false, 'message' => 'Pilih minimal satu teknisi untuk ditugaskan.', 'mode' => 'validation'];
        }

        // 2. Memulai Transaksi Database untuk keamanan data
        \DB::beginTransaction();

        try {
            // Mengambil ID order kerja atau membuat yang baru
            $is_insert = ($id == null);
            $order_kerja_id = $id ?? DbModel::getId('order_kerja', 2, 12);

            // 3. Menyiapkan data untuk tabel 'order_kerja'
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
                DbModel::insertData('order_kerja', $data_ok);
            } else {
                DbModel::updateData('order_kerja', $data_ok, ['order_kerja_id' => $id]);
            }


            // 4. Proses penugasan teknisi
            // Hapus penugasan lama terlebih dahulu untuk menghindari duplikat saat edit
            \DB::table('penugasan_teknisi')->where('order_kerja_id', $order_kerja_id)->delete();

            // Loop menggunakan nama yang benar dari form: 'pegawai_ids'
            foreach ($post_data['pegawai_ids'] as $pegawai_id) {
                $penugasan_data = [
                    'penugasan_id'    => DbModel::getId('penugasan_teknisi', 2, 12),
                    'order_kerja_id'  => $order_kerja_id,
                    'pegawai_id'      => $pegawai_id,
                    'status'          => 'ditugaskan', // Status default untuk penugasan baru
                ];
                \DB::table('penugasan_teknisi')->insert($penugasan_data);
            }

            // 5. Update status sumber pekerjaan
            if (!empty($post_data['jadwal_pm_id'])) {
                \DB::table('jadwal_pm')
                    ->where('jadwal_pm_id', $post_data['jadwal_pm_id'])
                    ->update(['status' => 'diproses']);
            }
            if (!empty($post_data['permintaan_id'])) {
                \DB::table('permintaan_komplain')
                    ->where('permintaan_id', $post_data['permintaan_id'])
                    ->update(['status' => 'diproses']);
            }

            // Jika semua proses berhasil, simpan perubahan secara permanen
            \DB::commit();
            // PERBAIKAN: Mengembalikan 'status' dan 'mode'
            return [
                'status' => true,
                'message' => 'Order Kerja berhasil disimpan.',
                'mode' => $is_insert ? 'insert' : 'update'
            ];
        } catch (\Exception $e) {
            \DB::rollBack();
            // Return saat GAGAL (exception)
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
            \DB::beginTransaction();
            DbModel::updateData('order_kerja', ['deleted_st' => 1], ['order_kerja_id' => $id]);
            DbModel::updateData('penugasan_teknisi', ['deleted_st' => 1], ['order_kerja_id' => $id]);
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            return false;
        }
    }
}