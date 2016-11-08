<?php
namespace App\Nrgi\Mail;

use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Mail\Mailer;

/**
 * Class Mail Queue Manager
 * @package App\Nrgi\Mail
 */
class MailQueue
{
    /**
     * @var Mailer
     */
    public $mailer;
    /**
     * @var Log
     */
    private $log;

    /**
     * @param Mailer $mailer
     * @param Log    $log
     */
    public function __construct(Mailer $mailer, Log $log)
    {
        $this->mailer = $mailer;
        $this->log    = $log;
    }

    /**
     * Get Notify Email IDs.
     *
     * @return array
     */
    public function getNotifyEmails()
    {
        return $this->getEmailsFromEnv('NOTIFY_MAIL');
    }

    /**
     * Get MTurk Notification Email IDs.
     *
     * @param $category
     *
     * @return array
     */
    public function getMTurkNotifyEmails($category)
    {
        $key = 'MTURK_NOTIFY_'.strtoupper($category);

        return $this->getEmailsFromEnv($key);
    }

    /**
     * Get Notification emails for Error.
     *
     * @return array
     */
    public function getNotifyErrorEmails()
    {
        return $this->getEmailsFromEnv('NOTIFY_ERROR_EMAIL');
    }

    /**
     * Get Emails from .env
     *
     * @param $key
     *
     * @return array
     */
    public function getEmailsFromEnv($key)
    {
        $emails = env($key);

        if (empty($emails)) {
            return [];
        }

        $emails = explode(',', $emails);
        $emails = array_unique(array_filter($emails));

        return array_map('trim', $emails);
    }

    /**
     * Send Email
     *
     * @param       $recipients
     * @param       $subject
     * @param       $view
     * @param array $data
     *
     * @return int
     */
    public function send($recipients, $subject, $view, $data = [])
    {
        if (is_string($recipients)) {
            $recipients = explode(',', $recipients);
            $recipients = array_map('trim', $recipients);
            $recipients = array_unique(array_filter($recipients));
        }

        $bcc  = array_diff($this->getNotifyEmails(), $recipients);
        $from = $this->getFromEmail();

        try {
            return $this->mailer->queueOn(
                'queue-mail',
                $view,
                $data,
                function ($message) use ($recipients, $subject, $from, $bcc) {
                    $message->to($recipients);
                    $message->subject($subject);
                    $message->from($from);

                    if (!empty($bcc)) {
                        $message->bcc($bcc);
                    }
                }
            );
        } catch (Exception $e) {
            $data = [
                'to'      => $recipients,
                'subject' => $subject,
                'bcc'     => $bcc,
            ];
            $this->log->error('Error while sending email:'.$e->getMessage(), $data);

            return false;
        }
    }

    /**
     * Send Error Message
     *
     * @param $exception
     * @param $current_url
     */
    public function sendErrorEmail($exception, $current_url)
    {
        $recipients = $this->getNotifyErrorEmails();
        $subject    = "ResourceContract Admin site has error - ".$current_url;
        $error      = $exception->getMessage();
        $body       = sprintf("Url: %s \n\rError: %s \n\rLog: %s", $current_url, $error, (string) $exception);
        $from       = $this->getFromEmail();
        $this->mailer->raw(
            $body,
            function ($message) use ($recipients, $subject, $from) {
                $message->subject($subject);
                $message->to($recipients);
                $message->from($from);
            }
        );
    }

    /**
     * Get From Email
     *
     * @return array
     */
    protected function getFromEmail()
    {
        return env('FROM_EMAIL');
    }

}
