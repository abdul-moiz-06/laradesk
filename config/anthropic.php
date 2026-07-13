<?php

declare(strict_types=1);

return [
    /*
     * Anthropic API key — used to authenticate with the Claude API.
     */
    'api_key' => env('ANTHROPIC_API_KEY'),

    /*
     * The model used for ticket triage. Swap to claude-sonnet-5 for higher-quality drafts.
     */
    'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5'),

    /*
     * Maximum seconds to wait for a response.
     */
    'request_timeout' => env('ANTHROPIC_REQUEST_TIMEOUT', 30),

    /*
     * Beta feature headers sent on every request (comma-separated in ANTHROPIC_BETA).
     */
    'beta' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ANTHROPIC_BETA', ''))
    ))),
];
