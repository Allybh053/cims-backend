<?php

namespace App\Mail;

use Dotenv\Dotenv;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FirstTimeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
     public $token;

    public function __construct($user, $token)
    {
        // $this->token = Password::createToken($user);
        $this->user = $user;
        $this->token = $token;
    }

    public function build()
    {
        $subject = 'Welcome to CIMS';

        $greetings = 'Hello, '.$this->user->name;

        $introLines = ['We are happy you are here. You are receiving this email because you are first time user email. Please click on button below to change your password.'];

        $actionText = 'Reset Password';

        $actionUrl = env('FRONTEND_URL').'/auth/reset-password?email=' . $this->user->email . '&token=' . $this->token;

        $outroLines = [''];

        $salutation = 'Thanks';

        $signature = 'The CIMS Team';


        return $this->subject($subject)
            ->markdown('EmailTemplate')
            ->with([
                'greetings' => $greetings,
                'introLines' => $introLines,
                'level' => 'error',
                'actionText' => $actionText,
                'actionUrl' => $actionUrl,
                'outroLines' => $outroLines,
                'salutation' => $salutation,
                'signature' => $signature,
                'displayableActionUrl' => $actionUrl,
            ]);
    }
}
