<?php

declare(strict_types=1);

use App\Http\Controllers\StopImpersonationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/impersonation/stop', StopImpersonationController::class)
    ->middleware('auth')
    ->name('impersonation.stop');
