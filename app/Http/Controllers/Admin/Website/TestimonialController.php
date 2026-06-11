<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Models\Platform\Testimonial;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): Response
    {
        return Inertia::render('admin/website/testimonials/index', [
            'testimonials' => Testimonial::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quote' => ['required', 'string', 'max:2000'],
            'author_name' => ['required', 'string', 'max:255'],
            'author_role' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $testimonial = Testimonial::query()->create($validated);

        $this->activityLog->log('Testimonial was created.', subject: $testimonial, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Testimonial created.')]);

        return to_route('admin.website.testimonials.index');
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'quote' => ['sometimes', 'required', 'string', 'max:2000'],
            'author_name' => ['sometimes', 'required', 'string', 'max:255'],
            'author_role' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $testimonial->update($validated);

        $this->activityLog->log('Testimonial was updated.', subject: $testimonial, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Testimonial updated.')]);

        return to_route('admin.website.testimonials.index');
    }

    public function destroy(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        $this->activityLog->log('Testimonial was deleted.', causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Testimonial deleted.')]);

        return to_route('admin.website.testimonials.index');
    }
}
