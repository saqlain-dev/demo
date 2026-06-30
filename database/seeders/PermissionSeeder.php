<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission1 = Permission::create(['name' => 'project_view']);
        $permission2 = Permission::create(['name' => 'project_create']);
        $permission3 = Permission::create(['name' => 'project_update']);
        $permission4 = Permission::create(['name' => 'project_delete']);

        $role = Role::findByName("Project Manager");
        $role->givePermissionTo([$permission1, $permission2, $permission3, $permission4]);

    }
}
