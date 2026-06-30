<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		/*Role::create(['name' => 'Super Admin']);
		$user = User::query()->find(1);
		$user->assignRole('Super Admin');*/

        Role::create(['name' => 'Project Manager']);
        $user = User::query()->find(2);
        $user->assignRole('Project Manager');

        Role::create(['name' => 'PDU']);
        $user = User::query()->find(3);
        $user->assignRole('PDU');

        Role::create(['name' => 'CEO']);
        $user = User::query()->find(4);
        $user->assignRole('CEO');

	}
}
