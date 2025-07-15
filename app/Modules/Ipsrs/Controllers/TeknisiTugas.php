<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\TeknisiModel;
use Illuminate\Support\Facades\Auth;
use Exception; // Pastikan ini ada

class TeknisiTugas extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TeknisiModel();
        $this->template = 'ipsrs::teknisi.tugas.';
    }

    public function index()
    {
        $teknisi_id = session('pegawai_id');
        $d['list_tugas_baru'] = $this->model->getListTugasByStatus($teknisi_id, 'ditugaskan');
        $d['list_tugas_dikerjakan'] = $this->model->getListTugasByStatus($teknisi_id, 'sedang_dikerjakan');
        $d['list_tugas_ditolak'] = $this->model->getListTugasByStatus($teknisi_id, 'dibatalkan');
        return $this->renderView($this->template . 'index', $d);
    }

    public function form_detail_modal($penugasan_id)
    {
        $d['tugas'] = $this->model->getDetailTugas($penugasan_id);
        if (!$d['tugas']) {
            return '<h5>Tugas tidak ditemukan.</h5>';
        }
        return $this->renderView($this->template . 'detail_modal', $d);
    }

    public function terima()
    {
        try {
            $penugasan_id = request('penugasan_id');
            if (!$penugasan_id) throw new Exception("ID Penugasan tidak ditemukan.");

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'sedang_dikerjakan');
            if ($result['success']) {
                return response()->json(['code' => '02', 'message' => 'Tugas berhasil diterima.', 'redirect_url' => url('ipsrs/teknisitugas')]);
            }
            return response()->json(['code' => '12', 'message' => $result['msg']]);
        } catch (Exception $e) {
            return response()->json(['code' => '12', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function batal_terima()
    {
        try {
            $penugasan_id = request('penugasan_id');
            if (!$penugasan_id) throw new Exception("ID Penugasan tidak ditemukan.");

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'ditugaskan');
            if ($result['success']) {
                return response()->json(['code' => '02', 'message' => 'Penerimaan tugas dibatalkan.', 'redirect_url' => url('ipsrs/teknisitugas')]);
            }
            return response()->json(['code' => '12', 'message' => $result['msg']]);
        } catch (Exception $e) {
            return response()->json(['code' => '12', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function form_tolak_modal($penugasan_id)
    {
        $d['penugasan_id'] = $penugasan_id;
        $d['form_act'] = url('ipsrs/teknisitugas/tolak');
        return $this->renderView($this->template . 'form_tolak_modal', $d);
    }

    public function tolak()
    {
        try {
            $penugasan_id = request('penugasan_id');
            $alasan = request('alasan');
            if (!$penugasan_id || !$alasan) throw new Exception("ID Penugasan atau alasan tidak boleh kosong.");

            $result = $this->model->updateStatusPenugasan($penugasan_id, 'dibatalkan', $alasan);
            if ($result['success']) {
                return response()->json(['code' => '02', 'message' => 'Tugas berhasil ditolak.', 'redirect_url' => url('ipsrs/teknisitugas')]);
            }
            return response()->json(['code' => '12', 'message' => $result['msg']]);
        } catch (Exception $e) {
            return response()->json(['code' => '12', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
