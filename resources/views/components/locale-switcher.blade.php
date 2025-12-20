@php
    $links = \Elonphp\LaravelOcadminModules\Support\LocaleHelper::switchLinks();
@endphp

<div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="fas fa-globe me-1"></i>
        {{ $links[ocadmin_locale()]['name'] ?? ocadmin_locale() }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach ($links as $locale => $link)
            <li>
                <a class="dropdown-item @if($link['is_current']) active @endif"
                   href="{{ $link['url'] }}">
                    {{ $link['name'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
