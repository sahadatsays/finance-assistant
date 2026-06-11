<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Dev\OneClickLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class OneClickLoginController extends Controller
{
    /**
     * Log in as a seeded development account.
     */
    public function __invoke(User $user, OneClickLoginService $oneClickLogin): RedirectResponse
    {
        abort_unless($oneClickLogin->isEnabled(), 404);
        abort_unless($oneClickLogin->isAllowedUser($user), 404);

        Auth::login($user, remember: true);

        return redirect()->intended($oneClickLogin->redirectPathFor($user));
    }
}
