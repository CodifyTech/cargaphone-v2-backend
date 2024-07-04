<?php

use Domains\Auth\Controllers\AuthController;
use Domains\Auth\Controllers\PermissionController;
use Domains\Auth\Controllers\RoleController;
use Domains\Auth\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');

    Route::post('forgot-password', 'forgotPassword')->name('forgot.password');
    Route::post('reset-password', 'resetPassword')->name('password.reset');
});

Route::get('getvideoslist', [Domains\Totem\Controllers\TotemController::class, 'totemsEAnuncios']);

Route::middleware(['auth:sanctum'])->group(function () {
    /*
     * Auth
     */
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::get('profile', 'profile');
        Route::post('logout', 'logout');
    });

    /*
     * Permissions
     */
    Route::prefix('permission')->apiResource('permission', PermissionController::class);
    Route::post('permission/remover/todas', [PermissionController::class, 'destroyAll']);
    Route::get('permission/listar/todas', [PermissionController::class, 'listAll']);
    Route::get('permission/listar/acoes', [PermissionController::class, 'listarAcoes']);
    Route::get('permission/atribuir-permission/{user:id}/{permission:id}', [PermissionController::class, 'atribuirUserPermission']);
    Route::get('permission/remover-permission/{user:id}/{permission:id}', [PermissionController::class, 'removerUserPermission']);

    /*
     * Roles
     */
    Route::prefix('roles')->apiResource('roles', RoleController::class);
    Route::get('roles/atribuir-role/{user:id}/{role:id}', [RoleController::class, 'atribuirUserRole']);
    Route::get('roles/atribuir-role-permission/{user:id}/{role:id}', [RoleController::class, 'atribuirUserRolePermission']);
    Route::get('roles/remover-role/{user:id}/{role:id}', [RoleController::class, 'removeUserRolePermission']);

    /*
     * User
     */
    Route::prefix('usuarios')->apiResource('usuarios', UserController::class);
    Route::get('usuarios/pesquisarpor/{field}/{value}/{relation?}', [UserController::class, 'search']);
    Route::get('usuarios/listar/roles', [UserController::class, 'roles']);

	/*
	 * Route: estabelecimento
	 * Created at: 2024-06-14 17:31:18
	 */
	Route::prefix('estabelecimento')->apiResource('estabelecimento', Domains\Estabelecimento\Controllers\EstabelecimentoController::class);
	Route::get('estabelecimento/pesquisarpor/{field}/{value}/{relation?}', [Domains\Estabelecimento\Controllers\EstabelecimentoController::class, 'search']);


	/*
	 * Route: unidade
	 * Created at: 2024-06-18 15:36:54
	 */
	Route::prefix('unidade')->apiResource('unidade', Domains\Unidade\Controllers\UnidadeController::class);
	Route::get('unidade/pesquisarpor/{field}/{value}/{relation?}', [Domains\Unidade\Controllers\UnidadeController::class, 'search']);

	/*
	 * Route: totem
	 * Created at: 2024-06-24 16:07:24
	 */
	Route::prefix('totem')->apiResource('totem', Domains\Totem\Controllers\TotemController::class);
	Route::get('totem/pesquisarpor/{field}/{value}/{relation?}', [Domains\Totem\Controllers\TotemController::class, 'search']);
	Route::get('totem/listar/estabelecimento', [Domains\Totem\Controllers\TotemController::class, 'listarEstabelecimento']);
	Route::get('totem/listar/unidade', [Domains\Totem\Controllers\TotemController::class, 'listarUnidade']);

	/*
	 * Route: anuncio
	 * Created at: 2024-07-03 16:05:21
	 */
	Route::prefix('anuncio')->apiResource('anuncio', Domains\Anuncio\Controllers\AnuncioController::class);
	Route::get('anuncio/pesquisarpor/{field}/{value}/{relation?}', [Domains\Anuncio\Controllers\AnuncioController::class, 'search']);
    Route::get('anuncio/listar/totem', [Domains\Anuncio\Controllers\AnuncioController::class, 'listarTotems']);

});
