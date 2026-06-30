<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		User::query()->create([
			'name' => 'Super Admin',
			'email' => 'super@admin.com',
			'password' => bcrypt('123456'),
		]);
	}
}
