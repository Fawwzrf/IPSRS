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
        if (is_null(self::$nav_sess)) self::$nav_sess = session(request('n'));
    }

    static function loadDatatables()
    {
        self::initSession();
        $query = "SELECT p.*, s.sparepart_nm 
                  FROM trx_penerimaan_sparepart p
                  JOIN mst_sparepart s ON p.sparepart_id = s.sparepart_id";
        $searchableColumns = ['p.penerimaan_id', 's.sparepart_nm', 'p.vendor', 'p.no_faktur'];
        $whereConditions = ['p.deleted_st' => 0];
        // ... (Tambahkan logika filter jika diperlukan) ...

        $result = DbModel::datatablesQuery($query, $searchableColumns, $whereConditions, '');
        return response()->json($result);
    }

    public function getById($id)
    {
        return DbModel::getData('trx_penerimaan_sparepart', ['penerimaan_id' => $id]);
    }

    public function saveData($id, $data)
    {
        $saveData = [
            'sparepart_id' => $data['sparepart_id'],
            'tgl' => to_date($data['tgl'], '-', 'date'),
            'jumlah' => $data['jumlah'],
            'harga_satuan' => $data['harga_satuan'] ?? 0.00,
            'vendor' => $data['vendor'] ?? null,
            'no_faktur' => $data['no_faktur'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ];

        try {
            DB::beginTransaction();
            $stokToAdjust = 0;

            if ($id == null) {
                $saveData['penerimaan_id'] = DbModel::getId('trx_penerimaan_sparepart', 2, 12);
                DbModel::insertData('trx_penerimaan_sparepart', $saveData);
                $stokToAdjust = $saveData['jumlah'];
                $mode = 'insert';
            } else {
                $oldData = $this->getById($id);
                $stokToAdjust = $saveData['jumlah'] - $oldData['jumlah'];
                DbModel::updateData('trx_penerimaan_sparepart', $saveData, ['penerimaan_id' => $id]);
                $mode = 'update';
            }

            DB::table('mst_sparepart')->where('sparepart_id', $data['sparepart_id'])->increment('stok', $stokToAdjust);
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
            $data = $this->getById($id);
            if (!$data) return ['status' => false, 'message' => 'Data tidak ditemukan.'];

            DB::table('mst_sparepart')->where('sparepart_id', $data['sparepart_id'])->decrement('stok', $data['jumlah']);
            DbModel::updateData('trx_penerimaan_sparepart', ['deleted_st' => 1], ['penerimaan_id' => $id]);

            DB::commit();
            return ['status' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => 'Transaksi gagal: ' . $e->getMessage()];
        }
    }
}
