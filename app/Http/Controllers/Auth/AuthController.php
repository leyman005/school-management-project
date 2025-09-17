<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\AuthService;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Str;

class AuthController extends Controller
{
	protected $auth_service;

	public function __construct(AuthService $auth_service)
	{
		$this->auth_service = $auth_service;
	}

	public function login(Request $request)
	{
		// Validate input and provide custom error messages.
		$validator = Validator::make(
			$request->all(),
			[
				'user_number' => 'required|numeric|digits:9',
				'user_pin'    => 'required|numeric|digits:5',
				'role' => 'nullable|string|in:student,admin,personnel',
			],
			[
				'user_number.required' => $request->input('role') === 'student'
					|| $request->input('role') === '' ? 'Student number is required.' : 'Staff number is required.',
				'user_number.numeric' => $request->input('role') === 'student'
					|| $request->input('role') === '' ? 'Student number must be numeric.' : 'Staff number must be numeric.',
				'user_number.digits'  => $request->input('role') === 'student'
					|| $request->input('role') === '' ? 'Student number must be 9 digits.' : 'Staff number must be 9 digits.',
				'user_pin.required' => 'PIN is required.',
				'user_pin.numeric'  => 'PIN must be numeric.',
				'user_pin.digits'   => 'PIN must be 5 digits.',
			]
		);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 422);
		}

		try {
			$result = $this->auth_service->attemptLogin($request->only('user_number', 'user_pin', 'role', 'otp'), $request->ip());
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
	 * @param \App\Http\Requests\UserRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function register(UserRequest $request)
	{
		// Validate the request
		$validated_data = $request->validated();

		// Hash the pin
		$validated_data['user_pin'] = Hash::make($validated_data['user_pin']);

		// Create the user
		$user = User::create($validated_data);

		if (!$user) {
			return response()->json(['error' => 'User registration failed'], 500);
		}
		// Return response
		return response()->json(['message' => 'User registered successfully'], 201);
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
			'email' => 'required|email|exists:users,email',
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 422);
		}

		$user = User::where('email', $request->email)->first();

		if ($user) {
			return response()->json(['message' => 'Password reset email sent']);
		}

		return response()->json(['message' => 'User not found'], 404);
	}

	public function profile(Request $request)
	{
		return response()->json($request->user());
	}
}
