<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\JadwalPmModel;


class AdminJadwalPm extends MyController
{
    public function __construct()
    {
        parent::__construct();
        // Menggunakan path view yang sudah benar
        $this->template = 'ipsrs::admin.pekerjaan.jadwal_pm.';
    }

    public function index()
    {
        $d = [];
        $this->save_session_search($d);
        $d['nav_sess'] = session(request('n'));

        // Ambil data aset untuk dropdown filter
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        
        $d['main'] = DbModel::getData('jadwal_pm', ['jadwal_pm_id' => $id]);
        $d['all_asset'] = DbModel::allData('asset', ['deleted_st' => 0, 'active_st' => 1]);

        if ($id === null) {
            $d['form_act'] = url('ipsrs/adminjadwalpm/save');
        } else {
            $d['form_act'] = url('ipsrs/adminjadwalpm/save/' . $id);
        }

        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();

        // Validasi Backend
        if (empty($d['asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Aset wajib dipilih!']));
        }
        if (empty($d['frekuensi'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Frekuensi wajib dipilih!']));
        }
        if (empty($d['tgl_berikutnya'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Tanggal PM Berikutnya wajib diisi!']));
        }

        // Format tanggal
        $d['tgl_terakhir'] = !empty($d['tgl_terakhir']) ? to_date($d['tgl_terakhir'], '-', 'date') : null;
        $d['tgl_berikutnya'] = to_date($d['tgl_berikutnya'], '-', 'date');

        if ($id == null) {
            $d['jadwal_pm_id'] = DbModel::getId('jadwal_pm', 2, 12);
            $result = DbModel::insertData('jadwal_pm', $d);
            return response()->json(_response($result ? '01' : '11', $this->uri, $d));
        } else {
            $result = DbModel::updateData('jadwal_pm', $d, ['jadwal_pm_id' => $id]);
            return response()->json(_response($result ? '02' : '12', $this->uri, $d));
        }
    }

    public function delete($id)
    {
        if (DbModel::getData('order_kerja', ['jadwal_pm_id' => $id, 'deleted_st' => 0])) {
            return response()->json(_response('13', $this->uri, ['message' => 'Jadwal ini sudah digunakan di Order Kerja.']));
        }
        $result = DbModel::deleteData('jadwal_pm', ['jadwal_pm_id' => $id]);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return JadwalPmModel::loadDatatables();
    }
}
