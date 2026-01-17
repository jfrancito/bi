<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ResumenCalidadController;
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



});
















