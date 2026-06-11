<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\SessionResource;
use App\Services\Auth\SessionManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function __construct(
        private SessionManagementService $sessions,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('settings/sessions', [
            'sessions' => SessionResource::collection(
                $this->sessions->listSessions($request->user(), $request),
            )->resolve(),
        ]);
    }

    public function destroy(Request $request, string $session): RedirectResponse
    {
        if (! $this->sessions->revokeSession($request->user(), $session, $request)) {
            return back()->withErrors([
                'session' => __('Unable to revoke session.'),
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session revoked.')]);

        return to_route('sessions.index');
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        $this->sessions->revokeOtherSessions($request->user(), $request);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Other sessions revoked.')]);

        return to_route('sessions.index');
    }
}
