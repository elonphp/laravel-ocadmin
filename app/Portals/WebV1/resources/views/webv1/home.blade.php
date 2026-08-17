<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebV1 — Public Site</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 720px; margin: 4rem auto; padding: 0 1rem; line-height: 1.6; color: #222; }
        nav { margin-bottom: 2rem; font-size: 0.9rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        nav a { color: #555; margin-right: 1rem; text-decoration: none; }
        nav a:hover { color: #000; text-decoration: underline; }
        nav a.current { color: #000; font-weight: 600; }
        h1 { font-weight: 600; margin-top: 0; }
        .meta { color: #888; font-size: 0.85rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee; }
        code { background: #f4f4f4; padding: 0.1rem 0.4rem; border-radius: 3px; font-size: 0.9em; }
    </style>
</head>
<body>
    @php
        $currentLocale = app()->getLocale();
        $supported = \App\Helpers\Classes\LocaleHelper::getSupportedLocales();
        $names = \App\Helpers\Classes\LocaleHelper::getLocaleNames();
    @endphp

    <nav>
        @foreach ($supported as $locale)
            @php $urlLocale = \App\Helpers\Classes\LocaleHelper::toUrlFormat($locale); @endphp
            <a href="/{{ $urlLocale }}" class="{{ $locale === $currentLocale ? 'current' : '' }}">{{ $names[$locale] ?? $locale }}</a>
        @endforeach
        <span style="float: right;"><a href="/{{ \App\Helpers\Classes\LocaleHelper::getCurrentUrlLocale() }}/admin">Admin →</a></span>
    </nav>

    <h1>WebV1 — 公開官網範例</h1>

    <p>本 portal 是 ocadmin 範本層的前台官網 scaffold（Mode A — 前後台分離）。</p>
    <p>實際商業專案 fork 後可在 <code>app/Portals/WebV1/</code> 內擴充 Modules / views / controllers。</p>

    <p class="meta">
        Current locale: <code>{{ $currentLocale }}</code> ·
        URL prefix: <code>/{{ \App\Helpers\Classes\LocaleHelper::getCurrentUrlLocale() }}/</code> ·
        Route name: <code>lang.webv1.home</code>
    </p>
</body>
</html>
