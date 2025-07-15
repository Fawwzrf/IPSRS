<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\TeknisiModel;
use Illuminate\Support\Facades\Auth;

class TeknisiDashboard extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new TeknisiModel();
        $this->template = 'ipsrs::teknisi.dashboard.';
    }

    public function index()
    {

        $teknisi_id = session('pegawai_id');

        // Mengambil data untuk widget dan daftar tugas
        $d['tugas_baru_count'] = $this->model->getCountTugasByStatus($teknisi_id, 'ditugaskan'); //
        $d['tugas_aktif_list'] = $this->model->getListTugasByStatus($teknisi_id, 'sedang_dikerjakan', 5); //

        // (PERBAIKAN) Ambil parameter 'n' dari request dan kirim ke view
        $d['n'] = request('n');

        return $this->renderView($this->template . 'index', $d);
    }
}
