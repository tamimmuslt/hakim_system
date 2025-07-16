<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;  

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('مرحبا بك في التطبيق')
                    ->view('emails.welcome'); 
    }
}
