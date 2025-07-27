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
        // Penting: Gunakan save_session_search untuk mengelola session pencarian
        $this->save_session_search($d);

        // Hapus $d['nav_sess'] = session(request('n')); karena redundan (sudah ada di parent)

        // Data untuk dropdown filter di halaman utama
        $d['all_asset'] = DbModel::allData('mst_asset', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1]);

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d['main'] = $id ? $this->model->getById($id) : null;

        // Mengirim semua data yang dibutuhkan untuk logika JavaScript di sisi klien
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_asset'] = DbModel::allData('mst_asset', ['deleted_st' => 0, 'active_st' => 1]);
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);

        // Standardisasi format URI untuk konsistensi
        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    public function save($id = null)
    {
        $d = _post();

        // Validasi dasar
        if (empty($d['asset_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Aset wajib dipilih!']));
        }
        if (empty($d['deskripsi'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'Deskripsi wajib diisi.']));
        }

        try {
            // Alihkan logika save ke model untuk konsistensi
            $result = $this->model->saveData($id, $d);

            if ($result['status']) {
                if ($result['mode'] === 'insert') {
                    return response()->json(_response('01', $this->uri, $d));
                } else {
                    return response()->json(_response('02', $this->uri, $d));
                }
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
        if (DbModel::getData('order_kerja', ['permintaan_id' => $id, 'deleted_st' => 0])) {
            return response()->json(_response('13', $this->uri, ['message' => 'Permintaan komplain ini sudah dibuatkan Order Kerja dan tidak dapat dihapus.']));
        }

        $result = $this->model->deleteData($id);
        return response()->json(_response($result ? '03' : '13', $this->uri));
    }

    public function ajax_datatables()
    {
        return $this->model->loadDatatables();
    }
}
