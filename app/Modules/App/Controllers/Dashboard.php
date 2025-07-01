<?php

namespace App\Modules\App\Controllers;

use App\Http\Controllers\MyController;
use Illuminate\View\View;

class Dashboard extends MyController
{
  public function index(): View
  {
    return $this->renderView('app::dashboard.index');
  }
}
