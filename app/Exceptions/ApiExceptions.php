<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponses;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptions extends Exception
{
    use ApiResponses;

    public static array $handlers = [
        AuthenticationException::class => 'handleAuthenticationException',
        ValidationException::class => 'handleValidationException',
        ModelNotFoundException::class => 'handleNotFoundException',
        NotFoundHttpException::class => 'handleNotFoundException',
    ];

    public static function handleAuthenticationException(AuthenticationException $e, Request $request): JsonResponse
    {
        // log that sensitive stuff
        // should move this out to custom logger
        $source = 'Line: ' . $e->getLine() . ', File: ' . $e->getFile();
        Log::notice(basename(get_class($e)) . ' - ' . $e->getMessage() . ' - ' . $source);

        return (new self)->error([
            'status' => 401,
            'message' => $e->getMessage(),
            'type' => basename(get_class($e))
        ], 401);
    }

    public static function handleValidationException(ValidationException $e, Request $request): JsonResponse
    {
        $errors = [];
        foreach ($e->errors() as $key => $value) {
            foreach ($value as $message) {
                $errors[] = [
                    'type' => basename(get_class($e)),
                    'status' => 422,
                    'message' => $message,
                ];
            }
        }

        return (new self)->error($errors, 422);
    }

    public static function handleNotFoundException(ModelNotFoundException|NotFoundHttpException $e, Request $request): JsonResponse
    {
        return (new self)->error([
            'status' => 404,
            'message' => 'Not Found ' . $request->getRequestUri(),
            'type' => basename(get_class($e))
        ], 404);
    }
}
