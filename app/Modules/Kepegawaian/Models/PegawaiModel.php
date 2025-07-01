<?php

namespace App\Modules\Kepegawaian\Models;

use App\Modules\App\Models\DbModel;
use Illuminate\Database\Eloquent\Model;

class PegawaiModel extends Model
{
  protected static $nav_sess;

  public function __construct()
  {
    parent::__construct();
    self::initSession();
  }

  protected static function initSession()
  {
    if (is_null(self::$nav_sess)) {
      self::$nav_sess = session(request('n'));
    }
  }

  static function loadDatatables()
  {
    self::initSession();

    $where = "1 = 1 ";

    if (@self::$nav_sess['search']['data']['divisi_id'] != '') {
      $where .= " AND a.divisi_id = '" . @self::$nav_sess['search']['data']['divisi_id'] . "' ";
    }

    if (@self::$nav_sess['search']['data']['jabatan_id'] != '') {
      $where .= " AND a.jabatan_id = '" . @self::$nav_sess['search']['data']['jabatan_id'] . "' ";
    }

    if (@self::$nav_sess['search']['data']['active_st'] != '') {
      $where .= " AND a.active_st = '" . @self::$nav_sess['search']['data']['active_st'] . "' ";
    }

    if (@self::$nav_sess['search']['data']['term'] != '') {
      $where .= " AND LOWER(a.pegawai_nm) LIKE '%" . @strtolower(self::$nav_sess['search']['data']['term']) . "%' ";
    }

    $query = "SELECT 
                *
              FROM (
                SELECT 
                  a.*,
                  b.divisi_nm,
                  c.jabatan_nm 
                FROM mst_pegawai a
                LEFT JOIN mst_divisi b ON a.divisi_id = b.divisi_id
                LEFT JOIN mst_jabatan c ON a.jabatan_id = c.jabatan_id
                WHERE $where
              ) x ";
    $search = ['pegawai_id', 'pegawai_nm'];
    $where = null;
    $isWhere = null;

    $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
    return response()->json($result);
  }

  static function allData()
  {
    $sql = "SELECT * FROM mst_pegawai";
    $result = DbModel::rawData('result_array', $sql);
    return $result;
  }

  static function loadDatatablesPermissions($id)
  {
    self::initSession();

    $query = "SELECT
                a.nav_id,
                a.nav_nm,
                a.active_st,
                b.pegawai_id,
                b.nav_id AS checked_nav,
                b.all_data_st AS checked_all  
              FROM
                app_nav a
              LEFT JOIN app_permission b ON a.nav_id = b.nav_id AND b.pegawai_id = '$id'";
    $search = ['a.nav_id', 'a.nav_nm'];
    $where = null;
    $isWhere = null;

    $result = DbModel::datatablesQuery($query, $search, $where, $isWhere);
    return response()->json($result);
  }

  static function savePermission($id, $data)
  {
    DbModel::deleteData('app_permission', ['pegawai_id' => $id]);
    foreach (@$data['nav_id'] as $key => $val) {
      $data_save = array(
        'pegawai_id' => @$data['pegawai_id'],
        'nav_id' => @$val,
      );
      DbModel::insertData('app_permission', $data_save, null);
    }

    if (is_array(@$data['all_data_st'])) {
      foreach (@$data['all_data_st'] as $key => $val) {
        DbModel::updateData(
          'app_permission',
          ['all_data_st' => 1],
          [
            'pegawai_id' => @$data['pegawai_id'],
            'nav_id' => @$val,
          ]
        );
      }
    }
    return true;
  }
}
