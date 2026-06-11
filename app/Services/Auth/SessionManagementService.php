<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SessionManagementService
{
    /**
     * @return Collection<int, object{id: string, ip_address: string|null, user_agent: string|null, last_activity: int, is_current: bool}>
     */
    public function listSessions(User $user, Request $request): Collection
    {
        $currentSessionId = $request->hasSession() ? $request->session()->getId() : null;

        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn (object $session) => (object) [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => $session->last_activity,
                'is_current' => $session->id === $currentSessionId,
            ]);
    }

    /**
     * Revoke a specific web session.
     */
    public function revokeSession(User $user, string $sessionId, Request $request): bool
    {
        if ($request->hasSession() && $sessionId === $request->session()->getId()) {
            return false;
        }

        $deleted = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', $sessionId)
            ->delete();

        if ($deleted) {
            UserDevice::query()
                ->where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->delete();
        }

        return (bool) $deleted;
    }

    /**
     * Revoke all sessions except the current one.
     */
    public function revokeOtherSessions(User $user, Request $request): int
    {
        $currentSessionId = $request->hasSession() ? $request->session()->getId() : null;

        $query = DB::table('sessions')->where('user_id', $user->id);

        if ($currentSessionId !== null) {
            $query->where('id', '!=', $currentSessionId);
        }

        $sessionIds = $query->pluck('id');
        $deleted = $query->delete();

        if ($sessionIds->isNotEmpty()) {
            UserDevice::query()
                ->where('user_id', $user->id)
                ->whereIn('session_id', $sessionIds)
                ->delete();
        }

        $this->revokeOtherTokens($user, $request);

        return $deleted;
    }

    /**
     * Revoke all API tokens except the current bearer token.
     */
    public function revokeOtherTokens(User $user, Request $request): int
    {
        $currentToken = $user->currentAccessToken();

        $query = $user->tokens();

        if ($currentToken !== null) {
            $query->where('id', '!=', $currentToken->id);
        }

        $tokenIds = $query->pluck('id');
        $deleted = $query->delete();

        if ($tokenIds->isNotEmpty()) {
            UserDevice::query()
                ->where('user_id', $user->id)
                ->whereIn('token_id', $tokenIds)
                ->delete();
        }

        return $deleted;
    }
}
