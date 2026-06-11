<?php

namespace App\Listeners;

use App\Enums\LoginMethod;
use App\Models\User;
use App\Services\Auth\LoginHistoryService;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class RecordSuccessfulLogin
{
    public function __construct(
        private LoginHistoryService $loginHistory,
        private Request $request,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        if ($this->request->is('api/*')) {
            return;
        }

        $this->loginHistory->recordSuccess(
            $event->user,
            $this->request,
            LoginMethod::Password,
        );
    }
}
