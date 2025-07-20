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
                // Generate order_kerja_id baru jika tidak ada
                if (empty($d['order_kerja_id'])) {
                    $d['order_kerja_id'] = self::generateOrderKerjaId();
                }

                // PENTING: Cek apakah order sudah ada untuk mencegah double insert
                $existingOrder = DB::table('order_kerja')
                    ->where('order_kerja_id', $d['order_kerja_id'])
                    ->first();

                if ($existingOrder) {
                    // Order sudah ada, kembalikan sukses dengan order_id yang sama
                    \Log::info('OrderKerjaModel: Order already exists', ['order_kerja_id' => $d['order_kerja_id']]);

                    DB::commit();
                    $result['status'] = true;
                    $result['order_kerja_id'] = $d['order_kerja_id'];
                    $result['message'] = 'Order kerja sudah ada';
                    return $result;
                }

                // Validasi check constraint chk_order_source
                if (empty($d['jadwal_pm_id']) && empty($d['permintaan_id'])) {
                    throw new \Exception("Order kerja harus memiliki jadwal PM atau permintaan");
                }

                // Daftar field yang valid sesuai struktur tabel sebenarnya
                $validFields = [
                    'order_kerja_id',
                    'jadwal_pm_id',
                    'permintaan_id',
                    'jenis',
                    'tgl_dibuat',
                    'tgl_target_selesai',
                    'tgl_selesai',
                    'prioritas',
                    'estimasi_biaya',
                    'catatan',
                    'status',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                    'deleted_st',
                    'active_st'
                ];

                // Buat array data baru yang hanya berisi field valid
                $dataToInsert = [];

                foreach ($validFields as $field) {
                    if (isset($d[$field])) {
                        // Format tanggal jika perlu
                        if (in_array($field, ['tgl_dibuat', 'tgl_target_selesai', 'tgl_selesai']) && !empty($d[$field])) {
                            $dataToInsert[$field] = to_date($d[$field], '-', 'date');
                        } else {
                            $dataToInsert[$field] = $d[$field];
                        }
                    }
                } // END OF FOREACH - PENTING: Jangan taruh kode lain dalam foreach

                // Jika ada deskripsi, pindahkan ke catatan
                if (isset($d['deskripsi']) && !isset($dataToInsert['catatan'])) {
                    $dataToInsert['catatan'] = $d['deskripsi'];
                }

                // Set nilai default untuk kolom wajib
                $dataToInsert['tgl_dibuat'] = $dataToInsert['tgl_dibuat'] ?? date('Y-m-d');
                $dataToInsert['status'] = $dataToInsert['status'] ?? 'baru';
                $dataToInsert['created_at'] = date('Y-m-d H:i:s');
                $dataToInsert['created_by'] = session('nama_user') ?? session('nama_pegawai') ?? 'system';

                // Pastikan jadwal_pm_id ada dalam dataToInsert jika ada di input
                if (isset($d['jadwal_pm_id']) && !isset($dataToInsert['jadwal_pm_id'])) {
                    $dataToInsert['jadwal_pm_id'] = $d['jadwal_pm_id'];
                }

                // Set jenis berdasarkan sumber order kerja
                if (!isset($dataToInsert['jenis'])) {
                    if (!empty($dataToInsert['jadwal_pm_id'])) {
                        $dataToInsert['jenis'] = 'pemeliharaan';
                    } elseif (!empty($dataToInsert['permintaan_id'])) {
                        $dataToInsert['jenis'] = 'perbaikan';
                    }
                }

                // Insert ke tabel order_kerja - HANYA SEKALI
                \Log::info('OrderKerjaModel: Inserting filtered data', ['data' => $dataToInsert]);

                $insert = DbModel::insertData('order_kerja', $dataToInsert);
                if (!$insert) {
                    throw new \Exception("Gagal menyimpan order kerja");
                }

                $order_kerja_id = $dataToInsert['order_kerja_id'];

                // Jika ada teknisi yang dipilih, insert ke penugasan_teknisi
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
                        $insertPenugasan = DbModel::insertData('penugasan_teknisi', $penugasan);

                        if (!$insertPenugasan) {
                            throw new \Exception("Gagal menyimpan penugasan teknisi");
                        }
                    }

                    // Update status order kerja menjadi 'ditugaskan'
                    DbModel::updateData(
                        'order_kerja',
                        ['status' => 'ditugaskan'],
                        ['order_kerja_id' => $order_kerja_id]
                    );
                }

                // PENTING: Commit setelah semua operasi database selesai
                DB::commit();

                // Baru log setelah semua proses selesai dan berhasil
                \Log::info('OrderKerjaModel: Insert berhasil', ['order_kerja_id' => $order_kerja_id]);

                // Set status hasil
                $result['status'] = true;
                $result['order_kerja_id'] = $order_kerja_id;
                $result['message'] = 'Order kerja berhasil dibuat';
            } else {
                $validFields = [
                    'jadwal_pm_id',
                    'permintaan_id',
                    'jenis',
                    'tgl_dibuat',
                    'tgl_target_selesai',
                    'tgl_selesai',
                    'prioritas',
                    'estimasi_biaya',
                    'catatan',
                    'status',
                    'updated_at',
                    'updated_by',
                    'deleted_st',
                    'active_st'
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

                // Jika ada deskripsi, pindahkan ke catatan
                if (isset($d['deskripsi']) && !isset($dataToUpdate['catatan'])) {
                    $dataToUpdate['catatan'] = $d['deskripsi'];
                }

                $dataToUpdate['updated_at'] = date('Y-m-d H:i:s');
                $dataToUpdate['updated_by'] = session('nama_user') ?? session('nama_pegawai') ?? 'system';

                $update = DbModel::updateData('order_kerja', $dataToUpdate, ['order_kerja_id' => $id]);
                if (!$update) {
                    throw new \Exception("Gagal mengupdate order kerja");
                }

                // Jika ada teknisi yang dipilih, update penugasan_teknisi
                if (isset($d['teknisi']) && is_array($d['teknisi'])) {
                    // Soft delete penugasan lama
                    DbModel::updateData('penugasan_teknisi', [
                        'deleted_st' => 1,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => session('nama_user') ?? session('nama_pegawai') ?? 'system'
                    ], [
                        'order_kerja_id' => $id
                    ]);

                    // Insert penugasan baru
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
                        $insertPenugasan = DbModel::insertData('penugasan_teknisi', $penugasan);
                        if (!$insertPenugasan) {
                            throw new \Exception("Gagal menyimpan penugasan teknisi");
                        }
                    }

                    // Update status order kerja menjadi 'ditugaskan'
                    DbModel::updateData(
                        'order_kerja',
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

    /**
     * Generate ID unik untuk order kerja baru
     * Format: 12 digit angka
     * 
     * @return string ID order kerja yang baru
     */
    public static function generateOrderKerjaId()
    {
        try {
            // Ambil ID terakhir dari database
            $lastId = DB::table('order_kerja')
                ->orderBy('order_kerja_id', 'desc')
                ->value('order_kerja_id');

            \Log::info('Last order_kerja_id', ['id' => $lastId]);

            // Jika belum ada data, mulai dari 1
            if (!$lastId) {
                $nextId = '000000000001';
            } else {
                // Ambil angka dari ID terakhir dan tambahkan 1
                $lastNumber = intval($lastId);
                $nextNumber = $lastNumber + 1;

                // Format menjadi 12 digit dengan leading zeros
                $nextId = str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
            }

            \Log::info('Generated new order_kerja_id', ['id' => $nextId]);
            return $nextId;
        } catch (\Exception $e) {
            \Log::error('Error generating order_kerja_id: ' . $e->getMessage());
            // Fallback: gunakan timestamp + random number
            return date('YmdHis') . rand(100, 999);
        }
    }

    /**
     * Generate penugasan_id with proper format (12 digits with leading zeros)
     * 
     * @return string Formatted penugasan_id
     */
    public static function generatePenugasanId()
    {
        try {
            // Get the last penugasan_id from database
            $lastId = DB::table('penugasan_teknisi')
                ->orderBy('penugasan_id', 'desc')
                ->value('penugasan_id');

            \Log::info('Last penugasan_id', ['id' => $lastId]);

            // If no records exist yet, start with 1
            if (!$lastId) {
                $nextId = '000000000001';
            } else {
                // Extract the numeric part and increment
                $lastNumber = intval($lastId);
                $nextNumber = $lastNumber + 1;

                // Format as 12 digits with leading zeros
                $nextId = str_pad($nextNumber, 12, '0', STR_PAD_LEFT);
            }

            \Log::info('Generated new penugasan_id', ['id' => $nextId]);
            return $nextId;
        } catch (\Exception $e) {
            \Log::error('Error generating penugasan_id: ' . $e->getMessage());
            // Fallback: simple counter with leading zeros
            return '000000' . time() % 1000000;
        }
    }
}
