<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccessRoad extends Model
{
    protected $fillable = ['user_id', 'start_cell_id', 'end_cell_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function cells(): BelongsToMany
    {
        return $this->belongsToMany(CityGridCell::class, 'access_road_cells', 'access_road_id', 'cell_id')
                    ->withPivot('cell_order')
                    ->orderByPivot('cell_order');
    }

    public function startCell(): BelongsTo
    {
        return $this->belongsTo(CityGridCell::class, 'start_cell_id');
    }

    public function endCell(): BelongsTo
    {
        return $this->belongsTo(CityGridCell::class, 'end_cell_id');
    }
}