<ol class="breadcrumb">
    @foreach($breadcrumbs as $index => $crumb)
        @if($loop->last)
            <li class="breadcrumb-item active">{{ $crumb->text }}</li>
        @else
            <li class="breadcrumb-item"><a href="{{ $crumb->href }}">{{ $crumb->text }}</a></li>
        @endif
    @endforeach
</ol>
