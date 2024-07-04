<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChienDich_Controller;
use App\Http\Controllers\CongDongController;
use App\Http\Controllers\Quy_Controller;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/getPosts', [UserController::class, 'getPosts']);
Route::post('user_information', [UserController::class, 'getUserInformation']);
Route::post('/updateFollowStatus', [FollowController::class, 'updateFollowStatus']);
Route::post('/updateUserInfo', [UserController::class, 'updateUserInfo']);
Route::post('/checkLikeStatus', [PostController::class, 'checkLikeStatus']);
Route::post('/getComment', [PostController::class, 'getComment']);
Route::post('/toogleLike', [PostController::class, 'toogleLike']);
Route::post('/deletePost', [PostController::class, 'deletePost']);
Route::post('/getUnFollowedUsers', [FollowController::class, 'getUnFollowedUsers']);
Route::post('/updateFollower', [FollowController::class, 'updateFollower']);
Route::post('/getComments', [PostController::class, 'getComments']);
Route::post('/addComment', [PostController::class, 'addComment']);
Route::post('/getSocialPosts', [CongDongController::class, 'getSocialPosts']);

Route::post('createCampaign', [ChienDich_Controller::class, 'createCampaign']);
Route::get('listCampaignIng/{province}', [ChienDich_Controller::class, 'getCampaignsIng']);
Route::get('listCampaignWill/{province}', [ChienDich_Controller::class, 'getCampaignsWill']);
Route::get('listCampaignEd/{province}', [ChienDich_Controller::class, 'getCampaignsEd']);
Route::get('getCampaign/{id}', [ChienDich_Controller::class, 'getCampaignDetail']);
