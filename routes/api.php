<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\TicketReplyController;
use App\Http\Middleware\SetTenantFromToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Public: exchange credentials for a bearer token.
    Route::post('tokens', [AuthTokenController::class, 'store'])->middleware('throttle:api');

    // Authenticated: tenant is resolved from the token's user, not the host.
    Route::middleware(['auth:sanctum', SetTenantFromToken::class, 'throttle:api'])->group(function (): void {
        Route::delete('tokens', [AuthTokenController::class, 'destroy']);

        Route::apiResource('tickets', TicketController::class)->only(['index', 'store', 'show']);
        Route::post('tickets/{ticket}/replies', [TicketReplyController::class, 'store']);
    });
});
