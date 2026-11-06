<?php namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;

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
     * Report or log an exception.
     *
     * @param Throwable $e
     * @return void
     *
     * @throws Exception
     */
    public function report(Throwable $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
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
     * @param Throwable $e
     * @return bool
     */
    public function sendErrorMessage(Throwable $e): bool
    {
        foreach ($this->dontSendEmailMessage as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        // $url = Request::fullUrl();
        // $this->mailer->sendErrorEmail($e, $url);
        
        return false;
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request $request
     * @param AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
    
        return redirect()->guest('login');
    }
}
