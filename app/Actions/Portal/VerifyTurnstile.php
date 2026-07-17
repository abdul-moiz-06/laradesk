<?php

declare(strict_types=1);

namespace App\Actions\Portal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Verifies a Cloudflare Turnstile challenge on public forms to keep bots out.
 * Config-gated: when no secret is configured (local, CI) it passes, so the
 * feature can be switched on for production without changing the flow.
 */
final class VerifyTurnstile
{
    public function __invoke(Request $request): bool
    {
        $secret = config('services.turnstile.secret');

        if (! is_string($secret) || $secret === '') {
            return true;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret,
            'response' => $request->string('cf-turnstile-response')->toString(),
            'remoteip' => $request->ip(),
        ]);

        return $response->json('success') === true;
    }
}
