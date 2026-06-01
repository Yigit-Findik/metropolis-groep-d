<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'one_off_duration_value',
        'one_off_duration_unit',
    ];

    public function getTypeLabelAttribute(): string
    {
        return $this->event_type === 'recurring' ? 'Recurring' : 'One-off';
    }

    public function getScheduleSummaryAttribute(): string
    {
        if ($this->event_type === 'recurring') {
            $value = (int) ($this->recurring_frequency_value ?? 1);
            $unit = $this->recurring_frequency_unit ?? 'day';

            return 'Happens ' . $value . ' ' . Str::plural('time', $value) . ' per ' . $unit;
        }

        $value = (int) ($this->one_off_duration_value ?? 1);
        $unit = $this->one_off_duration_unit ?? 'day';

        return 'Lasts ' . $value . ' ' . Str::plural($unit, $value);
    }
}