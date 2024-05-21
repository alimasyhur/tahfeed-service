<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function() {
    Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::post('/reset-password', [\App\Http\Controllers\Api\AuthController::class, 'resetPassword']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('users')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\UserController::class, 'index']);
        Route::get('/me', [\App\Http\Controllers\Api\ProfileController::class, 'me']);
        Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
        Route::post('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'store']);
        Route::patch('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
    });

    Route::prefix('orgs')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\OrganizationController::class, 'index']);
        Route::get('/me/{uuid}', [\App\Http\Controllers\Api\OrganizationController::class, 'show']);
        Route::get('/domain/{domain}', [\App\Http\Controllers\Api\OrganizationController::class, 'showByDomain']);
        Route::post('/', [\App\Http\Controllers\Api\OrganizationController::class, 'store']);
        Route::patch('/{uuid}', [\App\Http\Controllers\Api\OrganizationController::class, 'update']);
    });

    Route::prefix('roles')->group(function() {
        Route::get('/users', [\App\Http\Controllers\Api\RoleUserController::class, 'index']);
        Route::get('/users/options', [\App\Http\Controllers\Api\RoleUserController::class, 'option']);
        Route::post('/users', [\App\Http\Controllers\Api\RoleUserController::class, 'store']);
        Route::patch('/users/{uuid}', [\App\Http\Controllers\Api\RoleUserController::class, 'update']);

        Route::post('/assign', [\App\Http\Controllers\Api\RoleUserController::class, 'store']);
        Route::delete('/assign/{uuid}', [\App\Http\Controllers\Api\RoleUserController::class, 'destroy']);

        Route::get('/', [\App\Http\Controllers\Api\RoleController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\RoleController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Api\RoleController::class, 'store']);
        Route::patch('/{uuid}', [\App\Http\Controllers\Api\RoleController::class, 'update']);
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\RoleController::class, 'destroy']);
    });
});
