<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Auth\LoginHistoryService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;

class RecordFailedLoginAttempt
{
    public function __construct(
        private LoginHistoryService $loginHistory,
        private Request $request,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;
        $email = is_string($event->credentials['email'] ?? null)
            ? $event->credentials['email']
            : null;

        $this->loginHistory->recordFailure(
            $this->request,
            $user,
            $email,
            'Invalid credentials.',
        );
    }
}
