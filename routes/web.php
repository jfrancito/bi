<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ResumenCalidadController;
use App\Http\Controllers\AcopioPrecioZonaController;
use App\Http\Controllers\AcopioAnalisisPrecioCalidadController;
use App\Http\Controllers\AcopioPrecioUnitarioHumedoController;
use App\Http\Controllers\AcopioPromedioIHController;
use App\Http\Controllers\AcopioPrecioUnitarioSecoController;
use App\Http\Controllers\AcopioTendenciaController;
use App\Http\Controllers\AcopioDashboardController;

// Rutas para invitados (no autenticados)
Route::middleware(['guestaw'])->group(function () {
	Route::any('/', [UserController::class, 'actionLogin']);
	Route::any('/login', [UserController::class, 'actionLogin'])->name('login');
});

Route::get('/cerrarsession', [UserController::class, 'actionCerrarSesion']);

// Rutas para usuarios autenticados
Route::middleware(['authaw'])->group(function () {

	Route::get('/bienvenido', [UserController::class, 'actionBienvenido'])->name('bienvenido');

	//GESTION DE USUARIOS
	Route::any('/gestion-de-usuarios/{idopcion}', [UserController::class, 'actionListarUsuarios']);
	Route::any('/agregar-usuario/{idopcion}', [UserController::class, 'actionAgregarUsuario']);
	Route::any('/modificar-usuario/{idopcion}/{idusuario}', [UserController::class, 'actionModificarUsuario']);

	//GESTION DE ROLES
	Route::any('/gestion-de-roles/{idopcion}', [UserController::class, 'actionListarRoles']);
	Route::any('/agregar-rol/{idopcion}', [UserController::class, 'actionAgregarRol']);
	Route::any('/modificar-rol/{idopcion}/{idrol}', [UserController::class, 'actionModificarRol']);

	//GESTION DE PERMISOS
	Route::any('/gestion-de-permisos/{idopcion}', [UserController::class, 'actionListarPermisos']);
	Route::any('/ajax-listado-de-opciones', [UserController::class, 'actionAjaxListarOpciones']);
	Route::any('/ajax-activar-permisos', [UserController::class, 'actionAjaxActivarPermisos']);

	//RESUMEN CALIDAD
	Route::any('/gestion-de-resumen-calidad/{idopcion}', [ResumenCalidadController::class, 'actionListarResumenCalidad']);
	Route::any('/ajax-listado-resumen-calidad', [ResumenCalidadController::class, 'actionAjaxListarResumenCalidad']);
	Route::any('/ajax-resumen-mensual', [ResumenCalidadController::class, 'actionAjaxResumenMensual']);
	Route::any('/ajax-resumen-calidad-mensual', [ResumenCalidadController::class, 'actionAjaxResumenCalidadMensual']);
	Route::any('/ajax-exportar-detalle-resumen-calidad', [ResumenCalidadController::class, 'actionExportarDetalleResumenCalidad']);

	//DASHBOARD
	Route::any('/ajax-dashboard-metrics', [AcopioDashboardController::class, 'actionAjaxGetDashboardMetrics']);

	//TENDENCIA
	Route::any('/gestion-de-tendencia/{idopcion}', [AcopioTendenciaController::class, 'actionListarTendencia']);
	Route::any('/ajax-listado-tendencia', [AcopioTendenciaController::class, 'actionAjaxListarTendencia']);
	//PRECIO ZONA
	Route::any('/gestion-de-precio-zona/{idopcion}', [AcopioPrecioZonaController::class, 'actionListarPrecioZona']);
	Route::any('/ajax-listado-precio-zona', [AcopioPrecioZonaController::class, 'actionAjaxListarPrecioZona']);
	Route::any('/ajax-comparativo-precios-mensual', [AcopioPrecioZonaController::class, 'actionAjaxComparativoPreciosMensual']);
	Route::any('/ajax-exportar-detalle-precio-zona', [AcopioPrecioZonaController::class, 'actionExportarDetallePrecioZona']);

	//ANALISIS PRECIO CALIDAD
	Route::any('/gestion-de-analisis-precio-calidad/{idopcion}', [AcopioAnalisisPrecioCalidadController::class, 'actionListarAnalisisPrecioCalidad']);
	Route::any('/ajax-listado-analisis-precio-calidad', [AcopioAnalisisPrecioCalidadController::class, 'actionAjaxListarAnalisisPrecioCalidad']);
	Route::any('/ajax-exportar-detalle-analisis', [AcopioAnalisisPrecioCalidadController::class, 'actionExportarDetalleAnalisis']);

	//PRECIO UNITARIO HUMEDO
	Route::any('/gestion-de-precio-unitario-humedo/{idopcion}', [AcopioPrecioUnitarioHumedoController::class, 'actionListarPrecioUnitarioHumedo']);
	Route::any('/ajax-listado-precio-unitario-humedo', [AcopioPrecioUnitarioHumedoController::class, 'actionAjaxListarPrecioUnitarioHumedo']);
	Route::any('/ajax-exportar-detalle-preciounitario-humedo', [AcopioPrecioUnitarioHumedoController::class, 'actionExportarDetallePrecioUnitario']);

	//PRECIO UNITARIO SECO
	Route::any('/gestion-de-precio-unitario-seco/{idopcion}', [AcopioPrecioUnitarioSecoController::class, 'actionListarPrecioUnitarioSeco']);
	Route::any('/ajax-listado-precio-unitario-seco', [AcopioPrecioUnitarioSecoController::class, 'actionAjaxListarPrecioUnitarioSeco']);
	Route::any('/ajax-exportar-detalle-preciounitario-seco', [AcopioPrecioUnitarioSecoController::class, 'actionExportarDetallePrecioUnitarioSeco']);

	//PROMEDIO I-H
	Route::any('/gestion-de-promedio-i-h/{idopcion}', [AcopioPromedioIHController::class, 'actionListarPromedioIH']);
	Route::any('/ajax-listado-promedio-i-h', [AcopioPromedioIHController::class, 'actionAjaxListarPromedioIH']);
	Route::any('/ajax-exportar-detalle-promedio-ih', [AcopioPromedioIHController::class, 'actionExportarDetallePromedioIH']);

});
















