<?php

Route::get('/{key}', 'RedirectController@redirect')->where('key', '[A-Za-z0-9]{6}');

Route::get('about/terms', 'StaticPagesController@terms');
Route::get('about/privacy-policy', 'StaticPagesController@privacy');
Route::get('about', 'StaticPagesController@about');

Route::get('/', 'HomeController@index');