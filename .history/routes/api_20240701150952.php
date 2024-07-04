<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('addproduct', [ProductController::class, 'addProduct']);
Route::get('list', [ProductController::class, 'list']);
Route::delete('delete/{id}', [ProductController::class, 'delete']);