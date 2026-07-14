<?php

declare(strict_types=1);

// Foundational rules — active now, apply to the whole app.

arch('no debugging statements are committed')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('the application declares strict types everywhere')
    ->expect('App')
    ->toUseStrictTypes();

arch('php best practices')
    ->preset()
    ->php();

arch('security best practices')
    ->preset()
    ->security();

// Layering rules — activated as each namespace comes into existence.

arch('actions are invokable single-responsibility classes')
    ->expect('App\Actions')
    ->toBeInvokable();

arch('DTOs are readonly and immutable')
    ->expect('App\DTOs')
    ->toBeReadonly();

arch('models never depend on services')
    ->expect('App\Models')
    ->not->toUse('App\Services');

arch('controllers stay thin — no direct DB access')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB');
