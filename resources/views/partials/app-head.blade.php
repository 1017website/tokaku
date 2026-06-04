{{-- Favicon + SEO meta. Sertakan di dalam <head> setiap layout. --}}
@php
    $favicon  = $appSettings['app_favicon'] ?? null;
    $ogImage  = $appSettings['seo_og_image'] ?? null;
    $seoTitle = $appSettings['seo_title'] ?? ($appSettings['app_name'] ?? 'Tokaku');
    $seoDesc  = $appSettings['seo_description'] ?? null;
    $seoKey   = $appSettings['seo_keywords'] ?? null;
@endphp

{{-- Favicon --}}
@if($favicon)
    <link rel="icon" href="{{ Storage::url($favicon) }}">
    <link rel="apple-touch-icon" href="{{ Storage::url($favicon) }}">
@endif

{{-- SEO --}}
@if($seoDesc)
    <meta name="description" content="{{ $seoDesc }}">
@endif
@if($seoKey)
    <meta name="keywords" content="{{ $seoKey }}">
@endif

{{-- Open Graph / Twitter --}}
<meta property="og:title" content="{{ $seoTitle }}">
@if($seoDesc)
    <meta property="og:description" content="{{ $seoDesc }}">
@endif
@if($ogImage)
    <meta property="og:image" content="{{ Storage::url($ogImage) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ Storage::url($ogImage) }}">
@endif
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
