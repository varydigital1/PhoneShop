<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Categorycontroller;
use App\Http\Controllers\Productcontroller;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('categories', Categorycontroller::class);
Route::apiResource('products', Productcontroller::class);
