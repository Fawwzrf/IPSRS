<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\OrderKerjaModel;

class AdminOrderKerja extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new OrderKerjaModel();
        $this->template = 'ipsrs::admin.pekerjaan.order_kerja.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));

        // Data untuk dropdown filter
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1', 'jabatan_id' => '90']);

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = $id ? $this->model->getById($id) : null;

        // Mengambil teknisi yang sudah ditugaskan untuk OK ini (saat mode edit)
        if ($id) {
            $d['assigned_teknisi'] = array_column(
                DbModel::allData('penugasan_teknisi', ['order_kerja_id' => $id, 'deleted_st' => 0]),
                'pegawai_id'
            );
        } else {
            $d['assigned_teknisi'] = [];
        }

        // Ambil sumber yang belum dibuatkan OK
        $d['all_jadwal_pm'] = DbModel::rawData('result_array', "SELECT jp.*, a.asset_nm FROM jadwal_pm jp JOIN asset a ON jp.asset_id = a.asset_id WHERE jp.deleted_st = 0 AND (jp.jadwal_pm_id NOT IN (SELECT jadwal_pm_id FROM order_kerja WHERE jadwal_pm_id IS NOT NULL AND deleted_st = 0) OR jp.jadwal_pm_id = ?)", [@$d['main']['jadwal_pm_id']]);
        $d['all_komplain'] = DbModel::rawData('result_array', "SELECT pk.*, a.asset_nm FROM permintaan_komplain pk JOIN asset a ON pk.asset_id = a.asset_id WHERE pk.deleted_st = 0 AND (pk.permintaan_id NOT IN (SELECT permintaan_id FROM order_kerja WHERE permintaan_id IS NOT NULL AND deleted_st = 0) OR pk.permintaan_id = ?)", [@$d['main']['permintaan_id']]);

        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1', 'jabatan_id' => '90']);

        $d['form_act'] = $id ? url('ipsrs/adminorderkerja/save/' . $id) : url('ipsrs/adminorderkerja/save');
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        // 1. Ambil semua data mentah dari form
        $post_data = _post();

        // 2. Panggil method saveData dari Model.
        $result = $this->model->saveData($id, $post_data);

        // 3. Log hasil dari model (opsional, bagus untuk debugging)
        \Log::info('Hasil dari model saveData:', $result);

        // 4. Periksa hasil dari model dan kirim response JSON yang sesuai
        if (isset($result['status']) && $result['status'] === true) {
            // Tentukan kode respons berdasarkan mode (insert atau update)
            $response_code = ($result['mode'] == 'insert') ? '01' : '02';

            // Kirim response sukses menggunakan pola yang sudah ada
            return response()->json(_response($response_code, $this->uri, $post_data));
        } else {
            // Jika gagal, kirim response error dengan pesan dari model
            return response()->json(_response('11', $this->uri, [
                'message' => $result['message'] ?? 'Terjadi kesalahan yang tidak diketahui.'
            ]));
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
