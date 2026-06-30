<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

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
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 401,
                    'success' => false,
                    'message' => 'Unauthenticated! token expired.'
                ], 401);
            }
        });
    }

    public function render($request, Throwable $exception)
    {

        if ($request->expectsJson()) {
            if ($exception instanceof ModelNotFoundException) {
                return resp(0,'Record not found.');
            }

            if ($exception instanceof QueryException) {
                return resp(0,'Database error occurred',$exception->getMessage(),Response::HTTP_SERVICE_UNAVAILABLE);
            }

            if ($exception instanceof PermissionAlreadyExists) {
                return resp(0,'A permission with this name already exists.',$exception->getMessage(),Response::HTTP_SERVICE_UNAVAILABLE);
            }
        }

        if ($exception instanceof AuthorizationException) {
            return resp(0,'You do not have required authorization.',[],Response::HTTP_FORBIDDEN);
        }

        if ($exception instanceof HttpException && $exception->getStatusCode() === 403) {
            $data = [
                'message' => $exception->getMessage(),
                'error' => 'Forbidden',
            ];
            return resp(0, $exception->getMessage(), $data,Response::HTTP_FORBIDDEN);
        }

        return parent::render($request, $exception);
    }
}
