<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\LaporanGudangController;
use App\Http\Controllers\Web\ChatWebController;

Route::get('/login', [AuthWebController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

// Route::middleware(['auth'])->group(function () {

//     Route::get('/', [DashboardWebController::class, 'index']);
//     Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');

//     // REQUEST
//     Route::prefix('report')
//         ->name('pages.report.')
//         ->group(function () {

//             Route::get('/stock', [LaporanGudangController::class, 'stock'])
//                 ->name('stock');

//             Route::get('/request', [LaporanGudangController::class, 'request'])
//                 ->name('request');

//         });

//     //REQUEST
//     Route::resource('request', \App\Http\Controllers\Web\RequestWebController::class, ['as' => 'pages']);

//     //PRODUCTS
//     Route::resource('product', \App\Http\Controllers\Web\ProductWebController::class, ['as' => 'pages']);

//     //REPORT
//     Route::prefix('report')->name('pages.report.')->group(function () {

//     Route::get('/stock', function () {
//         return view('pages.report.stock');
//     })->name('stock');

//     Route::get('/request', function () {
//         return view('pages.report.request');
//     })->name('request');

// });


//     // CHAT
//     Route::get('/chat', [ChatWebController::class, 'index'])->name('pages.chat.index');
//     Route::get('/chat/{room}', [ChatWebController::class, 'room'])->name('pages.chat.room');
//     Route::post('/chat/send', [ChatWebController::class, 'send'])->name('pages.chat.send');

//     //USER
//     Route::resource('users', \App\Http\Controllers\Web\UserWebController::class, ['as' => 'pages']);
// });


Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardWebController::class,'index']);

    Route::get('/users', [UserWebController::class,'index']);
    Route::put('/users/{user}/role', [UserWebController::class,'updateRole']);
    Route::put('/users/{user}/toggle', [UserWebController::class,'toggleStatus']);

    Route::get('/admins', [AdminManagementController::class,'index']);
    Route::post('/admins', [AdminManagementController::class,'store']);
    Route::put('/admins/{user}/deactivate', [AdminManagementController::class,'deactivate']);

    Route::resource('products', ProductWebController::class)->except(['show','create','edit']);

    Route::get('/reports/stock', [WarehouseReportController::class,'stock']);
    Route::get('/reports/request', [WarehouseReportController::class,'request']);

    Route::get('/logs/activity', [ActivityLogWebController::class,'index']);
});
