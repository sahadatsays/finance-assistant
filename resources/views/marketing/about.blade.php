@extends('layouts.marketing')

@section('content')
    <section class="bg-gradient-to-b from-white to-surface py-16 sm:py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-heading sm:text-5xl">We believe everyone deserves clarity about their money</h1>
            <p class="mt-6 text-lg text-gray-600">Finance Assistant makes personal finance accessible, collaborative, and stress-free for individuals, households, and small teams.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-3xl space-y-12 px-4 sm:px-6 lg:px-8">
            <div>
                <h2 class="text-2xl font-bold text-heading">Our mission</h2>
                <p class="mt-4 text-gray-600">Help people understand where their money goes, make better decisions, and reach financial goals — without drowning in spreadsheets or disconnected apps.</p>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-heading">Our story</h2>
                <p class="mt-4 text-gray-600">Finance Assistant started from a simple frustration: managing money across multiple accounts, shared household expenses, and long-term goals required too many tools. We built a multi-tenant platform where every workspace — whether personal, family, or team — gets the same powerful finance toolkit with clear isolation and security.</p>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-heading">Our values</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    @foreach (['Transparency' => 'Clear pricing, honest product capabilities, and no dark patterns.', 'Privacy' => 'Tenant-isolated data with encryption and audit trails.', 'Simplicity' => 'Complex finance made approachable for everyday users.', 'Empowerment' => 'Tools that help you act, not just observe.'] as $value => $desc)
                        <div class="rounded-xl border border-gray-200 bg-white p-5">
                            <h3 class="font-semibold text-heading">{{ $value }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-marketing.cta-strip />
@endsection
