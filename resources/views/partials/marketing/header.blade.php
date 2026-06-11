<header
    x-data="{ open: false }"
    class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/95 backdrop-blur"
>
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold text-heading">
            <x-marketing.logo class="size-8 text-brand" />
            {{ config('app.name') }}
        </a>

        <nav class="hidden items-center gap-8 lg:flex">
            <a href="{{ route('marketing.features') }}" class="text-sm font-medium text-gray-600 hover:text-brand {{ request()->routeIs('marketing.features') ? 'text-brand' : '' }}">Features</a>
            <a href="{{ route('marketing.pricing') }}" class="text-sm font-medium text-gray-600 hover:text-brand {{ request()->routeIs('marketing.pricing') ? 'text-brand' : '' }}">Pricing</a>
            <a href="{{ route('marketing.blog') }}" class="text-sm font-medium text-gray-600 hover:text-brand {{ request()->routeIs('marketing.blog*') ? 'text-brand' : '' }}">Blog</a>
            <a href="{{ route('marketing.help') }}" class="text-sm font-medium text-gray-600 hover:text-brand {{ request()->routeIs('marketing.help*') ? 'text-brand' : '' }}">Help</a>
            <a href="{{ route('marketing.about') }}" class="text-sm font-medium text-gray-600 hover:text-brand {{ request()->routeIs('marketing.about') ? 'text-brand' : '' }}">About</a>
            <a href="{{ route('marketing.contact') }}" class="text-sm font-medium text-gray-600 hover:text-brand {{ request()->routeIs('marketing.contact') ? 'text-brand' : '' }}">Contact</a>
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-brand">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-brand">Log in</a>
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark">Start Free</a>
            @endauth
        </div>

        <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden"
            @click="open = !open"
            aria-label="Toggle menu"
        >
            <svg x-show="!open" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-200 bg-white px-4 py-4 lg:hidden">
        <nav class="flex flex-col gap-3">
            <a href="{{ route('marketing.features') }}" class="text-sm font-medium text-gray-700">Features</a>
            <a href="{{ route('marketing.pricing') }}" class="text-sm font-medium text-gray-700">Pricing</a>
            <a href="{{ route('marketing.blog') }}" class="text-sm font-medium text-gray-700">Blog</a>
            <a href="{{ route('marketing.help') }}" class="text-sm font-medium text-gray-700">Help</a>
            <a href="{{ route('marketing.about') }}" class="text-sm font-medium text-gray-700">About</a>
            <a href="{{ route('marketing.contact') }}" class="text-sm font-medium text-gray-700">Contact</a>
            <hr class="border-gray-200">
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700">Log in</a>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white">Start Free</a>
            @endauth
        </nav>
    </div>
</header>

<style>[x-cloak] { display: none !important; }</style>
