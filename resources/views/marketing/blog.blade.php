@extends('layouts.marketing')

@section('content')
    <section class="bg-gradient-to-b from-white to-surface py-16 sm:py-12">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-heading">Personal finance tips &amp; product updates</h1>
            <p class="mt-4 text-lg text-gray-600">Guides, budgeting advice, and news from the Finance Assistant team.</p>
        </div>
    </section>

    @if (! empty($categories))
        <section class="border-b border-gray-100 pb-6">
            <div class="mx-auto flex max-w-6xl flex-wrap justify-center gap-2 px-4 lg:px-8">
                <a
                    href="{{ route('marketing.blog') }}"
                    class="rounded-full px-4 py-1.5 text-sm font-medium {{ empty($activeCategory) ? 'bg-brand text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                >
                    All
                </a>
                @foreach ($categories as $category)
                    <a
                        href="{{ route('marketing.blog', ['category' => $category]) }}"
                        class="rounded-full px-4 py-1.5 text-sm font-medium {{ $activeCategory === $category ? 'bg-brand text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                    >
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="pb-20 pt-10">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:grid-cols-2 lg:grid-cols-2 lg:px-8">
            @forelse ($posts as $post)
                <article class="flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-brand/30">
                    @if (! empty($post['featured_image_url']))
                        <img src="{{ $post['featured_image_url'] }}" alt="" class="h-44 w-full object-cover">
                    @endif
                    <div class="flex flex-1 flex-col p-6">
                        <span class="text-xs font-semibold uppercase tracking-wide text-brand">{{ $post['category'] }}</span>
                        <h2 class="mt-3 text-xl font-semibold text-heading">
                            <a href="{{ route('marketing.blog.show', $post['slug']) }}" class="hover:text-brand">{{ $post['title'] }}</a>
                        </h2>
                        <p class="mt-3 flex-1 text-sm text-gray-600">{{ $post['excerpt'] }}</p>
                        <div class="mt-4 flex items-center gap-3 text-xs text-gray-500">
                            <time datetime="{{ $post['date'] }}">{{ \Illuminate\Support\Carbon::parse($post['date'])->format('M j, Y') }}</time>
                            <span>&middot;</span>
                            <span>{{ $post['read_time'] }} read</span>
                            @if (! empty($post['author_name']))
                                <span>&middot;</span>
                                <span>{{ $post['author_name'] }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <p class="col-span-full text-center text-gray-500">No articles in this category yet.</p>
            @endforelse
        </div>
    </section>

    <x-marketing.cta-strip title="Put these tips into practice" subtitle="Create your free Finance Assistant workspace today." />
@endsection
