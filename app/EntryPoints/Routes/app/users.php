<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->namespace('Users')->prefix('users')->group(static function () {
    Route::get('', 'UsersController@index')->name('users.list');
    Route::post('', 'UsersController@create')->name('users.create');
    Route::get('{user_id}', 'UsersController@show')->name('users.show');
    Route::post('{user_id}', 'UsersController@update')->name('users.update');
    Route::delete('{user_id}', 'UsersController@destroy')->name('users.destroy');
});
