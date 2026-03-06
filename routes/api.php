<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;

// ==========================================
// 🟢 PUBLIC ROUTES (No login required)
// ==========================================
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('product-cate/{id}', [ProductController::class, 'getProductByCate']);
Route::get('product-search', [ProductController::class, 'search']);


// ==========================================
// 🔴 SECURED ROUTES (Login required)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // User Profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('user/update', [AuthController::class, 'update']);

    // Carts
    Route::post('cart', [CartController::class, 'addToCart']);
    Route::get('viewCart', [CartController::class, 'viewCart']);
    Route::delete('remove-cart-item/{proId}', [CartController::class, 'removeFromCart']);
    Route::delete('cart/clear', [CartController::class, 'clearCart']);

    // Addresses
    Route::post('address', [AddressController::class, 'store']);
    Route::get('address', [AddressController::class, 'index']);
    Route::put('address/{id}', [AddressController::class, 'update']);
    Route::delete('address/{id}', [AddressController::class, 'destroy']);

    // 🟢 Orders & KHQR Slip Upload
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('order/checkout', [OrderController::class, 'checkout']);
    
    // This matches the endpoint called by your Flutter APIProvider
    Route::post('orders/checkout-with-slip', [OrderController::class, 'checkout']);
    
    Route::post('orders/{id}/pay', [OrderController::class, 'markAsPaid']);

    // Notifications
    Route::post('update-fcm-token', [NotificationController::class, 'updateFcmToken']);
    Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
    Route::post('/send-notification-topic', [NotificationController::class, 'sendToTopic']);

    // Admin Data Management (Filament fallback)
    Route::post('category', [CategoryController::class, 'store']);
    Route::put('category/{id}', [CategoryController::class, 'update']);
    Route::delete('category/{id}', [CategoryController::class, 'destroy']);
    Route::post('product', [ProductController::class, 'store']);
});