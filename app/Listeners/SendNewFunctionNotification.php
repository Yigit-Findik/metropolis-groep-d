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
            // Wrapped in try-catch because the production server uses QUEUE_CONNECTION=sync,
            // which runs this listener synchronously during the HTTP request instead of in a
            // background worker. Shared hosting also commonly blocks outbound SMTP on port 2525
            // (Mailtrap's port), so the connection throws. Without this catch, that exception
            // would propagate back to the controller and return a 500 to the user even though
            // the city function was already saved successfully.
            try {
                Mail::to($expert->email)
                    ->later(now()->addSeconds($index), new NewFunctionAddedMail($event->cityFunction));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to queue new function notification email', [
                    'recipient' => $expert->email,
                    'error'     => $e->getMessage(),
                ]);
            }
        }
    }
}
