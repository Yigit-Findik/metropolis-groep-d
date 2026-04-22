<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminId   = DB::table('roles')->where('name', 'admin')->value('id');
        $plannerId = DB::table('roles')->where('name', 'planner')->value('id');
        $viewerId  = DB::table('roles')->where('name', 'viewer')->value('id');

        DB::table('users')->insert([
            [
                'name'              => 'Admin User',
                'email'             => 'admin@metropolis.test',
                'password'          => Hash::make('password'),
                'role_id'           => $adminId,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Planner User',
                'email'             => 'planner@metropolis.test',
                'password'          => Hash::make('password'),
                'role_id'           => $plannerId,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Viewer User',
                'email'             => 'viewer@metropolis.test',
                'password'          => Hash::make('password'),
                'role_id'           => $viewerId,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
