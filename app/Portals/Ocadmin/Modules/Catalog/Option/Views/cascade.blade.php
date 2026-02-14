@extends('ocadmin::layouts.app')

@section('title', $lang->cascade_heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <a href="{{ route('lang.ocadmin.catalog.option.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $lang->cascade_heading_title }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <p class="text-muted">{{ $lang->cascade_text_description }}</p>

        {{-- 層級選擇 --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ $lang->cascade_text_select_level }}</label>
            <div class="btn-group" role="group">
                @foreach($levels as $index => $level)
                <button type="button"
                    class="btn btn-outline-primary level-btn @if($index === 0) active @endif"
                    data-level="{{ $index }}"
                    data-parent-option-id="{{ $level['parent']->id }}"
                    data-child-option-id="{{ $level['child']->id }}">
                    {{ $level['parent']->name }} <i class="fa-solid fa-arrow-right mx-1"></i> {{ $level['child']->name }}
                </button>
                @endforeach
            </div>
        </div>

        <div class="row">
            {{-- 左側：父值清單 --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fa-solid fa-list"></i> <span id="parent-title">{{ $lang->cascade_text_parent_values }}</span>
                    </div>
                    <div id="parent-list" class="list-group list-group-flush">
                        {{-- 預設載入第一層的父值 --}}
                        @if(count($levels) > 0)
                            @foreach($levels[0]['parent']->optionValues->sortBy('sort_order') as $value)
                            <a href="#" class="list-group-item list-group-item-action parent-item" data-value-id="{{ $value->id }}">
                                {{ $value->name }}
                                <span class="badge bg-secondary float-end link-count" data-value-id="{{ $value->id }}"></span>
                            </a>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- 右側：子值勾選 --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-link"></i> <span id="child-title">{{ $lang->cascade_text_child_values }}</span></span>
                        <button type="button" id="button-save-links" class="btn btn-primary btn-sm" style="display: none;">
                            <i class="fa-solid fa-save"></i> {{ $lang->button_save }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="child-placeholder" class="text-center text-muted py-4">
                            <i class="fa-solid fa-hand-pointer fa-2x mb-2 d-block"></i>
                            {{ $lang->cascade_text_click_parent }}
                        </div>
                        <div id="child-checkboxes" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var currentParentValueId = null;
    var currentChildOptionId = null;
    var linksUrl = '{{ route("lang.ocadmin.catalog.option.cascade.links", ["optionValue" => "__ID__"]) }}';
    var saveUrl = '{{ route("lang.ocadmin.catalog.option.cascade.save-links") }}';

    // 所有層級的資料（由 PHP 傳入）
    var levelsData = @json($levelsJson);

    // 切換層級
    $('.level-btn').on('click', function() {
        $('.level-btn').removeClass('active');
        $(this).addClass('active');

        var levelIndex = $(this).data('level');
        var level = levelsData[levelIndex];
        currentChildOptionId = level.child_id;

        // 更新標題
        $('#parent-title').text(level.parent_name);
        $('#child-title').text(level.child_name);

        // 重建父值清單
        var html = '';
        $.each(level.parent_values, function(i, v) {
            html += '<a href="#" class="list-group-item list-group-item-action parent-item" data-value-id="' + v.id + '">';
            html += v.name;
            html += ' <span class="badge bg-secondary float-end link-count" data-value-id="' + v.id + '"></span>';
            html += '</a>';
        });
        $('#parent-list').html(html);

        // 重置右側
        resetChildPanel();

        // 載入每個父值的連動數量
        loadLinkCounts(level.parent_values);
    });

    // 載入連動數量 badge
    function loadLinkCounts(parentValues) {
        $.each(parentValues, function(i, v) {
            $.getJSON(linksUrl.replace('__ID__', v.id), function(data) {
                var count = data.linked_ids.length;
                $('.link-count[data-value-id="' + v.id + '"]').text(count > 0 ? count : '');
            });
        });
    }

    // 點選父值
    $(document).on('click', '.parent-item', function(e) {
        e.preventDefault();
        $('.parent-item').removeClass('active');
        $(this).addClass('active');

        currentParentValueId = $(this).data('value-id');
        var levelIndex = $('.level-btn.active').data('level');
        var level = levelsData[levelIndex];

        // 載入子值 checkbox 和已連動狀態
        $.getJSON(linksUrl.replace('__ID__', currentParentValueId), function(data) {
            var linkedIds = data.linked_ids;
            var html = '<div class="row">';

            if (level.child_values.length === 0) {
                html += '<div class="col-12 text-muted text-center py-3">{{ $lang->cascade_text_no_values }}</div>';
            } else {
                $.each(level.child_values, function(i, v) {
                    var checked = linkedIds.indexOf(v.id) !== -1 ? ' checked' : '';
                    html += '<div class="col-md-4 col-sm-6 mb-2">';
                    html += '<div class="form-check">';
                    html += '<input type="checkbox" class="form-check-input child-checkbox" value="' + v.id + '" id="child-' + v.id + '"' + checked + '>';
                    html += '<label class="form-check-label" for="child-' + v.id + '">' + v.name + '</label>';
                    html += '</div>';
                    html += '</div>';
                });
            }

            html += '</div>';
            $('#child-checkboxes').html(html).show();
            $('#child-placeholder').hide();
            $('#button-save-links').show();
        });
    });

    // 儲存連動
    $('#button-save-links').on('click', function() {
        if (!currentParentValueId) return;

        var childIds = [];
        $('.child-checkbox:checked').each(function() {
            childIds.push(parseInt($(this).val()));
        });

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: {
                parent_value_id: currentParentValueId,
                child_ids: childIds,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    // 更新 badge
                    $('.link-count[data-value-id="' + currentParentValueId + '"]').text(childIds.length > 0 ? childIds.length : '');

                    // 顯示成功訊息
                    var $btn = $('#button-save-links');
                    var originalHtml = $btn.html();
                    $btn.html('<i class="fa-solid fa-check"></i> ' + json.message).removeClass('btn-primary').addClass('btn-success');
                    setTimeout(function() {
                        $btn.html(originalHtml).removeClass('btn-success').addClass('btn-primary');
                    }, 1500);
                }
            }
        });
    });

    function resetChildPanel() {
        currentParentValueId = null;
        $('#child-checkboxes').hide().empty();
        $('#child-placeholder').show();
        $('#button-save-links').hide();
    }

    // 初始載入第一層的連動數量
    if (levelsData.length > 0) {
        loadLinkCounts(levelsData[0].parent_values);
    }
});
</script>
@endsection
