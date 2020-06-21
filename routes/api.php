<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [ 'as' => 'login', 'uses' => 'API\UserController@login']);
Route::post('register', [ 'as' => 'register', 'uses' => 'API\UserController@register']);

Route::get('unauthorized', 'API\UserController@unauthorized');
Route::group(['middleware' => ['CheckClientCredentials','auth:api']], function() {
    Route::post('details', 'API\UserController@details');
    Route::post('logout', 'API\UserController@logout');
});