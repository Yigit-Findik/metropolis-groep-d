<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GridCellSuggestion extends Model
{
    protected $fillable = ['cell_id', 'user_id', 'description', 'status'];

    public function cell(): BelongsTo
    {
        return $this->belongsTo(CityGridCell::class, 'cell_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
