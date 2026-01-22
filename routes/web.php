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
    ChatWebController
};

/*
|--------------------------------------------------------------------------
| ROOT
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
   Route::get('/users', [UserWebController::class, 'index'])
        ->name('users.index');

    Route::post('/users/{user}/toggle', [UserWebController::class, 'toggle'])
        ->name('users.toggle');

    Route::post('/users/{user}/role', [UserWebController::class, 'updateRole'])
        ->name('users.role');



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
    Route::get('/chat', [ChatWebController::class, 'index'])
        ->name('chat.index');

    Route::get('/chat/{roomId}', [ChatWebController::class, 'show'])
        ->name('chat.show');
    Route::post('/chat/{roomId}/send', [ChatWebController::class, 'send'])
        ->name('chat.send');
});
