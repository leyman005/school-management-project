<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Auth\StudentAuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Create a restful endpoint for students
// Route::prefix('api')->group(function () {
Route::apiResource('students', StudentController::class)->middleware('auth:sanctum');
// });

// Student authentication routes

Route::prefix('student')->group(function () {
	Route::post('/login', [StudentAuthController::class, 'login']);
	Route::post('/logout', [StudentAuthController::class, 'logout'])->middleware('auth:sanctum');
	Route::post('/register', [StudentAuthController::class, 'register']);
	Route::post('/refresh', [StudentAuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [StudentAuthController::class, 'forgotPassword']);
});
