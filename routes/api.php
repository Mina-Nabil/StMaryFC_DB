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
Route::middleware('auth:sanctum')->get('/attendance/details/{id}/{year}/{month}', 'ApiController@getAttendanceDetails');


//users&search
Route::middleware('auth:sanctum')->get('/current/user', 'ApiController@getCurrentUser');
Route::middleware('auth:sanctum')->get('/user/by/id/{id}', 'ApiController@getUserByID');
Route::middleware('auth:sanctum')->get('/get/users', 'ApiController@getUsers');
Route::middleware('auth:sanctum')->get('/get/user/payments/{id}', 'ApiController@getUserPayments');
Route::middleware('auth:sanctum')->get('/get/user/event/payments/{id}', 'ApiController@getUserEventPayments');
Route::middleware('auth:sanctum')->post('/user/by/face', 'ApiController@getUserByFaceID');
Route::middleware('auth:sanctum')->post('/search/name', 'ApiController@searchByName');
Route::middleware('auth:sanctum')->post('/add/user', 'ApiController@addUser');
Route::middleware('auth:sanctum')->get('/types', 'ApiController@getUsertypes');
Route::middleware('auth:sanctum')->post('/edit/user/password', 'ApiController@changePassword');
Route::middleware('auth:sanctum')->post('/edit/user/email', 'ApiController@changeEmail');
Route::middleware('auth:sanctum')->post('/edit/user', 'ApiController@editUser');
Route::middleware('auth:sanctum')->post('/add/payment', 'ApiController@addPayment');
Route::middleware('auth:sanctum')->post('/get/overview', 'ApiController@getUserOverview');
Route::middleware('auth:sanctum')->get('/get/next/code', 'ApiController@getNextUserID');
Route::middleware('auth:sanctum')->post('/delete/payment', 'ApiController@deleteUserPayment');
Route::middleware('auth:sanctum')->post('/delete/event/payment', 'ApiController@deleteEventPayment');
Route::middleware('auth:sanctum')->get('/user/balance/{id}', 'ApiController@getBalanceEntries');
Route::middleware('auth:sanctum')->post('/send/reminder', 'ApiController@sendReminder');

//groups
Route::middleware('auth:sanctum')->get('/users/by/group/id/{id}', 'ApiController@getUsersByGroupID');
Route::middleware('auth:sanctum')->post('/add/group', 'ApiController@addGroup');
Route::middleware('auth:sanctum')->get('/groups', 'ApiController@getGroups');
Route::middleware('auth:sanctum')->post('/del/group', 'ApiController@delGroup');
Route::middleware('auth:sanctum')->post('/toggle/group', 'ApiController@toggleGroup');

//categories
Route::middleware('auth:sanctum')->get('/categories', 'ApiController@getCategories');

//events
Route::middleware('auth:sanctum')->get('/events', 'ApiController@getEvents');
