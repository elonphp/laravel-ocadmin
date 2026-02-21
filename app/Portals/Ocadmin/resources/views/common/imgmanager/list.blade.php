<div class="row mb-3">
  <div class="col-sm-7">
    <a href="{{ $parent }}" id="button-parent" data-bs-toggle="tooltip" title="{{ $lang->button_parent }}" class="btn btn-light"><i class="fa-solid fa-level-up-alt"></i></a>
    <a href="{{ $refresh }}" id="button-refresh" data-bs-toggle="tooltip" title="{{ $lang->button_refresh }}" class="btn btn-light"><i class="fa-solid fa-rotate"></i></a>
    <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_upload }}" id="button-upload" class="btn btn-primary"><i class="fa-solid fa-upload"></i></button>
    <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_folder }}" id="button-folder" class="btn btn-light"><i class="fa-solid fa-folder"></i></button>
    <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_delete }}" id="button-delete" class="btn btn-danger"><i class="fa-regular fa-trash-can"></i></button>
    <span class="btn-group ms-2">
      <button type="button" id="button-view-grid" data-bs-toggle="tooltip" title="{{ $lang->button_grid }}" class="btn btn-outline-secondary btn-sm active"><i class="fa-solid fa-th"></i></button>
      <button type="button" id="button-view-list" data-bs-toggle="tooltip" title="{{ $lang->button_list }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-list"></i></button>
    </span>
    <input type="hidden" name="directory" value="{{ $directory }}" id="input-directory"/>
    <input type="hidden" id="input-sort" value="{{ $sort }}"/>
    <input type="hidden" id="input-order" value="{{ $order }}"/>
  </div>
  <div class="col-sm-4">
    <div class="input-group">
      <input type="text" name="search" value="{{ $filter_name }}" placeholder="{{ $lang->column_search }}" id="input-search" class="form-control">
      <button type="button" id="button-search" data-bs-toggle="tooltip" title="{{ $lang->button_search }}" class="btn btn-primary"><i class="fa-solid fa-search"></i></button>
    </div>
  </div>
</div>
<div id="modal-folder" class="row mb-3" style="display: none;">
  <div class="col-sm-12">
    <div class="input-group">
      <input type="text" name="folder" value="" placeholder="{{ $lang->column_folder }}" id="input-folder" class="form-control">
      <button type="button" title="{{ $lang->button_folder }}" id="button-create" class="btn btn-primary"><i class="fa-solid fa-plus-circle"></i></button>
    </div>
  </div>
</div>
<hr/>

@php $path_row = 0; @endphp

{{-- Grid View --}}
<div id="imgmanager-view-grid" class="row row-cols-sm-3 row-cols-lg-4">
  @foreach($directories as $dir)
    <div class="mb-3">
      <div class="mb-1" style="min-height: 140px;">
        <a href="{{ $dir['href'] }}" class="directory mb-1"><i class="fa-solid fa-folder fa-5x"></i></a>
      </div>
      <div class="form-check">
        <label for="input-path-{{ $path_row }}" class="form-check-label">{{ $dir['name'] }}</label>
        <input type="checkbox" name="path[]" value="{{ $dir['path'] }}" id="input-path-{{ $path_row }}" class="form-check-input"/>
      </div>
    </div>
    @php $path_row++; @endphp
  @endforeach

  @foreach($images as $image)
    <div class="mb-3">
      <div class="mb-1" style="min-height: 140px;">
        <a href="{{ $image['href'] }}" class="thumbnail mb-1"><img src="{{ $image['thumb'] ?: $image['href'] }}" alt="{{ $image['name'] }}" title="{{ $image['name'] }}" class="img-fluid"/></a>
      </div>
      <div class="form-check">
        <label for="input-path-{{ $path_row }}" class="form-check-label">{{ $image['name'] }}</label>
        <input type="checkbox" name="path[]" value="{{ $image['path'] }}" id="input-path-{{ $path_row }}" class="form-check-input"/>
      </div>
    </div>
    @php $path_row++; @endphp
  @endforeach
</div>

{{-- List View --}}
<div id="imgmanager-view-list" class="table-responsive" style="display: none;">
  <table class="table table-hover table-sm align-middle mb-0">
    <thead>
      <tr>
        <th style="width: 30px;"></th>
        <th style="width: 60px;">{{ $lang->column_image }}</th>
        <th>
          <a href="#" class="imgmanager-sort text-decoration-none" data-sort="name">
            {{ $lang->column_name }}
            @if($sort === 'name')
              <i class="fa-solid fa-sort-{{ $order === 'asc' ? 'up' : 'down' }}"></i>
            @else
              <i class="fa-solid fa-sort text-muted"></i>
            @endif
          </a>
        </th>
        <th style="width: 100px;">
          <a href="#" class="imgmanager-sort text-decoration-none" data-sort="size">
            {{ $lang->column_size }}
            @if($sort === 'size')
              <i class="fa-solid fa-sort-{{ $order === 'asc' ? 'up' : 'down' }}"></i>
            @else
              <i class="fa-solid fa-sort text-muted"></i>
            @endif
          </a>
        </th>
        <th style="width: 140px;">
          <a href="#" class="imgmanager-sort text-decoration-none" data-sort="mtime">
            {{ $lang->column_mtime }}
            @if($sort === 'mtime')
              <i class="fa-solid fa-sort-{{ $order === 'asc' ? 'up' : 'down' }}"></i>
            @else
              <i class="fa-solid fa-sort text-muted"></i>
            @endif
          </a>
        </th>
      </tr>
    </thead>
    <tbody>
      @php $path_row_list = 0; @endphp
      @foreach($directories as $dir)
        <tr>
          <td>
            <input type="checkbox" name="path[]" value="{{ $dir['path'] }}" id="input-path-list-{{ $path_row_list }}" class="form-check-input"/>
          </td>
          <td class="text-center">
            <a href="{{ $dir['href'] }}" class="directory"><i class="fa-solid fa-folder fa-2x text-warning"></i></a>
          </td>
          <td>
            <a href="{{ $dir['href'] }}" class="directory text-decoration-none">{{ $dir['name'] }}</a>
          </td>
          <td class="text-muted">&mdash;</td>
          <td class="text-muted">{{ $dir['mtime'] }}</td>
        </tr>
        @php $path_row_list++; @endphp
      @endforeach

      @foreach($images as $image)
        <tr>
          <td>
            <input type="checkbox" name="path[]" value="{{ $image['path'] }}" id="input-path-list-{{ $path_row_list }}" class="form-check-input"/>
          </td>
          <td class="text-center">
            <a href="{{ $image['href'] }}" class="thumbnail"><img src="{{ $image['thumb'] ?: $image['href'] }}" alt="{{ $image['name'] }}" style="max-height: 40px; max-width: 50px;"/></a>
          </td>
          <td>
            <a href="{{ $image['href'] }}" class="thumbnail text-decoration-none">{{ $image['name'] }}</a>
          </td>
          <td class="text-muted">{{ $image['size'] }}</td>
          <td class="text-muted">{{ $image['mtime'] }}</td>
        </tr>
        @php $path_row_list++; @endphp
      @endforeach
    </tbody>
  </table>
</div>

@if($pagination)
  <div class="modal-footer">{!! $pagination !!}</div>
@endif
