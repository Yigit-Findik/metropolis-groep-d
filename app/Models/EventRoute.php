<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventRoute extends Model
{
    protected $fillable = ['user_id', 'access_road_id', 'event_cell_id'];

    public function accessRoad(): BelongsTo
    {
        return $this->belongsTo(AccessRoad::class);
    }

    public function eventCell(): BelongsTo
    {
        return $this->belongsTo(CityGridCell::class, 'event_cell_id');
    }

    public function cells(): BelongsToMany
    {
        return $this->belongsToMany(CityGridCell::class, 'event_route_cells', 'event_route_id', 'cell_id')
                    ->withPivot('cell_order')
                    ->orderByPivot('cell_order');
    }
}
