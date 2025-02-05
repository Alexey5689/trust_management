<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['title' => 'view_clients']);
        Permission::create(['title' => 'edit_clients']);
        Permission::create(['title' => 'delete_clients']);
        Permission::create(['title' => 'view_managers']);
        Permission::create(['title' => 'edit_managers']);
        Permission::create(['title' => 'delete_managers']);
        Permission::create(['title' => 'view_logs']);
        Permission::create(['title' => 'view_all_transactions']);
        Permission::create(['title' => 'view_my_client_transactions']);
        Permission::create(['title' => 'view_my_transactions']);
      
    }
}
