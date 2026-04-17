<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CityGridCell;

class CityGridSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($row = 1; $row <= 3; $row++) {
            for ($col = 1; $col <= 4; $col++) {
                CityGridCell::create([
                    'row_index' => $row,
                    'col_index' => $col,
                    'function_name' => null,
                ]);
            }
        }
    }
}
