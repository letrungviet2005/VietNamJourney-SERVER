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

Route::post('createCampaign', [ChienDich_Controller::class, 'createCampaign']);
Route::get('listCampaignIng/{province}', [ChienDich_Controller::class, 'getCampaignsIng']);
Route::get('listCampaignWill/{province}', [ChienDich_Controller::class, 'getCampaignsWill']);
Route::get('listCampaignEd/{province}', [ChienDich_Controller::class, 'getCampaignsEd']);
Route::get('getCampaign/{province}', [ChienDich_Controller::class, 'getCampaignsEd']);