<?php

use Illuminate\Support\Facades\Route;

Route::get('/encode', 'ApiController@encode')->name('encode');
Route::get('/decode', 'ApiController@decode')->name('decode');

Route::get('/login', 'SiteController@getLogin')->name('site.getLogin');
Route::post('/login', 'SiteController@postLogin')->name('site.postLogin');

Route::middleware('sessionAuth')->group(function () {
    Route::name('site.')->group(function () {
        Route::get('/logout', 'SiteController@logout')->name('logout');
        Route::get('/', 'SiteController@home')->name('home');
        Route::get('/settings', 'SiteController@water')->name('settings');
        Route::get('/reports', 'SiteController@reports')->name('reports');
        Route::get('/configuration', 'SiteController@configuration')->name('configuration');
        Route::post('/configuration', 'SiteController@store')->name('config.store');
        Route::get('/about', 'SiteController@about')->name('about');

        Route::get('/valveSensors/{valve}', 'SiteController@getValveSensors')->name('getValveSensors');

        Route::get('/checkIfAtLeastOneValveIsOpen/{device}', 'SiteController@checkIfAtLeastOneValveIsOpen')->name('checkIfAtLeastOneValveIsOpen');
        Route::post('/changeMode', 'SiteController@changeMode')->name('changeMode');
    });
});

Route::prefix('api')->group(function() {
    Route::name('api.')->group(function () {
        Route::post('PostParams', 'ApiController@postParams')->name('postParams');
        Route::post('report', 'ApiController@report')->name('report');
    });
});

