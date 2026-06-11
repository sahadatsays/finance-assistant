@extends('layouts.marketing')

@section('content')
    <article class="py-16 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-brand">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('marketing.help') }}" class="hover:text-brand">Help Center</a>
                <span class="mx-2">/</span>
                <span class="text-heading">{{ $article['title'] }}</span>
            </nav>
            <h1 class="mt-6 text-3xl font-bold text-heading">{{ $article['title'] }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ $category['title'] }}</p>
            <div class="mt-8 space-y-4 text-gray-600">
                <p>This help article provides step-by-step guidance for {{ strtolower($article['title']) }} in Finance Assistant.</p>
                <p>For a hands-on walkthrough, create a free account and follow along in your workspace. Detailed screenshots and video guides will be added in upcoming releases.</p>
            </div>
            <div class="mt-10 flex items-center gap-4 rounded-xl border border-gray-200 bg-surface p-4">
                <span class="text-sm text-gray-600">Was this helpful?</span>
                <button type="button" class="rounded-lg border border-gray-300 px-3 py-1 text-sm hover:border-success-brand hover:text-success-brand">Yes</button>
                <a href="{{ route('marketing.contact') }}" class="rounded-lg border border-gray-300 px-3 py-1 text-sm hover:border-brand hover:text-brand">No — contact us</a>
            </div>
        </div>
    </article>
@endsection
