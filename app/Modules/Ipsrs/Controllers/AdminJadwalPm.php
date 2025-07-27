<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\JadwalPmModel;
use Illuminate\Support\Facades\Log;

class AdminJadwalPm extends MyController
{
    public function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::admin.pekerjaan.jadwal_pm.';
    }

    public function index()
    {
        $d = [];

        // Penting: Panggil save_session_search untuk mengelola session pencarian
        $this->save_session_search($d);

        // Tidak perlu set $d['nav_sess'] = session(request('n')) karena sudah dihandle di parent

        // Data untuk dropdown filter
        $d['all_asset'] = DbModel::allData('mst_asset', ['deleted_st' => 0, 'active_st' => 1]);

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('trx_jadwal_pm', ['jadwal_pm_id' => $id]);
        $d['all_asset'] = DbModel::allData('mst_asset', ['deleted_st' => 0, 'active_st' => 1]);
        $d['form_act'] = $this->uri . '/save/' . $id;

        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();

        // Validasi backend
        if (empty($d['asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Aset wajib dipilih!']));
        }
        if (empty($d['frekuensi'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Frekuensi wajib dipilih!']));
        }
        if (empty($d['jenis'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Jenis pekerjaan wajib dipilih!']));
        }
        if (empty($d['tgl_terakhir'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Tanggal terakhir PM wajib diisi!']));
        }

        // Pindahkan logika save ke model
        $result = JadwalPmModel::saveData($d, $id);

        if ($result) {
            if ($id == null) {
                return response()->json(_response('01', $this->uri, $d));
            } else {
                return response()->json(_response('02', $this->uri, $d));
            }
        } else {
            if ($id == null) {
                return response()->json(_response('11', $this->uri, ['message' => 'Gagal menyimpan data jadwal baru!']));
            } else {
                return response()->json(_response('12', $this->uri, ['message' => 'Gagal memperbarui data jadwal!']));
            }
        }
    }

    public function delete($id)
    {
        // Validasi relasi sebelum hapus
        if (DbModel::getData('trx_order_kerja', ['jadwal_pm_id' => $id, 'deleted_st' => 0])) {
            return response()->json(_response('13', $this->uri, ['message' => 'Jadwal ini sudah digunakan di Order Kerja dan tidak dapat dihapus.']));
        }

        $result = DbModel::deleteData('trx_jadwal_pm', ['jadwal_pm_id' => $id]);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return JadwalPmModel::loadDatatables();
    }
}
