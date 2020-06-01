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

Route::post('auth/login', 'Api\\AuthController@login');

Route::group(['middleware' => ['apiJwt']], function () {

    Route::post('auth/logout', 'Api\\AuthController@logout');
    Route::apiResource('users', 'Api\\UserController');
    Route::apiResource('courses', 'Api\\CourseController');
    Route::apiResource('enrollments', 'Api\\EnrollmentController');
    Route::apiResource('students', 'Api\\StudentController');

    Route::post('enrolled-courses', 'Api\\EnrolledCourseController@store');
    Route::post('enrolled-courses/student', 'Api\\EnrolledCourseController@readStudent');
    Route::get('enrolled-courses/age-range', 'Api\\EnrolledCourseController@readAgeRange');
});