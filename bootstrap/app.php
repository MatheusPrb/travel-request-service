<?php

use App\Constants\Messages;
use App\Exceptions\InvalidTravelDatesException;
use App\Exceptions\NotFoundException;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (TokenExpiredException|TokenInvalidException|JWTException|AuthenticationException $e) {
            return response()->json([
                'error' => Messages::INVALID_TOKEN
            ], 401);
        });

        $exceptions->render(function (InvalidTravelDatesException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        });

        $exceptions->render(function (NotFoundException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 404);
        });
    })->create();
