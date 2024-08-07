<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
//
//    /**
//     * Register the exception handling callbacks for the application.
//     */
//    public function register(): void
//    {
//        $this->reportable(function (Throwable $e) {
//            $status = $e->getStatusCode() ?? 500;
//
//            return response()->json([
//                'message' => $e->getMessage(),
//                'status' => $status,
//            ], $status);
//        });
//    }


    /**
     * Register the exception handling callbacks for the application.
     * Render an exception into an HTTP response.
     *
     * @return void
     */
    public function register(): void {

        $this->reportable(function (Throwable $e) {
            return $e->getMessage();
        });

    }
}
