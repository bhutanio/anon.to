<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {
        if (!($exception instanceof ValidationException) && $request->expectsJson()) {
            return $this->respondJsonException($request, $exception);
        }

        if ($exception instanceof TokenMismatchException) {
            return $this->handleTokenMismatch($request, $exception);
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    private function respondJsonException($request, $exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            return response()->json($exception->getMessage(), $exception->getStatusCode());
        }

        if ($exception instanceof AuthorizationException) {
            return response()->json('Unauthorized Action', 403);
        }

        return response()->json('Internal Server Error', 500);
    }

    private function handleTokenMismatch($request, TokenMismatchException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json('Your session has expired. Please try again.', 401);
        }

        flash('Your session has expired. Please try again.', 'warning');

        return redirect()->back()->withInput($request->except('_token'));
    }
}
