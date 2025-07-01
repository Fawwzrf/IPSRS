<?php

namespace App\Modules\App\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\App\Models\AppModel;

class App extends Controller
{
  public static function search_init()
  {
    $s = session(request('n'));
    $s['search'] = _post();
    $nav = AppModel::getNav(request('n'));
    session([request('n') => $s]);
    return response()->json(_response('00', url($nav['nav_url'])));
  }

  function search_reset()
  {
    $d = _post();
    $s = session(request('n'));
    $s['search'] = [];
    $s['cur_page'] = 0;
    $s['per_page'] = 10;
    session([request('n') => $s]);
    $nav = AppModel::getNav(request('n'));
    return response()->json(_response('00', url($nav['nav_url'])));
  }
}
