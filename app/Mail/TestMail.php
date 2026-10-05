<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        // $this->token = Password::createToken($user);
    }

    public function build()
    {
        $subject = 'This is TestMail Subject';

        $greeting = 'Hello World!';

        $introLines = ['This is TestMail Intro.'];

        $outroLines = ['This is TestMail Outro'];

        $actionText = 'This is TestMail ActionText';

        $actionUrl = url('/testmail');

        return $this->subject($subject)
            ->markdown('EmailTemplate')
            ->with([
                'greetings' => $greeting,
                'introLines' => $introLines,
                'level' => '',
                'outroLines' => $outroLines,
                'actionText' => $actionText,
                'actionUrl' => $actionUrl,
                'displayableActionUrl' => $actionUrl,
            ]);
    }
}
