<?php

namespace App\Mail;

use App\Models\CityFunction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * EFF.2 Mailable that formats the notification email sent to effects experts
 * when a new city function is added by an administrator.
 *
 * The $cityFunction property is public so the Blade view can access it directly.
 */
class NewFunctionAddedMail extends Mailable
{
    // Queueable allows this mail to be pushed onto the queue via Mail::later().
    // SerializesModels ensures the Eloquent model is safely stored and restored by the queue.
    use Queueable, SerializesModels;

    /**
     * @param CityFunction $cityFunction The newly created city function to include in the email.
     */
    public function __construct(public readonly CityFunction $cityFunction) {}

    /**
     * Defines the email subject, including the function name so the expert
     * can identify the new function at a glance in their inbox.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Function Added: ' . $this->cityFunction->name,
        );
    }

    /**
     * Points to the Blade view that renders the email body.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_function_added',
        );
    }
}
