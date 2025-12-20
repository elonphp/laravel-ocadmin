@php
    $links = \Elonphp\LaravelOcadminModules\Support\LocaleHelper::switchLinks();
@endphp

<div class="d-flex justify-content-center gap-2">
    @foreach ($links as $locale => $link)
        @if ($link['is_current'])
            <span class="text-white">{{ $link['name'] }}</span>
        @else
            <a href="{{ $link['url'] }}" class="text-white-50">{{ $link['name'] }}</a>
        @endif
        @if (!$loop->last)
            <span class="text-white-50">|</span>
        @endif
    @endforeach
</div>
