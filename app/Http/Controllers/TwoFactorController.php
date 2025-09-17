<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
	protected AuthService $authService;

	public function __construct(AuthService $authService)
	{
		$this->authService = $authService;
	}

	/**
	 * Step 1: Generate 2FA secret and QR code URL
	 */
	public function setup(Request $request)
	{
		$user = $request->user();

		$data = $this->authService->enable2FA($user);

		return response()->json([
			'message' => '2FA setup initiated.',
			'secret' => $data['secret'],
			'qr_code_url' => $data['qr_code_url'],
		]);
	}
}
