<?php

declare(strict_types=1);

use App\Http\Controllers\Portal\LoginController;
use App\Http\Controllers\Portal\SubmitTicketController;
use App\Http\Controllers\StopImpersonationController;
use App\Http\Middleware\SetTenantFromCustomer;
use App\Http\Middleware\SetTenantFromSignedRoute;
use App\Livewire\Portal\TicketList;
use App\Livewire\Portal\ViewTicket;
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
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])
        ->middleware('throttle:portal-login')
        ->name('login.store');
    Route::get('login/verify/{token}', [LoginController::class, 'verify'])->name('login.verify');
    Route::post('logout', [LoginController::class, 'destroy'])
        ->middleware('auth:customer')
        ->name('logout');

    // Public ticket submission from a company's signed support link. The tenant
    // is carried (by domain) in the tamper-proof signed URL.
    Route::view('submitted', 'portal.submitted')->name('submitted');
    Route::middleware(['signed', SetTenantFromSignedRoute::class])->group(function (): void {
        Route::get('submit/{tenant:domain}', [SubmitTicketController::class, 'show'])->name('submit.show');
        Route::post('submit/{tenant:domain}', [SubmitTicketController::class, 'store'])->name('submit.store');
    });

    Route::middleware(['auth:customer', SetTenantFromCustomer::class])->group(function (): void {
        Route::get('/', TicketList::class)->name('home');
        Route::get('tickets/{ulid}', ViewTicket::class)->name('tickets.show');
    });
});
