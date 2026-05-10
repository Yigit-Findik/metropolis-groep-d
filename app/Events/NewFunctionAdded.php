<?php

namespace App\Events;

use App\Models\CityFunction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewFunctionAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly CityFunction $cityFunction) {}
}
