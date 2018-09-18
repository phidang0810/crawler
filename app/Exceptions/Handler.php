<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if($request->is('api/*')){
            return $this->renderForApi($request, $exception);
        }

        return parent::render($request, $exception);
    }

    private function renderForApi($request, $exception, $status = 400)
    {
        if ($this->isHttpException($exception)) {
            $status = $exception->getStatusCode();
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return response()->json(['errors' => $exception->validator->errors()->all()]);
        }

        return response()->json(['error' => $exception->getMessage()], $status);
    }
}
