<?php

namespace App\Modules\Master\Controllers; // Perbaiki namespace: namespace App\Modules\Master\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Master\Models\LokasiModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Lokasi extends MyController
{
    function __construct()
    {
        parent::__construct();
        $this->template = 'master::lokasi.';
    }

    function index()
    {
        $d = [];

        $sql = "SELECT lokasi_id, lokasi_nm, tipe_lokasi FROM mst_lokasi WHERE deleted_st = 0 AND active_st = 1 AND tipe_lokasi IN ('Gedung', 'Lantai')";
        $d['all_parent_lokasi'] = DbModel::rawData('result_array', $sql);

        $d['nav_sess'] = session(request('n'));

        return $this->renderView($this->template . 'index', $d);
    }

    public function form_modal($id = null)
    {
        $d = [];
        $d['main'] = DbModel::getData('mst_lokasi', ['lokasi_id' => $id]);

        // Mengambil semua lokasi yang bisa menjadi parent untuk ditampilkan di dropdown
        $d['all_parent_lokasi'] = DbModel::allData('mst_lokasi', [
            'deleted_st' => '0',
            'active_st' => '1',
        ]);

        // Jika mode edit, filter agar lokasi itu sendiri tidak muncul di pilihan parent
        if ($id) {
            $d['all_parent_lokasi'] = array_filter($d['all_parent_lokasi'], function ($lokasi) use ($id) {
                return $lokasi['lokasi_id'] != $id;
            });
        }

        if ($id === null) {
            $d['form_act'] = $this->uri . '/save';
        } else {
            $d['form_act'] = $this->uri . '/save/' . $id;
        }

        return $this->renderView($this->template . 'form_modal', $d);
    }

    private function _generateLokasiId($tipe, $parentId = null)
    {
        $prefix = $parentId ? $parentId . '.' : '';
        // Query untuk mencari ID terakhir berdasarkan parent dan tipe
        $query = "SELECT MAX(lokasi_id) as last_id FROM mst_lokasi WHERE tipe_lokasi = ? AND deleted_st = 0";
        $params = [$tipe];

        if ($parentId) {
            $query .= " AND parent_lokasi_id = ?";
            $params[] = $parentId;
        } else {
            // Jika tidak ada parent (untuk tipe Gedung)
            $query .= " AND parent_lokasi_id IS NULL";
        }

        $lastData = DbModel::rawData('row_array', $query, $params);
        $lastId = $lastData['last_id'] ?? '';

        if (empty($lastId)) {
            // Jika ini adalah data pertama untuk parent/tipe ini, mulai dari 01
            return $prefix . '01';
        } else {
            // Ambil bagian numerik terakhir dari ID, tambahkan 1, format ulang
            $parts = explode('.', $lastId);
            $lastNumber = (int)end($parts);
            $newNumber = $lastNumber + 1;
            // str_pad untuk memastikan formatnya selalu 2 digit (01, 02, ... 10)
            return $prefix . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
        }
    }
    function save($id = null)
    {
        $d = _post();

        if (empty($d['parent_lokasi_id'])) {
            $d['parent_lokasi_id'] = null;
        }

        $queryCheckUnique = "SELECT * FROM mst_lokasi WHERE lokasi_nm = '" . addslashes($d['lokasi_nm']) . "' AND tipe_lokasi = '" . addslashes($d['tipe_lokasi']) . "' AND deleted_st = 0";
        if (!empty($d['parent_lokasi_id'])) {
            $queryCheckUnique .= " AND parent_lokasi_id = '" . addslashes($d['parent_lokasi_id']) . "'";
        } else {
            $queryCheckUnique .= " AND (parent_lokasi_id IS NULL OR parent_lokasi_id = '')";
        }
        if ($id != null) {
            $queryCheckUnique .= " AND lokasi_id != '" . addslashes($id) . "'";
        }
        $checkUnique = DbModel::rawData('row_array', $queryCheckUnique);
        if ($checkUnique != null) {
            return response()->json(_response('20', $this->uri, ['message' => 'Kombinasi Nama, Tipe, dan Lokasi Induk sudah ada!']));
        }

        if (!empty($d['parent_lokasi_id']) && !DbModel::validId('mst_lokasi', 'lokasi_id', $d['parent_lokasi_id'])) {
            return response()->json(_response('11', $this->uri, ['message' => 'ID Lokasi Induk tidak ditemukan!']));
        }

        if (isset($_FILES['denah_url']) && $_FILES['denah_url']['error'] == 0) {

            // 1. Ambil informasi file yang diupload
            $fileTmpPath = $_FILES['denah_url']['tmp_name'];
            $fileMimeType = mime_content_type($fileTmpPath); // Cara aman mendapatkan tipe MIME

            // 2. Baca konten file mentah (binary)
            $fileContent = file_get_contents($fileTmpPath);

            // 3. Encode konten menjadi Base64
            $base64Content = base64_encode($fileContent);

            // 4. Buat string Data URL lengkap untuk disimpan di database
            $d['denah_url'] = 'data:' . $fileMimeType . ';base64,' . $base64Content;
        } else {
            // Jika tidak ada file baru, pertahankan data lama dari hidden input
            $d['denah_url'] = $d['denah_url_old'];
        }

        // Hapus field helper dari array data
        unset($d['denah_url_old']);
        try {
            if ($id == null) {

                $d['lokasi_id'] = $this->_generateLokasiId($d['tipe_lokasi'], $d['parent_lokasi_id']);


                $result = DbModel::insertData('mst_lokasi', $d);
                if ($result) {
                    return response()->json(_response('01', $this->uri, $d));
                } else {
                    return response()->json(_response('11', $this->uri, $d));
                }
            } else {
                $result = DbModel::updateData('mst_lokasi', $d, ['lokasi_id' => $id]);
                if ($result) {
                    return response()->json(_response('02', $this->uri, $d));
                } else {
                    return response()->json(_response('12', $this->uri, $d));
                }
            }

            
        } catch (\Throwable $th) {
            Log::error('Error saving location: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json(_response('10', $this->uri, ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $th->getMessage()]));
        }

        if ($id) {
            // Mode Edit
            $old_id = $id;
            $new_id = $d['lokasi_id'];

            // Jika ID diubah, pastikan ID baru belum ada
            if ($old_id != $new_id) {
                if (DbModel::validId('mst_lokasi', 'lokasi_id', $new_id)) {
                    return response()->json(_response('20', $this->uri, ['message' => 'ID Lokasi baru sudah digunakan!']));
                }
            }

            $result = DbModel::updateData('mst_lokasi', $d, ['lokasi_id' => $old_id]);
            return response()->json(_response($result ? '02' : '12', $this->uri, $d));
        } else {
            // Mode Insert (ID dibuat otomatis)
            $d['lokasi_id'] = $this->_generateLokasiId($d['tipe_lokasi'], $d['parent_lokasi_id']);
            $result = DbModel::insertData('mst_lokasi', $d);
            return response()->json(_response($result ? '01' : '11', $this->uri, $d));
        }
    }

    public function delete($id)
    {
        $hasChildren = DbModel::getData('mst_lokasi', ['parent_lokasi_id' => $id, 'deleted_st' => 0]);
        if ($hasChildren) {
            return response()->json(_response('13', $this->uri, ['message' => 'Lokasi ini memiliki sub-lokasi dan tidak dapat dihapus.']));
        }

        $hasAssets = DbModel::getData('asset', ['lokasi_id' => $id, 'deleted_st' => 0]);
        if ($hasAssets) {
            return response()->json(_response('13', $this->uri, ['message' => 'Lokasi ini masih terhubung dengan aset dan tidak dapat dihapus.']));
        }
        $result = DbModel::deleteData('mst_lokasi', ['lokasi_id' => $id]);
        if ($result) {
            return response()->json(_response('03', $this->uri));
        } else {
            return response()->json(_response('13', $this->uri));
        }
    }



    public function ajax_datatables()
    {
        return LokasiModel::loadDatatables();
    }
}
