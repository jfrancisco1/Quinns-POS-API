<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeOwnerMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly string $businessName,
        public readonly string $username,
        public readonly string $temporaryPassword,
        public readonly string $loginUrl,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject("Welcome to {$this->businessName}'s new POS account")
            ->view('emails.welcome-owner');
    }
}
