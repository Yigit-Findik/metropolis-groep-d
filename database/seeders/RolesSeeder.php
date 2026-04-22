<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->truncate();

        foreach (['admin', 'planner', 'viewer'] as $name) {
            DB::table('roles')->insert([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
