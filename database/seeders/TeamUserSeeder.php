<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TeamUserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		User::query()->create([
			'name' => 'Waheed Qasim',
			'email' => 'waheed@fincon.com',
			'password' => bcrypt('123456'),
		]);

		User::query()->create([
			'name' => 'Muzammal Hussain',
			'email' => 'muzammal@fincon.com',
			'password' => bcrypt('123456'),
		]);

		User::query()->create([
			'name' => 'Azeem Khan',
			'email' => 'azeem@fincon.com',
			'password' => bcrypt('123456'),
		]);

        User::query()->create([
            'name' => 'Saqlain Mushtaq ',
            'email' => 'saqlain@fincon.com',
            'password' => bcrypt('123456'),
        ]);
	}
}
