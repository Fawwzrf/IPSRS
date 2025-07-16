<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\OrderKerjaModel;
use App\Modules\Ipsrs\Models\LogKerjaModel;
use Illuminate\Http\Request; // Gunakan Request class untuk file
use Illuminate\Support\Facades\Log;

class AdminOrderKerja extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderKerjaModel();
        $this->template = 'ipsrs::admin.pekerjaan.order_kerja.';
    }

    // ... (metode index dan form_modal biarkan seperti yang sudah ada)
    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1', 'jabatan_id' => '90']);
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = $id ? $this->model->getById($id) : null;
        if ($id) {
            $d['assigned_teknisi'] = array_column(
                DbModel::allData('penugasan_teknisi', ['order_kerja_id' => $id, 'deleted_st' => 0]),
                'pegawai_id'
            );
        } else {
            $d['assigned_teknisi'] = [];
        }
        $d['all_jadwal_pm'] = DbModel::rawData('result_array', "SELECT jp.*, a.asset_nm FROM jadwal_pm jp JOIN asset a ON jp.asset_id = a.asset_id WHERE jp.deleted_st = 0 AND (jp.jadwal_pm_id NOT IN (SELECT jadwal_pm_id FROM order_kerja WHERE jadwal_pm_id IS NOT NULL AND deleted_st = 0) OR jp.jadwal_pm_id = ?)", [@$d['main']['jadwal_pm_id']]);
        $d['all_komplain'] = DbModel::rawData('result_array', "SELECT pk.*, a.asset_nm FROM permintaan_komplain pk JOIN asset a ON pk.asset_id = a.asset_id WHERE pk.deleted_st = 0 AND (pk.permintaan_id NOT IN (SELECT permintaan_id FROM order_kerja WHERE permintaan_id IS NOT NULL AND deleted_st = 0) OR pk.permintaan_id = ?)", [@$d['main']['permintaan_id']]);
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1', 'jabatan_id' => '90']);
        $d['form_act'] = $id ? url('ipsrs/adminorderkerja/save/' . $id) : url('ipsrs/adminorderkerja/save');
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save(Request $request, $id = null)
    {
        $d = $request->all();

        // Cek apakah ini aksi simpan log kerja
        if (isset($d['action']) && $d['action'] == 'save_log_kerja') {
            $logKerjaModel = new LogKerjaModel();
            $result = $logKerjaModel->saveData($id, $d);

            if ($result['status']) {
                // INI ADALAH RESPONSE YANG BENAR: Kode 01, tanpa redirect
                return response()->json(_response('01', $this->uri, ['message' => 'Laporan kerja berhasil disimpan.']));
            } else {
                return response()->json(_response('11', $this->uri, ['message' => $result['message']]));
            }
        } else {
            // Ini adalah blok untuk menyimpan Order Kerja (bukan log kerja), JANGAN DIHAPUS
            $result = $this->model->saveData($id, $d);
            if ($result['status']) {
                $response_code = ($result['mode'] == 'insert') ? '01' : '02';
                return response()->json(_response($response_code, $this->uri, $d));
            } else {
                return response()->json(_response('11', $this->uri, ['message' => $result['message']]));
            }
        }
    }

    public function delete($id)
    {
        $result = $this->model->deleteData($id);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return OrderKerjaModel::loadDatatables();
    }
}
