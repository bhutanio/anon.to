<?php

Route::group(['middleware' => ['web']], function () {
    Route::get('csrf', function () {
        return csrf_token();
    })->middleware(['ajax']);;

    Route::post('shorten', 'ShortenLinkController@shorten')->middleware(['ajax', 'throttle:20,1']);
});

Route::get('/{key}', 'RedirectController@redirect');
Route::get('/', 'HomeController@index');