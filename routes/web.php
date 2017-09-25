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
    Route::group(['prefix'=> 'gzh'], function(){
        Route::resource('config', 'WApi\Gzh\ConfigController');
        Route::resource('button', 'WApi\Gzh\ButtonController');
        Route::resource('media', 'WApi\Gzh\MediaController');
        Route::resource('user', 'WApi\Gzh\UserController');
        Route::get('user/wxinfo/{openid}', 'WApi\Gzh\UserController@wxinfo');
    });
//     Route::resource('gzh', 'WApi\GzhController');
});