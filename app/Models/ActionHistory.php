<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionHistory extends Model
{
    protected $table = 'action_history';
    protected $fillable = [
        'user_id',
        'action',
        'cell_id',
        'old_city_function_id',
        'new_city_function_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cell(): BelongsTo
    {
        return $this->belongsTo(CityGridCell::class, 'cell_id');
    }

    public function oldCityFunction(): BelongsTo
    {
        return $this->belongsTo(CityFunction::class, 'old_city_function_id')->withTrashed();
    }

    public function newCityFunction(): BelongsTo
    {
        return $this->belongsTo(CityFunction::class, 'new_city_function_id')->withTrashed();
    }
}
