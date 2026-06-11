@extends('layouts.marketing')

@section('content')
    <section class="bg-gradient-to-b from-white to-surface py-16 sm:py-12">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-heading">Personal finance tips &amp; product updates</h1>
            <p class="mt-4 text-lg text-gray-600">Guides, budgeting advice, and news from the Finance Assistant team.</p>
        </div>
    </section>

    <section class="pb-20">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:grid-cols-2 lg:grid-cols-2 lg:px-8">
            @foreach ($posts as $post)
                <article class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-brand/30">
                    <span class="text-xs font-semibold uppercase tracking-wide text-brand">{{ $post['category'] }}</span>
                    <h2 class="mt-3 text-xl font-semibold text-heading">
                        <a href="{{ route('marketing.blog.show', $post['slug']) }}" class="hover:text-brand">{{ $post['title'] }}</a>
                    </h2>
                    <p class="mt-3 flex-1 text-sm text-gray-600">{{ $post['excerpt'] }}</p>
                    <div class="mt-4 flex items-center gap-3 text-xs text-gray-500">
                        <time datetime="{{ $post['date'] }}">{{ \Illuminate\Support\Carbon::parse($post['date'])->format('M j, Y') }}</time>
                        <span>&middot;</span>
                        <span>{{ $post['read_time'] }} read</span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <x-marketing.cta-strip title="Put these tips into practice" subtitle="Create your free Finance Assistant workspace today." />
@endsection
