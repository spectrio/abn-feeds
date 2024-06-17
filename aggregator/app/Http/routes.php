<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('digichiefFeeds/{feedType?}', 'FeedsController@digichiefFeeds');
Route::get('portalFeeds/{feedType?}/{channel?}', 'FeedsController@portalFeeds');
Route::post('api/playerLogUploads', 'LogUploadsController@uploadSubmit');
Route::get('api/mi', 'LogUploadsController@listMagicInfo');
Route::post('api/mi', 'LogUploadsController@uploadMagicInfo');
Route::get('api/asc', 'LogUploadsController@listASC');
Route::post('api/asc', 'LogUploadsController@uploadASC');
Route::get('cachingFeeds/fillFeeds/{feedToFill?}/{channel?}', 'CachingController@fillFeeds');
Route::get('cachingFeeds/fillAccounts', 'CachingController@fillAccounts');
Route::get('cachingFeeds/testAccounts12345', 'CachingController@testAccounts');
Route::get('cachingFeeds/fillScrollers', 'CachingController@fillScrollers');
Route::get('cachingFeeds/fillThemes', 'CachingController@fillThemes');
Route::get('cachingFeeds/fillWeather', 'CachingController@fillWeather');
Route::get('buildFeeds/buildScrollers', 'CachingController@buildScrollers');
Route::get('buildFeeds/testBuildScrollers', 'CachingController@testBuildScrollers');
Route::get('cache/logos/{sliceSize?}', 'CachingController@cacheLogos');
Route::get('device/{name?}', 'FeedsController@deviceConfiguration');
Route::get('/', function () {
    return view('welcome');
});
    