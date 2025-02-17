<?php

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
        Route::get('/roles', [\App\Http\Controllers\Api\UserController::class, 'usersRoles']);
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

        Route::post('/assign', [\App\Http\Controllers\Api\RoleUserController::class, 'assign']);
        Route::delete('/assign/{uuid}', [\App\Http\Controllers\Api\RoleUserController::class, 'destroy']);

        Route::get('/', [\App\Http\Controllers\Api\RoleController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\RoleController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Api\RoleController::class, 'store']);
        Route::patch('/{uuid}', [\App\Http\Controllers\Api\RoleController::class, 'update']);
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\RoleController::class, 'destroy']);
    });

    Route::prefix('grades')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\GradeController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\GradeController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Api\GradeController::class, 'store']);
        Route::patch('/{uuid}', [\App\Http\Controllers\Api\GradeController::class, 'update']);
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\GradeController::class, 'destroy']);
    });

    Route::prefix('teachers')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\TeacherController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\TeacherController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Api\TeacherController::class, 'store']);
        Route::patch('/{uuid}', [\App\Http\Controllers\Api\TeacherController::class, 'update']);
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\TeacherController::class, 'destroy']);
    });

    Route::prefix('students')->group(function() {
        Route::get('/options', [\App\Http\Controllers\Api\StudentController::class, 'option']);
        Route::get('/', [\App\Http\Controllers\Api\StudentController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\StudentController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Api\StudentController::class, 'store']);
        Route::patch('/{uuid}', [\App\Http\Controllers\Api\StudentController::class, 'update']);
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\StudentController::class, 'destroy']);
    });

    Route::prefix('kelases')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\KelasController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\KelasController::class, 'show']);
        Route::get('/{uuid}/students', [\App\Http\Controllers\Api\KelasController::class, 'kelasStudent']);
        Route::post('/assign', [\App\Http\Controllers\Api\KelasController::class, 'assignStudent']);
        Route::post('/', [\App\Http\Controllers\Api\KelasController::class, 'store']);
        Route::patch('/{uuid}/activate', [\App\Http\Controllers\Api\KelasController::class, 'activate']);
        Route::patch('/{uuid}/stop', [\App\Http\Controllers\Api\KelasController::class, 'stop']);
        Route::patch('/{uuid}/reactivate', [\App\Http\Controllers\Api\KelasController::class, 'reactivate']);
        Route::patch('/{uuid}', [\App\Http\Controllers\Api\KelasController::class, 'update']);
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\KelasController::class, 'destroy']);
    });

    Route::prefix('kelases-students')->group(function() {
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\KelasController::class, 'destroyKelasStudent']);
    });

    Route::prefix('template-qurans')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\TemplateQuranController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\TemplateQuranController::class, 'show']);
    });

    Route::prefix('template-quran-juzes')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\TemplateQuranJuzController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\TemplateQuranJuzController::class, 'show']);
    });

    Route::prefix('template-quran-pages')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\TemplateQuranPageController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\TemplateQuranPageController::class, 'show']);
    });

    Route::prefix('reports')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\ReportController::class, 'index']);
        Route::get('/{uuid}', [\App\Http\Controllers\Api\ReportController::class, 'show']);
        Route::patch('/{uuid}/lock', [\App\Http\Controllers\Api\ReportController::class, 'lock']);
        Route::patch('/{uuid}/unlock', [\App\Http\Controllers\Api\ReportController::class, 'unlock']);
        Route::patch('/{uuid}', [\App\Http\Controllers\Api\ReportController::class, 'update']);
        Route::post('/', [\App\Http\Controllers\Api\ReportController::class, 'store']);
        Route::delete('/{uuid}', [\App\Http\Controllers\Api\ReportController::class, 'destroy']);
    });

    Route::prefix('achievements')->group(function() {
        Route::get('/', [\App\Http\Controllers\Api\SummaryController::class, 'index']);
    });
});
