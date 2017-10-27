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
    $center = new \App\Models\Center();
    $center->name = '医学运动中心';
    $center->code = 'YD';
    $center->save();

    return \App\Models\Center::find($center->id);
//    return view('welcome');
});


//中心
Route::get('center/list','CenterController@getCenterList');
Route::post('center/add','CenterController@addCenter');
Route::post('center/edit','CenterController@editCenter');
Route::get('center/del','CenterController@delCenter');
