<?php

declare(strict_types=1);

use App\Http\Controllers\StopImpersonationController;
use App\Http\Middleware\SetTenantFromCustomer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/impersonation/stop', StopImpersonationController::class)
    ->middleware('auth')
    ->name('impersonation.stop');

/*
 * Customer support portal. The tenant is resolved from the signed-in customer
 * (identity-based, like the API and panels), never the host. Sign-in itself
 * (magic link) and onboarding are added in the following slices.
 */
Route::prefix('portal')->name('portal.')->group(function (): void {
    Route::view('login', 'portal.login')->name('login');

    Route::middleware(['auth:customer', SetTenantFromCustomer::class])->group(function (): void {
        Route::view('/', 'portal.home')->name('home');
    });
});
