<?php

use Illuminate\Http\Request;

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


Route::post('/get-quote-fc', 'HomeController@getQuoteFC');
Route::post('/get-quote-manna', 'HomeController@getQuoteManna');
Route::post('/get-quote-priority', 'HomeController@getQuotePriority');
Route::post('/get-quote-convey', 'HomeController@getQuoteConvey');