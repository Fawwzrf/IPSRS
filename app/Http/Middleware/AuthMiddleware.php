<?php

namespace App\Http\Middleware;

use Closure;
use App\Modules\App\Models\AppModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
  public function handle(Request $request, Closure $next): Response
  {
    if ($request->session()->get('login_st') != 1) {
      return redirect('app/auth/login');
    }
    if (AppModel::getNav($request->input('n')) == null) {
      return redirect('dashboard/index?n=' . md5('00'));
    }
    return $next($request);
  }
}
