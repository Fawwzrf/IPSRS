<?php

namespace App\Modules\Ipsrs\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenerimaanSparepartModel extends Model
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
        
        $where = "p.deleted_st = 0";
        
        // Filter berdasarkan pencarian jika ada
        if (@self::$nav_sess['search']['data']['term'] != '') {
            $term = strtolower(self::$nav_sess['search']['data']['term']);
            $where .= " AND (
                LOWER(p.penerimaan_id) LIKE '%" . $term . "%' OR
                LOWER(s.sparepart_nm) LIKE '%" . $term . "%' OR
                LOWER(p.vendor) LIKE '%" . $term . "%' OR
                LOWER(p.no_faktur) LIKE '%" . $term . "%'
            )";
        }
        
        // Gunakan subquery dengan alias untuk konsistensi dengan modul lain
        $query = "SELECT * FROM (
                    SELECT 
                        p.penerimaan_id,
                        p.tgl,
                        p.jumlah,
                        p.harga_satuan,
                        p.vendor,
                        p.no_faktur,
                        p.catatan,
                        s.sparepart_nm,
                        s.sparepart_id
                    FROM 
                        trx_penerimaan_sparepart p
                        JOIN mst_sparepart s ON p.sparepart_id = s.sparepart_id
                    WHERE $where
                  ) x";
        
        $search = ['penerimaan_id', 'sparepart_nm', 'vendor', 'no_faktur'];
        $where = null;
        $isWhere = null;
        
        $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
        return response()->json($result);
    }

    public function getById($id)
    {
        if (!$id) return null;
        return DbModel::getData('trx_penerimaan_sparepart', ['penerimaan_id' => $id]);
    }

    public function saveData($id, $data)
    {
        $sparepart_id = $data['sparepart_id'];
        $jumlah_diterima = (int)$data['jumlah']; // Dihapus pembagian 2 yang menyebabkan bug
        $harga_penerimaan = (float)($data['harga_satuan'] ?? 0);
        
        try {
            DB::beginTransaction();

            $sparepartMaster = DB::table('mst_sparepart')
                ->where('sparepart_id', $sparepart_id)
                ->lockForUpdate()
                ->first();
                
            if (!$sparepartMaster) {
                throw new \Exception("Sparepart tidak ditemukan.");
            }

            $stok_lama = (int)$sparepartMaster->stok;
            $harga_lama = (float)$sparepartMaster->harga;
            
            // Perhitungan Stok & Harga Baru
            $stok_baru = $stok_lama + $jumlah_diterima;
            $nilai_total_lama = $stok_lama * $harga_lama;
            $nilai_penerimaan_baru = $jumlah_diterima * $harga_penerimaan;
            $harga_rata_rata_baru = ($stok_baru > 0) ? ($nilai_total_lama + $nilai_penerimaan_baru) / $stok_baru : $harga_penerimaan;

            // Update data master sparepart
            DB::table('mst_sparepart')
                ->where('sparepart_id', $sparepart_id)
                ->update([
                    'stok' => $stok_baru,
                    'harga' => $harga_rata_rata_baru,
                    'updated_by' => session('user_name'),
                    'updated_at' => now()
                ]);

            // Persiapkan data transaksi penerimaan
            $saveData = [
                'sparepart_id' => $sparepart_id,
                'tgl' => to_date($data['tgl'], '-', 'date'),
                'jumlah' => $jumlah_diterima,
                'harga_satuan' => $harga_penerimaan,
                'vendor' => $data['vendor'] ?? null,
                'no_faktur' => $data['no_faktur'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ];

            if ($id) {
                // Mode update
                $penerimaan_lama = $this->getById($id);
                if (!$penerimaan_lama) {
                    throw new \Exception("Data penerimaan tidak ditemukan.");
                }
                
                // Kembalikan stok lama sebelum update
                $jumlah_lama = (int)$penerimaan_lama['jumlah'];
                DB::table('mst_sparepart')
                    ->where('sparepart_id', $sparepart_id)
                    ->update(['stok' => DB::raw("stok - $jumlah_lama")]);
                    
                // Update penerimaan
                $saveData['updated_by'] = session('user_name');
                $saveData['updated_at'] = now();
                DbModel::updateData('trx_penerimaan_sparepart', $saveData, ['penerimaan_id' => $id]);
                $mode = 'update';
            } else {
                // Mode insert baru
                $saveData['penerimaan_id'] = DbModel::getId('trx_penerimaan_sparepart', 2, 12);
                $saveData['created_by'] = session('user_name');
                $saveData['created_at'] = now();
                DbModel::insertData('trx_penerimaan_sparepart', $saveData);
                $mode = 'insert';
            }

            DB::commit();
            return ['status' => true, 'mode' => $mode];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => 'Transaksi gagal: ' . $e->getMessage()];
        }
    }

    public function deleteData($id)
    {
        try {
            DB::beginTransaction();
            
            $penerimaan = $this->getById($id);
            if (!$penerimaan) {
                return ['status' => false, 'message' => 'Data penerimaan tidak ditemukan.'];
            }

            // Kurangi stok di master
            $sparepart_id = $penerimaan['sparepart_id'];
            $jumlah = (int)$penerimaan['jumlah'];
            
            $sparepartMaster = DB::table('mst_sparepart')
                ->where('sparepart_id', $sparepart_id)
                ->lockForUpdate()
                ->first();
                
            if (!$sparepartMaster) {
                throw new \Exception("Sparepart tidak ditemukan.");
            }
            
            $stok_lama = (int)$sparepartMaster->stok;
            if ($stok_lama < $jumlah) {
                return ['status' => false, 'message' => 'Stok tidak cukup untuk dibatalkan. Ada kemungkinan sparepart sudah digunakan.'];
            }
            
            // Update stok master
            DB::table('mst_sparepart')
                ->where('sparepart_id', $sparepart_id)
                ->update([
                    'stok' => $stok_lama - $jumlah,
                    'updated_by' => session('user_name'),
                    'updated_at' => now()
                ]);

            // Soft delete transaksi
            DbModel::updateData('trx_penerimaan_sparepart', [
                'deleted_st' => 1,
                'updated_by' => session('user_name'),
                'updated_at' => now()
            ], ['penerimaan_id' => $id]);

            DB::commit();
            return ['status' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => 'Transaksi gagal: ' . $e->getMessage()];
        }
    }
}
