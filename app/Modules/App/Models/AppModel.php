<?php

namespace App\Modules\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppModel extends Model
{
  static function getIdentitas()
  {
    $sql = "SELECT 
              a.*,
              b.pegawai_nm as kepala_nm
            FROM mst_identitas a
            LEFT JOIN mst_pegawai b ON a.kepala_id = b.pegawai_id";
    $result = DbModel::rawData('row', $sql);
    return $result;
  }

  /**
   * NAVIGATION
   */
  static function listNav($nav_parent = '')
  {
    $pegawai_id = session('pegawai_id');

    $where = ($nav_parent != '') ? "b.nav_parent = '$nav_parent'" : "(b.nav_parent = '' OR b.nav_parent IS NULL)";
    $sql = "SELECT 
              a.pegawai_id, a.nav_id, 
              b.nav_nm, b.nav_url, b.icon
            FROM app_permission a
            JOIN app_nav b ON a.nav_id = b.nav_id
            WHERE 
              $where
              AND a.pegawai_id = '$pegawai_id'
              AND b.active_st = 1
              AND a.nav_id != '00'
            ORDER BY a.nav_id";
    $res = DbModel::rawData('result_array', $sql);
    if ($res != null) {
      foreach ($res as $key => $val) {
        $res[$key]['child'] = AppModel::listNav($res[$key]['nav_id']);
      }
      return $res;
    } else {
      return array();
    }
  }

  static function getNav($nav_id)
  {
    $pegawai_id = session('pegawai_id');
    $nav_id = e($nav_id);

    $sql = "SELECT 
                  a.*, b.all_data_st 
                FROM app_nav a
                JOIN app_permission b ON a.nav_id = b.nav_id
                WHERE 
                  b.pegawai_id = '$pegawai_id'
                  AND md5(a.nav_id) = '$nav_id'";
    $res = DbModel::rawData('row_array', $sql);

    return $res;
  }
}
