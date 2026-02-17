<meta charset="utf-8">
<title> {{ $pageTitle }} | {{ \App\Models\AppSetting::get('branding_app_name') ?: 'Partner Manager' }}</title>
<meta name="description" content="Page Description">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=5">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Standard favicon for browsers -->
@php $customFavicon = \App\Models\AppSetting::get('branding_favicon_path'); @endphp
@if($customFavicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($customFavicon))
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::url($customFavicon) }}" type="image/png">
@else
    <link rel="icon" href="/img/favicon-32x32.png" type="image/png" sizes="32x32">
    <link rel="icon" href="/img/favicon-16x16.png" type="image/png" sizes="16x16">
@endif

<!-- Apple Touch Icon (iOS) -->
<link rel="apple-touch-icon" href="/img/apple-touch-icon.png" sizes="180x180">

<!-- Android/Chrome (Progressive Web App) -->
<link rel="icon" href="/img/favicon-192x192.png" type="image/png" sizes="192x192">

<!-- Call App Mode on ios devices -->
<meta name="mobile-web-app-capable" content="yes">

<!-- Remove Tap Highlight on Windows Phone IE -->
<meta name="msapplication-tap-highlight" content="no">