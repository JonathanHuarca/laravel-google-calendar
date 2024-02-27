<?php

use Illuminate\Support\Facades\Route;
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

// Managing Google accounts and webhooks.
Route::name('google.index')->get('google', 'App\Http\Controllers\GoogleAccountController@index');
Route::name('google.store')->get('google/oauth', 'App\Http\Controllers\GoogleAccountController@store');
Route::name('google.destroy')->delete('google/{googleAccount}', 'App\Http\Controllers\GoogleAccountController@destroy');
Route::name('google.webhook')->post('google/webhook', 'App\Http\Controllers\GoogleWebhookController');

// Viewing events.
Route::name('event.index')->get('event', 'App\Http\Controllers\EventController@index');
