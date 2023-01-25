<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Settings\GroupUsers')->prefix('settings/group-users')->group(static function () {
    Route::get('', 'GroupUsersController@index')->name('settings.group-users.list');
    Route::put('', 'GroupUsersController@store')->name('settings.group-users.store');
    Route::get('{groupuser_id}', 'GroupUsersController@show')->name('settings.group-users.show');
    Route::post('{groupuser_id}', 'GroupUsersController@update')->name('settings.group-users.update');
    Route::delete('{groupuser_id}', 'GroupUsersController@destroy')->name('settings.group-users.destroy');
});
