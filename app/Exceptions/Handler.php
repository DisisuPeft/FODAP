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
     * Report or log an exception.
     *
     * @param Throwable $e
     * @return void
     *
     * @throws Exception|Throwable
     */
    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param Throwable $e
     * @return JsonResponse
     */
    public function render($request, Throwable $e): JsonResponse
    {
        // Obtener el código de estado de la excepción
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        // Devolver siempre una respuesta JSON con el mensaje de error y el código de estado
        return response()->json([
            'message' => $e->getMessage(),
            'status' => $status,
        ], $status);
    }
}
