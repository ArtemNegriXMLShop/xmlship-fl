<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Users')->prefix('users')->group(static function () {
    Route::get('', 'UsersController@index')->name('users.list');
    Route::put('', 'UsersController@store')->name('users.store');
    Route::get('{user_id}', 'UsersController@show')->name('users.show');
    Route::post('{user_id}', 'UsersController@update')->name('users.update');
    Route::delete('{user_id}', 'UsersController@destroy')->name('users.destroy');
});
