<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\globalController;
use App\Http\Controllers\logController;
use App\Http\Controllers\permissionsController;
use App\Http\Controllers\programsController;
use App\Http\Controllers\programs\authController as outAuthController;
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
    Route::get('abilities', 'abilities')->middleware('auth:sanctum');
});


Route::group(['middleware' => 'auth:sanctum'], function () {

    // Global
    Route::group(['controller' => globalController::class], function () {
        Route::get('/home', 'home');
    });

    // Permissions
    Route::group(['prefix' => 'permissions', 'controller' => permissionsController::class], function () {
        Route::get('/', 'getPermissions');
        Route::post('create', 'create');
        Route::post('update', 'update');
        Route::post('/delete/{permissionsId}', 'delete');
        Route::post('/force_delete/{permissionId}', 'forceDelete');
        Route::post('/restore/{permissionsId}', 'restore');
        Route::get('/{permissionId}', 'getPermission');
    });

    // Users
    Route::group(['prefix' => 'users', 'controller' => usersController::class], function () {
        Route::get('/', 'getUsers');
        Route::get('/profile', 'getProfile');
        Route::get('/form-init', 'usersFilters');
        Route::get('/users-list', 'getUsersList');
        Route::post('create', 'create');
        Route::post('update', 'update');
        Route::post('/update/profile', 'updateProfile');
        Route::post('specification', 'addSpecification');
        Route::post('/delete/{userId}', 'delete');
        Route::post('/force_delete/{userId}', 'forceDelete');
        Route::post('/restore/{userId}', 'restore');
        Route::get('/{userId}', 'getUser');
    });

    // Programs
    Route::group(['prefix' => 'programs', 'controller' => programsController::class], function () {
        Route::get('/', 'getPrograms');
        Route::get('/general-keys', 'getGeneralKeys');
        Route::get('/users-list', 'usersList');
        Route::post('create', 'create');
        Route::post('update', 'update');
        Route::post('keys/regenerate', 'regenerateKeys');
        Route::post('upload-files', 'uploadProgramFiles');
        Route::group(['prefix' => 'users'], function () {
            Route::post('add', 'addUsers');
            Route::post('delete', 'deleteUsers');
        });
        Route::post('/delete/{programId}', 'delete');
        Route::post('/force_delete/{programId}', 'forceDelete');
        Route::post('/restore/{programId}', 'restore');
        Route::group(['prefix' => 'auth',  'controller' => outAuthController::class], function () {
            Route::post('login-program', 'programLogin');
            Route::post('general-login', 'generalLogin')->withoutMiddleware('auth:sanctum');
            Route::post('logout', 'logout');
        });
    });

    // Logs
    Route::group(['prefix' => 'logs', 'controller' => logController::class], function () {
        Route::get('/', 'getLogs');
        Route::post('create', 'create');
    });
});
