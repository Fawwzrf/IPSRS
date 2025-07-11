<?php

namespace App\Modules\Ipsrs\Controllers; // Namespace diubah ke IPSRS

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\DashboardModel; // Memanggil model dari namespace yang sama

class AdminDashboard extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new DashboardModel();
        // Path view disesuaikan dengan permintaan Anda
        $this->template = 'ipsrs::admin.dashboard.';
    }

    public function index()
    {
        $d = [];

        // Mengambil data untuk widget ringkasan
        $d['count_komplain_baru'] = $this->model->getCountKomplainBaru();
        $d['count_order_kerja_aktif'] = $this->model->getCountOrderKerjaAktif();
        $d['count_aset_perbaikan'] = $this->model->getCountAsetPerbaikan();
        $d['count_sparepart_kritis'] = $this->model->getCountSparepartKritis();

        // Mengambil data untuk grafik
        $d['chart_komplain_harian'] = $this->model->getChartKomplainHarian();

        // Mengambil data untuk tabel pekerjaan darurat
        $d['urgent_jobs'] = $this->model->getUrgentJobs();

        return $this->renderView($this->template . 'index', $d);
    }
}
