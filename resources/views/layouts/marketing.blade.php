<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.marketing.seo')
    @vite(['resources/css/app.css', 'resources/js/marketing.js'])
</head>
<body class="bg-surface font-sans text-heading antialiased">
    @include('partials.marketing.header')

    <main>
        @yield('content')
    </main>

    @include('partials.marketing.footer')
</body>
</html>
