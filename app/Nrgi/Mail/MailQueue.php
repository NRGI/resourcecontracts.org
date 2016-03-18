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
    public function __construct (Mailer $mailer)
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
    public function send (array $to, $subject, $view, $data = [])
    {
        return $this->mailer->queueOn(
            'queue-mail',
            $view,
            $data,
            function ($message) use ($to, $subject) {
                $message->to($to['email'], $to['name'])->subject($subject);
                if (env('NOTIFY_MAIL')) {
                    $message->bcc(env('NOTIFY_MAIL'));
                }
            }
        );
    }

    /**
     * send mail to multiple recipients
     * @param array $recipients
     * @param       $subject
     * @param       $view
     * @param array $data
     * @return int
     */
    public function sendMultiple (array $recipients, $subject, $view, $data = [])
    {
        $bcc = $this->getMailAddress();

        return $this->mailer->queueOn(
            'queue-mail',
            $view,
            $data,
            function ($message) use ($recipients, $subject, $bcc) {
                $message->to($recipients)->subject($subject);
                $message->bcc($bcc);
            }
        );
    }

    /**
     * Get multiple mail address
     *
     * @return array
     */
    public function getMailAddress ()
    {
        $multipleMail = explode(',', env('NOTIFY_MAIL'));

        return array_unique(array_filter($multipleMail));
    }
}