<?php

namespace Database\Seeders;

use App\Models\CityGridCell;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        CityGridCell::ensureGridExists();

        // Order matters, roles must exist before users are seeded.
        $this->call([
            CityFunctionsTableSeeder::class,
            RolesSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
