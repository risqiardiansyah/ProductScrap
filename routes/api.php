<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'apps', 'middleware' => 'api'], function () {
        Route::get('produk', [UserController::class, 'getProduk']);
    });
});
