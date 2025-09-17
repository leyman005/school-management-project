<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Gender;

class UserRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'middle_name' => 'nullable|string|max:255',
      'email' => 'required|email|unique:users,email',
      'user_pin' => 'required|numeric|digits:5',
      'user_number'  => 'required|numeric|digits:9|unique:users',
      'phone' => 'nullable|string|max:15',
      'date_of_birth' => 'nullable|date',
      'gender' => [
        'nullable',
        'string',
        'max:10',
        new Enum(Gender::class),
      ],
      'address' => 'nullable|string|max:255',
      'status' => 'nullable|string|in:active,inactive,suspended,graduated,expelled',
      'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      'role' => 'nullable|string|in:student,admin,personnel',
    ];
  }
}
