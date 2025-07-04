<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\SparepartModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Dipertahankan sesuai Asset.php

class Sparepart extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::sparepart.'; // Lokasi views untuk modul ini
    }

    function index()
    {
        $d = [];
        $d['nav_sess'] = session(request('n')); // Untuk filter di index

        return $this->renderView($this->template . 'index', $d);
    }

    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('mst_sparepart', ['sparepart_id' => $id]);
        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    function save($id = null)
    {
        $d = _post();

        $d['sparepart_nm'] = strtoupper($d['sparepart_nm']); // Nama sparepart diubah ke uppercase
        if (isset($d['merk'])) $d['merk'] = strtoupper($d['merk']);

        // Validasi unik sparepart_id (untuk tambah data)
        if ($id == null) {
            if (empty($d['sparepart_id'])) {
                return response()->json(_response('11', $this->uri, ['message' => 'ID Sparepart wajib diisi!']));
            }
            if (DbModel::validId('mst_sparepart', 'sparepart_id', $d['sparepart_id'])) {
                return response()->json(_response('20', $this->uri, ['message' => 'ID Sparepart sudah ada!']));
            }
        }

        // Validasi unik sparepart_no_seri (jika ada)
        if (!empty($d['no_seri'])) {
            $queryCheckNoSeri = DbModel::rawData('row_array', "SELECT * FROM mst_sparepart WHERE no_seri = '" . addslashes($d['no_seri']) . "' AND sparepart_id != '" . addslashes($id) . "' AND deleted_st = 0");
            if ($queryCheckNoSeri != null) {
                return response()->json(_response('20', $this->uri, ['message' => 'Nomor Seri sparepart sudah digunakan!']));
            }
        }
        
        try {
            if ($id == null) {
                $result = DbModel::insertData('mst_sparepart', $d);
                if ($result) {
                    return response()->json(_response('01', $this->uri, $d));
                } else {
                    return response()->json(_response('11', $this->uri, $d));
                }
            } else {
                $result = DbModel::updateData('mst_sparepart', $d, ['sparepart_id' => $id]);
                if ($result) {
                    return response()->json(_response('02', $this->uri, $d));
                } else {
                    return response()->json(_response('12', $this->uri, $d));
                }
            }
        } catch (\Throwable $th) {
            Log::error('Error saving sparepart: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }
    }

    public function delete($id)
    {
        // Validasi integritas data sebelum menghapus (penting untuk MyISAM)
        // Periksa apakah sparepart ini masih terhubung dengan penggunaan_sparepart atau trx_penerimaan_sparepart
        $hasUsage = DbModel::getData('penggunaan_sparepart', ['sparepart_id' => $id, 'deleted_st' => 0]);
        if ($hasUsage) {
            return response()->json(_response('13', $this->uri, ['message' => 'Sparepart ini telah digunakan dalam log kerja dan tidak dapat dihapus.']));
        }
        $hasReceipt = DbModel::getData('trx_penerimaan_sparepart', ['sparepart_id' => $id, 'deleted_st' => 0]);
        if ($hasReceipt) {
            return response()->json(_response('13', $this->uri, ['message' => 'Sparepart ini memiliki riwayat penerimaan dan tidak dapat dihapus.']));
        }

        $result = DbModel::deleteData('mst_sparepart', ['sparepart_id' => $id]); // Logically delete
        if ($result) {
            return response()->json(_response('03', $this->uri));
        } else {
            return response()->json(_response('13', $this->uri));
        }
    }

    public function ajax_datatables()
    {
        return SparepartModel::loadDatatables();
    }
}