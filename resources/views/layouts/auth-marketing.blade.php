<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.marketing.seo')
    @vite(['resources/css/app.css', 'resources/js/marketing.js'])
</head>
<body class="min-h-screen bg-surface font-sans text-heading antialiased">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <div class="relative hidden w-full flex-col justify-between bg-heading p-10 text-white lg:flex lg:w-1/2">
            <div class="absolute inset-0 bg-gradient-to-br from-brand to-brand-dark"></div>
            <div class="relative z-10">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold text-white">
                    <x-marketing.logo class="size-8 text-white" />
                    {{ config('app.name') }}
                </a>
            </div>
            <div class="relative z-10 max-w-md space-y-4">
                <p class="text-2xl font-semibold leading-snug">Take control of your money — without the spreadsheet chaos.</p>
                <p class="text-white/80">Track transactions, master budgets, and reach your savings goals in one secure workspace.</p>
            </div>
            <p class="relative z-10 text-sm text-white/60">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

        <div class="flex w-full flex-1 flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 flex items-center justify-center gap-2 lg:hidden">
                    <x-marketing.logo class="size-8 text-brand" />
                    <span class="text-lg font-semibold text-heading">{{ config('app.name') }}</span>
                </a>
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
