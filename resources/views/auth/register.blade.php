@extends('layouts.auth-marketing')

@section('content')
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-2xl font-bold text-heading">Start managing your money for free</h1>
        <p class="mt-2 text-sm text-gray-600">No credit card required. Free plan includes accounts, transactions, and basic reports.</p>
        @if ($selectedPlan)
            <p class="mt-2 rounded-lg bg-brand/10 px-3 py-2 text-sm text-brand">You selected the <strong>{{ ucfirst($selectedPlan) }}</strong> plan — start free and upgrade anytime.</p>
        @endif
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium text-heading">Full name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-heading">Email address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-heading">Password</label>
            <input type="password" name="password" id="password" required autocomplete="new-password" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-heading">Confirm password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
        </div>
        <p class="text-xs text-gray-500">By signing up, you agree to our <a href="{{ route('marketing.terms') }}" class="text-brand hover:text-brand-dark">Terms of Service</a> and <a href="{{ route('marketing.privacy') }}" class="text-brand hover:text-brand-dark">Privacy Policy</a>.</p>
        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Create free account</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600 lg:text-left">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-brand hover:text-brand-dark">Log in</a>
    </p>
@endsection
