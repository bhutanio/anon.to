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

$app->get('/{hash:[A-Za-z0-9]{6}}', [
    'as' => 'home', 'uses' => 'App\Http\Controllers\HomeController@index'
]);

$app->get('/', [
    'as' => 'home', 'uses' => 'App\Http\Controllers\HomeController@index'
]);
