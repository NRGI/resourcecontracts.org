<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailsProcessSuccess extends Mailable
{
    use Queueable, SerializesModels;
    var $contract_title;
    var $contract_id;
    var $contract_detail_url;
    var $start_time;
    var $end_time;
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
        $this->contract_title = $mailData['contract_title'];
        $this->contract_id = $mailData['contract_id'];
        $this->contract_detail_url = $mailData['contract_detail_url'];
        $this->start_time = $mailData['start_time'];
        $this->end_time = $mailData['end_time'];
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
        return $this->from($this->fromEmail)->subject($this->emailSubject)->view('emails.process_success');
    }
}
