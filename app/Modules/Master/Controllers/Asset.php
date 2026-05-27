<?php

namespace App\Modules\Master\Controllers;

use App\Modules\App\Models\DbModel;
use App\Http\Controllers\MyController;
use App\Modules\Master\Models\AssetModel;

class Asset extends MyController
{
        /**
         * Konstruktor kelas Asset
         */
        function __construct()
        {
                parent::__construct();
                $this->template = 'master::asset.';
        }

        /**
         * Menampilkan halaman utama aset
         */
        function index()
        {
                $d = [];
                $this->save_session_search($d);
                $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => '0', 'active_st' => '1', 'tipe_lokasi' => 'Ruangan']);
                $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => '0', 'active_st' => '1']);
                return $this->renderView($this->template . 'index', $d);
        }

        /**
         * Menampilkan form modal untuk tambah/edit aset
         */
        function form_modal($id = null)
        {
                $d['all_lokasi'] = DbModel::allData('mst_lokasi', ['deleted_st' => '0', 'active_st' => '1', 'tipe_lokasi' => 'Ruangan']);
                $d['all_kategori_asset'] = DbModel::allData('mst_kategori_asset', ['deleted_st' => '0', 'active_st' => '1']);
                $d['main'] = DbModel::getData('mst_asset', ['asset_id' => $id]);
                $d['form_act'] = $this->uri . '/save' . $id;
                return $this->renderView($this->template . 'form_modal', $d);
        }

        /**
         * Menyimpan data aset (insert/update)
         */
        function save($id = null)
        {
                $d = _post();

                // Validasi dan normalisasi tanggal
                $d['perolehan_tgl'] = !empty($d['perolehan_tgl']) ? to_date($d['perolehan_tgl'], '-', 'date') : null;
                $d['pm_berikutnya'] = !empty($d['pm_berikutnya']) ? to_date($d['pm_berikutnya'], '-', 'date') : null;

                // Validasi wajib
                $required = [
                        'asset_nm' => 'Nama Aset wajib diisi!',
                        'no_seri' => 'Nomor Seri wajib diisi!',
                        'kategori_asset_id' => 'Kategori Aset wajib diisi!',
                        'lokasi_id' => 'Lokasi wajib diisi!',
                ];
                foreach ($required as $field => $msg) {
                        if (empty($d[$field])) {
                                return response()->json(_response('20', $this->uri, ['message' => $msg]));
                        }
                }

                // Validasi nomor seri unik
                if (!empty($d['no_seri'])) {
                        $queryCheckNoSeri = DbModel::rawData(
                                'row_array',
                                "SELECT * FROM mst_asset WHERE no_seri = ? AND asset_id != ? AND deleted_st = 0",
                                [$d['no_seri'], $id ?? '']
                        );
                        if ($queryCheckNoSeri != null) {
                                return response()->json(_response('20', $this->uri, ['message' => 'Nomor Seri sudah digunakan untuk aset lain!']));
                        }
                }

                // Proses simpan/ubah
                if ($id == null) {
                        $d['asset_id'] = DbModel::getId('mst_asset', 2, 12);
                        if (empty($d['asset_id'])) {
                                return response()->json(_response('11', $this->uri, ['message' => 'Gagal membuat ID Aset baru!']));
                        }
                        if (DbModel::validId('mst_asset', 'asset_id', $d['asset_id'])) {
                                return response()->json(_response('20', $this->uri, ['message' => 'ID Aset sudah digunakan!']));
                        }
                        $result = DbModel::insertData('mst_asset', $d);
                        if ($result) {
                                return response()->json(_response('01', $this->uri, $d));
                        } else {
                                return response()->json(_response('11', $this->uri, ['message' => 'Gagal menyimpan data!']));
                        }
                } else {
                        $result = DbModel::updateData('mst_asset', $d, ['asset_id' => $id]);
                        if ($result) {
                                return response()->json(_response('02', $this->uri, $d));
                        } else {
                                return response()->json(_response('12', $this->uri, ['message' => 'Gagal mengupdate data!']));
                        }
                }
        }

        /**
         * Menghapus data aset (soft delete)
         */
        public function delete($id)
        {
                $asset = DbModel::getData('mst_asset', ['asset_id' => $id, 'deleted_st' => 0]);
                if (!$asset) {
                        return response()->json(_response('13', $this->uri, ['message' => 'Aset tidak ditemukan atau sudah dihapus.']));
                }

                $result = DbModel::updateData('mst_asset', ['deleted_st' => 1], ['asset_id' => $id]);
                if ($result) {
                        return response()->json(_response('03', $this->uri));
                } else {
                        return response()->json(_response('13', $this->uri, ['message' => 'Gagal menghapus aset.']));
                }
        }
        /**
         * Menampilkan modal detail aset
         */
        function form_detail_modal($id = null)
        {
                $assetModel = new AssetModel();
                $d['asset'] = $assetModel->getAssetDetailById($id);
                $d['history'] = $assetModel->getAssetHistory($id);
                $d['title'] = 'Detail Aset: ' . ($d['asset']['asset_nm'] ?? '');
                return $this->renderView($this->template . 'detail_modal', $d);
        }

        /**
         * Fungsi untuk datatables yang memanggil model
         */
        function ajax_datatables()
        {
                return AssetModel::loadDatatables();
        }
}
