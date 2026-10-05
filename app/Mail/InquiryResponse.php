<?php

namespace App\Mail;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryResponse extends Mailable
{
    use Queueable, SerializesModels;

    private Inquiry $inquiry;
    private User $sender;
    private User $receiver;

    /**
     * Create a new message instance.
     */
    public function __construct(User $sender, User $receiver, Inquiry $inquiry)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->inquiry = $inquiry;
    }

    public function build(): InquiryResponse
    {
        return $this->subject($this->inquiry->title)
            ->markdown('InquiryResponseEmail')
            ->with([
                'body' => $this->inquiry->answer,
                'sender' => $this->sender,
            ]);
    }
}
