<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class KinerjaTimModel extends Model
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

        $where = "pt.deleted_st = 0"; // Mulai dengan kondisi dasar
        $bindings = [];

        // Filter berdasarkan rentang tanggal (berdasarkan tanggal selesai tugas)
        if (!empty(self::$nav_sess['search']['data']['tgl_start']) && !empty(self::$nav_sess['search']['data']['tgl_end'])) {
            $where .= " AND DATE(pt.tgl_selesai) BETWEEN ? AND ?";
            $bindings[] = to_date(self::$nav_sess['search']['data']['tgl_start'], '-', 'date');
            $bindings[] = to_date(self::$nav_sess['search']['data']['tgl_end'], '-', 'date');
        }

        // Filter berdasarkan teknisi
        if (!empty(self::$nav_sess['search']['data']['pegawai_id'])) {
            $where .= " AND pt.pegawai_id = ?";
            $bindings[] = self::$nav_sess['search']['data']['pegawai_id'];
        }

        // Query utama untuk mengambil data dan menghitung durasi
        $query = "SELECT * FROM (
                    SELECT 
                        ok.order_kerja_id,
                        p.pegawai_nm as nama_teknisi,
                        COALESCE(a1.asset_nm, a2.asset_nm) as nama_aset,
                        pk.created_at as waktu_komplain_masuk,
                        ok.tgl_dibuat as waktu_ok_dibuat,
                        pt.tgl_mulai as waktu_tugas_diterima,
                        pt.tgl_selesai as waktu_tugas_selesai,
                        TIMESTAMPDIFF(MINUTE, pk.created_at, ok.tgl_dibuat) as durasi_respon_admin,
                        TIMESTAMPDIFF(MINUTE, ok.tgl_dibuat, pt.tgl_mulai) as durasi_penerimaan_teknisi,
                        TIMESTAMPDIFF(MINUTE, pt.tgl_mulai, pt.tgl_selesai) as durasi_pengerjaan,
                        TIMESTAMPDIFF(MINUTE, pk.created_at, pt.tgl_selesai) as durasi_total
                    FROM 
                        penugasan_teknisi pt
                    JOIN order_kerja ok ON pt.order_kerja_id = ok.order_kerja_id
                    JOIN mst_pegawai p ON pt.pegawai_id = p.pegawai_id
                    LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                    LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                    LEFT JOIN asset a1 ON pk.asset_id = a1.asset_id
                    LEFT JOIN asset a2 ON jp.asset_id = a2.asset_id
                    WHERE $where
                ) x";

        // Parameter untuk DataTables
        $search = ['order_kerja_id', 'nama_teknisi', 'nama_aset'];
        $isWhere = null;

        // Gunakan datatablesQuery helper dengan bindings
        return self::datatablesQueryWithBindings($query, $search, $isWhere, $bindings);
    }

    // Helper kustom untuk menangani query dengan bindings
    static function datatablesQueryWithBindings($query, $keyword, $iswhere, $bindings)
    {
        $d = _post();
        $_search_value = @$d['search']['value'];
        $search = strtolower(htmlspecialchars($_search_value));
        $limit = (int)@$d['length'];
        $start = (int)@$d['start'];
        $_order_field = @$d['order'][0]['column'];
        $_order_ascdesc = @$d['order'][0]['dir'];
        $order_field_name = @$d['columns'][$_order_field]['data'];

        $queryAllRecords = str_replace_between($query, 'SELECT', 'FROM', ' COUNT(1) AS count ');
        $recordsTotal = DbModel::rawData('row_array', $queryAllRecords, $bindings)['count'];

        if (!empty($search) && is_array($keyword)) {
            $searchKeyword = implode(" LIKE ? OR ", $keyword) . " LIKE ?";
            $query .= " WHERE (" . $searchKeyword . ")";
            for ($i = 0; $i < count($keyword); $i++) {
                $bindings[] = '%' . $search . '%';
            }
        }

        $queryFiltered = str_replace_between($query, 'SELECT', 'FROM', ' COUNT(1) AS count ');
        $recordsFiltered = DbModel::rawData('row_array', $queryFiltered, $bindings)['count'];

        $query .= " ORDER BY " . $order_field_name . " " . $_order_ascdesc;
        $query .= " LIMIT " . $limit . " OFFSET " . $start;

        $data = DbModel::rawData('result_array', $query, $bindings);

        return response()->json([
            'draw' => $d['draw'],
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }
}
