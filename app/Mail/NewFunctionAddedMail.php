<?php

namespace App\Mail;

use App\Models\CityFunction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewFunctionAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly CityFunction $cityFunction) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Function Added: ' . $this->cityFunction->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_function_added',
        );
    }
}
