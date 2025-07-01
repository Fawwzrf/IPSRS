<?php

namespace App\Modules\Master\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Master\Models\JabatanModel;

class Jabatan extends MyController
{
  function __construct()
  {
    parent::__construct();
    $this->template = 'master::jabatan.';
  }

  function index()
  {
    $d = [];
    return $this->renderView($this->template . 'index', $d);
  }

  function form_modal($id = null)
  {
    $d['main'] = DbModel::getData('mst_jabatan', ['jabatan_id' => $id]);
    $d['parent'] = DbModel::allData('mst_jabatan', ['deleted_st' => '0', 'active_st' => '1']);
    $d['form_act'] = $this->uri . '/save/' . $id;
    return $this->renderView($this->template . 'form_modal', $d);
  }

  function save($id = null)
  {
    $d = _post();
    if ($id == null) {
      if (DbModel::validId('mst_jabatan', 'jabatan_id', @$d['jabatan_id']) == true) {
        return response()->json(_response('20', $this->uri, $d));
      } else {
        $result = DbModel::insertData('mst_jabatan', $d);
        if ($result) {
          return response()->json(_response('01', $this->uri, $d));
        } else {
          return response()->json(_response('11', $this->uri, $d));
        }
      }
    } else {
      $result = DbModel::updateData('mst_jabatan', $d, ['jabatan_id' => $id]);
      if ($result) {
        return response()->json(_response('02', $this->uri, $d));
      } else {
        return response()->json(_response('12', $this->uri, $d));
      }
    }
  }

  public function delete($id)
  {
    $result = DbModel::deleteData('mst_jabatan', ['jabatan_id' => $id]);
    if ($result) {
      return response()->json(_response('03', $this->uri));
    } else {
      return response()->json(_response('13', $this->uri));
    }
  }

  public function ajax_datatables()
  {
    return JabatanModel::loadDatatables();
  }
}
