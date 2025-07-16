<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\TeknisiModel;
use App\Modules\App\Models\DbModel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Dapatkan semua data dashboard dengan satu panggilan
        $dashboard_data = $this->model->getDashboardData($teknisi_id);

        // Pastikan data chart kinerja selalu tersedia dalam format yang benar
        if (
            !isset($dashboard_data['chart_kinerja']) ||
            !isset($dashboard_data['chart_kinerja']['selesai']) ||
            !isset($dashboard_data['chart_kinerja']['baru']) ||
            !is_array($dashboard_data['chart_kinerja']['selesai']) ||
            !is_array($dashboard_data['chart_kinerja']['baru'])
        ) {

            $dashboard_data['chart_kinerja'] = [
                'selesai' => [0, 0, 0, 0],
                'baru' => [0, 0, 0, 0]
            ];
        }

        // Tambahkan parameter navigasi
        $dashboard_data['n'] = request('n');

        return $this->renderView($this->template . 'index', $dashboard_data);
    }
}
