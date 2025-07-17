<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\Ipsrs\Models\TeknisiModel;
use App\Modules\App\Models\DbModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    /**
     * Menampilkan dashboard teknisi
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $d = [];
        $teknisi_id = session('pegawai_id');

        // Validasi ID teknisi
        if (!$teknisi_id) {
            Log::error('TeknisiDashboard: Tidak dapat menemukan ID teknisi dari session');
            return redirect('login')->with('error', 'Session telah berakhir, silakan login kembali');
        }

        try {
            // Dapatkan semua data dashboard dengan satu panggilan
            $dashboard_data = $this->model->getDashboardData($teknisi_id);
            
            // Gabungkan data dashboard ke array $d
            $d = array_merge($d, $dashboard_data);
            
            // Pastikan data chart kinerja selalu tersedia dalam format yang benar
            if (
                !isset($d['chart_kinerja']) ||
                !isset($d['chart_kinerja']['selesai']) ||
                !isset($d['chart_kinerja']['baru']) ||
                !is_array($d['chart_kinerja']['selesai']) ||
                !is_array($d['chart_kinerja']['baru'])
            ) {
                $d['chart_kinerja'] = [
                    'selesai' => [0, 0, 0, 0],
                    'baru' => [0, 0, 0, 0]
                ];
            }

            // Tambahkan parameter navigasi untuk penggunaan di view
            $d['n'] = request('n');

            return $this->renderView($this->template . 'index', $d);
            
        } catch (\Exception $e) {
            Log::error('TeknisiDashboard error: ' . $e->getMessage());
            return $this->renderView($this->template . 'index', [
                'error_message' => 'Terjadi kesalahan saat memuat dashboard. Silakan coba lagi nanti.',
                'chart_kinerja' => [
                    'selesai' => [0, 0, 0, 0],
                    'baru' => [0, 0, 0, 0]
                ],
                'tugas_baru_count' => 0,
                'tugas_aktif_list' => [],
                'jadwal_mendatang' => []
            ]);
        }
    }

    /**
     * API endpoint untuk refresh data dashboard (bisa digunakan untuk AJAX)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshData()
    {
        $teknisi_id = session('pegawai_id');
        
        if (!$teknisi_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        try {
            $dashboard_data = $this->model->getDashboardData($teknisi_id);
            return response()->json($dashboard_data);
        } catch (\Exception $e) {
            Log::error('TeknisiDashboard refresh error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat memuat data'], 500);
        }
    }
}
