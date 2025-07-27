<?php

namespace App\Modules\Ipsrs\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LaporanModel;
use Illuminate\Support\Facades\Log;


class AdminLaporan extends MyController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new LaporanModel();
        $this->template = 'ipsrs::admin.laporan.';
    }

    protected function applyPeriodeFilter(&$filter)
    {
        $periode = $filter['periode'] ?? 'custom';
        if ($periode === 'custom') {
            // Sudah ada tgl_start & tgl_end dari form
            return;
        }
        if ($periode === 'harian' && !empty($filter['tgl_single'])) {
            $filter['tgl_start'] = $filter['tgl_single'];
            $filter['tgl_end'] = $filter['tgl_single'];
            return;
        }
        if ($periode === 'bulanan' && !empty($filter['bulan']) && !empty($filter['tahun_bulan'])) {
            $bulan = $filter['bulan'];
            $tahun = $filter['tahun_bulan'];
            $lastDay = date('t', strtotime("01-$bulan-$tahun"));
            $filter['tgl_start'] = "01-$bulan-$tahun";
            $filter['tgl_end'] = "$lastDay-$bulan-$tahun";
            return;
        }
        if ($periode === 'tahunan' && !empty($filter['tahun'])) {
            $filter['tgl_start'] = '01-01-' . $filter['tahun'];
            $filter['tgl_end'] = '31-12-' . $filter['tahun'];
            return;
        }
        // Default: hari ini
        $filter['tgl_start'] = date('d-m-Y');
        $filter['tgl_end'] = date('d-m-Y');
    }
    /**
     * Menampilkan laporan Kinerja Aset.
     */
    public function kinerjaAset()
    {
        $d = [];
        $this->save_session_search($d);

        if (!isset($d['laporan']) || !is_array($d['laporan'])) {
            $d['laporan'] = [];
        }

        $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => 0]);
        $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => 0]);

        // Export Excel
        if (request('export') == 'excel') {
            $filter = $this->getSessionFilter();
            $this->applyPeriodeFilter($filter);

            $result = $this->model->getDatatablesKinerjaAset($filter, 0, 10000, [], $filter['search'] ?? '');
            $data = $result['data'] ?? [];
            $headers = [
                'Nama Aset', 'Merk', 'Kategori', 'Lokasi', 'Jumlah OK',
                'Jumlah Perbaikan', 'Jumlah Pemeliharaan', 'Terakhir Ditangani'
            ];
            $filters = [
                'Periode' => $this->getPeriodeLabel($filter),
                'Kategori Aset' => $this->getNamaKategoriAset($filter['kategori_asset_id'] ?? null),
                'Lokasi' => $this->getNamaLokasi($filter['lokasi_id'] ?? null),
                'Pencarian' => $filter['search'] ?? '-'
            ];
            return $this->exportToExcel($data, 'Laporan_Kinerja_Aset', $headers, $filters, 'Laporan Kinerja Aset');
        }

        // AJAX datatables
        if (request()->ajax()) {
            $nav_sess = session(request('n'));
            $filter = $nav_sess['search']['data'] ?? [];
            $start = intval(request('start', 0)); // Ambil dari request, bukan session!
            $length = intval(request('length', 10));
            $order = request('order', []);
            $search = request('search.value', ''); // Ambil dari request DataTables!
            \Log::debug('AJAX Filter:', $filter);
            \Log::debug('AJAX Search:', ['search' => $search]);
            $result = $this->model->getDatatablesKinerjaAset($filter, $start, $length, $order, $search);
            return response()->json($result);
        }
        // Print
        if (request('print') == '1') {
            $filter = $this->getSessionFilter();
            $d['judul'] = 'Laporan Kinerja Aset';
            $d['periode_label'] = $this->getPeriodeLabel($filter);
            $d['kategori_asset_label'] = $this->getNamaKategoriAset($filter['kategori_asset_id'] ?? null);
            $d['lokasi_label'] = $this->getNamaLokasi($filter['lokasi_id'] ?? null);
            $d['teknisi_label'] = '-';
            $d['pencarian_label'] = $filter['search'] ?? '-';
            return view($this->template . 'kinerja_aset', $d);
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
        if (!isset($d['laporan']) || !is_array($d['laporan'])) {
            $d['laporan'] = [];
        }
        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['jabatan_id' => '90', 'deleted_st' => 0]);

        if (request('export') == 'excel') {
            $filter = $this->getSessionFilter();
            $this->applyPeriodeFilter($filter);

            $result = $this->model->getDatatablesKinerjaTeknisi($filter, 0, 10000, [], $filter['search'] ?? '');
            $data = $result['data'] ?? [];
            $headers = [
                'Nama Teknisi',
                'Total Tugas',
                'Tugas Selesai',
                'Rata-rata Durasi'
            ];
            $filters = [
                'Periode' => $this->getPeriodeLabel($filter),
                'Teknisi' => $this->getNamaTeknisi($filter['teknisi_id'] ?? null),
                'Pencarian' => $filter['search'] ?? '-'
            ];
            return $this->exportToExcel($data, 'Laporan_Kinerja_Teknisi', $headers, $filters, 'Laporan Kinerja Teknisi');
        }

        if (request()->ajax()) {
            $nav_sess = session(request('n'));
            $filter = $nav_sess['search']['data'] ?? [];
            $start = intval(request('start', 0)); // Ambil dari request, bukan session!
            $length = intval(request('length', 10));
            $order = request('order', []);
            $search = request('search.value', ''); // Ambil dari request DataTables!
            $result = $this->model->getDatatablesKinerjaTeknisi($filter, $start, $length, $order, $search);
            return response()->json($result);
        }

        if (empty($d['nav_sess']['search']['data']['tgl_start']) || empty($d['nav_sess']['search']['data']['tgl_end'])) {
            $d['nav_sess']['search']['data']['tgl_start'] = date('01-m-Y');
            $d['nav_sess']['search']['data']['tgl_end'] = date('t-m-Y');
        }

        if (request('print') == '1') {
            $filter = $this->getSessionFilter();
            $d['judul'] = 'Laporan Kinerja Teknisi';
            $d['periode_label'] = $this->getPeriodeLabel($filter);
            $d['kategori_asset_label'] = '-';
            $d['lokasi_label'] = '-';
            $d['teknisi_label'] = $this->getNamaTeknisi($filter['teknisi_id'] ?? null);
            $d['pencarian_label'] = $filter['search'] ?? '-';
            return view($this->template . 'kinerja_teknisi', $d);
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

        // Pastikan $d['laporan'] selalu terdefinisi agar tidak undefined
        if (!isset($d['laporan']) || !is_array($d['laporan'])) {
            $d['laporan'] = [];
        }

        $filter = $this->getSessionFilter();
        $this->applyPeriodeFilter($filter);
        $total_biaya = $this->model->getTotalBiayaPemeliharaan($filter);

        $d['total_biaya'] = $total_biaya;

        // Untuk ekspor ke Excel jika diminta
        if (request('export') == 'excel') {
            $this->applyPeriodeFilter($filter);

            $result = $this->model->getDatatablesBiayaPemeliharaan($filter, 0, 10000, [], $filter['search'] ?? '');
            $data = $result['data'] ?? [];
            $headers = [
                'Tanggal',
                'Order Kerja',
                'Aset',
                'Jenis',
                'Total Biaya Sparepart',
                'Biaya Lain',
                'Total Biaya'
            ];
            $filters = [
                'Periode' => $this->getPeriodeLabel($filter),
                'Pencarian' => $filter['search'] ?? '-'
            ];
            return $this->exportToExcel($data, 'Laporan_Biaya_Pemeliharaan', $headers, $filters, 'Laporan Biaya Pemeliharaan & Perbaikan');
        }

        if (request()->ajax()) {
            $nav_sess = session(request('n'));
            $filter = $nav_sess['search']['data'] ?? [];
            $this->applyPeriodeFilter($filter);
            $start = intval(request('start', 0)); // Ambil dari request, bukan session!
            $length = intval(request('length', 10));
            $order = request('order', []);
            $search = request('search.value', ''); // Ambil dari request DataTables!
            $result = $this->model->getDatatablesBiayaPemeliharaan($filter, $start, $length, $order, $search);
            return response()->json($result);
        }

        // Tambahkan kode ini untuk memastikan filter tanggal selalu ada
        if (empty($d['nav_sess']['search']['data']['tgl_start']) || empty($d['nav_sess']['search']['data']['tgl_end'])) {
            $d['nav_sess']['search']['data']['tgl_start'] = date('01-m-Y');
            $d['nav_sess']['search']['data']['tgl_end'] = date('t-m-Y');
        }

        if (request('print') == '1') {
            $filter = $this->getSessionFilter();
            $d['judul'] = 'Laporan Biaya Pemeliharaan & Perbaikan';
            $d['periode_label'] = $this->getPeriodeLabel($filter);
            $d['kategori_asset_label'] = '-';
            $d['lokasi_label'] = '-';
            $d['teknisi_label'] = '-';
            $d['pencarian_label'] = $filter['search'] ?? '-';
            return view($this->template . 'biaya_pemeliharaan', $d);
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

        if (!isset($d['laporan']) || !is_array($d['laporan'])) {
            $d['laporan'] = [];
        }

        $d['all_teknisi'] = DbModel::allData('mst_pegawai', ['jabatan_id' => '90', 'deleted_st' => 0]);

        // Ekspor Excel
        if (request('export') == 'excel') {
            $filter = $this->getSessionFilter();
            $this->applyPeriodeFilter($filter);

            $result = $this->model->getDatatablesKinerjaTim($filter, 0, 10000, [], $filter['search'] ?? '');
            $data = $result['data'] ?? [];
            $headers = [
                'Order ID',
                'Teknisi',
                'Aset',
                'Respon Admin',
                'Penerimaan Teknisi',
                'Pengerjaan',
                'Total Penyelesaian'
            ];
            $filters = [
                'Periode' => $this->getPeriodeLabel($filter),
                'Teknisi' => $this->getNamaTeknisi($filter['teknisi_id'] ?? null),
                'Pencarian' => $filter['search'] ?? '-'
            ];
            return $this->exportToExcel($data, 'Laporan_Kinerja_Tim', $headers, $filters, 'Laporan Kinerja Tim');
        }

        if (request()->ajax()) {
            $nav_sess = session(request('n'));
            $filter = $nav_sess['search']['data'] ?? [];
            $this->applyPeriodeFilter($filter);
            $start = intval(request('start', 0)); // Ambil dari request, bukan session!
            $length = intval(request('length', 10));
            $order = request('order', []);
            $search = request('search.value', ''); // Ambil dari request DataTables!
            $result = $this->model->getDatatablesKinerjaTim($filter, $start, $length, $order, $search);
            $result['rata_rata_penyelesaian'] = $this->model->getRataRataPenyelesaianTim($filter);
            return response()->json($result);
        }

        // Tambahkan kode ini untuk memastikan filter tanggal selalu ada
        if (empty($d['nav_sess']['search']['data']['tgl_start']) || empty($d['nav_sess']['search']['data']['tgl_end'])) {
            $d['nav_sess']['search']['data']['tgl_start'] = date('01-m-Y');
            $d['nav_sess']['search']['data']['tgl_end'] = date('t-m-Y');
        }

        if (request('print') == '1') {
            $filter = $this->getSessionFilter();
            $d['judul'] = 'Laporan Kinerja Tim';
            $d['periode_label'] = $this->getPeriodeLabel($filter);
            $d['kategori_asset_label'] = '-';
            $d['lokasi_label'] = '-';
            $d['teknisi_label'] = $this->getNamaTeknisi($filter['teknisi_id'] ?? null);
            $d['pencarian_label'] = $filter['search'] ?? '-';
            return view($this->template . 'kinerja_tim', $d);
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
        $filename = $filename . '_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $headers);

        foreach ($data as $row) {
            $values = [];
            foreach ($headers as $header) {
                switch ($header) {

                    case 'Nama Aset':
                        $values[] = $row['asset_nm'] ?? '';
                        break;
                    case 'Merk':
                        $values[] = $row['merk'] ?? '';
                        break;
                    case 'Kategori':
                        $values[] = $row['kategori_asset_nm'] ?? '';
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
                    case 'Jumlah Pemeliharaan':
                        $values[] = $row['jumlah_pemeliharaan'] ?? 0;
                        break;
                    case 'Terakhir Ditangani':
                        $values[] = to_date($row['terakhir_ditangani'] ?? '');
                        break;
                    case 'Tanggal':
                        $values[] = to_date($row['tgl_dibuat'] ?? '');
                        break;
                    case 'Total Tugas':
                        $values[] = $row['total_tugas'] ?? 0;
                        break;
                    case 'Tugas Selesai':
                        $values[] = $row['tugas_selesai'] ?? 0;
                        break;
                    case 'Rata-rata Durasi (Menit)':
                    case 'Rata-rata Durasi':
                        $values[] = $row['rata_rata_durasi'] ?? 0;
                        break;
                    case 'Tanggal OK':
                        $values[] = to_date($row['tgl_dibuat'] ?? '') ?? '';
                        break;
                    case 'Order Kerja':
                    case 'ID Order Kerja':
                    case 'Order ID':
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
                    case 'Teknisi':
                        $values[] = $row['nama_teknisi'] ?? ($row['pegawai_nm'] ?? '');
                        break;
                    case 'Aset':
                        $values[] = $row['asset_nm'] ?? '';
                        break;
                    case 'Jenis':
                        $values[] = $row['jenis'] ?? '';
                        break;
                    case 'Total Biaya Sparepart':
                        $values[] = $row['total_biaya_sparepart'] ?? 0;
                        break;
                    case 'Biaya Lain':
                        $values[] = $row['biaya_lain'] ?? 0;
                        break;
                    case 'Total Biaya':
                        $values[] = $row['total_biaya_ok'] ?? 0;
                        break;
                    case 'Nama Teknisi':
                        $values[] = $row['pegawai_nm'] ?? ($row['nama_teknisi'] ?? '');
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

        $headersCsv = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response($content, 200, $headersCsv);
    }

    public function ajax_datatables()
    {
        $nav_sess = session(request('n'));
        $filter = $nav_sess['search']['data'] ?? [];
        $start = intval($nav_sess['start'] ?? 0);
        $length = intval($nav_sess['length'] ?? 10);
        $order = $nav_sess['order'] ?? [];
        $search = $nav_sess['search']['value'] ?? '';
        $laporan = $nav_sess['laporan'] ?? '';

        switch ($laporan) {
            case 'kinerja_aset':
                $result = $this->model->getDatatablesKinerjaAset($filter, $start, $length, $order, $search);
                break;
            case 'kinerja_teknisi':
                $result = $this->model->getDatatablesKinerjaTeknisi($filter, $start, $length, $order, $search);
                break;
            case 'biaya_pemeliharaan':
                $result = $this->model->getDatatablesBiayaPemeliharaan($filter, $start, $length, $order, $search);
                break;
            case 'kinerja_tim':
                $result = $this->model->getDatatablesKinerjaTim($filter, $start, $length, $order, $search);
                break;
            default:
                $result = [
                    'draw' => intval($nav_sess['draw'] ?? 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ];
        }
        return response()->json($result);
    }

    protected function getPeriodeLabel($filter)
    {
        $periode = $filter['periode'] ?? 'custom';
        if ($periode === 'harian' && !empty($filter['tgl_single'])) {
            return 'Tanggal ' . to_date($filter['tgl_single']);
        }
        if ($periode === 'bulanan' && !empty($filter['bulan']) && !empty($filter['tahun_bulan'])) {
            $bulan = DateTime::createFromFormat('!m', $filter['bulan'])->format('F');
            return "Bulan $bulan {$filter['tahun_bulan']}";
        }
        if ($periode === 'tahunan' && !empty($filter['tahun'])) {
            return "Tahun {$filter['tahun']}";
        }
        if (!empty($filter['tgl_start']) && !empty($filter['tgl_end'])) {
            if ($filter['tgl_start'] == $filter['tgl_end']) {
                return 'Tanggal ' . to_date($filter['tgl_start']);
            }
            return 'Tanggal ' . to_date($filter['tgl_start']) . ' - ' . to_date($filter['tgl_end']);
        }
        return 'Semua Periode';
    }

    protected function getNamaKategoriAset($id)
    {
        if (!$id) return 'Semua';
        $row = \App\Modules\App\Models\DbModel::rowData('mst_kategori_asset', ['kategori_asset_id' => $id]);
        return $row['kategori_asset_nm'] ?? '-';
    }
    protected function getNamaLokasi($id)
    {
        if (!$id) return 'Semua';
        $row = \App\Modules\App\Models\DbModel::rowData('mst_lokasi', ['lokasi_id' => $id]);
        return $row['lokasi_nm'] ?? '-';
    }
    protected function getNamaTeknisi($id)
    {
        if (!$id) return 'Semua';
        $row = \App\Modules\App\Models\DbModel::rowData('mst_pegawai', ['pegawai_id' => $id]);
        return $row['pegawai_nm'] ?? '-';
    }

    // Tambahkan helper untuk ambil filter dari session
    protected function getSessionFilter()
    {
        $nav_sess = session(request('n'));
        $filter = $nav_sess['search']['data'] ?? [];
        // Normalisasi nama key
        if (isset($filter['pegawai_id']) && !isset($filter['teknisi_id'])) {
            $filter['teknisi_id'] = $filter['pegawai_id'];
        }
        return $filter;
    }
}
