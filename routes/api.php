<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);
Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('user/update',[AuthController::class,'update'])->middleware('auth:sanctum');

//categories 

Route::get('categories',[CategoryController::class,'index']);
Route::post('category',[CategoryController::class,'store']);
Route::put('category/{id}',[CategoryController::class,'update']);
Route::delete('category/{id}',[CategoryController::class,'destroy']);



//products 
Route::post('product',[ProductController::class,'store']);
Route::get('products',[ProductController::class,'index']);
Route::get('product-cate/{id}',[ProductController::class,'getProductByCate']);
Route::get('product-search',[ProductController::class,'search']);

//carts 
Route::post('cart',[CartController::class,'addToCart'])->middleware('auth:sanctum');
Route::get('viewCart',[CartController::class,'viewCart'])->middleware('auth:sanctum');
Route::post('remove-cart-item/{proId}',[CartController::class,'removeFromCart'])->middleware('auth:sanctum');
Route::post('cart/clear',[CartController::class,'clearCart'])->middleware('auth:sanctum');

//addresses
Route::post('address',[AddressController::class,'store'])->middleware('auth:sanctum');
Route::put('address/{id}',[AddressController::class,'update'])->middleware('auth:sanctum');
Route::get('address',[AddressController::class,'index'])->middleware('auth:sanctum');
Route::delete('address/{id}',[AddressController::class,'destroy'])->middleware('auth:sanctum');

//orders 
Route::post('order',[OrderController::class,'store'])->middleware('auth:sanctum');
Route::get('orders',[OrderController::class,'index'])->middleware('auth:sanctum');
Route::post('order/checkout',[OrderController::class,'checkout'])->middleware('auth:sanctum');

Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
Route::post('/send-notification-topic', [NotificationController::class, 'sendToTopic']);

