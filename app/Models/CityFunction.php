<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CityFunction extends Model
{
    use SoftDeletes;

    // Single source of truth for the five QoL effect score columns.
    public const EFFECT_COLUMNS = [
        'Safety',
        'Recreation',
        'Environment Quality',
        'Facilities',
        'Mobility',
    ];

    protected $fillable = [
        'name',
        'category',
        'Safety',
        'Recreation',
        'Environment Quality',
        'Facilities',
        'Mobility',
        'image_path',
        'description',
    ];

    public function pendingActions(): HasMany
    {
        return $this->hasMany(PendingAction::class);
    }

    public function functionConditions(): HasMany
    {
        return $this->hasMany(FunctionCondition::class, 'city_function_id');
    }

    public function cityEvents(): BelongsToMany
    {
        return $this->belongsToMany(CityEvent::class, 'city_event_city_function')
            ->withPivot([
                'safety_modifier',
                'recreation_modifier',
                'environment_quality_modifier',
                'facilities_modifier',
                'mobility_modifier',
            ])
            ->withTimestamps();
    }
}
