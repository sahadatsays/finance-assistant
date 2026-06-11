@extends('layouts.auth-marketing')

@section('content')
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-2xl font-bold text-heading">Welcome back</h1>
        <p class="mt-2 text-sm text-gray-600">Sign in to your {{ config('app.name') }} account</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-success-brand/30 bg-success-brand/10 px-4 py-3 text-sm text-heading">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-heading">Email address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-heading">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-brand hover:text-brand-dark">Forgot password?</a>
                @endif
            </div>
            <input type="password" name="password" id="password" required autocomplete="current-password" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="size-4 rounded border-gray-300 text-brand focus:ring-brand">
            <label for="remember" class="text-sm text-gray-600">Remember me</label>
        </div>
        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Log in</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600 lg:text-left">
        Don&rsquo;t have an account?
        <a href="{{ route('register') }}" class="font-semibold text-brand hover:text-brand-dark">Create one free</a>
    </p>
@endsection
