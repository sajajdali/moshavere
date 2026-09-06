<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'voip_secret',
        'sip_secret',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof NotFoundHttpException && !$request->expectsJson()) {
            return response()->view('front::errors.404', [], 404);
        }

        return parent::render($request, $exception);
    }
}
