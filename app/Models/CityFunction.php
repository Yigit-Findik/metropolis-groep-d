<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityFunction extends Model
{
    protected $fillable = [
        'name',
        'category',
        'qol_score',
        'Safety',
        'Recreation',
        'Environment Quality',
        'Facilities',
        'Mobility',
        'image_path',
        'description',
    ];
}
