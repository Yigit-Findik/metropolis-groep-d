<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class CityGridCell extends Model
{
    use HasFactory;

    protected $fillable = [
        'row_index',
        'column_index',
        'function_name',
    ];

    protected $casts = [
        'row_index' => 'integer',
        'column_index' => 'integer',
    ];

    public static function ensureGridExists(): Collection
    {
        if (! Schema::hasTable((new static())->getTable())) {
            return static::fallbackGridCells();
        }

        for ($rowIndex = 1; $rowIndex <= 3; $rowIndex++) {
            for ($columnIndex = 1; $columnIndex <= 4; $columnIndex++) {
                static::firstOrCreate(
                    [
                        'row_index' => $rowIndex,
                        'column_index' => $columnIndex,
                    ],
                    [
                        'function_name' => null,
                    ]
                );
            }
        }

        return static::query()
            ->orderBy('row_index')
            ->orderBy('column_index')
            ->get();
    }

    /**
     * Build a non-persistent 3x4 grid when the backing table is unavailable.
     */
    protected static function fallbackGridCells(): Collection
    {
        $cells = collect();

        for ($rowIndex = 1; $rowIndex <= 3; $rowIndex++) {
            for ($columnIndex = 1; $columnIndex <= 4; $columnIndex++) {
                $cells->push((object) [
                    'row_index' => $rowIndex,
                    'column_index' => $columnIndex,
                    'function_name' => null,
                ]);
            }
        }

        return $cells;
    }
}
