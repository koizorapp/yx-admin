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
   return view('index');
//    return view('welcome');
});



//中心
Route::get('center/list','CenterController@getCenterList');
Route::post('center/add','CenterController@addCenter');
Route::post('center/edit','CenterController@editCenter');
Route::get('center/del','CenterController@delCenter');

//人员
Route::get('personnel/list','PersonnelController@getPersonnelList');
Route::post('personnel/add','PersonnelController@addPersonnel');
Route::post('personnel/edit','PersonnelController@editPersonnel');
Route::get('personnel/del','PersonnelController@delPersonnel');

//类别
Route::get('category/list','CategoryController@getCategoryList');
Route::post('category/add','CategoryController@addCategory');
Route::post('category/edit','CategoryController@editCategory');
Route::get('category/del','CategoryController@delCategory');

//标签
Route::get('label/list','LabelController@getLabelList');
Route::post('label/add','LabelController@addLabel');
Route::post('label/edit','LabelController@editLabel');
Route::get('label/del','LabelController@delLabel');





