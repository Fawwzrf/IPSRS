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

        $where = "1 = 1 ";

        // Filter berdasarkan aset
        if (@self::$nav_sess['search']['data']['asset_id'] != '') {
            $where .= " AND jp.asset_id = '" . @self::$nav_sess['search']['data']['asset_id'] . "' ";
        }

        // Filter berdasarkan status
        if (@self::$nav_sess['search']['data']['status'] != '') {
            $where .= " AND jp.status = '" . @self::$nav_sess['search']['data']['status'] . "' ";
        }

        // Filter berdasarkan pencarian
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $where .= " AND (LOWER(a.asset_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' 
                      OR LOWER(jp.jenis) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%'
                      OR LOWER(jp.frekuensi) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%') ";
        }

        // Gunakan subquery dengan alias seperti pada modul acuan
        $query = "SELECT * FROM (
                    SELECT 
                      jp.jadwal_pm_id,
                      a.asset_nm,
                      jp.frekuensi,
                      jp.jenis,
                      jp.tgl_terakhir,
                      jp.tgl_berikutnya,
                      jp.status,
                      jp.active_st
                    FROM trx_jadwal_pm jp
                      LEFT JOIN mst_asset a ON jp.asset_id = a.asset_id
                    WHERE $where AND jp.deleted_st = 0
                ) x ";

        $search = ['asset_nm', 'frekuensi', 'jenis', 'status'];
        $where = null;
        $isWhere = null;

        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }

    public static function saveData($post, $id = null)
    {
        // Memulai transaksi database untuk memastikan integritas data
        DB::beginTransaction();

        try {
            // 1. Siapkan array untuk menampung data yang akan disimpan
            $dataToSave = [
                'asset_id'      => $post['asset_id'],
                'frekuensi'     => $post['frekuensi'] ?? 'Bulanan', // Berikan nilai default
                'jenis'         => $post['jenis'],
                'status'        => $post['status'],
                'deskripsi'     => $post['deskripsi'] ?? null,
                'estimasi_menit' => $post['estimasi_menit'] ?? 0,
                'active_st'     => $post['active_st'] ?? 1,
            ];

            // 2. Konversi tanggal ke format database
            $dataToSave['tgl_terakhir'] = to_date($post['tgl_terakhir'], '-', 'date');

            // 3. Kalkulasi tanggal berikutnya
            $tglTerakhir = Carbon::createFromFormat('Y-m-d', $dataToSave['tgl_terakhir']);
            $tglBerikutnya = clone $tglTerakhir;

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
                    break;
                case 'Tahunan':
                    $tglBerikutnya->addYear();
                    break;
                default:
                    $tglBerikutnya = null;
                    break;
            }

            $dataToSave['tgl_berikutnya'] = $tglBerikutnya ? $tglBerikutnya->format('Y-m-d') : null;

            // 4. Proses simpan data
            if ($id == null) {
                // Insert mode
                if (empty($post['jadwal_pm_id'])) {
                    $dataToSave['jadwal_pm_id'] = DbModel::getId('trx_jadwal_pm', 2, 12);
                } else {
                    $dataToSave['jadwal_pm_id'] = $post['jadwal_pm_id'];
                }

                $dataToSave['created_by'] = session('user_name');
                $dataToSave['created_at'] = now();

                $result = DB::table('trx_jadwal_pm')->insert($dataToSave);
            } else {
                // Update mode
                $dataToSave['updated_by'] = session('user_name');
                $dataToSave['updated_at'] = now();

                $result = DB::table('trx_jadwal_pm')
                    ->where('jadwal_pm_id', $id)
                    ->update($dataToSave);
            }

            // Commit transaksi jika berhasil
            DB::commit();

            return true;
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();

            // Log error untuk debugging
            \Log::error('Error saving jadwal_pm: ' . $e->getMessage());

            return false;
        }
    }

    // Method lainnya tetap dipertahankan...
}
