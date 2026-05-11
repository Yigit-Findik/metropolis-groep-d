<?php

namespace App\Events;

use App\Models\CityFunction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * EFF.2 Fired by CityFunctionController::store() after a new city function is saved.
 * Listeners subscribed to this event handle the notification side-effects
 * (e.g. emailing effects experts) without blocking the admin's save action.
 */
class NewFunctionAdded
{
    // Dispatchable adds the static ::dispatch() helper used in the controller.
    // SerializesModels safely serialises the Eloquent model so it survives queue storage.
    use Dispatchable, SerializesModels;

    /**
     * @param CityFunction $cityFunction The newly created city function.
     */
    public function __construct(public readonly CityFunction $cityFunction) {}
}
