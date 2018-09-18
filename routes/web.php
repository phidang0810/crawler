<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/', 'HomeController@quote');
Route::get('/load-modal', function(){
    return view('_modal');
})->name('loadModal');

Route::post('/get-quote', 'HomeController@getQuote');

Route::post('/get-quote-fc', 'HomeController@getQuoteFC')->name('get-quote-fc');
Route::post('/get-quote-manna', 'HomeController@getQuoteManna')->name('get-quote-manna');
Route::post('/get-quote-convey', 'HomeController@getQuoteConvey')->name('get-quote-convey');
Route::post('/get-quote-priority', 'HomeController@getQuotePriority')->name('get-quote-priority');
Route::post('/export', 'HomeController@export')->name('export');

