<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JWTController;

Route::get('/',function(){
    return response()->json([
        'success' => true,
        'message' => 'Api is working',
    ]); 
});

// Auth api
Route::controller(AuthController::class)->prefix('/auth')->group(function(){
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/register',[AuthController::class,'register']);
    Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
});

Route::get('/profile',[AuthController::class,'show'])->middleware('auth:sanctum');

//JWT Auth
Route::post('/jwt-register', [JWTController::class, 'register']);
Route::post('/jwt-login', [JWTController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/jwt-user', [JWTController::class, 'user']);
    Route::post('/jwt-logout', [JWTController::class, 'logout']);
    Route::post('/jwt-refresh', [JWTController::class, 'refresh']);
});
