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

//attendance
Route::middleware('auth:sanctum')->post('/take/attendance', 'ApiController@takeAttendance');
Route::middleware('auth:sanctum')->post('/take/bulk/attendance', 'ApiController@takeBulkAttendance');


//users&search
Route::middleware('auth:sanctum')->get('/current/user', 'ApiController@getCurrentUser');
Route::middleware('auth:sanctum')->get('/user/by/id/{id}', 'ApiController@getUserByID');
Route::middleware('auth:sanctum')->get('/get/users', 'ApiController@getUsers');
Route::middleware('auth:sanctum')->post('/user/by/face', 'ApiController@getUserByFaceID');
Route::middleware('auth:sanctum')->post('/search/name', 'ApiController@searchByName');
Route::middleware('auth:sanctum')->post('/add/user', 'ApiController@addUser');
Route::middleware('auth:sanctum')->get('/types', 'ApiController@getUsertypes');

//groups
Route::middleware('auth:sanctum')->get('/users/by/group/id/{id}', 'ApiController@getUsersByGroupID');
Route::middleware('auth:sanctum')->post('/add/group', 'ApiController@addGroup');
Route::middleware('auth:sanctum')->get('/groups', 'ApiController@getGroups');
Route::middleware('auth:sanctum')->post('/del/group', 'ApiController@delGroup');
Route::middleware('auth:sanctum')->post('/toggle/group', 'ApiController@toggleGroup');
