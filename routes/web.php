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

Route::group(['prefix'=>'wx'], function(){
    // 微信验证
    Route::get('index', 'Ext\WxController@index');
    Route::post('index', 'Ext\WxController@index');
});

Route::group(['prefix'=>'wapi'], function(){
    Route::resource('gzh/config', 'WApi\Gzh\ConfigController');
    Route::resource('gzh/button', 'WApi\Gzh\ButtonController');
    Route::resource('gzh/media', 'WApi\Gzh\MediaController');
    Route::resource('gzh/user', 'WApi\Gzh\UserController');
    Route::resource('gzh', 'WApi\GzhController');
});