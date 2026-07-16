<?php

declare(strict_types=1);

namespace App\Actions\Portal;

use App\Models\Tenant;
use Illuminate\Support\Facades\URL;

/**
 * Builds a company's public support link: a signed (tamper-proof) URL to their
 * ticket-submission page. The company embeds it on their own site. The tenant
 * is identified by its domain, never an internal auto-increment id, and the
 * signature means the tenant cannot be swapped.
 */
final class GenerateSupportLink
{
    public function __invoke(Tenant $tenant): string
    {
        return URL::signedRoute('portal.submit.show', ['tenant' => $tenant->domain]);
    }
}
