<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Portal\VerifyTurnstile;
use App\Actions\Tenants\RegisterCompany;
use App\Http\Requests\CompanySignupRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * The public marketing landing and self-serve company sign-up.
 */
final class CompanySignupController extends Controller
{
    public function landing(): View
    {
        return view('welcome');
    }

    public function show(): View
    {
        return view('signup');
    }

    public function store(
        CompanySignupRequest $request,
        VerifyTurnstile $verifyTurnstile,
        RegisterCompany $registerCompany,
    ): RedirectResponse {
        if (! $verifyTurnstile($request)) {
            return back()->withErrors(['email' => 'We could not verify that you are human. Please try again.'])->withInput();
        }

        $tenant = $registerCompany(
            $request->string('company_name')->toString(),
            $request->string('name')->toString(),
            $request->string('email')->toString(),
        );

        if ($tenant === null) {
            return back()->withErrors(['email' => 'This email is already registered.'])->withInput();
        }

        return redirect()->route('signup.thanks');
    }
}
