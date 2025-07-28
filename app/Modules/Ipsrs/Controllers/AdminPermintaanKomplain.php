<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\PermintaanKomplainModel;
use Illuminate\Support\Facades\Log;

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

        $d['all_asset']   = $this->model->getAllActiveAsset();
        $d['all_pegawai'] = $this->model->getAllActivePegawai();
        $d['all_lokasi']  = $this->model->getAllActiveLokasi();

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main']        = $id ? $this->model->getById($id) : null;
        $d['all_lokasi']  = $this->model->getAllActiveLokasi();
        $d['all_asset']   = $this->model->getAllActiveAsset();
        $d['all_pegawai'] = $this->model->getAllActivePegawai();
        $d['form_act']    = $this->uri . '/save/' . $id;

        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();

        // Validasi
        if (empty($d['asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Aset wajib dipilih!']));
        }
        if (empty($d['deskripsi'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Deskripsi wajib diisi.']));
        }

        try {
            $result = $this->model->saveData($id, $d);

            if ($result['status']) {
                $code = $result['mode'] === 'insert' ? '01' : '02';
                return response()->json(_response($code, $this->uri, $d));
            } else {
                throw new \Exception("Gagal menyimpan data ke database.");
            }
        } catch (\Throwable $th) {
            Log::error('Error saving komplain: ' . $th->getMessage());
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan laporan.']));
        }
    }

    public function delete($id)
    {
        // Validasi relasi sebelum hapus
        if ($this->model->isOrderKerjaExistByPermintaanId($id)) {
            return response()->json(_response('13', $this->uri, [
                'message' => 'Permintaan komplain ini sudah dibuatkan Order Kerja dan tidak dapat dihapus.'
            ]));
        }

        $result = $this->model->deleteData($id);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return $this->model->loadDatatables();
    }
}
