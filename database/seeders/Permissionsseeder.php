<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Permissionsseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create([
            'title' => 'Admin',
            'permissions' => 'users_view,users_restore,users_create,users_update,users_delete,permissions_view,permissions_create,permissions_update,permissions_delete,programs_view,programs_access_keys,programs_create,programs_update,programs_delete,programs_add_users,programs_delete_users,logs_view,programs_restore,permissions_restore,logs_all'
        ]);
    }
}
