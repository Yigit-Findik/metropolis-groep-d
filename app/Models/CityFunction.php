<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityFunction extends Model
{
    protected $fillable = [
        'name',
        'category',
        'qol_score',
        'livability',
        'safety',
        'economy',
        'environment',
        'welfare',
        'image_path',
        'description',
    ];
}
