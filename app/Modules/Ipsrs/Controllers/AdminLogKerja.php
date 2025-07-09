<?php

namespace App\Modules\Ipsrs\Controllers; // Perbaiki namespace jika masih salah di file Anda

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Ipsrs\Models\LogKerjaModel; // Perbaiki namespace
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LogKerja extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'ipsrs::log_kerja.';
    }

    function index()
    {
        $d = [];
        $sqlOrders = "SELECT ok.order_kerja_id, ok.jenis, ok.status,
                             ast.asset_nm, ast.no_seri, loc.lokasi_nm as asset_lokasi_nm
                      FROM order_kerja ok
                      LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                      LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                      LEFT JOIN asset ast ON (jp.asset_id = ast.asset_id OR pk.asset_id = ast.asset_id)
                      LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id
                      WHERE ok.deleted_st = 0 AND ok.active_st = 1";
        $d['all_orders'] = DbModel::rawData('result_array', $sqlOrders);

        $sqlAllAsset = "SELECT a.asset_id, a.asset_nm, a.no_seri, l.lokasi_nm FROM asset a LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id WHERE a.deleted_st = 0 AND a.active_st = 1";
        $d['all_asset'] = DbModel::rawData('result_array', $sqlAllAsset);

        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);


        $s = session(request('n'));
        if (!isset($s['search']['data']['jenis_filter'])) {
            $s['search']['data']['jenis_filter'] = '';
            session([request('n') => $s]);
        }
        $d['nav_sess'] = session(request('n'));

        return $this->renderView($this->template . 'index', $d);
    }

    function pemeliharaan()
    {
        $d = [];
        $s = session(request('n'));
        $s['search']['data']['jenis_filter'] = 'pemeliharaan';
        session([request('n') => $s]);
        $d['nav_sess'] = session(request('n'));
        
        $sqlOrders = "SELECT ok.order_kerja_id, ok.jenis, ok.status,
                             ast.asset_nm, ast.no_seri, loc.lokasi_nm as asset_lokasi_nm
                      FROM order_kerja ok
                      LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                      LEFT JOIN asset ast ON jp.asset_id = ast.asset_id
                      LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id
                      WHERE ok.deleted_st = 0 AND ok.active_st = 1 AND ok.jenis = 'pemeliharaan'";
        $d['all_orders'] = DbModel::rawData('result_array', $sqlOrders);

        $sqlAllAsset = "SELECT a.asset_id, a.asset_nm, a.no_seri, l.lokasi_nm FROM asset a LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id WHERE a.deleted_st = 0 AND a.active_st = 1";
        $d['all_asset'] = DbModel::rawData('result_array', $sqlAllAsset);

        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);

        return $this->renderView($this->template . 'index', $d);
    }

    function perbaikan()
    {
        $d = [];
        $s = session(request('n'));
        $s['search']['data']['jenis_filter'] = 'perbaikan';
        session([request('n') => $s]);
        $d['nav_sess'] = session(request('n'));

        $sqlOrders = "SELECT ok.order_kerja_id, ok.jenis, ok.status,
                             ast.asset_nm, ast.no_seri, loc.lokasi_nm as asset_lokasi_nm
                      FROM order_kerja ok
                      LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                      LEFT JOIN asset ast ON pk.asset_id = ast.asset_id
                      LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id
                      WHERE ok.deleted_st = 0 AND ok.active_st = 1 AND ok.jenis = 'perbaikan'";
        $d['all_orders'] = DbModel::rawData('result_array', $sqlOrders);

        $sqlAllAsset = "SELECT a.asset_id, a.asset_nm, a.no_seri, l.lokasi_nm FROM asset a LEFT JOIN mst_lokasi l ON a.lokasi_id = l.lokasi_id WHERE a.deleted_st = 0 AND a.active_st = 1";
        $d['all_asset'] = DbModel::rawData('result_array', $sqlAllAsset);

        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);

        return $this->renderView($this->template . 'index', $d);
    }


    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('log_kerja', ['log_kerja_id' => $id]);
        
        $sqlAllOrders = "SELECT ok.order_kerja_id, ok.jenis, ok.status,
                            ast.asset_nm, ast.no_seri, loc.lokasi_nm as asset_lokasi_nm
                         FROM order_kerja ok
                         LEFT JOIN jadwal_pm jp ON ok.jadwal_pm_id = jp.jadwal_pm_id
                         LEFT JOIN permintaan_komplain pk ON ok.permintaan_id = pk.permintaan_id
                         LEFT JOIN asset ast ON (jp.asset_id = ast.asset_id OR pk.asset_id = ast.asset_id)
                         LEFT JOIN mst_lokasi loc ON ast.lokasi_id = loc.lokasi_id
                         WHERE ok.deleted_st = 0 AND ok.active_st = 1";
        
        if ($id === null) {
            $sqlAllOrders .= " AND ok.order_kerja_id NOT IN (SELECT order_kerja_id FROM log_kerja WHERE deleted_st = 0)";
        } else {
            $sqlAllOrders .= " OR ok.order_kerja_id = '" . addslashes(@$d['main']['order_kerja_id']) . "'";
        }
        
        $d['all_orders'] = DbModel::rawData('result_array', $sqlAllOrders);
        
        $d['all_pegawai'] = DbModel::allData('mst_pegawai', ['deleted_st' => '0', 'active_st' => '1']);

        $d['form_act'] = $this->uri . '/save/' . $id;
        return $this->renderView($this->template . 'form_modal', $d);
    }

    function save($id = null)
    {
        $d_raw = _post();

        // Mapping Kolom Standar
        $dataToSave = [];
        $dataToSave['log_kerja_id'] = $d_raw['log_kerja_id'];
        $dataToSave['order_kerja_id'] = $d_raw['order_kerja_id'];
        $dataToSave['active_st'] = $d_raw['active_st'];

        // Map pegawai_id dari form ke teknisi_pegawai_id DB
        $dataToSave['teknisi_pegawai_id'] = $d_raw['pegawai_id'];
        
        // Map biaya_total dari form ke total_biaya DB
        $dataToSave['total_biaya'] = isset($d_raw['biaya_total']) ? $d_raw['biaya_total'] : 0.00;
        
        // Pemrosesan tanggal untuk DATETIME (hanya tgl_mulai_input, tanpa jam_mulai_input)
        if (isset($d_raw['tgl_mulai_input']) && $d_raw['tgl_mulai_input'] != '') {
            try { 
                // Jika input tanggal ada, gunakan dengan waktu default 00:00:00
                $dataToSave['tgl_mulai'] = (new \DateTime($d_raw['tgl_mulai_input']))->format('Y-m-d 00:00:00'); 
            } catch (\Exception $e) { $dataToSave['tgl_mulai'] = null; Log::error('Date conversion failed for tgl_mulai: ' . $e->getMessage()); }
        } else { 
            // Jika kosong, set ke tanggal dan jam saat ini (00:00:00) jika NOT NULL
            $dataToSave['tgl_mulai'] = now()->format('Y-m-d 00:00:00'); // Set ke awal hari ini
        }
        
        if (isset($d_raw['tgl_selesai_input']) && $d_raw['tgl_selesai_input'] != '') {
            try { 
                // Jika input tanggal selesai ada, gunakan dengan waktu default 00:00:00
                $dataToSave['tgl_selesai'] = (new \DateTime($d_raw['tgl_selesai_input']))->format('Y-m-d 00:00:00'); 
            } catch (\Exception $e) { $dataToSave['tgl_selesai'] = null; Log::error('Date conversion failed for tgl_selesai: ' . $e->getMessage()); }
        } else { $dataToSave['tgl_selesai'] = null; }


        // Mapping Kolom Baru
        $dataToSave['diagnosa'] = isset($d_raw['diagnosa']) ? $d_raw['diagnosa'] : null;
        $dataToSave['tindakan'] = isset($d_raw['tindakan']) ? $d_raw['tindakan'] : null;
        $dataToSave['hasil'] = isset($d_raw['hasil']) ? $d_raw['hasil'] : null;
        $dataToSave['durasi_menit'] = isset($d_raw['durasi_menit']) ? $d_raw['durasi_menit'] : 0;
        // Catatan teknisi sudah dihapus, jadi tidak diproses di sini


        // Validasi ID Order Kerja
        if (empty($dataToSave['order_kerja_id']) || !DbModel::validId('order_kerja', 'order_kerja_id', $dataToSave['order_kerja_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Order Kerja tidak valid atau kosong!']));
        }

        // Validasi duplikasi Log Kerja
        if ($id == null) {
            $checkDuplicateLog = DbModel::getData('log_kerja', ['order_kerja_id' => $dataToSave['order_kerja_id'], 'deleted_st' => 0]);
            if ($checkDuplicateLog) {
                return response()->json(_response('20', $this->uri, ['message' => 'Order Kerja ini sudah memiliki Log Kerja!']));
            }
        } else {
            $checkDuplicateLog = DbModel::rawData('row_array', "SELECT * FROM log_kerja WHERE order_kerja_id = '" . addslashes($dataToSave['order_kerja_id']) . "' AND log_kerja_id != '" . addslashes($id) . "' AND deleted_st = 0");
            if ($checkDuplicateLog) {
                return response()->json(_response('20', $this->uri, ['message' => 'Order Kerja ini sudah memiliki Log Kerja lain!']));
            }
        }
        
        // Validasi ID Teknisi
        if (!empty($dataToSave['teknisi_pegawai_id']) && !DbModel::validId('mst_pegawai', 'pegawai_id', $dataToSave['teknisi_pegawai_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Teknisi tidak valid!']));
        }
        
        try {
            DB::beginTransaction();

            if ($id == null) {
                $dataToSave['log_kerja_id'] = DbModel::getId('log_kerja', 2, 12);
                if (empty($dataToSave['log_kerja_id'])) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Gagal membuat ID Log Kerja!']));
                }

                $result = DbModel::insertData('log_kerja', $dataToSave);
                if (!$result) {
                    DB::rollBack();
                    return response()->json(_response('11', $this->uri, ['message' => 'Data log kerja gagal disimpan!']));
                }

                DbModel::updateData('order_kerja', ['status' => 'selesai'], ['order_kerja_id' => $dataToSave['order_kerja_id']]);

                DB::commit();
                return response()->json(_response('01', $this->uri, $dataToSave));
            } else {
                $result = DbModel::updateData('log_kerja', $dataToSave, ['log_kerja_id' => $id]);
                if ($result) {
                    DB::commit();
                    return response()->json(_response('02', $this->uri, $dataToSave));
                } else {
                    DB::rollBack();
                    return response()->json(_response('12', $this->uri, ['message' => 'Data log kerja gagal diubah!']));
                }
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error saving log kerja: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $dataToDelete = DbModel::getData('log_kerja', ['log_kerja_id' => $id]);
            if (!$dataToDelete) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Data log kerja tidak ditemukan!']));
            }

            DbModel::updateData('order_kerja', ['status' => 'diproses'], ['order_kerja_id' => $dataToDelete['order_kerja_id']]);

            $result = DbModel::deleteData('log_kerja', ['log_kerja_id' => $id]);
            if (!$result) {
                DB::rollBack();
                return response()->json(_response('13', $this->uri, ['message' => 'Data log kerja gagal dihapus!']));
            }

            DB::commit();
            return response()->json(_response('03', $this->uri));
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting log kerja: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('13', $this->uri, ['message' => 'Terjadi kesalahan saat menghapus data: ' . $th->getMessage()]));
        }
    }

    public function ajax_datatables()
    {
        return LogKerjaModel::loadDatatables();
    }
}