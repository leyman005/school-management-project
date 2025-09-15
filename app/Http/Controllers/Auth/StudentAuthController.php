<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use App\Http\Requests\StudentRequest;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\AuthService;

class StudentAuthController extends Controller
{
	protected $auth_service;

	public function __construct(AuthService $auth_service) {
		$this->auth_service = $auth_service;
	}

	/**
	 * Summary of login
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function login(Request $request)
	{
		// Validate input
		$validator = Validator::make($request->all(), [
			'student_number' => 'required|alpha_num|min:4|max:15',
			'student_pin'    => 'required|numeric|digits:5',
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 422);
		}

		try {
			$result = $this->auth_service->attemptLogin($request->only('student_number', 'student_pin'), $request->ip());
			return response()->json($result);
		} catch (\Illuminate\Validation\ValidationException $e) {
			return response()->json($e->errors(), 429);
		} catch (\Illuminate\Auth\AuthenticationException $e) {
			return response()->json(['error' => $e->getMessage()], 401);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Login failed: ' . $e->getMessage()], 500);
		}
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

		// Hash the student_pin
		$validated_data['student_pin'] = Hash::make($validated_data['student_pin']);

		// Combine first_name, middle_name, and last_name into name (Still need to be implemented).
		// $validated_data['name'] = $validated_data['first_name'] . ' ' . ($validated_data['middle_name'] ?? '') . ' ' . $validated_data['last_name'];

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
