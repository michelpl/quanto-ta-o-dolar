<?php

use Illuminate\Http\Request;

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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::prefix('/')->group(function () {
    Route::prefix('price')->group(function () {
        Route::get('/', 'API\PriceController@index');
        Route::get('/buy/{city}/{amountToBuy}', 'API\PriceController@buyByCity');
    });

    Route::prefix('webhook')->group(function () {
        Route::get('/', 'API\WebhookController@index');
        Route::post('/bot', 'API\WebhookController@bot');
    });

    Route::apiResource('/alert', 'API\AlertController');
});