@extends('layouts.marketing')

@section('content')
    <section class="bg-gradient-to-b from-white to-surface py-16 sm:py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-heading sm:text-5xl">Everything you need to manage money — in one place</h1>
            <p class="mt-6 text-lg text-gray-600">From daily spending to long-term goals. Built for individuals, households, and teams.</p>
            <div class="mt-8 flex flex-col justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-brand px-6 py-3 text-sm font-semibold text-white hover:bg-brand-dark">Start Free</a>
                <a href="{{ route('marketing.pricing') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold text-heading hover:border-brand hover:text-brand">Compare Plans</a>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl space-y-16 px-4 sm:px-6 lg:px-8">
            @foreach ($featureCategories as $category)
                <div id="{{ $category['id'] }}" class="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div class="{{ $loop->even ? 'lg:order-2' : '' }}">
                        <h2 class="text-2xl font-bold text-heading">{{ $category['title'] }}</h2>
                        <p class="mt-4 text-gray-600">{{ $category['description'] }}</p>
                        <ul class="mt-6 space-y-3">
                            @foreach ($category['items'] as $item)
                                <li class="flex items-center gap-3 text-sm text-gray-700">
                                    <svg class="size-5 shrink-0 text-success-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-500">
                            Available on: {{ collect($category['plans'])->map(fn ($p) => ucfirst($p))->join(', ') }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm {{ $loop->even ? 'lg:order-1' : '' }}">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-brand/10 text-brand">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <p class="mt-4 text-sm text-gray-500">Module preview</p>
                        <p class="mt-2 font-medium text-heading">{{ $category['title'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <x-marketing.cta-strip title="See it in action" subtitle="Create your free workspace and explore every feature with demo data." />
@endsection
