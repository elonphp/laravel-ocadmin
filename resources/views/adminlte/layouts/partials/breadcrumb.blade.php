@if(!empty($breadcrumbs))
<ol class="breadcrumb float-sm-end">
    @foreach($breadcrumbs as $breadcrumb)
        @if(!$loop->last)
        <li class="breadcrumb-item"><a href="{{ $breadcrumb->href }}">{{ $breadcrumb->text }}</a></li>
        @else
        <li class="breadcrumb-item active">{{ $breadcrumb->text }}</li>
        @endif
    @endforeach
</ol>
@endif
