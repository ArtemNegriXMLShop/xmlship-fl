<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Auth')->prefix('auth')->group(static function () {
    // Put the routes here
    Route::post('/refresh-token', 'AuthController@refresh')->name('jwt.auth.refresh.token');
    Route::post('/logout', 'AuthController@logout')->name('jwt.auth.logout');
});
