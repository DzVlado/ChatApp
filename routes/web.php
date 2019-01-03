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

//Route::get('test-broadcast', function(){
//    broadcast(new \App\Events\ExampleEvent);
//});

//Route::get('test-private', function(){
//    broadcast(new \App\Events\PrivateMessageSent(auth()->user()));
//});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::group(['middleware' => 'auth'], function(){
    Route::get('messages', 'MessageController@index');
    Route::post('messages', 'MessageController@store');

    Route::get('users', 'UserController@index');
});