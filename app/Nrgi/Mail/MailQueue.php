<?php
namespace App\Nrgi\Mail;

use Exception;
use Psr\Log\LoggerInterface as Log;
use Illuminate\Support\Facades\Mail;
// use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class Mail Queue Manager
 * @package App\Nrgi\Mail
 */
class MailQueue 
{
    use Queueable;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param Log    $log
     */
    public function __construct(Log $log)
    {
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
            return Mail::send($view, $data, function ($message) use ($recipients, $subject, $from, $bcc) {
                $message->to($recipients);
                $message->subject($subject);
                $message->from($from);

                if (!empty($bcc)) {
                    $message->bcc($bcc);
                }
            });

            // $this->mailable->queueOn(
            //     'queue-mail',
            //     $view,
            //     $data,
            //     function ($message) use ($recipients, $subject, $from, $bcc) {
            //         $message->to($recipients);
            //         $message->subject($subject);
            //         $message->from($from);

            //         if (!empty($bcc)) {
            //             $message->bcc($bcc);
            //         }
            //     }
            // );
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
        $this->mailable->raw(
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
