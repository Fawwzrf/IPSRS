<?php

namespace App\Http\Controllers;

use App\Modules\App\Models\AppModel;
abstract class Controller
{
  var $identitas;
  public function __construct()
  {
    $this->identitas = AppModel::getIdentitas();
  }
}
