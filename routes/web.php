<?php

Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'my'], function () {
        Route::get('/', 'My\MyLinksController@index');
    });

    Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
        Route::get('links', 'My\MyLinksController@index');
        Route::get('reports', 'Admin\AdminController@reports');
    });

    Route::delete('delete/link', 'My\MyLinksController@delete')->middleware(['admin']);
});

Auth::routes();
Route::get('activate/{token}', 'Auth\ActivationController@activate');

Route::get('report', 'ReportLinkController@report');
Route::post('report', 'ReportLinkController@postReport');

Route::get('email/unsubscribe', 'StaticPagesController@unsubscribe');

Route::post('shorten', 'ShortenLinkController@shorten')->middleware(['ajax', 'throttle:20,1']);

Route::get('csrf', function () {
    return response()->json(csrf_token());
})->middleware(['ajax']);

Route::get('/', 'HomeController@index');