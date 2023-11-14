<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// routes/web.php

// Route::resource('stores', StoreController::class);
// Route::resource('products', ProductController::class);



// Route::post('/register', [UserController::class, 'register']);

Route::post('/login', [UserController::class, 'login']);



Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
});


Route::prefix('stores')->group(function () {
    Route::get('{storeId}/products', [ProductController::class, 'index']);
    Route::get('{storeId}/products/{productId}', [ProductController::class, 'show']);
    Route::post('{storeId}/products', [ProductController::class, 'store']);
    Route::put('{storeId}/products/{productId}', [ProductController::class, 'update']);
    Route::delete('{storeId}/products/{productId}', [ProductController::class, 'destroy']);
});

Route::prefix('stores')->group(function () {
    Route::get('/', [StoreController::class, 'index']);
    Route::get('{id}', [StoreController::class, 'show']);
    Route::post('/', [StoreController::class, 'store']);
    Route::put('{id}', [StoreController::class, 'update']);
    Route::delete('{id}', [StoreController::class, 'destroy']);
    Route::get('{id}/Inventory', [StoreController::class, 'Inventory']);
});


Route::group(['middleware' => 'App\Http\Middleware\JwtMiddleware'], function () {
    // Route::get('user/', [GatewayController::class, 'getUsers']);0002
});
