<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Http\Requests\StudentRequest;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;

class StudentAuthController extends Controller
{
	/**
	 * Summary of login
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function login(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'student_number' => 'required|string',
			'student_pin' => 'required|string',
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 422);
		}

		$credentials = $request->only('student_number', 'student_pin');

		$student = Student::where('student_number', $credentials['student_number'])->first();

		if ($student && Hash::check($credentials['student_pin'], $student->student_pin)) {
			$token = $student->createToken('auth_token')->plainTextToken;

			return response()->json([
				'access_token' => $token,
				'token_type' => 'Bearer',
				'student' => $student,
			]);
		}

		return response()->json(['message' => 'Invalid credentials'], 401);
	}

	/**
	 * Summary of logout
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function logout(Request $request)
	{
		try {
			$token = $request->bearerToken();

			if (!$token) {
				return response()->json(['error' => 'No token provided'], 400);
			}

			// Find the token
			$personalAccessToken = PersonalAccessToken::findToken($token);

			if (!$personalAccessToken) {
				return response()->json(['error' => 'Invalid token'], 400);
			}

			// Delete the token
			$personalAccessToken->delete();

			return response()->json(['message' => 'Successfully logged out']);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Logout failed: ' . $e->getMessage()], 500);
		}
	}

	/**
	 * Summary of register
	 * @param \App\Http\Requests\StudentRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function register(StudentRequest $request)
	{
		// Validate the request
		$validated_data = $request->validated();
		$validated_data['student_pin'] = Hash::make($validated_data['student_pin']);

		// Create the student
		$student = Student::create($validated_data);

		// Create token
		$token = $student->createToken('auth_token')->plainTextToken;

		// Return response
		return response()->json(['access_token' => $token, 'token_type' => 'Bearer', 'student' => $student], 201);
	}

	/**
	 * Summary of refresh
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function refresh(Request $request)
	{
		try {
			$token = $request->bearerToken();

			if (!$token) {
				return response()->json(['error' => 'No authentication token provided'], 400);
			}

			// Find the current token
			$currentToken = PersonalAccessToken::findToken($token);

			if (!$currentToken) {
				return response()->json(['error' => 'Invalid or expired token'], 400);
			}

			$user = $currentToken->tokenable;

			if (!$user) {
				return response()->json(['error' => 'User not found'], 404);
			}

			// Delete the current token
			$currentToken->delete();

			// Create a new token
			$newToken = $user->createToken('auth_token')->plainTextToken;

			return response()->json([
				'access_token' => $newToken,
				'token_type' => 'Bearer',
				'expires_in' => config('sanctum.expiration') * 60
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Token refresh failed: ' . $e->getMessage()], 500);
		}
	}

	/**
	 * Summary of forgotPassword
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function forgotPassword(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|email|exists:students,email',
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 422);
		}

		$student = Student::where('email', $request->email)->first();

		if ($student) {
			// Generate a password reset token
			$token = Str::random(60);
			// $student->update(['reset_token' => $token]);

			// Send the password reset email
			// Mail::to($student->email)->send(new PasswordResetMail($token));

			return response()->json(['message' => 'Password reset email sent']);
		}

		return response()->json(['message' => 'Student not found'], 404);
	}
}
