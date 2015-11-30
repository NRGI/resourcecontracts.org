<?php namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
            if (env('NOTIFY_ERROR_EMAIL')) {
                $this->sendMail($e);
            }
        }

        return parent::render($request, $e);
    }

    /**
     * Sends email
     * @param $exception
     */
    protected function sendMail($exception)
    {
        $error = $exception->getMessage();
        \Mail::raw(
            (string) $exception,
            function ($msg) use ($error) {
                $recipients = [env('NOTIFY_MAIL')];
                $msg->subject("ResourceContract Admin site has error.Please check and resolve." . $error);
                $msg->to($recipients);
                $msg->from(['nrgi@yipl.com.np']);
            }
        );
    }
}
