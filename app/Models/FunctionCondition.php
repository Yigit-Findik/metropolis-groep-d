<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunctionCondition extends Model
{
    protected $fillable = [
        'city_function_id',
        'target_function_id',
        'type',
    ];

    public function cityFunction()
    {
        return $this->belongsTo(CityFunction::class, 'city_function_id');
    }

    public function targetFunction()
    {
        return $this->belongsTo(CityFunction::class, 'target_function_id');
    }
}