<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CityFunction;

class ActionHistory extends Model
{
    protected $table = 'action_history';
    protected $fillable = [
        'user_id',
        'action',
        'cell_id',
        'old_city_function_id',
        'new_city_function_id',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function oldCityFunction()
    {
        return $this->belongsTo(CityFunction::class, 'old_city_function_id');
    }

    public function newCityFunction()
    {
        return $this->belongsTo(CityFunction::class, 'new_city_function_id');
    }

    protected static function booted(): void
    {
        // Prevent updates and deletes to keep entries immutable once written.
        static::updating(function () {
            return false;
        });

        static::deleting(function () {
            return false;
        });
    }
}
