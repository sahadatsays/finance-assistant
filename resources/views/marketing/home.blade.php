@extends('layouts.marketing')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-b from-white to-surface py-16 sm:py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-brand">Multi-tenant personal finance platform</p>
                <h1 class="mt-4 text-4xl font-bold tracking-tight text-heading sm:text-5xl">Take control of your money — without the spreadsheet chaos</h1>
                <p class="mt-6 text-lg text-gray-600">Track transactions, master budgets, reach savings goals, and understand your net worth. Free for individuals. Built for households and teams.</p>
                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-brand px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark">Start Free</a>
                    <a href="{{ route('marketing.pricing') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-heading transition hover:border-brand hover:text-brand">View Pricing</a>
                </div>
                <p class="mt-4 text-sm text-gray-500">Free forever plan &middot; No credit card &middot; Setup in 2 minutes</p>
            </div>
            <div class="relative">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Dashboard overview</span>
                        <span class="rounded-full bg-success-brand/10 px-2.5 py-0.5 text-xs font-medium text-success-brand">Live</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl bg-surface p-4">
                            <p class="text-xs text-gray-500">Net worth</p>
                            <p class="mt-1 text-2xl font-bold text-heading">$24,580</p>
                        </div>
                        <div class="rounded-xl bg-surface p-4">
                            <p class="text-xs text-gray-500">Monthly savings</p>
                            <p class="mt-1 text-2xl font-bold text-success-brand">+12%</p>
                        </div>
                        <div class="col-span-2 rounded-xl bg-surface p-4">
                            <p class="text-xs text-gray-500">Budget utilization</p>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200">
                                <div class="h-full w-3/5 rounded-full bg-brand"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.trust-badges />

    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-heading">Why Finance Assistant?</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600">Scattered accounts and guesswork end here. One workspace for clarity, control, and confidence.</p>
            </div>
            <div class="mt-12 grid gap-8 sm:grid-cols-3">
                @foreach ([
                    ['title' => 'See everything', 'desc' => 'Accounts, transactions, and net worth in one real-time dashboard.', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['title' => 'Stay on budget', 'desc' => 'Monthly budgets with alerts before you overspend.', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['title' => 'Reach goals faster', 'desc' => 'Savings goals with progress tracking and completion forecasts.', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                ] as $benefit)
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $benefit['icon'] }}"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-heading">{{ $benefit['title'] }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $benefit['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-20" x-data="{ tab: 'dashboard' }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-heading">Built for how you manage money</h2>
                <p class="mx-auto mt-4 max-w-2xl text-gray-600">From daily spending to long-term goals — explore the modules that power your workspace.</p>
            </div>
            <div class="mt-8 flex flex-wrap justify-center gap-2">
                @foreach (['dashboard' => 'Dashboard', 'transactions' => 'Transactions', 'budgets' => 'Budgets', 'goals' => 'Goals'] as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-brand text-white' : 'bg-gray-100 text-gray-600'" class="rounded-lg px-4 py-2 text-sm font-medium transition">{{ $label }}</button>
                @endforeach
            </div>
            <div class="mx-auto mt-10 max-w-3xl rounded-2xl border border-gray-200 bg-surface p-8 text-center">
                <template x-if="tab === 'dashboard'"><p class="text-gray-600">Real-time income, expense, and net worth metrics with interactive charts and tenant workspace switching.</p></template>
                <template x-if="tab === 'transactions'"><p class="text-gray-600">Track income, expenses, and transfers with categories, tags, and receipt attachments.</p></template>
                <template x-if="tab === 'budgets'"><p class="text-gray-600">Set monthly budgets, monitor utilization, and get alerts before overspending.</p></template>
                <template x-if="tab === 'goals'"><p class="text-gray-600">Create savings goals, add contributions, and see forecasted completion dates.</p></template>
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-3xl font-bold text-heading">Loved by individuals, households, and teams</h2>
            <div class="mt-12 grid gap-8 sm:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <blockquote class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-gray-600">&ldquo;{{ $testimonial['quote'] }}&rdquo;</p>
                        <footer class="mt-4">
                            <p class="text-sm font-semibold text-heading">{{ $testimonial['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $testimonial['role'] }}</p>
                        </footer>
                    </blockquote>
                @endforeach
            </div>
        </div>
    </section>

    @if ($plans->isNotEmpty())
        <section class="bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-heading">Simple plans that grow with you</h2>
                <div class="mt-12 grid gap-8 sm:grid-cols-3">
                    @foreach ($plans as $plan)
                        <div class="rounded-2xl border border-gray-200 p-6 {{ $plan->slug === 'pro' ? 'border-brand ring-2 ring-brand/20' : '' }}">
                            <h3 class="text-lg font-semibold text-heading">{{ $plan->name }}</h3>
                            <p class="mt-2 text-3xl font-bold text-heading">${{ number_format($plan->price_monthly, 2) }}<span class="text-sm font-normal text-gray-500">/mo</span></p>
                            <p class="mt-2 text-sm text-gray-600">{{ $plan->description }}</p>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('marketing.pricing') }}" class="mt-8 inline-flex items-center text-sm font-semibold text-brand hover:text-brand-dark">Compare all features &rarr;</a>
            </div>
        </section>
    @endif

    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-heading">Get started in 3 steps</h2>
            <ol class="mt-10 space-y-6 text-left">
                @foreach (['Create your free account', 'Add accounts and transactions', 'Set budgets and watch your progress'] as $index => $step)
                    <li class="flex items-start gap-4">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand text-sm font-bold text-white">{{ $index + 1 }}</span>
                        <span class="pt-1 text-gray-700">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <x-marketing.cta-strip />
@endsection
