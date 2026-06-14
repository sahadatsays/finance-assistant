@extends('layouts.marketing')

@section('content')
    <article class="py-16 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-brand">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('marketing.blog') }}" class="hover:text-brand">Blog</a>
                <span class="mx-2">/</span>
                <span class="text-heading">{{ $post['title'] }}</span>
            </nav>

            <header class="mt-6">
                <span class="text-xs font-semibold uppercase tracking-wide text-brand">{{ $post['category'] }}</span>
                <h1 class="mt-3 text-4xl font-bold text-heading">{{ $post['title'] }}</h1>
                <p class="mt-4 text-sm text-gray-500">
                    {{ \Illuminate\Support\Carbon::parse($post['date'])->format('F j, Y') }}
                    &middot; {{ $post['read_time'] }} read
                    @if (! empty($post['author_name']))
                        &middot; {{ $post['author_name'] }}
                    @endif
                </p>
            </header>

            @if (! empty($post['featured_image_url']))
                <img
                    src="{{ $post['featured_image_url'] }}"
                    alt="{{ $post['title'] }}"
                    class="mt-8 w-full rounded-2xl border border-gray-200 object-cover"
                >
            @endif

            <div class="prose prose-gray mt-10 max-w-none">
                @if (! empty($post['excerpt']))
                    <p class="text-lg text-gray-600">{{ $post['excerpt'] }}</p>
                @endif

                @if (! empty($post['body']))
                    {!! \Illuminate\Support\Str::markdown($post['body']) !!}
                @else
                    <p class="mt-6 text-gray-600">This article is part of our growing library of personal finance guides. Explore Finance Assistant and put these principles into practice with real budgets, goals, and reports.</p>
                @endif
            </div>

            <div class="mt-12 rounded-2xl border border-brand/20 bg-brand/5 p-6">
                <h2 class="font-semibold text-heading">Ready to take control?</h2>
                <p class="mt-2 text-sm text-gray-600">Start free and apply what you learned in your own workspace.</p>
                <a href="{{ route('register') }}" class="mt-4 inline-flex rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Start Free</a>
            </div>
        </div>
    </article>
@endsection
