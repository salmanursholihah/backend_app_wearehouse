

<?php

use App\Http\Controllers\Api\AboutUsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\SuperAdmin\RoleApprovalController;
use App\Http\Controllers\Api\ProfileController;


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

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
    Route::post('/request-admin', [App\Http\Controllers\Api\User\RoleRequestController::class, 'requestAdmin']);
    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
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

        ///APPROVE USER REQUEST TO BE ADMIN
        Route::get('/role-requests', [RoleApprovalController::class, 'index']);
        Route::post('/role-requests/{id}/approve', [RoleApprovalController::class, 'approve']);
        Route::post('/role-requests/{id}/reject', [RoleApprovalController::class, 'reject']);
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

        Route::get ('about-us', [AboutUsController::class, 'index']);

    });
});
