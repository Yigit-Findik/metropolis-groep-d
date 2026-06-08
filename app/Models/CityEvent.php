<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CityEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'event_type',
        'recurring_frequency_value',
        'recurring_frequency_unit',
        'recurring_active_duration_value',
        'recurring_active_duration_unit',
        'one_off_duration_value',
        'one_off_duration_unit',
        'is_active',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'activated_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function cityFunctions(): BelongsToMany
    {
        return $this->belongsToMany(CityFunction::class, 'city_event_city_function')
            ->withPivot([
                'safety_modifier',
                'recreation_modifier',
                'environment_quality_modifier',
                'facilities_modifier',
                'mobility_modifier',
            ])
            ->withTimestamps();
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function cycleDurationSeconds(): int
    {
        $unitSeconds = match ($this->recurring_frequency_unit) {
            'hour'  => 3600,
            'day'   => 86400,
            'week'  => 604800,
            'month' => 2592000,
            default => 86400,
        };

        return (int) ($unitSeconds / max(1, (int) $this->recurring_frequency_value));
    }

    public function activeDurationSeconds(): int
    {
        $unitSeconds = match ($this->recurring_active_duration_unit) {
            'minute' => 60,
            'hour'   => 3600,
            'day'    => 86400,
            'week'   => 604800,
            default  => 3600,
        };

        return (int) $unitSeconds * max(1, (int) $this->recurring_active_duration_value);
    }

    public function oneOffDurationSeconds(): int
    {
        $unitSeconds = match ($this->one_off_duration_unit) {
            'day'  => 86400,
            'week' => 604800,
            default => 3600,
        };

        return (int) $unitSeconds * max(1, (int) $this->one_off_duration_value);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->event_type === 'recurring' ? 'Recurring' : 'One-off';
    }

    public function getScheduleSummaryAttribute(): string
    {
        if ($this->event_type === 'recurring') {
            $freq  = (int) ($this->recurring_frequency_value ?? 1);
            $fUnit = $this->recurring_frequency_unit ?? 'day';
            $aDur  = (int) ($this->recurring_active_duration_value ?? 1);
            $aUnit = $this->recurring_active_duration_unit ?? 'hour';

            return 'Active for ' . $aDur . ' ' . Str::plural($aUnit, $aDur)
                 . ', ' . $freq . ' ' . Str::plural('time', $freq) . ' per ' . $fUnit;
        }

        $value = (int) ($this->one_off_duration_value ?? 1);
        $unit = $this->one_off_duration_unit ?? 'day';

        return 'Lasts ' . $value . ' ' . Str::plural($unit, $value);
    }
}