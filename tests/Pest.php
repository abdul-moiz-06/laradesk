<?php

declare(strict_types=1);

use App\Contracts\AIServiceContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakeAIService;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Unit');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Feature tests must never hit the real Anthropic API: bind a canned AI service.
// Also stub Vite, so views that reference built assets do not need a compiled
// manifest (CI does not build front-end assets).
pest()->beforeEach(function (): void {
    $this->withoutVite();
    $this->app->bind(AIServiceContract::class, FakeAIService::class);
})->in('Feature');

/**
 * Mark a user as having completed app-based (TOTP) two-factor setup, so panel
 * requests pass the MFA-required guard in tests.
 */
function withMfa(User $user): User
{
    $user->app_authentication_secret = 'JBSWY3DPEHPK3PXP';
    $user->save();

    return $user;
}
