<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunctionCondition extends Model
{
    protected $fillable = [
        'city_function_id',
        'target_function_id',
        'type',
    ];

    public function cityFunction(): BelongsTo
    {
        return $this->belongsTo(CityFunction::class, 'city_function_id');
    }

    public function targetFunction(): BelongsTo
    {
        return $this->belongsTo(CityFunction::class, 'target_function_id');
    }
}
