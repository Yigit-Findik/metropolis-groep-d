<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $administratorId    = DB::table('roles')->where('name', 'Administrator')->value('id');
        $cityPlannerId      = DB::table('roles')->where('name', 'City planner')->value('id');
        $expertInEffectsId  = DB::table('roles')->where('name', 'Expert in effects')->value('id');

        DB::table('users')->insert([
            [
                'name'              => 'Administrator',
                'email'             => 'administrator@metropolis.test',
                'password'          => Hash::make('administrator@metropolis.test'),
                'role_id'           => $administratorId,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'City Planner',
                'email'             => 'cityplanner@metropolis.test',
                'password'          => Hash::make('cityplanner@metropolis.test'),
                'role_id'           => $cityPlannerId,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Expert in Effects',
                'email'             => 'expert@metropolis.test',
                'password'          => Hash::make('expert@metropolis.test'),
                'role_id'           => $expertInEffectsId,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
