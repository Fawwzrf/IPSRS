<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalPmModel extends Model
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

        // 1. Siapkan query utama TANPA klausa WHERE
        $query = "SELECT
                    pm.jadwal_pm_id,
                    a.asset_nm,
                    pm.frekuensi,
                    pm.jenis,
                    pm.tgl_terakhir,
                    pm.tgl_berikutnya,
                    pm.status,
                    pm.active_st
                  FROM jadwal_pm pm
                  LEFT JOIN asset a ON pm.asset_id = a.asset_id";

        // 2. Kolom yang bisa dicari oleh Datatables
        $searchableColumns = ['a.asset_nm', 'pm.frekuensi', 'pm.jenis', 'pm.status'];

        // 3. Kumpulkan kondisi WHERE dalam bentuk array key-value
        $whereConditions = [];
        $whereConditions['pm.deleted_st'] = 0; // Kondisi dasar

        if (!empty(self::$nav_sess['search']['data']['asset_id'])) {
            $whereConditions['pm.asset_id'] = self::$nav_sess['search']['data']['asset_id'];
        }
        if (!empty(self::$nav_sess['search']['data']['status'])) {
            $whereConditions['pm.status'] = self::$nav_sess['search']['data']['status'];
        }

        // 4. Kondisi pencarian 'term' sebagai string SQL murni (jika ada)
        $whereString = '';
        if (!empty(self::$nav_sess['search']['data']['term'])) {
            $searchTerm = strtolower(addslashes(self::$nav_sess['search']['data']['term']));
            $whereString = " (LOWER(a.asset_nm) LIKE '%{$searchTerm}%' OR LOWER(pm.jenis) LIKE '%{$searchTerm}%') ";
        }
        
        // 5. Panggil datatablesQuery dengan parameter yang benar
        $result = DbModel::datatablesQuery($query, $searchableColumns, $whereConditions, $whereString);
        
        return response()->json($result);
    }
    public static function saveData($post)
    {
        // Memulai transaksi database untuk memastikan integritas data
        DB::beginTransaction();

        try {
            // 1. Ambil ID dari data post. Jika kosong, berarti ini adalah record baru.
            $id = $post['jadwal_pm_id'] ?? null;

            // 2. Siapkan array untuk menampung data yang akan disimpan.
            $dataToSave = [
                'asset_id'      => $post['asset_id'],
                'frekuensi'     => $post['frekuensi'],
                'jenis'         => $post['jenis'],
                'status'        => $post['status'],
                'deskripsi'     => $post['deskripsi'] ?? null,
                'estimasi_menit' => $post['estimasi_menit'] ?? 0,
                'active_st'     => $post['active_st'] ?? 1,
                // Meta data untuk tracking
                'updated_by'    => session('user_name'),
                'updated_at'    => now()
            ];

            // 3. Kalkulasi dan format tanggal secara otomatis
            $tglTerakhir = Carbon::createFromFormat('d-m-Y', $post['tgl_terakhir']);
            $dataToSave['tgl_terakhir'] = $tglTerakhir->format('Y-m-d'); // Simpan format Y-m-d ke DB

            $tglBerikutnya = clone $tglTerakhir;

            // Menggunakan enum dari file .sql Anda
            switch ($post['frekuensi']) {
                case 'Harian':
                    $tglBerikutnya->addDay();
                    break;
                case 'Mingguan':
                    $tglBerikutnya->addWeek();
                    break;
                case 'Bulanan':
                    $tglBerikutnya->addMonth();
                    break;
                case 'Kuartalan':
                    $tglBerikutnya->addMonths(3);
                    break; // Sesuai file .sql
                case 'Tahunan':
                    $tglBerikutnya->addYear();
                    break;
                default:
                    $tglBerikutnya = null;
                    break;
            }

            // Masukkan tanggal berikutnya ke array data jika berhasil dihitung
            $dataToSave['tgl_berikutnya'] = $tglBerikutnya ? $tglBerikutnya->format('Y-m-d') : null;

            // 4. Proses Insert atau Update
            if (is_null($id)) {
                // INSERT (Data Baru)
                // Generate ID baru & tambahkan meta data 'created'
                $dataToSave['jadwal_pm_id'] = self::generateId();
                $dataToSave['created_by'] = session('user_name');
                $dataToSave['created_at'] = now();

                DB::table('jadwal_pm')->insert($dataToSave);
            } else {
                // UPDATE (Data Lama)
                DB::table('jadwal_pm')->where('jadwal_pm_id', $id)->update($dataToSave);
            }

            // Jika semua proses berhasil, commit transaksi
            DB::commit();
            return ['success' => true, 'msg' => 'Data jadwal PM berhasil disimpan!'];
        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua query dalam transaksi
            DB::rollBack();
            // Kembalikan pesan error yang informatif untuk debugging
            return ['success' => false, 'msg' => 'Gagal menyimpan data: ' . $e->getMessage()];
        }
    }
}
