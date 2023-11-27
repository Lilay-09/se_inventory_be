<?php

use App\Http\Controllers\UM\UserAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login',[UserAuthentication::class,'intnLogin']);
Route::prefix("user")->group(function () {
    Route::post('/auth/save-user',[UserAuthentication::class,'saveUser']);
    Route::post('/profile',[UserAuthentication::class,'getUserProfile']);
});
