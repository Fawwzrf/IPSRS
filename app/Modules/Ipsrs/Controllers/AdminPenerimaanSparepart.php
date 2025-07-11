<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\PenerimaanSparepartModel;

class AdminPenerimaanSparepart extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PenerimaanSparepartModel();
        // Path view yang sudah direvisi
        $this->template = 'ipsrs::admin.inventaris.penerimaan_sparepart.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => 0, 'active_st' => 1]);
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = $id ? $this->model->getById($id) : null;
        $d['all_sparepart'] = DbModel::allData('mst_sparepart', ['deleted_st' => 0, 'active_st' => 1]);
        $d['form_act'] = $id ? url('ipsrs/adminpenerimaansparepart/save/' . $id) : url('ipsrs/adminpenerimaansparepart/save');
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();
        if (empty($d['sparepart_id'])) return response()->json(_response('11', $this->uri, ['message' => 'Sparepart wajib dipilih.']));
        if (empty($d['jumlah']) || (int)$d['jumlah'] <= 0) return response()->json(_response('11', $this->uri, ['message' => 'Jumlah harus lebih dari 0.']));

        // Membersihkan format harga SEBELUM dikirim ke model
        if (isset($d['harga_satuan'])) {
            $d['harga_satuan'] = str_replace(',', '.', str_replace('.', '', $d['harga_satuan']));
        }

        $result = $this->model->saveData($id, $d);
        if ($result['status']) {
            $response_code = ($result['mode'] == 'insert') ? '01' : '02';
            return response()->json(_response($response_code, $this->uri, $d));
        } else {
            return response()->json(_response('11', $this->uri, ['message' => $result['message']]));
        }
    }

    public function delete($id)
    {
        $result = $this->model->deleteData($id);
        if (!$result['status']) {
            return response()->json(_response('13', $this->uri, ['message' => $result['message']]));
        }
        return response()->json(_response('03', $this->uri));
    }

    public function ajax_datatables()
    {
        return PenerimaanSparepartModel::loadDatatables();
    }
}
