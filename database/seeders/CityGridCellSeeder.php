<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CityGridCell;

class CityGridCellSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($row = 1; $row <= 3; $row++) {
            for ($column = 1; $column <= 4; $column++) {
                CityGridCell::create([
                    'row_index' => $row,
                    'column_index' => $column,
                    'function_name' => null,
                ]);
            }
        }
    }
}
