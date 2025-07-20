<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LaporanModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class AdminLaporan extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LaporanModel();
        $this->template = 'ipsrs::admin.laporan.';
    }

    /**
     * Menampilkan laporan Kinerja Aset.
     */
    public function kinerjaAset()
    {
        $d = [];
        $this->save_session_search($d);
        
        // Ambil data untuk filter
        $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => 0]);
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0]);

        // Ambil data laporan berdasarkan filter
        $d['laporan'] = $this->model->getLaporanKinerjaAset($d['nav_sess']['search']['data'] ?? []);

        // Refactor: gunakan helper untuk format tanggal/angka pada data laporan
        foreach ($d['laporan'] as &$row) {
            $row['tgl_mulai'] = to_date($row['tgl_mulai'] ?? '');
            $row['total_biaya'] = numId($row['total_biaya'] ?? 0);
        }

        // Untuk ekspor ke Excel jika diminta
        if (request('export') == 'excel') {
            $headers = [
                'No', 'Kode Aset', 'Nama Aset', 'Kategori', 'Lokasi', 'Tgl Mulai', 'Total Biaya (Rp)'
            ];
            $data = [];
            $no = 1;
            foreach ($d['laporan'] as $row) {
                $data[] = [
                    $no++,
                    $row['kode_aset'] ?? '',
                    $row['nama_aset'] ?? '',
                    $row['kategori'] ?? '',
                    $row['lokasi'] ?? '',
                    $row['tgl_mulai'] ?? '',
                    $row['total_biaya'] ?? 0,
                ];
            }
            return $this->exportToExcel($data, 'Laporan_Kinerja_Aset', $headers);
        }

        return $this->renderView($this->template . 'kinerja_aset', $d);
    }

    /**
     * Menampilkan laporan Kinerja Teknisi.
     */
    public function kinerjaTeknisi()
    {
        $d = [];
        $this->save_session_search($d);
        
        // Ambil data untuk filter
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['jabatan_id' => '90', 'deleted_st' => 0]);

        // Ambil data laporan berdasarkan filter
        $d['laporan'] = $this->model->getLaporanKinerjaTeknisi($d['nav_sess']['search']['data'] ?? []);

        // Untuk ekspor ke Excel jika diminta
        if (request('export') == 'excel') {
            try {
                return $this->exportToExcel($d['laporan'], 'Laporan_Kinerja_Teknisi', [
                    'Nama Teknisi', 'Total Tugas', 'Tugas Selesai', 'Rata-rata Durasi (Menit)'
                ]);
            } catch (\Exception $e) {
                Log::error('Error exporting laporan: ' . $e->getMessage());
                return back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
            }
        }

        return $this->renderView($this->template . 'kinerja_teknisi', $d);
    }

    /**
     * Menampilkan laporan Biaya Pemeliharaan & Perbaikan.
     */
    public function biayaPemeliharaan()
    {
        $d = [];
        $this->save_session_search($d);
        
        // Set default date range jika tidak ada
        if (empty($d['nav_sess']['search']['data']['tgl_start'])) {
            $d['nav_sess']['search']['data']['tgl_start'] = date('d-m-Y', strtotime('-30 days'));
            $d['nav_sess']['search']['data']['tgl_end'] = date('d-m-Y');
        }

        // Ambil data laporan berdasarkan filter
        $d['laporan'] = $this->model->getLaporanBiaya($d['nav_sess']['search']['data'] ?? []);
        $d['total_biaya'] = array_sum(array_column($d['laporan'], 'total_biaya_ok'));

        // Untuk ekspor ke Excel jika diminta
        if (request('export') == 'excel') {
            try {
                return $this->exportToExcel($d['laporan'], 'Laporan_Biaya_Pemeliharaan', [
                    'Tanggal OK', 'ID Order Kerja', 'Aset', 'Jenis Pekerjaan', 
                    'Biaya Sparepart (Rp)', 'Biaya Lain (Rp)', 'Total Biaya (Rp)'
                ]);
            } catch (\Exception $e) {
                Log::error('Error exporting laporan: ' . $e->getMessage());
                return back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
            }
        }

        return $this->renderView($this->template . 'biaya_pemeliharaan', $d);
    }

    /**
     * Menampilkan laporan Kinerja Tim.
     */
    public function kinerjaTim()
    {
        $d = [];
        $this->save_session_search($d);

        // Set default tanggal jika belum ada di session
        if (empty($d['nav_sess']['search']['data']['tgl_start'])) {
            $d['nav_sess']['search']['data']['tgl_start'] = date('01-m-Y');
            $d['nav_sess']['search']['data']['tgl_end'] = date('d-m-Y');
        }

        // Dropdown teknisi
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['jabatan_id' => '90', 'deleted_st' => 0]);

        // Data laporan
        $filter = $d['nav_sess']['search']['data'] ?? [];
        $d['laporan'] = LaporanModel::getLaporanKinerjaTim($filter);

        // Handler jika request AJAX dari _search(e)
        if (request()->input('_is_ajax')) {
            // Redirect ke halaman yang sama agar _page(res.uri, "search") bisa reload konten
            return response()->json([
                'uri' => url()->current() . '?n=' . request('n')
            ]);
        }

        // Ekspor Excel
        if (request('export') == 'excel') {
            $headers = [
                'Order ID', 'Teknisi', 'Aset', 'Respon Admin', 'Penerimaan Teknisi', 'Pengerjaan', 'Total Penyelesaian'
            ];
            $data = [];
            foreach ($d['laporan'] as $row) {
                $data[] = [
                    $row['order_kerja_id'] ?? '',
                    $row['nama_teknisi'] ?? '',
                    $row['nama_aset'] ?? '',
                    $row['durasi_respon_admin'] ?? 0,
                    $row['durasi_penerimaan_teknisi'] ?? 0,
                    $row['durasi_pengerjaan'] ?? 0,
                    $row['durasi_total'] ?? 0,
                ];
            }
            return $this->exportToExcel($data, 'Laporan_Kinerja_Tim', $headers);
        }

        return $this->renderView($this->template . 'kinerja_tim', $d);
    }

    /**
     * Helper method untuk mengekspor data ke Excel.
     * 
     * @param array $data Data yang akan diekspor
     * @param string $filename Nama file excel
     * @param array $headers Header kolom
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportToExcel($data, $filename, $headers)
    {
        // Fungsi ini bisa diimplementasikan dengan library Excel seperti PhpSpreadsheet
        // Untuk sekarang, kita gunakan CSV sebagai fallback sederhana
        
        $filename = $filename . '_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        // Tulis header
        fputcsv($handle, $headers);
        
        // Tulis data
        foreach ($data as $row) {
            $values = [];
            foreach ($headers as $header) {
                switch ($header) {
                    case 'Nama Aset':
                        $values[] = $row['asset_nm'] ?? '';
                        break;
                    case 'Lokasi':
                        $values[] = $row['lokasi_nm'] ?? '';
                        break;
                    case 'Jumlah OK':
                        $values[] = $row['jumlah_ok'] ?? 0;
                        break;
                    case 'Jumlah Perbaikan':
                        $values[] = $row['jumlah_perbaikan'] ?? 0;
                        break;
                    case 'Jumlah PM':
                        $values[] = $row['jumlah_pemeliharaan'] ?? 0;
                        break;
                    case 'Terakhir Ditangani':
                        $values[] = to_date($row['terakhir_ditangani'] ?? '') ?? '';
                        break;
                    case 'Nama Teknisi':
                        $values[] = $row['pegawai_nm'] ?? '';
                        break;
                    case 'Total Tugas':
                        $values[] = $row['total_tugas'] ?? 0;
                        break;
                    case 'Tugas Selesai':
                        $values[] = $row['tugas_selesai'] ?? 0;
                        break;
                    case 'Rata-rata Durasi (Menit)':
                        $values[] = numId($row['rata_rata_durasi'] ?? 0, true);
                        break;
                    case 'Tanggal OK':
                        $values[] = to_date($row['tgl_dibuat'] ?? '') ?? '';
                        break;
                    case 'ID Order Kerja':
                        $values[] = $row['order_kerja_id'] ?? '';
                        break;
                    case 'Jenis Pekerjaan':
                        $values[] = ucfirst($row['jenis'] ?? '');
                        break;
                    case 'Biaya Sparepart (Rp)':
                        $values[] = numId($row['total_biaya_sparepart'] ?? 0);
                        break;
                    case 'Biaya Lain (Rp)':
                        $values[] = numId($row['biaya_lain'] ?? 0);
                        break;
                    case 'Total Biaya (Rp)':
                        $values[] = numId($row['total_biaya_ok'] ?? 0);
                        break;
                    default:
                        $values[] = '';
                }
            }
            fputcsv($handle, $values);
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        // Set headers dan kirim file
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        return response($content, 200, $headers);
    }
}
