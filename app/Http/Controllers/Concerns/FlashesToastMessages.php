<?php

namespace App\Http\Controllers\Concerns;

use Inertia\Inertia;

trait FlashesToastMessages
{
    protected function flashSuccess(string $message): void
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);
    }

    protected function flashError(string $message): void
    {
        Inertia::flash('toast', ['type' => 'error', 'message' => $message]);
    }
}
