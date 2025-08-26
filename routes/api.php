<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Create a restful endpoint for students
// Route::prefix('api')->group(function () {
Route::apiResource('students', StudentController::class);
// });
