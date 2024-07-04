<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChienDich_Controller;
use App\Http\Controllers\CongDong_Controller;
use App\Http\Controllers\Quy_Controller;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('addchien', [ChienDich_Controller::class, 'addChienDich']);
Route::get('listchien', [ChienDich_Controller::class, 'listChienDich']);
Route::delete('deletechien/{id}', [ChienDich_Controller::class, 'deleteChienDich']);
Route::post('addcongdong', [CongDong_Controller::class, 'addCongDong']);
Route::get('listcongdong', [CongDong_Controller::class, 'listCongDong']);
Route::delete('deletecongdong/{id}', [CongDong_Controller::class, 'deleteCongDong']);
Route::post('addquy', [Quy_Controller::class, 'addQuy']);
Route::get('listquy', [Quy_Controller::class, 'listQuy']);
Route::delete('deletequy/{id}', [Quy_Controller::class, 'deleteQuy']);
