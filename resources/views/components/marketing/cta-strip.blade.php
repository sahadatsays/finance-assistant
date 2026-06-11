@props([
    'title' => 'Ready to manage money with confidence?',
    'subtitle' => 'Start free — no credit card required. Setup takes less than 2 minutes.',
    'primaryLabel' => 'Start Free',
    'primaryUrl' => null,
    'secondaryLabel' => 'View Pricing',
    'secondaryUrl' => null,
])

@php
    $primaryUrl ??= route('register');
    $secondaryUrl ??= route('marketing.pricing');
@endphp

<section class="bg-gradient-to-br from-brand to-brand-dark py-16 text-white">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $title }}</h2>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-white/85">{{ $subtitle }}</p>
        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ $primaryUrl }}" class="inline-flex items-center rounded-lg bg-white px-6 py-3 text-sm font-semibold text-brand shadow-sm transition hover:bg-gray-50">{{ $primaryLabel }}</a>
            <a href="{{ $secondaryUrl }}" class="inline-flex items-center rounded-lg border border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">{{ $secondaryLabel }}</a>
        </div>
    </div>
</section>
