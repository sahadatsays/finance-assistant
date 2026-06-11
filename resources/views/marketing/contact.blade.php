@extends('layouts.marketing')

@section('content')
    <section class="bg-gradient-to-b from-white to-surface py-16 sm:py-12">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-heading">We're here to help</h1>
            <p class="mt-4 text-lg text-gray-600">Reach out for sales, support, or general inquiries. We typically respond within 24&ndash;48 hours.</p>
        </div>
    </section>

    <section class="pb-20">
        <div class="mx-auto grid max-w-5xl gap-12 px-4 sm:px-6 lg:grid-cols-5 lg:px-8">
            <div class="lg:col-span-3">
                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-success-brand/30 bg-success-brand/10 px-4 py-3 text-sm text-heading">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('marketing.contact.store') }}" class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-heading">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-heading">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-heading">Subject</label>
                        <select name="subject" id="subject" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                            @foreach ($subjects as $value => $label)
                                <option value="{{ $value }}" @selected(old('subject') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-heading">Message</label>
                        <textarea name="message" id="message" rows="5" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark sm:w-auto">Send message</button>
                </form>
            </div>
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-6">
                    <h2 class="font-semibold text-heading">Help Center</h2>
                    <p class="mt-2 text-sm text-gray-600">Find instant answers to common questions.</p>
                    <a href="{{ route('marketing.help') }}" class="mt-4 inline-flex text-sm font-semibold text-brand hover:text-brand-dark">Browse help articles &rarr;</a>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-6">
                    <h2 class="font-semibold text-heading">Existing customer?</h2>
                    <p class="mt-2 text-sm text-gray-600">Log in to your dashboard for account-specific support.</p>
                    <a href="{{ route('login') }}" class="mt-4 inline-flex text-sm font-semibold text-brand hover:text-brand-dark">Log in &rarr;</a>
                </div>
            </div>
        </div>
    </section>
@endsection
