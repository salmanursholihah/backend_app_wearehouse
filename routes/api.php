

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\UserApiController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SEMUA ROLE (USER, ADMIN, SUPER ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::get('/products', [ProductController::class, 'index']);

    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests', [RequestController::class, 'index']);

    Route::post('/chat', [ChatController::class, 'send']);
    Route::get('/chat/{room}', [ChatController::class, 'history']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN & SUPER ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,super_admin')->group(function () {

        // PRODUCT
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // REQUEST (GANTI process → approve / reject)
        Route::post('/requests/{id}/approve', [RequestController::class, 'approve']);
        Route::post('/requests/{id}/reject', [RequestController::class, 'reject']);
    });

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:super_admin')->group(function () {

        Route::get('/users', [UserApiController::class, 'index']);
        Route::post('/users', [UserApiController::class, 'store']);
        Route::delete('/users/{id}', [UserApiController::class, 'destroy']);
    });
});
