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
			'student_number' => '123456789',
			'student_pin' => bcrypt('12345'),
			'email' => 'john.doe@example.com',
			'status' => 'active',
			'profile_picture' => null,
			'phone' => null,
			'date_of_birth' => null,
			'address' => null,
			'google2fa_secret' => null,
		]);

		Student::create([
			'first_name' => 'Cedric',
			'middle_name' => 'B.',
			'last_name' => 'kafuka',
			'student_number' => '241715280',
			'student_pin' => bcrypt('12345'),
			'email' => 'cedngk1985@gmail.com',
			'status' => 'active',
			'profile_picture' => null,
			'phone' => '840681296',
			'date_of_birth' => '1980-09-19',
			'gender' => 'male',
			'address' => '125 Becker Street, Bellevue, Johannesburg',
			'google2fa_secret' => null,
		]);
	}
}
