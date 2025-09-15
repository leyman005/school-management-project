<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Create some sample students
		Student::create([
			'first_name' => 'John',
			'middle_name' => 'A.',
			'last_name' => 'Doe',
			'student_number' => 'S123456',
			'student_pin' => bcrypt('12345'),
			'email' => 'john.doe@example.com',
			'status' => 'active',
			'profile_picture' => null,
			'phone' => null,
			'date_of_birth' => null,
			'address' => null,
			'google2fa_secret' => null,
		]);
	}
}
