<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		// User::factory(10)->create();

		// User::factory()->create([
		// 	'name' => 'Test User',
		// 	'email' => 'test@example.com',
		// ]);

		// Create some sample users
		User::create([
			'first_name' => 'John',
			'middle_name' => 'A.',
			'last_name' => 'Doe',
			'user_number' => '123456789',
			'user_pin' => bcrypt('12345'),
			'email' => 'john.doe@example.com',
			'status' => 'active',
			'profile_picture' => null,
			'phone' => null,
			'date_of_birth' => null,
			'address' => null,
			'google2fa_secret' => null,
		]);

		User::create([
			'first_name' => 'Cedric',
			'middle_name' => 'B.',
			'last_name' => 'kafuka',
			'user_number' => '241715280',
			'user_pin' => bcrypt('12345'),
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
