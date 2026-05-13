<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $vendorRole = Role::create(['name' => 'vendor']);
        $userRole = Role::create(['name' => 'user']);

        $manageProductsPermission = Permission::create(['name' => 'manage products']);
        $manageCategoriesPermission = Permission::create(['name' => 'manage categories']);
        $manageOrdersPermission = Permission::create(['name' => 'manage orders']);
        $manageUsersPermission = Permission::create(['name' => 'manage users']);

        $adminRole->syncPermissions([
            $manageProductsPermission,
            $manageCategoriesPermission,
            $manageOrdersPermission,
            $manageUsersPermission,
        ]);

        $vendorRole->syncPermissions([
            $manageProductsPermission,
            $manageOrdersPermission,
        ]);
    }
}
