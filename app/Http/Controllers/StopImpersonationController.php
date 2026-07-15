<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Impersonation\StopImpersonation;
use Illuminate\Http\RedirectResponse;

/**
 * Ends the current impersonation session (from the panel banner) and returns
 * the SuperAdmin to the platform panel.
 */
final class StopImpersonationController extends Controller
{
    public function __invoke(StopImpersonation $stop): RedirectResponse
    {
        $stop();

        return redirect()->to('/super');
    }
}
