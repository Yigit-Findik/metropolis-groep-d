<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionHistory extends Model
{
    protected $table = 'action_history';
    protected $fillable = [
        'user_id',
        'action',
        'cell_id',
        'old_city_function_id',
        'new_city_function_id'
    ];
}
