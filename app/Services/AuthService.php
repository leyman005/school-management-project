<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
  public function attemptLogin(array $credentials, string $ip)
  {
    if (env('LOGIN_ATTEMPT_FEATURE', true)) {
      $this->checkThrottle($credentials['student_number'], $ip);
    }

    $student = Student::where('student_number', $credentials['student_number'])->first();

    if (!$student || !Hash::check($credentials['student_pin'], $student->student_pin)) {
      throw new AuthenticationException('Invalid credentials.');
    }

    if ($student->status === 'inactive') {
      throw new AuthenticationException('Account is inactive. Please contact administration.');
    }

    if (env('LOGIN_ATTEMPT_FEATURE', true)) {
      $this->clearThrottle($credentials['student_number'], $ip);
    }

    $token = $student->createToken('auth_token')->plainTextToken;

    return [
      'access_token' => $token,
      'token_type' => 'Bearer',
      'student' => $student,
    ];
  }

  protected function checkThrottle($studentNumber, $ip)
  {
    $key = $this->getThrottleKey($studentNumber, $ip);
    $maxAttempts = env('LOGIN_ATTEMPT_LIMIT', 5);
    $decayMinutes = env('LOGIN_ATTEMPT_DECAY_MINUTES', 1);

    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
      $seconds = RateLimiter::availableIn($key);
      throw ValidationException::withMessages([
        'error' => "Too many login attempts. Please try again in {$seconds} seconds."
      ])->status(429);
    }

    RateLimiter::hit($key, $decayMinutes * 60);
  }

  protected function clearThrottle($studentNumber, $ip)
  {
    $key = $this->getThrottleKey($studentNumber, $ip);
    RateLimiter::clear($key);
  }

  protected function getThrottleKey($studentNumber, $ip)
  {
    return strtolower($studentNumber) . '|' . $ip;
  }
}
