<?php

declare(strict_types=1);

use App\Exceptions\InvalidCredentialsException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Unauthenticated customer-portal requests go to the portal sign-in,
        // not the (non-existent) default login route. The Filament panels keep
        // their own sign-in redirects.
        $middleware->redirectGuestsTo(fn (Request $request): ?string => $request->is('portal', 'portal/*')
            ? route('portal.login')
            : null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(fn (InvalidCredentialsException $e): JsonResponse => response()->json([
            'message' => $e->getMessage(),
            'code' => 'invalid_credentials',
        ], 401));
    })->create();
