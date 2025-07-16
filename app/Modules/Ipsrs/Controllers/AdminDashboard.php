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

        $d['count_jadwal_belum_ok'] = $this->model->getCountJadwalBelumDibuatOK();

        // Mengambil data untuk tabel pekerjaan darurat
        $d['urgent_jobs'] = $this->model->getUrgentJobs();

        // Pastikan data chart valid
        if (!isset($d['chart_komplain_harian']) || !is_array($d['chart_komplain_harian'])) {
            $d['chart_komplain_harian'] = [];
        }

        // Tambahkan flag untuk memuat ApexCharts
        $d['load_apexcharts'] = true;

        return $this->renderView('ipsrs::admin.dashboard.index', $d);
    }
}
