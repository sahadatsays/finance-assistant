@extends('layouts.marketing')

@section('content')
    <section class="bg-gradient-to-b from-white to-surface py-16 sm:py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-heading sm:text-5xl">Simple pricing that grows with you</h1>
            <p class="mt-6 text-lg text-gray-600">Start free. Upgrade when you need budgets, exports, or team features.</p>
        </div>
    </section>

    <section class="pb-16 sm:pb-20">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:grid-cols-3 sm:px-6 lg:px-8">
            @foreach ($plans as $plan)
                @php
                    $isPro = $plan->slug === 'pro';
                    $ctaUrl = $plan->slug === 'business' ? route('marketing.contact') : route('register', $plan->slug !== 'free' ? ['plan' => $plan->slug] : []);
                    $ctaLabel = match ($plan->slug) {
                        'free' => 'Start Free',
                        'pro' => 'Start Pro',
                        default => 'Contact Sales',
                    };
                @endphp
                <div class="relative flex flex-col rounded-2xl border {{ $isPro ? 'border-brand shadow-lg ring-2 ring-brand/20' : 'border-gray-200' }} bg-white p-8">
                    @if ($isPro)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-brand px-3 py-1 text-xs font-semibold text-white">Most popular</span>
                    @endif
                    <h2 class="text-xl font-semibold text-heading">{{ $plan->name }}</h2>
                    <p class="mt-4 text-4xl font-bold text-heading">${{ number_format($plan->price_monthly, 2) }}<span class="text-base font-normal text-gray-500">/mo</span></p>
                    <p class="mt-2 text-sm text-gray-600">{{ $plan->description }}</p>
                    <p class="mt-4 text-sm font-medium text-gray-700">Up to {{ $plan->max_users }} {{ Str::plural('user', $plan->max_users) }}</p>
                    <ul class="mt-6 flex-1 space-y-3">
                        @foreach ($plan->features ?? [] as $feature)
                            <li class="flex items-center gap-2 text-sm text-gray-700">
                                <svg class="size-4 shrink-0 text-success-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $featureLabels[$feature] ?? Str::headline(str_replace('_', ' ', $feature)) }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $ctaUrl }}" class="mt-8 inline-flex items-center justify-center rounded-lg {{ $isPro ? 'bg-brand text-white hover:bg-brand-dark' : 'border border-gray-300 text-heading hover:border-brand hover:text-brand' }} px-4 py-2.5 text-sm font-semibold transition">{{ $ctaLabel }}</a>
                </div>
            @endforeach
        </div>
    </section>

    @if ($plans->isNotEmpty())
        <section class="border-t border-gray-200 bg-white py-16">
            <div class="mx-auto max-w-5xl overflow-x-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-heading">Feature comparison</h2>
                <table class="mt-8 w-full min-w-[600px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="py-3 pr-4 font-semibold text-heading">Feature</th>
                            @foreach ($plans as $plan)
                                <th class="px-4 py-3 text-center font-semibold text-heading">{{ $plan->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($featureLabels as $key => $label)
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-gray-700">{{ $label }}</td>
                                @foreach ($plans as $plan)
                                    <td class="px-4 py-3 text-center">
                                        @if (in_array($key, $plan->features ?? [], true))
                                            <span class="text-success-brand">&#10003;</span>
                                        @else
                                            <span class="text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="py-16 sm:py-20" x-data="{ open: null }">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-2xl font-bold text-heading">Frequently asked questions</h2>
            <div class="mt-10 space-y-4">
                @foreach ($faq as $index => $item)
                    <div class="rounded-xl border border-gray-200 bg-white">
                        <button type="button" @click="open = open === {{ $index }} ? null : {{ $index }}" class="flex w-full items-center justify-between px-5 py-4 text-left text-sm font-semibold text-heading">
                            {{ $item->question ?? $item['question'] }}
                            <svg class="size-5 shrink-0 text-gray-400 transition" :class="open === {{ $index }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === {{ $index }}" x-cloak class="border-t border-gray-100 px-5 py-4 text-sm text-gray-600">
                            {{ $item->answer ?? $item['answer'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <x-marketing.cta-strip />
@endsection
