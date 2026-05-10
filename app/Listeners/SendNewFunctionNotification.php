<?php

namespace App\Listeners;

use App\Events\NewFunctionAdded;
use App\Mail\NewFunctionAddedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * EFF.2 Listens for the NewFunctionAdded event and emails every user
 * that has the "Expert in effects" role.
 *
 * Implements ShouldQueue so Laravel pushes this work onto the job queue
 * instead of running it inlinethe admin's save action returns immediately
 * and the emails are delivered in the background.
 */
class SendNewFunctionNotification implements ShouldQueue
{
    public function handle(NewFunctionAdded $event): void
    {
        // Fetch every user assigned the "Expert in effects" role.
        // Using whereHas keeps the role check in a single DB query.
        $experts = User::whereHas('role', fn ($q) => $q->where('name', 'Expert in effects'))->get();

        foreach ($experts as $index => $expert) {
            // ->later() schedules each email as its own queued job, staggered
            // 1 second apart per recipient. This prevents hitting Mailtrap's
            // (and most SMTP providers') rate limit when there are multiple experts.
            Mail::to($expert->email)
                ->later(now()->addSeconds($index), new NewFunctionAddedMail($event->cityFunction));
        }
    }
}
