<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\PermintaanKomplainModel;

class AdminPermintaanKomplain extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PermintaanKomplainModel();
        $this->template = 'ipsrs::admin.pekerjaan.permintaan_komplain.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));

        // Data untuk dropdown filter di halaman utama
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1]);

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = $id ? $this->model->getById($id) : null;

        // Mengirim semua data yang dibutuhkan untuk logika JavaScript di sisi klien
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);

        $d['form_act'] = $id ? url('ipsrs/adminpermintaankomplain/save/' . $id) : url('ipsrs/adminpermintaankomplain/save');
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();

        // Validasi
        if (empty($d['lokasi_id'])) return response()->json(_response('11', $this->uri, ['message' => 'Lokasi wajib dipilih!']));
        if (empty($d['asset_id'])) return response()->json(_response('11', $this->uri, ['message' => 'Aset wajib dipilih!']));
        if (empty($d['pegawai_id'])) return response()->json(_response('11', $this->uri, ['message' => 'Pembuat komplain wajib dipilih!']));
        if (empty($d['deskripsi'])) return response()->json(_response('11', $this->uri, ['message' => 'Deskripsi wajib diisi!']));

        $asset = DbModel::getData('asset', ['asset_id' => $d['asset_id'], 'deleted_st' => 0]);
        if (!$asset || $asset['lokasi_id'] != $d['lokasi_id']) {
            return response()->json(_response('11', $this->uri, [
                'message' => 'Data tidak konsisten! Aset yang dipilih tidak sesuai dengan lokasi yang ditentukan.'
            ]));
        }
        
        $result = $this->model->saveData($id, $d);

        if ($result['status']) {
            $response_code = ($result['mode'] == 'insert') ? '01' : '02';
            return response()->json(_response($response_code, $this->uri, $d));
        } else {
            $response_code = ($result['mode'] == 'insert') ? '11' : '12';
            return response()->json(_response($response_code, $this->uri, ['message' => 'Gagal menyimpan data.']));
        }
    }

    public function delete($id)
    {
        $result = $this->model->deleteData($id);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return PermintaanKomplainModel::loadDatatables();
    }
}
