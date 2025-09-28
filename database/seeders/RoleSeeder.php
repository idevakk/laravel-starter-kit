<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::findOrCreate(name: 'admin', guardName: 'web');
        Role::findOrCreate(name: 'user', guardName: 'web');

        $permissionManageMails = Permission::findOrCreate(name: 'manage mails', guardName: 'web');
        $role->givePermissionTo($permissionManageMails);

        $permissionManageFilamentPanel = Permission::findOrCreate(name: 'manage panels', guardName: 'web');
        $role->givePermissionTo($permissionManageFilamentPanel);
    }
}
