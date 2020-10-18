<?php

use Illuminate\Support\Facades\Auth;
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

//Attendance routes
Route::get('attendance/show', "AttendanceController@month");
Route::get('attendance/last/week', "AttendanceController@week");
Route::get('attendance/query', "AttendanceController@queryPage");
Route::post('attendance/query', "AttendanceController@queryRes");
Route::get('attendance/add', "AttendanceController@newAttendance");
Route::post('attendance/insert', "AttendanceController@insert");
Route::post('attendance/take', "AttendanceController@takeAttendance");
Route::get('attendance/delete/{id}','AttendanceController@deleteAttendance');

//Users routes
Route::get("users/show/{type?}", 'UsersController@home');
Route::get("users/profile/{id}", 'UsersController@profile');
Route::get("users/toggle/{id}", 'UsersController@toggle');
Route::get("users/add", 'UsersController@addPage');
Route::post("users/insert", 'UsersController@insert');
Route::post("users/update", 'UsersController@update');
Route::post("users/add/image", 'UsersController@attachImage');
Route::get("users/setimage/{userID}/{imageID}", 'UsersController@setMainImage');

//Groups routes
Route::get('groups/show', 'GroupsController@home');
Route::get('groups/edit/{id}', 'GroupsController@edit');
Route::post('groups/insert', 'GroupsController@insert');
Route::post('groups/update', 'GroupsController@update');

//Dashboard users
Route::get("dash/users/all", 'DashUsersController@index');
Route::post("dash/users/insert", 'DashUsersController@insert');
Route::get("dash/users/edit/{id}", 'DashUsersController@edit');
Route::post("dash/users/update", 'DashUsersController@update');


Route::get('logout', 'HomeController@logout')->name('logout');
Route::get('/login', 'HomeController@login')->name('login');
Route::post('/login', 'HomeController@authenticate')->name('login');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/', 'HomeController@index')->name('home');
