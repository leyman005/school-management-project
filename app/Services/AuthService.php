<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
  public function attemptLogin(array $credentials, string $ip)
  {
    if (config('auth.login.attempts_enabled', true)) {
      $this->checkThrottle($credentials['user_number'], $ip);
    }

    $user = User::where('user_number', $credentials['user_number'])->first();

    if (!$user) {
      throw new AuthenticationException('Invalid credentials.');
    }

    if (!Hash::check($credentials['user_pin'], $user->user_pin)) {
      throw new AuthenticationException('Invalid credentials.');
    }

    if ($user->status === 'inactive') {
      throw new AuthenticationException('Account is inactive. Please contact administration.');
    }

    if (config('auth.login.attempts_enabled', true)) {
      $this->clearThrottle($credentials['user_number'], $ip);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return [
      'access_token' => $token,
      'token_type' => 'Bearer',
    ];
  }

  protected function checkThrottle($userNumber, $ip)
  {
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

  protected function clearThrottle($UserNumber, $ip)
  {
    $key = $this->getThrottleKey($UserNumber, $ip);
    RateLimiter::clear($key);
  }

  protected function getThrottleKey($UserNumber, $ip)
  {
    return strtolower($UserNumber) . '|' . $ip;
  }
}
