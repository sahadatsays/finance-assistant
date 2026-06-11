@extends('layouts.marketing')

@section('content')
    <section class="bg-gradient-to-b from-white to-surface py-16 sm:py-12">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-heading">Help Center</h1>
            <p class="mt-4 text-lg text-gray-600">Find answers about accounts, budgets, goals, billing, and security.</p>
            <div class="mx-auto mt-8 max-w-xl" x-data="{ query: '' }">
                <input type="search" x-model="query" placeholder="Search help articles..." class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
            </div>
        </div>
    </section>

    <section class="pb-20">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:grid-cols-2 lg:grid-cols-3 sm:px-6 lg:px-8">
            @foreach ($categories as $category)
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-heading">{{ $category['title'] }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $category['description'] }}</p>
                    <ul class="mt-4 space-y-2">
                        @foreach ($category['articles'] as $article)
                            <li>
                                <a href="{{ route('marketing.help.show', [$category['slug'], $article['slug']]) }}" class="text-sm text-brand hover:text-brand-dark">{{ $article['title'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        <div class="mx-auto mt-12 max-w-2xl px-4 text-center">
            <p class="text-sm text-gray-600">Can&rsquo;t find what you need?</p>
            <a href="{{ route('marketing.contact') }}" class="mt-2 inline-flex text-sm font-semibold text-brand hover:text-brand-dark">Contact support &rarr;</a>
        </div>
    </section>
@endsection
