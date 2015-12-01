<?php namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Request;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
    ];

    /**
     * A list of the exception types that should not send email.
     *
     * @var array
     */
    protected $dontSendEmailMessage = [
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException'
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof HttpException) {
            return parent::render($request, $e);
        }

        if (env('APP_ENV') === 'production') {
            $this->sendErrorMessage($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Send Error message in email
     *
     * @param $e
     * @return bool
     */
    public function sendErrorMessage($e)
    {
        foreach ($this->dontSendEmailMessage as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        if (env('NOTIFY_ERROR_EMAIL')) {
            $this->sendMail($e);
        }
    }

    /**
     * Sends email
     * @param $exception
     */
    protected function sendMail($exception)
    {
        $error = $exception->getMessage();

        $current_url = Request::fullUrl();

        $message = sprintf("Url: %s \n\rError: %s \n\rLog: %s",  $current_url, $error, (string) $exception);

        \Mail::raw(
              $message  ,
            function ($msg) use ($current_url) {
                $recipients = [env('NOTIFY_ERROR_EMAIL')];
                $msg->subject("ResourceContract Admin site has error - " . $current_url);
                $msg->to($recipients);
                $msg->from(['nrgi@yipl.com.np']);
            }
        );
    }
}
