<?php

namespace App\Exceptions;

use App\Services\MetaDataService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof TokenMismatchException) {
            return $this->handleTokenMismatch($request);
        }

        meta()->setMeta('Error'.(($e instanceof HttpException) ? ' '.$e->getStatusCode() : ''));
        return parent::render($request, $e);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    private function handleTokenMismatch($request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response(json_encode('Your session has expired. Please refresh the page and try again.'), 401);
        }

        flash()->warning('Your session has expired. Please try again.');

        return redirect()->back()->withInput($request->except('_token'));
    }
}
