<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
  protected Google2FA $google2fa;

  public function __construct(Google2FA $google2fa)
  {
    $this->google2fa = $google2fa;
  }

  public function attemptLogin(array $credentials, string $ip): array
  {
    $this->handleRateLimiting($credentials['user_number'], $ip);

    $user = $this->getUserByCredentials($credentials);

    $this->validateCredentials($user, $credentials);

    // Verify 2FA if enabled
    if ($user->google2fa_secret) {
      $this->verifyTwoFactor($user, $credentials);
    }

    $this->clearRateLimit($credentials['user_number'], $ip);

    return $this->generateTokenResponse($user);
  }

  public function enable2FA(User $user): array
  {
    if ($user->google2fa_secret) {
      throw new \Exception('Two-factor authentication is already enabled for this account.');
    }

    $secretKey = $this->google2fa->generateSecretKey();

    $user->google2fa_secret = $secretKey;
    $user->save();

    return [
      'secret' => $secretKey,
      'qr_code_url' => $this->google2fa->getQRCodeUrl(
        config('app.name'),
        $user->email,
        $secretKey
      ),
    ];
  }

  // ------------------ PRIVATE METHODS ------------------

  private function getUserByCredentials(array $credentials): User
  {
    $role = $credentials['role'] ?? 'student';

    $user = User::where([
      'user_number' => $credentials['user_number'],
      'role' => $role,
    ])->first();

    if (!$user) {
      throw new AuthenticationException('Invalid credentials.');
    }

    return $user;
  }

  private function validateCredentials(User $user, array $credentials): void
  {
    if (!Hash::check($credentials['user_pin'], $user->user_pin)) {
      throw new AuthenticationException('Invalid credentials.');
    }

    if ($user->status === 'inactive') {
      throw new AuthenticationException('Account is inactive. Please contact administration.');
    }
  }

  private function verifyTwoFactor(User $user, array $credentials): void
  {
    $otp = $credentials['otp'] ?? null;

    if (!$otp || !$this->google2fa->verifyKey($user->google2fa_secret, $otp)) {
      throw new AuthenticationException('Invalid or missing two-factor authentication token.');
    }
  }

  private function generateTokenResponse(User $user): array
  {
    $token = $user->createToken('auth_token')->plainTextToken;

    return [
      'access_token' => $token,
      'token_type' => 'Bearer',
    ];
  }

  private function handleRateLimiting(string $userNumber, string $ip): void
  {
    if (!config('auth.login.attempts_enabled', true)) {
      return;
    }

    $key = $this->getThrottleKey($userNumber, $ip);
    $maxAttempts = config('auth.login.attempt_limit', 5);
    $decayMinutes = config('auth.login.attempt_decay_minutes', 1);

    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
      $seconds = RateLimiter::availableIn($key);
      throw ValidationException::withMessages([
        'error' => "Too many login attempts. Please try again in {$seconds} seconds."
      ])->status(429);
    }

    RateLimiter::hit($key, $decayMinutes * 60);
  }

  private function clearRateLimit(string $userNumber, string $ip): void
  {
    if (!config('auth.login.attempts_enabled', true)) {
      return;
    }

    RateLimiter::clear($this->getThrottleKey($userNumber, $ip));
  }

  private function getThrottleKey(string $userNumber, string $ip): string
  {
    return strtolower($userNumber) . '|' . $ip;
  }
}
