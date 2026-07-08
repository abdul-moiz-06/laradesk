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

// Layering rules — uncomment each as the corresponding namespace is created.
// These enforce the architecture defined in CLAUDE.md.
//
// arch('controllers stay thin — no direct DB access')
//     ->expect('App\Http\Controllers')
//     ->not->toUse('Illuminate\Support\Facades\DB');
//
// arch('actions are invokable single-responsibility classes')
//     ->expect('App\Actions')
//     ->toBeClasses();
//
// arch('models hold no business logic — never depend on services')
//     ->expect('App\Models')
//     ->not->toUse('App\Services');
//
// arch('DTOs are readonly and immutable')
//     ->expect('App\DTOs')
//     ->toBeReadonly();
