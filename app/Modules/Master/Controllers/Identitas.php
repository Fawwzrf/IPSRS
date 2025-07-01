<?php

namespace App\Modules\Master\Controllers;

use App\Http\Controllers\MyController;
use App\Modules\App\Models\DbModel;
use App\Modules\Master\Models\IdentitasModel;
use App\Modules\Kepegawaian\Models\PegawaiModel;

class Identitas extends MyController
{
  function __construct()
  {
    parent::__construct();
    $this->template = 'master::identitas.';
  }

  function index()
  {
    $d['all_pegawai'] = PegawaiModel::allData();
    
    $d['main'] = IdentitasModel::getData();
    $d['form_act'] = $this->uri . '/save';
    return $this->renderView($this->template . 'index', $d);
  }

  function save()
  {
    $d = _post();

    $logo = upload_base64('logo');
    if ($logo != null) $d['logo'] = $logo;

    $photo = upload_base64('photo');
    if ($photo != null) $d['photo'] = $photo;

    $background = upload_base64('background');
    if ($background != null) $d['background'] = $background;

    $kop_surat = upload_base64('kop_surat');
    if ($kop_surat != null) $d['kop_surat'] = $kop_surat;

    $result = DbModel::updateData('mst_identitas', $d, ['identitas_id' => 1]);
    if ($result) {
      return response()->json(_response('02', $this->uri, $d));
    } else {
      return response()->json(_response('12', $this->uri, $d));
    }
  }
}
