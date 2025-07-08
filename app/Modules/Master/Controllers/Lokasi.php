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

    function form_modal($id = null)
    {
        $d['main'] = DbModel::getData('mst_lokasi', ['lokasi_id' => $id]);
        $d['all_parent_lokasi'] = DbModel::allData('mst_lokasi', [
            ['deleted_st', '=', '0'],
            ['active_st', '=', '1'],
            ['lokasi_id', '!=', $id]
        ]);

        if ($id === null) {
            $d['form_act'] = $this->uri . '/save';
        } else {
            $d['form_act'] = $this->uri . '/save/' . $id;
        }

        return $this->renderView($this->template . 'form_modal', $d);
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
                if (empty($d['lokasi_id'])) {
                    return response()->json(_response('11', $this->uri, ['message' => 'ID Lokasi wajib diisi!']));
                }
                if (DbModel::validId('mst_lokasi', 'lokasi_id', $d['lokasi_id'])) {
                    return response()->json(_response('20', $this->uri, ['message' => 'ID Lokasi sudah ada!']));
                }

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
        $lokasi_data = DbModel::getData('mst_lokasi', ['lokasi_id' => $id]);
        if ($lokasi_data && $lokasi_data['denah_url']) {
            $file_path = public_path($lokasi_data['denah_url']);
            if (file_exists($file_path)) {
                unlink($file_path); // Hapus file fisik
            }
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
