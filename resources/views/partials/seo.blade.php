{{-- Everything the SeoService collected during this request. --}}
<title>{{ $seo->getTitle() }}</title>
<meta name="description" content="{{ $seo->getDescription() }}">
<link rel="canonical" href="{{ $seo->getCanonical() }}">

@if ($seo->isNoindex())
    <meta name="robots" content="noindex, follow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
@endif

@foreach ($seo->alternates() as $altLocale => $href)
    <link rel="alternate" hreflang="{{ config("site.locales.{$altLocale}.hreflang") }}" href="{{ $href }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $seo->getCanonical() }}">

<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:type" content="{{ $seo->getType() }}">
<meta property="og:title" content="{{ $seo->getTitle() }}">
<meta property="og:description" content="{{ $seo->getDescription() }}">
<meta property="og:url" content="{{ $seo->getCanonical() }}">
<meta property="og:image" content="{{ $seo->getImage() }}">
<meta property="og:locale" content="{{ config('site.locales.'.app()->getLocale().'.hreflang') }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->getTitle() }}">
<meta name="twitter:description" content="{{ $seo->getDescription() }}">
<meta name="twitter:image" content="{{ $seo->getImage() }}">

@foreach ($seo->getSchemas() as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
