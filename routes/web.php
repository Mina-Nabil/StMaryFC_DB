<?php

use App\Http\Controllers\CategoriesController;
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

//catgs routes
Route::get('users/categories', [CategoriesController::class, 'index']);
Route::post('users/categories', [CategoriesController::class, 'manage']);
Route::post('users/categories/details', [CategoriesController::class, 'setDetails']);
Route::get('users/categories/{id}', [CategoriesController::class, 'show']);
Route::post('users/categories/{id}', [CategoriesController::class, 'manage']);
Route::get('users/categories/{id}/delete', [CategoriesController::class, 'delete']);

//events routes
Route::get('events/all', "EventsController@all");
Route::get('events/add', "EventsController@add");
Route::get('events/{id}', "EventsController@details");
Route::post('events/insert', "EventsController@insert");
Route::post('events/update', "EventsController@update");
Route::get('events/delete/{id}', "EventsController@delete");
Route::post('events/attach', "EventsController@attachUser");
Route::post('events/detach', "EventsController@detachUser");
Route::get('events/payments/report', 'EventsController@queryPage');
Route::post('events/payments/report', 'EventsController@queryRes');
Route::post('events/payments/delete', 'EventsController@deletePayments');


//Payments routes
Route::get('payments/show', "PaymentsController@month");
Route::get('payments/due', "PaymentsController@due");
Route::get('payments/query', "PaymentsController@queryPage");
Route::get('payments/query/group', "PaymentsController@groupQueryPage");
Route::post('payments/query', "PaymentsController@queryRes");
Route::get('payments/add', "PaymentsController@addPayment");
Route::post('payments/insert', "PaymentsController@insert");
Route::get('payments/delete/{id}', 'PaymentsController@delete');
Route::get('payments/get/unpaid/{userID}', 'PaymentsController@getUnpaidDays');

//Attendance routes
Route::get('attendance/show', "AttendanceController@month");
Route::get('attendance/last/week', "AttendanceController@week");
Route::get('attendance/user/query', "AttendanceController@queryUser");
Route::get('attendance/group/query', "AttendanceController@queryGroup");
Route::post('attendance/query', "AttendanceController@queryRes");
Route::get('attendance/add', "AttendanceController@newAttendance");
Route::post('attendance/insert', "AttendanceController@insert");
Route::post('attendance/take', "AttendanceController@takeAttendance");
Route::get('attendance/delete/{id}', 'AttendanceController@deleteAttendance');
Route::get('attendance/overview', 'AttendanceController@queryOverview');
Route::post('overview/query', 'AttendanceController@overviewLoad');

//Users routes
Route::get("users/show/{type?}", 'UsersController@home');
Route::get("users/profile/{id}", 'UsersController@profile');
Route::post("users/profile/{id}", 'UsersController@profile');
Route::get("users/toggle/{id}", 'UsersController@toggle');
Route::get("users/add", 'UsersController@addPage');
Route::post("users/insert", 'UsersController@insert');
Route::post("users/update", 'UsersController@update');
Route::post("users/add/image", 'UsersController@attachImage');
Route::get("users/setimage/{userID}/{imageID}", 'UsersController@setMainImage');
Route::get("users/delete/{id}", 'UsersController@delete');
Route::get("users/delete/image/{id}", 'UsersController@deleteImage');
Route::get("users/send/reminder/{id}", 'UsersController@sendReminder');

//Groups routes
Route::get('groups/show', 'GroupsController@home');
Route::get('groups/edit/{id}', 'GroupsController@edit');
Route::get('groups/toggle/{id}', 'GroupsController@toggle');
Route::post('groups/insert', 'GroupsController@insert');
Route::post('groups/update', 'GroupsController@update');

//Dashboard users
Route::get("dash/users/all", 'DashUsersController@index');
Route::post("dash/users/insert", 'DashUsersController@insert');
Route::get("dash/users/edit/{id}", 'DashUsersController@edit');
Route::post("dash/users/update", 'DashUsersController@update');
Route::get("dash/users/delete/{id}", 'DashUsersController@delete');


Route::get('logout', 'HomeController@logout')->name('logout');
Route::get('/login', 'HomeController@login')->name('login');
Route::post('/login', 'HomeController@authenticate')->name('login');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/', 'HomeController@index')->name('home');
