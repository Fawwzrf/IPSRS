<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\DashboardModel;
use Illuminate\Support\Facades\Log;

class AdminDashboard extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new DashboardModel();
        $this->template = 'ipsrs::admin.dashboard.';
    }

    public function index()
    {
        $d = [];
        
        try {
            // Mengambil data untuk widget ringkasan
            $d['count_komplain_baru'] = $this->model->getCountKomplainBaru();
            $d['count_order_kerja_aktif'] = $this->model->getCountOrderKerjaAktif();
            $d['count_aset_perbaikan'] = $this->model->getCountAsetPerbaikan();
            $d['count_sparepart_kritis'] = $this->model->getCountSparepartKritis();
            $d['count_jadwal_belum_ok'] = $this->model->getCountJadwalBelumDibuatOK();

            // Mengambil data untuk grafik
            $d['chart_komplain_harian'] = $this->model->getChartKomplainHarian();

            // Mengambil data untuk tabel pekerjaan darurat
            $d['urgent_jobs'] = $this->model->getUrgentJobs();

            // Pastikan data chart valid
            if (!isset($d['chart_komplain_harian']) || !is_array($d['chart_komplain_harian'])) {
                $d['chart_komplain_harian'] = [];
            }

            // Tambahkan flag untuk memuat ApexCharts
            $d['load_apexcharts'] = true;
            
            Log::info('Dashboard data loaded successfully');
        } catch (\Exception $e) {
            Log::error('Error loading dashboard data: ' . $e->getMessage());
            // Beri nilai default jika terjadi error
            $d['count_komplain_baru'] = 0;
            $d['count_order_kerja_aktif'] = 0;
            $d['count_aset_perbaikan'] = 0;
            $d['count_sparepart_kritis'] = 0;
            $d['count_jadwal_belum_ok'] = 0;
            $d['chart_komplain_harian'] = [];
            $d['urgent_jobs'] = [];
            $d['load_apexcharts'] = true;
        }

        return $this->renderView($this->template . 'index', $d);
    }
}
