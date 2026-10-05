<?php

namespace App\Http\Controllers;

use App\Mail\FirstTimeMail;
use App\Mail\TestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendMail()
    {
        $email='your@email.com';

        Mail::to($email)->send(new FirstTimeMail($email));

        return view('TestMail');
    }
}
