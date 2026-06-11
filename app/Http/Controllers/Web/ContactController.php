<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        Log::info('Marketing contact form submission', $request->validated());

        return redirect()
            ->route('marketing.contact')
            ->with('success', 'Thank you for your message. We will get back to you within 24–48 hours.');
    }
}
