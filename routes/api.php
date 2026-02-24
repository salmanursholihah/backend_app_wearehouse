<?php

// use App\Http\Controllers\Api\AboutUsController;
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\ProductController;
// use App\Http\Controllers\Api\ProfileController;
// use App\Http\Controllers\Api\SuperAdmin\ProductRequestApprovalController;
// use App\Http\Controllers\Api\SuperAdmin\RoleApprovalController;
// use App\Http\Controllers\Api\User\ProductRequestController;
// use App\Http\Controllers\Api\User\RoleRequestController;
// use Illuminate\Support\Facades\Route;

// /*
// |--------------------------------------------------------------------------
// | AUTH
// |--------------------------------------------------------------------------
// */
// Route::prefix('auth')->group(function () {
//     Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');

//     Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
// });

// /*
// |--------------------------------------------------------------------------
// | AUTHENTICATED ROUTES
// |--------------------------------------------------------------------------
// */

// Route::middleware('auth:sanctum')->group(function () {
//     /*
//     |--------------------------------------------------------------------------
//     | AUTH ACTIONS
//     |--------------------------------------------------------------------------
//     */

//     Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

//     Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');

//     Route::put('/profile', [ProfileController::class, 'updateProfile']);

//     Route::post('/change-password', [ProfileController::class, 'changePassword'])
//         ->middleware('auth:sanctum');

//     Route::get('/about', [AboutUsController::class, 'index']);




//     /*
//     |--------------------------------------------------------------------------
//     | notification route
//     |--------------------------------------------------------------------------
//     */
//     Route::middleware('auth:sanctum')->get('/notifications', function () {
//         return response()->json([
//             'success' => true,
//             'data' => auth()->user()->notifications
//         ]);
//     });


//     /*
//     |--------------------------------------------------------------------------
//     | USER ROUTES
//     |--------------------------------------------------------------------------
//     */

//     Route::prefix('user')
//         ->middleware('role:user')
//         ->group(function () {
//             Route::get('/dashboard', function () {
//                 return response()->json([
//                     'success' => true,
//                     'message' => 'Welcome User',
//                 ]);
//             });

//             //product
//             Route::get('/products', [ProductController::class, 'index']);
//             Route::get('/products/{id}', [ProductController::class, 'show']);

//             //product request

//             Route::post('/product-requests', [ProductRequestController::class, 'store']);
//             Route::get('/product-requests', [ProductRequestController::class, 'index']);
//             Route::get('/product-requests/{id}', [ProductRequestController::class, 'show']);

//             //user_request_to_Admin
//             Route::post('/role-requests', [RoleRequestController::class, 'store']);
//             Route::get('/my-role-request', [RoleRequestController::class, 'myRequest']);
//         });

//     /*
//     |--------------------------------------------------------------------------
//     | ADMIN ROUTES
//     |--------------------------------------------------------------------------
//     */

//     Route::prefix('admin')
//         ->middleware('role:admin,super_admin')
//         ->group(function () {
//             Route::get('/dashboard', function () {
//                 return response()->json([
//                     'success' => true,
//                     'message' => 'Welcome Admin',
//                 ]);
//             });
//             //admin approve request
//             Route::get('/admin/product-requests', [ProductRequestApprovalController::class, 'index']);

//             Route::post('/admin/product-requests/{id}/approve', [ProductRequestApprovalController::class, 'approve']);

//             Route::post('/admin/product-requests/{id}/reject', [ProductRequestApprovalController::class, 'reject']);

//             //super admin approve/reject role request
//             Route::get('/superadmin/role-requests', [RoleApprovalController::class, 'index']);

//             Route::post('/superadmin/role-requests/{id}/approve', [RoleApprovalController::class, 'approve']);

//             Route::post('/superadmin/role-requests/{id}/reject', [RoleApprovalController::class, 'reject']);
//         });
// });


///code 2//

use Illuminate\Support\Facades\Route;

// AUTH
use App\Http\Controllers\Api\User\UserAuthController;
use App\Http\Controllers\Api\Auth\AdminAuthController;

use App\Http\Controllers\Api\Admin\AdminAboutController;

/*
|--------------------------------------------------------------------------
| AUTH - USER
|--------------------------------------------------------------------------
*/
Route::prefix('auth/user')->group(function () {
    // Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [UserAuthController::class, 'me']);
        Route::post('/logout', [UserAuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| AUTH - ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('auth/admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        // bikin admin baru hanya super_admin
        Route::post('/register', [AdminAuthController::class, 'register'])
            ->middleware('role:super_admin');
    });
});


/*
|--------------------------------------------------------------------------
| PUBLIC (tanpa login) - dari about_us (opsional)
|--------------------------------------------------------------------------
*/
Route::get('/about', [AdminAboutController::class, 'publicIndex']); // user/admin bisa baca tanpa login (opsional)


/*
|--------------------------------------------------------------------------
| USER APP (Warehouse User)
|--------------------------------------------------------------------------
*/
Route::prefix('user')
    ->middleware(['auth:sanctum', 'role:user'])
    ->group(function () {

    // USERS table (profil user sendiri)
    // Route::get('/profile', [UserProfileController::class, 'show']);
    // Route::put('/profile', [UserProfileController::class, 'update']);
    // Route::post('/profile/image', [UserProfileController::class, 'updateImage']); // kalau pakai upload

    // // PRODUCTS + PRODUCT_IMAGES (user hanya lihat yang approved)
    // Route::get('/products', [UserProductController::class, 'index']);          // list produk approved
    // Route::get('/products/{id}', [UserProductController::class, 'show']);      // detail
    // Route::get('/products/{id}/images', [UserProductController::class, 'images']);

    // // REQUESTS + REQUEST_ITEMS (user membuat permintaan barang)
    // Route::post('/requests', [UserRequestController::class, 'store']);         // create request + items
    // Route::get('/requests', [UserRequestController::class, 'index']);          // list request milik user
    // Route::get('/requests/{id}', [UserRequestController::class, 'show']);      // detail + items
    // Route::put('/requests/{id}/cancel', [UserRequestController::class, 'cancel']); // batalkan (jika masih pending)
    // Route::put('/requests/{id}/confirm-taken', [UserRequestController::class, 'confirmTaken']); // status -> taken (opsional, tergantung flow kamu)

    // // PRODUCT_REQUESTS (user request barang untuk maintenance/distributor)
    // Route::post('/product-requests', [UserProductRequestController::class, 'store']); // create + upload file_path
    // Route::get('/product-requests', [UserProductRequestController::class, 'index']);
    // Route::get('/product-requests/{id}', [UserProductRequestController::class, 'show']);
    // Route::put('/product-requests/{id}/cancel', [UserProductRequestController::class, 'cancel']); // kalau masih pending

    // // ROLE_REQUESTS (user minta jadi admin)
    // Route::post('/role-requests', [UserRoleRequestController::class, 'store']); // request role admin
    // Route::get('/role-requests', [UserRoleRequestController::class, 'index']);  // history milik user
    // Route::get('/role-requests/{id}', [UserRoleRequestController::class, 'show']);

    // // CHAT (chat_rooms, chat_participants, chat_messages) - dipakai user
    // Route::get('/chat/rooms', [ChatController::class, 'rooms']);
    // Route::post('/chat/rooms', [ChatController::class, 'createRoom']);                 // personal/group
    // Route::get('/chat/rooms/{roomId}/messages', [ChatController::class, 'messages']);
    // Route::post('/chat/rooms/{roomId}/messages', [ChatController::class, 'sendMessage']);
    // Route::post('/chat/rooms/{roomId}/participants', [ChatController::class, 'addParticipant']); // group only
    // Route::delete('/chat/rooms/{roomId}/participants/{userId}', [ChatController::class, 'removeParticipant']);

    // // ACTIVITY LOGS (opsional: user lihat log sendiri)
    // Route::get('/activity-logs', [AdminActivityLogController::class, 'myLogs']); // atau bikin UserActivityLogController
});


/*
|--------------------------------------------------------------------------
| ADMIN APP (Warehouse Admin)
|--------------------------------------------------------------------------
*/
// Route::prefix('admin')
//     ->middleware(['auth:sanctum', 'role:admin,super_admin'])
//     ->group(function () {

    // USERS table (manajemen user)
    // Route::get('/users', [AdminUserController::class, 'index']);
    // Route::get('/users/{id}', [AdminUserController::class, 'show']);
    // Route::put('/users/{id}', [AdminUserController::class, 'update']);                 // update name/phone/address/is_active/image
    // Route::put('/users/{id}/activate', [AdminUserController::class, 'activate']);
    // Route::put('/users/{id}/deactivate', [AdminUserController::class, 'deactivate']);

    // set role (super_admin only)
    // Route::put('/users/{id}/set-role', [AdminUserController::class, 'setRole'])
    //     ->middleware('role:super_admin');

    // PRODUCTS (CRUD)
    // Route::get('/products', [AdminProductController::class, 'index']);                 // list semua (pending/approved/rejected)
    // Route::post('/products', [AdminProductController::class, 'store']);                // create product
    // Route::get('/products/{id}', [AdminProductController::class, 'show']);
    // Route::put('/products/{id}', [AdminProductController::class, 'update']);
    // Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);

    // product approval (status + approved_by)
    // Route::put('/products/{id}/approve', [AdminProductController::class, 'approve']);
    // Route::put('/products/{id}/reject', [AdminProductController::class, 'reject']);

    // PRODUCT_IMAGES
    // Route::get('/products/{id}/images', [AdminProductController::class, 'images']);
    // Route::post('/products/{id}/images', [AdminProductController::class, 'addImage']);
    // Route::delete('/product-images/{imageId}', [AdminProductController::class, 'deleteImage']);

    // REQUESTS (admin proses request user)
    // Route::get('/requests', [AdminRequestController::class, 'index']);
    // Route::get('/requests/{id}', [AdminRequestController::class, 'show']);
    // Route::put('/requests/{id}/approve', [AdminRequestController::class, 'approve']); // status approved, processed_by
    // Route::put('/requests/{id}/reject', [AdminRequestController::class, 'reject']);   // status rejected, processed_by
    // Route::put('/requests/{id}/taken', [AdminRequestController::class, 'markTaken']); // status taken (setelah barang diserahkan)

    // STOCK_LOGS (dan update stok product)
    // Route::get('/stock-logs', [AdminStockController::class, 'logs']);                  // list log
    // Route::get('/stock-logs/{id}', [AdminStockController::class, 'show']);

    // manual stock in/out (buat kasus selain request)
    // Route::post('/stock/in', [AdminStockController::class, 'stockIn']);                // type=in
    // Route::post('/stock/out', [AdminStockController::class, 'stockOut']);              // type=out

    // PRODUCT_REQUESTS (approve/reject permintaan)
    // Route::get('/product-requests', [AdminProductRequestController::class, 'index']);
    // Route::get('/product-requests/{id}', [AdminProductRequestController::class, 'show']);
    // Route::put('/product-requests/{id}/approve', [AdminProductRequestController::class, 'approve']);
    // Route::put('/product-requests/{id}/reject', [AdminProductRequestController::class, 'reject']);

    // ROLE_REQUESTS (approve/reject user minta jadi admin)
    // Route::get('/role-requests', [AdminRoleRequestController::class, 'index']);
    // Route::get('/role-requests/{id}', [AdminRoleRequestController::class, 'show']);
    // Route::put('/role-requests/{id}/approve', [AdminRoleRequestController::class, 'approve'])
    //     ->middleware('role:super_admin'); // rekomendasi: hanya super_admin boleh approve admin
    // Route::put('/role-requests/{id}/reject', [AdminRoleRequestController::class, 'reject'])
    //     ->middleware('role:super_admin');

    // ABOUT_US (CMS)
    // Route::get('/about', [AdminAboutController::class, 'index']);
    // Route::post('/about', [AdminAboutController::class, 'store']);
    // Route::get('/about/{id}', [AdminAboutController::class, 'show']);
    // Route::put('/about/{id}', [AdminAboutController::class, 'update']);
    // Route::delete('/about/{id}', [AdminAboutController::class, 'destroy']);

    // CHAT (admin juga butuh)
    // Route::get('/chat/rooms', [ChatController::class, 'rooms']);
    // Route::post('/chat/rooms', [ChatController::class, 'createRoom']);
    // Route::get('/chat/rooms/{roomId}/messages', [ChatController::class, 'messages']);
    // Route::post('/chat/rooms/{roomId}/messages', [ChatController::class, 'sendMessage']);
    // Route::post('/chat/rooms/{roomId}/participants', [ChatController::class, 'addParticipant']);
    // Route::delete('/chat/rooms/{roomId}/participants/{userId}', [ChatController::class, 'removeParticipant']);

    // ACTIVITY_LOGS (admin lihat semua)
    // Route::get('/activity-logs', [AdminActivityLogController::class, 'index']);
    // Route::get('/activity-logs/{id}', [AdminActivityLogController::class, 'show']);
// });
