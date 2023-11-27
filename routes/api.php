<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\Location\CityController;
use App\Http\Controllers\Location\CountryController;
use App\Http\Controllers\Location\DistrictController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('country')->group(function(){
    Route::post('/save',[CountryController::class,'save']);
    Route::post('/list',[CountryController::class,'getListPaginate']);
    Route::post('/details',[CountryController::class,'getDetails']);
    Route::post('/delete',[CountryController::class,'delete']);
});

Route::prefix('city')->group(function(){
    Route::post('/save',[CityController::class,'save']);
    Route::post('/list',[CityController::class,'getListPaginate']);
    Route::post('/details',[CityController::class,'getDetails']);
    Route::post('/delete',[CityController::class,'delete']);
});

Route::prefix('district')->group(function(){
    Route::post('/save',[DistrictController::class,'save']);
    Route::post('/list',[DistrictController::class,'getListPaginate']);
    Route::post('/details',[DistrictController::class,'getDetails']);
    Route::post('/delete',[DistrictController::class,'delete']);
});

Route::prefix('brand')->group(function(){
    Route::post('/save',[BrandController::class,'save']);
    Route::post('/list',[BrandController::class,'getListPaginate']);
    Route::post('/details',[BrandController::class,'getDetails']);
    Route::post('/delete',[BrandController::class,'delete']);
});

Route::prefix('category')->group(function(){
    Route::post('/save',[CategoryController::class,'save']);
    Route::post('/list',[CategoryController::class,'getListPaginate']);
    Route::post('/details',[CategoryController::class,'getDetails']);
    Route::post('/delete',[CategoryController::class,'delete']);
});

Route::prefix('group')->group(function(){
    Route::post('/save',[GroupController::class,'save']);
    Route::post('/list',[GroupController::class,'getListPaginate']);
    Route::post('/details',[GroupController::class,'getDetails']);
    Route::post('/delete',[GroupController::class,'delete']);
});

