<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
}
