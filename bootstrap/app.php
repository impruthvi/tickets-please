<?php

use App\Exceptions\ApiExceptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: [ // <-- we can use an array here
            __DIR__ . '/../routes/api.php',
            __DIR__ . '/../routes/api_v1.php',
        ],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            $className = get_class($e);
            $handlers = ApiExceptions::$handlers;
            if (array_key_exists($className, $handlers)) {
                $method = $handlers[$className];
                return ApiExceptions::$method($e, $request);
            }

            return response()->json([
                'error' => [
                    'type' => basename(get_class($e)),
                    'status' => intval($e->getCode()),
                    'message' =>  $e->getMessage()
                ]
            ]);
        });
    })->create();
