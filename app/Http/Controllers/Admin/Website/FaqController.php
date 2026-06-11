<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Models\Platform\Faq;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): Response
    {
        return Inertia::render('admin/website/faqs/index', [
            'faqs' => Faq::query()->orderBy('category')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:5000'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $faq = Faq::query()->create($validated);

        $this->activityLog->log('FAQ was created.', subject: $faq, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('FAQ created.')]);

        return to_route('admin.website.faqs.index');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'question' => ['sometimes', 'required', 'string', 'max:500'],
            'answer' => ['sometimes', 'required', 'string', 'max:5000'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $faq->update($validated);

        $this->activityLog->log('FAQ was updated.', subject: $faq, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('FAQ updated.')]);

        return to_route('admin.website.faqs.index');
    }

    public function destroy(Request $request, Faq $faq): RedirectResponse
    {
        $faq->delete();

        $this->activityLog->log('FAQ was deleted.', causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('FAQ deleted.')]);

        return to_route('admin.website.faqs.index');
    }
}
