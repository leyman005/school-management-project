<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Gender;

class StudentRequest extends FormRequest
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
      'email' => 'required|email|unique:students,email,' . $this->route('student'),
      'student_pin' => 'required|numeric|digits:5',
      'student_number' => 'required|alpha_num|min:4|max:15|unique:students,student_number,' . $this->route('student'),
      'phone' => 'nullable|string|max:15',
      'date_of_birth' => 'nullable|date',
      'gender' => [
        'nullable',
        'string',
        'max:10',
        new Enum(Gender::class),
      ],
      'address' => 'nullable|string|max:255',
      'status' => 'nullable|string|in:active,inactive,suspended,graduated,expelled,blocked',
      'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ];
  }
}
