<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function(){

	Route::group(['middleware' => 'jwt'], function(){

		Route::get('/checktoken', [
			'uses' => 'AuthController@checkToken'
		]);

		Route::resource('playlist', 'PlaylistController', [
			'except' => ['create', 'edit']
		]);

		Route::group(['prefix' => 'userdata'], function(){

			Route::get('collection', [
				'uses' => 'UserDataController@getCollection'
			]);
			Route::post('collection', [
				'uses' => 'UserDataController@updateCollection'
			]);

			Route::get('songplayed', [
				'uses' => 'UserDataController@getSongPlayed'
			]);
			Route::post('songplayed', [
				'uses' => 'UserDataController@updateSongPlayed'
			]);
		});
		
	});

	Route::resource('song', 'SongController', [
		'except' => ['create', 'edit', 'destroy']
	]);

	Route::group(['prefix' => 'recommendation'], function(){

		Route::get('/search', [
			'uses' => 'SongController@search'
		]);

		Route::get('/peoplefavorites', [
			'uses' => 'SongController@peopleFavorites'
		]);

	});

	Route::post('/login', [
		'uses' => 'AuthController@login'
	]);

	Route::post('/logout', [
		'uses' => 'AuthController@logout'
	]);






	Route::resource('slide', 'SlideController', [
		'except' => ['create', 'edit', 'destroy']
	]);

	

});
