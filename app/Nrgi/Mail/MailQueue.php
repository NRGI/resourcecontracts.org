<?php
namespace App\Nrgi\Mail;

use Exception;
use Psr\Log\LoggerInterface as Log;
use Illuminate\Mail\Mailer;
use App\Mail\MTurkNotify;
use App\Mail\MTurkBalance;
use App\Mail\EmailsProcessSuccess;
use App\Mail\EmailsProcessError;

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
            $messageVal = null;
            if($view === 'mturk.email.notify') {
                $messageVal = (new MTurkNotify($data, $from, $subject));
            } else if($view === 'mturk.email.balance') {
                $messageVal = (new MTurkBalance($data, $from, $subject));
            } else if($view === 'emails.process_success') {
                $messageVal = (new EmailsProcessSuccess($data, $from, $subject));
            } else if($view === 'emails.process_error') {
                $messageVal = (new EmailsProcessError($data, $from, $subject));
            }
            if($messageVal) {
                $messageVal = $messageVal->onQueue('queue-mail');
            }
            return $this->mailer->to($recipients)->queue($messageVal);
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
        $request    = json_encode(['request' => $_REQUEST, 'server' => $_SERVER]);
        $body       = sprintf(
            "Url: %s \n\rRequest: %s \n\rError: %s \n\rLog: %s",
            $current_url,
            $request,
            $error,
            (string) $exception
        );
        $from       = $this->getFromEmail();
        $this->mailer->send([], [],
            function ($message) use ($recipients, $subject, $from, $body) {
                $message->subject($subject);
                $message->to($recipients);
                $message->from($from);
                $message->setBody($body);
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
