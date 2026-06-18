<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): Response
    {
        $loginUrl = route('login');

        if ($request instanceof Request && $request->header('X-Inertia')) {
            return Inertia::location($loginUrl);
        }

        return redirect()->to($loginUrl);
    }
}
