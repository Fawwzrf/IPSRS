<?php

namespace App\Modules\Kepegawaian\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Kepegawaian\Models\PegawaiModel;

class Pegawai extends MyController
{
  function __construct()
  {
    parent::__construct();
    $this->template = 'kepegawaian::pegawai.';
  }

  function index()
  {
    $d = [];
    $d['all_jabatan'] = DbModel::allData('mst_jabatan');
    $d['all_divisi'] = DbModel::allData('mst_divisi');
    $d['all_pendidikan'] = DbModel::allData('mst_pendidikan');
    return $this->renderView($this->template . 'index', $d);
  }

  function form_modal($id = null)
  {
    $d['all_jabatan'] = DbModel::allData('mst_jabatan');
    $d['all_divisi'] = DbModel::allData('mst_divisi');
    $d['all_pendidikan'] = DbModel::allData('mst_pendidikan');

    $d['main'] = DbModel::getData('mst_pegawai', ['pegawai_id' => $id]);
    $d['form_act'] = $this->uri . '/save/' . $id;
    return $this->renderView($this->template . 'form_modal', $d);
  }

  function save($id = null)
  {
    $d = _post();

    $d['pegawai_nm'] = strtoupper($d['pegawai_nm']);
    $d['lahir_tmp'] = strtoupper($d['lahir_tmp']);
    $d['lahir_tgl'] = to_date($d['lahir_tgl'], '-', 'date');
    $d['pegawai_tmt'] = to_date($d['pegawai_tmt'], '-', 'date');
    if ($d['pegawai_tat'] != '') {
      $d['pegawai_tat'] = to_date($d['pegawai_tat'], '-', 'date');
    }

    $ttd = upload_base64('ttd');
    if ($ttd != null) $d['ttd'] = $ttd;

    if ($id == null) {
      if (DbModel::validId('mst_pegawai', 'pegawai_id', @$d['pegawai_id']) == true) {
        return response()->json(_response('20', $this->uri, $d));
      } else {
        $result = DbModel::insertData('mst_pegawai', $d);
        if ($result) {
          return response()->json(_response('01', $this->uri, $d));
        } else {
          return response()->json(_response('11', $this->uri, $d));
        }
      }
    } else {
      $result = DbModel::updateData('mst_pegawai', $d, ['pegawai_id' => $id]);
      if ($result) {
        return response()->json(_response('02', $this->uri, $d));
      } else {
        return response()->json(_response('12', $this->uri, $d));
      }
    }
  }

  function form_auth_modal($id = null)
  {
    $d['main'] = DbModel::getData('mst_pegawai', ['pegawai_id' => $id]);
    $d['form_act'] = $this->uri . '/save_auth/' . $id;
    return $this->renderView($this->template . 'form_auth_modal', $d);
  }

  function form_permission_modal($id = null)
  {
    $d['main'] = DbModel::getData('mst_pegawai', ['pegawai_id' => $id]);
    $d['form_act'] = $this->uri . '/save_permission/' . $id;
    return $this->renderView($this->template . 'form_permission_modal', $d);
  }

  public function save_auth($id = null)
  {
    $d = _post();
    $check_user_nm = DbModel::rawData('row_array', "SELECT * FROM mst_pegawai WHERE user_nm = '" . $d['user_nm'] . "' AND pegawai_id != '$id'");

    if ($check_user_nm != null) {
      return response()->json(_response('29', $this->uri, [
        'message' => 'Username sudah digunakan pegawai lain!'
      ]));
    } else {
      if ($d['password'] != $d['password_repeat']) {
        return response()->json(_response('29', $this->uri, [
          'message' => 'Password tidak sama!'
        ]));
      } else {
        $dPegawai = array(
          'user_nm' => $d['user_nm'],
          'user_hash' => password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]),
        );
        DbModel::updateData('mst_pegawai', $dPegawai, ['pegawai_id' => $id]);
        return response()->json(_response('02', $this->uri));
      }
    }
  }

  public function save_permission($id = null)
  {
    $d = _post();
    PegawaiModel::savePermission($id, $d);
    return response()->json(_response('01', $this->uri));
  }

  public function ajax_datatables()
  {
    return PegawaiModel::loadDatatables();
  }

  public function ajax_datatables_permissions($id)
  {
    return PegawaiModel::loadDatatablesPermissions($id);
  }
}
