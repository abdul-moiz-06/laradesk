<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Actions\Portal\SubmitPortalTicket;
use App\Actions\Portal\VerifyTurnstile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\SubmitTicketRequest;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * The public, unauthenticated ticket-submission flow reached from a company's
 * signed support link. The company is already the current tenant (set by the
 * signed-route middleware), so a submitted ticket is scoped to it.
 */
final class SubmitTicketController extends Controller
{
    public function show(Tenant $tenant): View
    {
        return view('portal.submit', ['tenant' => $tenant]);
    }

    public function store(
        SubmitTicketRequest $request,
        Tenant $tenant,
        VerifyTurnstile $verifyTurnstile,
        SubmitPortalTicket $submitTicket,
    ): RedirectResponse {
        if (! $verifyTurnstile($request)) {
            return back()->withErrors(['email' => 'We could not verify that you are human. Please try again.'])->withInput();
        }

        $accepted = $submitTicket(
            $tenant,
            $request->string('name')->toString(),
            $request->string('email')->toString(),
            $request->string('subject')->toString(),
            $request->string('message')->toString(),
        );

        if (! $accepted) {
            return back()->withErrors(['email' => 'This email is already in use.'])->withInput();
        }

        return redirect()->route('portal.submitted');
    }
}
