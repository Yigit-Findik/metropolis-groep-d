<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingAction extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';

    public const TRIGGER_CREATED = 'created';
    public const TRIGGER_UPDATED = 'updated';
    public const TRIGGER_SOFT_DELETED = 'soft_deleted';
    public const TRIGGER_EFFECT_VALUES = 'effect_values';

    protected $fillable = [
        'city_function_id',
        'function_name',
        'trigger_type',
        'status',
        'created_by_user_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function cityFunction(): BelongsTo
    {
        return $this->belongsTo(CityFunction::class)->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function getTriggerLabelAttribute(): string
    {
        return match ($this->trigger_type) {
            self::TRIGGER_CREATED => 'Function created',
            self::TRIGGER_UPDATED => 'Function updated',
            self::TRIGGER_SOFT_DELETED => 'Function archived',
            self::TRIGGER_EFFECT_VALUES => 'Fill in effect values',
            default => ucfirst(str_replace('_', ' ', (string) $this->trigger_type)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            default => ucfirst((string) $this->status),
        };
    }

    public function getResolvedFunctionNameAttribute(): string
    {
        return $this->cityFunction?->name ?? $this->function_name ?? 'Deleted function';
    }
}