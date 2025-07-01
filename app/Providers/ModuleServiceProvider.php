<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ModuleServiceProvider extends ServiceProvider
{
  public function register() {}

  public function boot()
  {
    // Pastikan hanya menambahkan namespace jika View tersedia
    if (class_exists(View::class)) {
      foreach (glob(base_path('app/Modules/*/Views'), GLOB_ONLYDIR) as $viewPath) {
        $moduleName = basename(dirname($viewPath));
        View::addNamespace(strtolower($moduleName), $viewPath);
      }
    }
  }
}
