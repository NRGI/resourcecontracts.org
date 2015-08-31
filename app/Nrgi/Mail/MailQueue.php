<?php
namespace App\Nrgi\Mail;

use Illuminate\Mail\Mailer;


/**
 * Class AddMailToQueue
 * @package App\Nrgi\Mail
 */
class MailQueue
{
    /**
     * @var Mailer
     */
    public $mailer;

    /**
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param array $to
     * @param       $subject
     * @param       $view
     * @param array $data
     * @return int
     */
    public function send(array $to, $subject, $view, $data = [])
    {
        return $this->mailer->queueOn(
            'queue-mail',
            $view,
            $data,
            function ($message) use ($to, $subject) {
                $message->to($to['email'], $to['name'])->subject($subject);
                $message->bcc('manoj.byanjankar@yipl.com.np', 'Manoj Byanjankar');
            }
        );
    }
}