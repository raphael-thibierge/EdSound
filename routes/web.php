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

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/privacy-policy', 'HomeController@privacyPolicy')->name('privacy.policy ');

// botman
Route::match(['get', 'post'], '/botman', 'BotManController@handle')->middleware('botman');


Route::get('/spotify/login/{user}', 'SpotifyController@login')->name('spotify.login');
Route::match(['get', 'post'], '/spotify/callback', 'SpotifyController@callback')->name('spotify.callback');


Route::match(['get', 'post'], '/botman/authorize', 'MessengerAccountLinkingController@showMessengerLoginForm')
    ->middleware('botman')
    ->name('botman.authorize');

Route::post('/botman/authorize', 'MessengerAccountLinkingController@authorizePost')
    ->middleware('botman')
    ->name('botman.authorize.post');

Route::get('/botman/confirm', 'MessengerAccountLinkingController@showConfirm')
    ->middleware('botman')
    ->name('botman.confirm.show');

Route::post('/botman/confirm', 'MessengerAccountLinkingController@confirm')
    ->middleware('botman')
    ->name('botman.confirm');


/**
 *
 * Playlist routes
 */
Route::resource('playlist', 'PlaylistController');