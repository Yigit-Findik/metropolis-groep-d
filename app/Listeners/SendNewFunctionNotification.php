<?php

namespace App\Listeners;

use App\Events\NewFunctionAdded;
use App\Mail\NewFunctionAddedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendNewFunctionNotification implements ShouldQueue
{
    public function handle(NewFunctionAdded $event): void
    {
        $experts = User::whereHas('role', fn ($q) => $q->where('name', 'Expert in effects'))->get();

        foreach ($experts as $expert) {
            Mail::to($expert->email)->send(new NewFunctionAddedMail($event->cityFunction));
        }
    }
}
