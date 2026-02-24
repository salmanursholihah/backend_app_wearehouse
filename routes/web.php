

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
    RoleApprovalWebController,
    AboutUsWebController,
    ProductApprovalWebController
};

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('login'));


/*
|--------------------------------------------------------------------------
| AUTH (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthWebController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login'])->name('login.process');

    Route::get('/register', [AuthWebController::class, 'registerForm'])->name('register');
});


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthWebController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardWebController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | USER ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:user')->group(function () {

        // Request barang (user only)
        Route::get('/request', [RequestWebController::class, 'index'])
            ->name('request.index');
    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (admin + super_admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,super_admin')->group(function () {

        // Product Management
        Route::resource('product', ProductWebController::class);

        Route::post(
            '/product/{product}/approve',
            [ProductWebController::class, 'approve']
        )->name('product.approve');

        Route::post(
            '/product/{product}/reject',
            [ProductWebController::class, 'reject']
        )->name('product.reject');

        // Approve Request Barang
        Route::post(
            '/request/{id}/approve',
            [RequestWebController::class, 'approve']
        )->name('request.approve');

        Route::post(
            '/request/{id}/reject',
            [RequestWebController::class, 'reject']
        )->name('request.reject');

        // Activity Log
        Route::get(
            '/activity-log',
            [ActivityLogWebController::class, 'index']
        )->name('logs.activity');

        // Admin page
        Route::get(
            '/admin',
            [AdminWebController::class, 'index']
        )->name('admin.index');
    });


    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:super_admin')->group(function () {

        // Manage Users
        Route::resource('users', UserWebController::class);

        Route::post(
            '/users/{user}/toggle',
            [UserWebController::class, 'toggle']
        )->name('users.toggle');

        Route::post(
            '/users/{user}/role',
            [UserWebController::class, 'updateRole']
        )->name('users.role');

        // Role Approval
        Route::get(
            '/role-requests',
            [RoleApprovalWebController::class, 'index']
        )
            ->name('super_admin.role_requests');

        Route::post(
            '/role-requests/{id}/approve',
            [RoleApprovalWebController::class, 'approve']
        )
            ->name('super_admin.role_requests.approve');

        Route::post(
            '/role-requests/{id}/reject',
            [RoleApprovalWebController::class, 'reject']
        )
            ->name('super_admin.role_requests.reject');

        // Product Request Approval
        Route::get(
            '/product-requests',
            [ProductApprovalWebController::class, 'index']
        )
            ->name('superadmin.product.requests');

        Route::post(
            '/product-requests/{id}/approve',
            [ProductApprovalWebController::class, 'approve']
        )
            ->name('superadmin.product.approve');

        Route::post(
            '/product-requests/{id}/reject',
            [ProductApprovalWebController::class, 'reject']
        )
            ->name('superadmin.product.reject');
    });


    /*
    |--------------------------------------------------------------------------
    | SHARED (SEMUA ROLE LOGIN)
    |--------------------------------------------------------------------------
    */

    // About Us
    Route::get('/about-us', [AboutUsWebController::class, 'index']);
    Route::post('/about-us', [AboutUsWebController::class, 'store']);

    // Chat
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatWebController::class, 'index'])->name('index');
        Route::get('/start/{user}', [ChatWebController::class, 'start'])->name('start');
        Route::get('/{room}', [ChatWebController::class, 'room'])->name('room');
        Route::post('/send', [ChatWebController::class, 'send'])->name('send');
    });
});
