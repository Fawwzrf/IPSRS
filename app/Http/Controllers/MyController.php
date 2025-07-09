<?php

namespace App\Http\Controllers;

use App\Modules\App\Models\AppModel;

abstract class MyController extends Controller
{
  var $identitas;
  var $nav_id, $nav, $nav_sess;
  var $title, $uri, $parent, $search_act, $template;

  public function __construct()
  {
    $request = request();
    $this->identitas = AppModel::getIdentitas();

    // Nav
    $this->nav_id = $request->get('n');
    $this->nav = AppModel::getNav($this->nav_id);

    // Navigation
    $this->title = $this->nav['nav_nm'];
    $this->uri = url($this->nav['nav_url']);
    $this->parent = $this->nav['nav_parent'];

    // Session
    if (session($this->nav_id) == null) {
      session([$this->nav_id => $this->nav_sess]);
    }
    $this->nav_sess = session($this->nav_id);

    // Search
    $this->search_act = url('app/search_init?n=' . $this->nav_id);
  }

  function renderView(String $content, array $data = array())
  {
    $request = request();
    $data['identitas'] = $this->identitas;
    $data['nav_list'] = AppModel::listNav();

    $data['nav_id'] = $this->nav_id;
    $data['nav'] = $this->nav;
    $data['title'] = $this->title;
    $data['uri'] = $this->uri;
    $data['search_act'] = $this->search_act;
    $data['nav_sess'] = session(request('n'));
    $data['content'] = $content;

    // @load ajax
    $_is_ajax = $request->query('_is_ajax');
    if ($_is_ajax == true) {
      return View($content, $data)->render();
    } else {
      return View('app::template.index', $data);
    }
  }
  protected function save_session_search(&$d)
  {
    
    $session_name = request('n');

    
    if (request('search_act') == 'save') {
      
      session([
        $session_name => [
          'search' => [
            'data' => request('search'),
          ],
        ],
      ]);
    }
    
    else if (request('search_act') == 'reset') {
      
      session()->forget($session_name);
    }

    $d['nav_sess'] = session($session_name);
  }
}
