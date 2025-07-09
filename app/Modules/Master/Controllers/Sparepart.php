<?php

namespace App\Modules\Master\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Master\Models\SparepartModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class Sparepart extends MyController
{
    public function __construct()
    {
        parent::__construct();
        $this->template = 'master::sparepart.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('mst_sparepart', ['sparepart_id' => $id]);
        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();

        // --- AWAL PERBAIKAN: VALIDASI BACKEND ---
        if ($id == null && empty($d['sparepart_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Sparepart wajib diisi!']));
        }
        if (empty($d['sparepart_nm'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Nama Sparepart wajib diisi!']));
        }
        if (empty($d['satuan'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Satuan wajib diisi!']));
        }
        // --- AKHIR PERBAIKAN ---

        // Hapus field stok dari array agar tidak bisa diupdate manual
        unset($d['stok']);

        // Validasi unik ID dan No. Seri
        if ($id == null) {
            if (DbModel::validId('mst_sparepart', 'sparepart_id', $d['sparepart_id'])) {
                return response()->json(_response('20', $this->uri, ['message' => 'ID Sparepart sudah ada!']));
            }
        }
        if (!empty($d['no_seri'])) {
            $sql = "SELECT sparepart_id FROM mst_sparepart WHERE no_seri = ? AND sparepart_id != ? AND deleted_st = 0";
            $params = [$d['no_seri'], (string)$id];
            if (DbModel::rawData('row_array', $sql, $params)) {
                return response()->json(_response('20', $this->uri, ['message' => 'Nomor Seri sudah digunakan!']));
            }
        }

        // Alur Insert/Update (tidak berubah)
        if ($id == null) {
            $result = DbModel::insertData('mst_sparepart', $d);
            return response()->json(_response($result ? '01' : '11', $this->uri, $d));
        } else {
            $result = DbModel::updateData('mst_sparepart', $d, ['sparepart_id' => $id]);
            return response()->json(_response($result ? '02' : '12', $this->uri, $d));
        }
    }

    public function delete($id)
    {
        // Logika pengecekan relasi sudah baik, pertahankan
        if (DbModel::getData('penggunaan_sparepart', ['sparepart_id' => $id, 'deleted_st' => 0])) {
            return response()->json(_response('13', $this->uri, ['message' => 'Sparepart ini telah digunakan dan tidak dapat dihapus.']));
        }
        if (DbModel::getData('trx_penerimaan_sparepart', ['sparepart_id' => $id, 'deleted_st' => 0])) {
            return response()->json(_response('13', $this->uri, ['message' => 'Sparepart ini memiliki riwayat penerimaan dan tidak dapat dihapus.']));
        }

        $result = DbModel::deleteData('mst_sparepart', ['sparepart_id' => $id]);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return SparepartModel::loadDatatables();
    }
}
