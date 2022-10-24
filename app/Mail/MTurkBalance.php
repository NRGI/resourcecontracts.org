<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MTurkBalance extends Mailable
{
    use Queueable, SerializesModels;

    var $balance;
    var $contract;
    var $fromEmail;
    var $emailSubject;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $mailData, string $from, string $subject)
    {
        //
        $this->balance = $mailData['balance'];
        $this->fromEmail = $from;
        $this->emailSubject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->fromEmail)->subject($this->emailSubject)->view('mturk.email.balance');
    }
}
