<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\logController;
use App\Http\Controllers\permissionsController;
use App\Http\Controllers\programsController;
use App\Http\Controllers\usersController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['prefix' => 'auth', 'controller' => authController::class], function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});


Route::group(['middleware' => 'auth:sanctum'], function () {

    // Permissions
    Route::group(['prefix' => 'permissions', 'controller' => permissionsController::class], function () {
        Route::get('/', 'getPermissions');
        Route::post('create', 'create');
        Route::post('update', 'update');
        Route::post('/delete/{permissionsId}', 'delete');
        Route::post('/restore/{permissionsId}', 'restore');
        Route::get('/{permissionId}', 'getPermission');
    });

    // Users
    Route::group(['prefix' => 'users', 'controller' => usersController::class], function () {
        Route::get('/', 'getUsers');
        Route::get('/form-init', 'usersFilters');
        Route::post('create', 'create');
        Route::post('update', 'update');
        Route::post('/delete/{userId}', 'delete');
        Route::post('/restore/{userId}', 'restore');
        Route::get('/{userId}', 'getUser');
    });

    // Programs
    Route::group(['prefix' => 'programs', 'controller' => programsController::class], function () {
        Route::get('/', 'getPrograms');
        Route::get('/users-list', 'usersList');
        Route::post('create', 'create');
        Route::post('update', 'update');
        Route::group(['prefix' => 'users'], function () {
            Route::post('add', 'addUsers');
            Route::post('delete', 'deleteUsers');
        });
        Route::post('/delete/{programId}', 'delete');
        Route::post('/restore/{programId}', 'restore');
    });

    // Logs
    Route::group(['prefix' => 'logs', 'controller' => logController::class], function () {
        Route::get('/', 'getLogs');
        Route::post('create', 'create')->withoutMiddleware('auth:sanctum');
    });
});
