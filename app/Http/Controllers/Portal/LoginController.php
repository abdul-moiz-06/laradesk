<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Actions\Portal\ConsumeLoginLink;
use App\Actions\Portal\RequestLoginLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\RequestLoginLinkRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Passwordless (magic-link) sign-in for the customer portal.
 */
final class LoginController extends Controller
{
    public function show(): View
    {
        return view('portal.login');
    }

    public function store(RequestLoginLinkRequest $request, RequestLoginLink $requestLink): RedirectResponse
    {
        $requestLink($request->string('email')->toString());

        // Always the same message, whether or not an account exists, so the
        // portal never reveals which emails are registered.
        return back()->with('status', 'If an account exists for that email, a sign-in link is on its way.');
    }

    public function verify(Request $request, string $token, ConsumeLoginLink $consumeLink): RedirectResponse
    {
        $customer = $consumeLink($token);

        if ($customer === null) {
            return redirect()
                ->route('portal.login')
                ->withErrors(['email' => 'This sign-in link is invalid or has expired.']);
        }

        $request->session()->regenerate();

        return redirect()->route('portal.home');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
