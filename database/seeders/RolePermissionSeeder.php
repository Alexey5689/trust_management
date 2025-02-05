<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('title', 'admin')->first();
        $managerRole = Role::where('title', 'manager')->first();
        $clientRole = Role::where('title', 'client')->first();

        $permissions = Permission::all();

      
        $adminRole->permissions()->attach($permissions);

        
        $managerPermissions = $permissions->filter(function ($permission) {
            return in_array($permission->title, [
                'view_clients',
                'edit_clients',
                'edit_managers',
                'view_my_client_transactions'
            ]);
        });
        $managerRole->permissions()->attach($managerPermissions);

   
        $clientPermissions = $permissions->filter(function ($permission) {
            return in_array($permission->title, [
                'view_my_transactions',
                'edit_clients',
                'view_clients'
            ]);
        });
        $clientRole->permissions()->attach($clientPermissions);
    }
  

}
