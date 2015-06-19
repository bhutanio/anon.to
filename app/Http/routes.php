<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->post('shorten', [
    'as' => 'shorten', 'uses' => 'App\Http\Controllers\HomeController@shorten',
]);

$app->get('/{hash:[A-Za-z0-9]{6}}', [
    'as' => 'hash', 'uses' => 'App\Http\Controllers\HomeController@hash',
]);

$app->get('/', [
    'as' => 'home', 'uses' => 'App\Http\Controllers\HomeController@index',
]);
