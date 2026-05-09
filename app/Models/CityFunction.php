<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CityFunction extends Model
{
    use SoftDeletes;

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
}
