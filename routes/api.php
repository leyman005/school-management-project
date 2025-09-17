<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Container\Attributes\Auth;

Route::get('/user', function (Request $request) {
	return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/register', [AuthController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {
	Route::get('/profile', [AuthController::class, 'profile']);
	Route::post('/refresh', [AuthController::class, 'refresh']);
	Route::post('/logout', [AuthController::class, 'logout']);

	Route::post('/2fa/setup', [TwoFactorController::class, 'setup']);
	// Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
});
