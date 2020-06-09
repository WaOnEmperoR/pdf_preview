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


Route::get('/home', 'HomeController@index')->name('home');

Route::get('/pdf', 'PDFController@index');

Auth::routes();



Route::group(['middleware' => 'auth'], function () {
    
    Route::post('signPDF', 'PDFController@signPDF');

    // Route::get('/home', 'HomeController@index')->name('home');
    Route::resource('product','ProductController');
    Route::get('/product/report/statistic', 'ProductController@statistic')->name('product.statistic');
    Route::post('/product/report/api', 'ProductController@statisticApi')->name('product.stat-api');
});
