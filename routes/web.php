<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthMiddleware; // Pastikan ini sesuai dengan path middleware kamu

Route::redirect('/', 'app/auth/login');

$modulePath = app_path('Modules');

if (is_dir($modulePath)) {
	foreach (scandir($modulePath) as $moduleName) {
		if ($moduleName === '.' || $moduleName === '..') {
			continue;
		}

		$controllerPath = $modulePath . '/' . $moduleName . '/Controllers';

		if (is_dir($controllerPath)) {
			// Tentukan middleware berdasarkan nama modul dan controller
			$middlewares = ['web'];
			if (strtolower($moduleName) !== 'app') {
				$middlewares[] = AuthMiddleware::class;
			}

			Route::prefix(strtolower($moduleName))->middleware($middlewares)->group(function () use ($controllerPath, $moduleName) {
				foreach (glob($controllerPath . '/*.php') as $controllerFile) {
					$controllerName = pathinfo($controllerFile, PATHINFO_FILENAME);
					$controllerClass = "App\\Modules\\$moduleName\\Controllers\\$controllerName";

					if (class_exists($controllerClass)) {
						$methods = (new ReflectionClass($controllerClass))->getMethods(ReflectionMethod::IS_PUBLIC);

						foreach ($methods as $method) {
							$methodName = $method->name;

							if ($methodName === '__construct') {
								continue;
							}

							// Tentukan apakah perlu menambahkan AuthMiddleware
							$methodMiddlewares = [];
							if (strtolower($controllerName) !== 'auth') {
								$methodMiddlewares[] = AuthMiddleware::class;
							}

							// Cek apakah controller memiliki nama yang sama dengan modul
							$isMainController = strtolower($controllerName) === strtolower($moduleName);
							$routeBase = $isMainController ? '' : strtolower($controllerName) . '/';

							// Cek parameter method
							$parameters = $method->getParameters();
							$paramString = '';
							$optionalParams = [];

							if (count($parameters) > 0 && count($parameters) <= 5) {
								foreach ($parameters as $param) {
									$paramName = $param->name;
									$optionalParams[] = $param->isOptional() ? '{' . $paramName . '?}' : '{' . $paramName . '}';
								}
								$paramString = '/' . implode('/', $optionalParams);
							}

							// Buat route dengan GET dan POST untuk setiap method
							if ($methodName === 'index') {
								$route = Route::get($routeBase . $paramString, [$controllerClass, $methodName])
									->name(strtolower($moduleName) . '.' . strtolower($controllerName) . '.' . strtolower($methodName));
								$route = Route::post($routeBase . $paramString, [$controllerClass, $methodName])
									->name(strtolower($moduleName) . '.' . strtolower($controllerName) . '.' . strtolower($methodName));
							}

							$route = Route::get($routeBase . strtolower($methodName) . $paramString, [$controllerClass, $methodName])
								->name(strtolower($moduleName) . '.' . strtolower($controllerName) . '.' . strtolower($methodName));

							if (!empty($methodMiddlewares)) {
								$route->middleware($methodMiddlewares);
							}

							$route = Route::post($routeBase . strtolower($methodName) . $paramString, [$controllerClass, $methodName])
								->name(strtolower($moduleName) . '.' . strtolower($controllerName) . '.' . strtolower($methodName));

							if (!empty($methodMiddlewares)) {
								$route->middleware($methodMiddlewares);
							}
						}
					}
				}
			});
		}
	}
}
