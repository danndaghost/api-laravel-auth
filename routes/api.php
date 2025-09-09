<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;

// Rutas públicas
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);  // Cambiado a AuthController

// Rutas de reset de password (públicas)
Route::post('/auth/password/reset-request', [AuthController::class, 'requestPasswordReset']);
Route::post('/auth/password/reset-confirm', [AuthController::class, 'confirmPasswordReset']);

// Rutas protegidas
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Solo admin
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions']);

        Route::apiResource('permissions', PermissionController::class);

        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/roles', [UserController::class, 'assignRoles']);
        Route::post('users/{user}/permissions', [UserController::class, 'assignPermissions']);
    });

    // Ejemplos de permisos
    Route::get('/admin/dashboard', fn() => response()->json(['msg' => 'Panel de admin']))->middleware('role:admin');
    Route::get('/reports/view', fn() => response()->json(['msg' => 'Reportes']))->middleware('permission:view reports');
    Route::get('/articles/edit', fn() => response()->json(['msg' => 'Editor de artículos']))->middleware('permission:edit articles');
});