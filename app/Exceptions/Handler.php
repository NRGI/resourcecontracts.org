<?php namespace App\Exceptions;


use App\Nrgi\Mail\MailQueue;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Request;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * A list of the exception types that should not send email.
     *
     * @var array
     */
    protected $dontSendEmailMessage = [
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
    ];

    /**
     * @var MailQueue
     */
    protected $mailer;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Handler constructor.
     *
     * @param MailQueue       $mailer
     * @param Container       $container
     */
    public function __construct(Container $container, MailQueue $mailer)
    {
        parent::__construct($container);
        $this->mailer = $mailer;
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     *
     * @return void
     */
    public function report(Throwable $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof HttpException) {
            return parent::render($request, $e);
        }

        if ($e instanceof TokenMismatchException) {
            return redirect()->back()->withError("Your page session has been expired. Please try again.");
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
     *
     * @return bool
     */
    public function sendErrorMessage($e)
    {
        foreach ($this->dontSendEmailMessage as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        $url = Request::fullUrl();
        $this->mailer->sendErrorEmail($e, $url);
    }
}
