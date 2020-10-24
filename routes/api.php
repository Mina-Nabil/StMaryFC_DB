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

Route::post('/login', 'ApiController@login');

Route::middleware('auth:sanctum')->get('/current/user', 'ApiController@getCurrentUser');
Route::middleware('auth:sanctum')->get('/user/by/id/{id}', 'ApiController@getUserByID');
Route::middleware('auth:sanctum')->post('/user/by/face', 'ApiController@getUserByFaceID');
Route::middleware('auth:sanctum')->get('/groups', 'ApiController@getGroups');
