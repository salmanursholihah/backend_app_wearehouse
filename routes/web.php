<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\{
    AuthWebController,
    DashboardWebController,
    ProductWebController,
    RequestWebController,
    UserWebController,
    AdminWebController,
    ActivityLogWebController,
    ChatWebController,
    RoleApprovalController,
    RoleApprovalWebController
};

/*
|--------------------------------------------------------------------------
| ROOTeb

|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| AUTH (GUEST ONLY)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'loginForm'])
        ->name('login');

    Route::post('/login', [AuthWebController::class, 'login'])
        ->name('login.process');

    Route::get('/register', [AuthWebController::class, 'registerForm'])
        ->name('register');
});


/*
|--------------------------------------------------------------------------
| LOGOUT (AUTH ONLY)
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthWebController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| PROTECTED (AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardWebController::class, 'index'])
        ->name('dashboard');

    /*
    | PRODUCTS
    */
    Route::resource('product', ProductWebController::class);

    /*
    | REQUEST BARANG
    */
    Route::get('/request', [RequestWebController::class, 'index'])
        ->name('request.index');

    Route::post('/request/{id}/approve', [RequestWebController::class, 'approve'])
        ->name('request.approve');

    Route::post('/request/{id}/reject', [RequestWebController::class, 'reject'])
        ->name('request.reject');

    /*
    | USERS
    */
    Route::resource('users', UserWebController::class);

    Route::post('/users/{user}/toggle', [UserWebController::class, 'toggle'])
        ->name('users.toggle');

    Route::post('/users/{user}/role', [UserWebController::class, 'updateRole'])
        ->name('users.role');




    /*
    | REQUEST ADMIN ROLE
    */

    Route::get('/role-requests', [RoleApprovalWebController::class, 'index'])
        ->name('super_admin.role_requests');

    Route::post('/role-requests/{id}/approve', [RoleApprovalWebController::class, 'approve'])
        ->name('super_admin.role_requests.approve');

    Route::post('/role-requests/{id}/reject', [RoleApprovalWebController::class, 'reject'])
        ->name('super_admin.role_requests.reject');


    /*
    | ADMIN
    */
    Route::get('/admin', [AdminWebController::class, 'index'])
        ->name('admin.index');

    /*
    | ACTIVITY LOG
    */
    Route::get('/activity-log', [ActivityLogWebController::class, 'index'])
        ->name('logs.activity');

    /*
    | CHAT
    */
Route::prefix('chat')->name('pages.chat.')->middleware('auth')->group(function () {
    Route::get('/', [ChatWebController::class, 'index'])->name('index');
    Route::get('/start/{user}', [ChatWebController::class, 'start'])->name('start');
    Route::get('/{room}', [ChatWebController::class, 'room'])->name('room');
    Route::post('/send', [ChatWebController::class, 'send'])->name('send');

    });
});
