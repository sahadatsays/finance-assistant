<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $pageTitle = $seo['title'] ?? config('app.name');
    $pageDescription = $seo['description'] ?? '';
    $fullTitle = ($pageTitle === config('app.name')) ? $pageTitle : "{$pageTitle} | ".config('app.name');
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
