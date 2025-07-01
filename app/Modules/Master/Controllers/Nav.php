<?php

namespace App\Modules\Master\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Master\Models\NavModel;

class Nav extends MyController
{
  function __construct()
  {
    parent::__construct();
    $this->template = 'master::nav.';
  }

  function index()
  {
    $d = [];
    return $this->renderView($this->template . 'index', $d);
  }

  function form_modal($id = null)
  {
    $d['main'] = DbModel::getData('app_nav', ['nav_id' => $id]);
    $d['parent'] = DbModel::allData('app_nav', ['deleted_st' => '0', 'active_st' => '1']);
    $d['form_act'] = $this->uri . '/save/' . $id;
    return $this->renderView($this->template . 'form_modal', $d);
  }

  function save($id = null)
  {
    $d = _post();
    if ($id == null) {
      if (DbModel::validId('app_nav', 'nav_id', @$d['nav_id']) == true) {
        return response()->json(_response('20', $this->uri, $d));
      } else {
        $result = DbModel::insertData('app_nav', $d);
        if ($result) {
          return response()->json(_response('01', $this->uri, $d));
        } else {
          return response()->json(_response('11', $this->uri, $d));
        }
      }
    } else {
      $result = DbModel::updateData('app_nav', $d, ['nav_id' => $id]);
      if ($result) {
        return response()->json(_response('02', $this->uri, $d));
      } else {
        return response()->json(_response('12', $this->uri, $d));
      }
    }
  }

  public function delete($id)
  {
    $result = DbModel::deleteData('app_nav', ['nav_id' => $id]);
    if ($result) {
      return response()->json(_response('03', $this->uri));
    } else {
      return response()->json(_response('13', $this->uri));
    }
  }

  function full_access($id)
  {
    $result = NavModel::fullAccess($id);
    if ($result) {
      return response()->json(_response('02', $this->uri));
    } else {
      return response()->json(_response('12', $this->uri));
    }
  }

  public function ajax_datatables()
  {
    return NavModel::loadDatatables();
  }
}
