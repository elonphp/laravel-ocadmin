@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1>{{ $lang->heading_title }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        {{-- 群組選擇 --}}
        <div class="card mb-3">
            <div class="card-header"><i class="fa-solid fa-link"></i> {{ $lang->column_group }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <select id="select-group" class="form-select">
                            <option value="">{{ $lang->text_select_group }}</option>
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>{{ $group->name }} ({{ $group->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        @if($groupData)
        <div class="row">
            {{-- 連動設定區 --}}
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header"><i class="fa-solid fa-sitemap"></i> {{ $lang->text_link_setting }}</div>
                    <div class="card-body">
                        {{-- 層級切換頁籤 --}}
                        @if(count($groupData['levels']) > 1)
                        <ul class="nav nav-tabs mb-3" id="level-tabs">
                            @foreach($groupData['levels'] as $i => $level)
                            @if($i < count($groupData['levels']) - 1)
                            <li class="nav-item">
                                <a class="nav-link {{ $i === 0 ? 'active' : '' }}" href="#" data-level="{{ $i }}" onclick="switchLevel({{ $i }}); return false;">
                                    Level {{ $level['level'] }}: {{ $level['option_name'] }} &rarr; {{ $groupData['levels'][$i + 1]['option_name'] }}
                                </a>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                        @endif

                        {{-- 父值 / 子值面板 --}}
                        @foreach($groupData['levels'] as $i => $level)
                        @if($i < count($groupData['levels']) - 1)
                        @php $childLevel = $groupData['levels'][$i + 1]; @endphp
                        <div class="level-panel" id="level-panel-{{ $i }}" style="{{ $i === 0 ? '' : 'display:none;' }}">
                            <div class="row">
                                {{-- 左：父值列表 --}}
                                <div class="col-md-4">
                                    <h6>{{ $lang->text_parent_values }} ({{ $level['option_name'] }})</h6>
                                    <div class="list-group" id="parent-list-{{ $i }}">
                                        @foreach($level['values'] as $val)
                                        <a href="#" class="list-group-item list-group-item-action parent-value-item"
                                           data-parent-id="{{ $val['id'] }}"
                                           data-level="{{ $i }}"
                                           onclick="selectParent({{ $val['id'] }}, {{ $i }}); return false;">
                                            {{ $val['name'] }}
                                            @if($val['code']) <small class="text-muted">({{ $val['code'] }})</small> @endif
                                        </a>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- 右：子值勾選 --}}
                                <div class="col-md-8">
                                    <h6>{{ $lang->text_child_values }} ({{ $childLevel['option_name'] }})</h6>
                                    <div id="child-panel-{{ $i }}" class="border rounded p-3">
                                        <p class="text-muted mb-0">{{ $lang->text_select_parent }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 連動測試區 --}}
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header"><i class="fa-solid fa-flask"></i> {{ $lang->text_cascade_test }}</div>
                    <div class="card-body">
                        @foreach($groupData['levels'] as $i => $level)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Level {{ $level['level'] }}: {{ $level['option_name'] }}</label>
                            <select class="form-select cascade-select" id="cascade-select-{{ $i }}" data-level="{{ $i }}" {{ $i > 0 ? 'disabled' : '' }}>
                                <option value="">{{ $lang->text_select_value }}</option>
                                @if($i === 0)
                                @foreach($level['values'] as $val)
                                <option value="{{ $val['id'] }}">{{ $val['name'] }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="fa-solid fa-link fa-3x mb-3"></i>
                <p>{{ $lang->text_no_group }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
var groupData = @json($groupData);
var url_index = '{{ $url_index }}';
var url_links = '{{ $url_links }}';
var url_save_links = '{{ $url_save_links }}';
var url_children = '{{ $url_children }}';

// 群組切換
$('#select-group').on('change', function() {
    var groupId = $(this).val();
    if (groupId) {
        window.location.href = url_index + '?group_id=' + groupId;
    } else {
        window.location.href = url_index;
    }
});

@if($groupData)
// 層級頁籤切換
function switchLevel(level) {
    $('.level-panel').hide();
    $('#level-panel-' + level).show();
    $('#level-tabs .nav-link').removeClass('active');
    $('#level-tabs .nav-link[data-level="' + level + '"]').addClass('active');
}

// 選擇父值
function selectParent(parentId, level) {
    // 標示選中
    $('#parent-list-' + level + ' .parent-value-item').removeClass('active');
    $('#parent-list-' + level + ' .parent-value-item[data-parent-id="' + parentId + '"]').addClass('active');

    // 取得已存連動
    $.getJSON(url_links.replace('__ID__', parentId), function(data) {
        var childIds = data.child_ids || [];
        var childLevel = groupData.levels[level + 1];
        var html = '';

        for (var i = 0; i < childLevel.values.length; i++) {
            var val = childLevel.values[i];
            var checked = childIds.indexOf(val.id) !== -1 ? 'checked' : '';
            html += '<div class="form-check">';
            html += '<input type="checkbox" class="form-check-input child-check" value="' + val.id + '" id="child-' + val.id + '" ' + checked + ' data-parent-id="' + parentId + '" data-level="' + level + '">';
            html += '<label class="form-check-label" for="child-' + val.id + '">' + val.name;
            if (val.code) html += ' <small class="text-muted">(' + val.code + ')</small>';
            html += '</label></div>';
        }

        html += '<div class="mt-3"><button type="button" class="btn btn-primary btn-sm" onclick="saveLinks(' + parentId + ', ' + level + ')"><i class="fa-solid fa-save"></i> {{ $lang->button_save }}</button> <span id="save-status-' + level + '"></span></div>';

        $('#child-panel-' + level).html(html);
    });
}

// 儲存連動
function saveLinks(parentId, level) {
    var childIds = [];
    $('#child-panel-' + level + ' .child-check:checked').each(function() {
        childIds.push(parseInt($(this).val()));
    });

    $.ajax({
        url: url_save_links,
        type: 'POST',
        data: {
            parent_option_value_id: parentId,
            child_option_value_ids: childIds,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function(json) {
            if (json.success) {
                $('#save-status-' + level).html('<span class="text-success"><i class="fa-solid fa-check"></i> {{ $lang->text_saved }}</span>');
                setTimeout(function() { $('#save-status-' + level).html(''); }, 2000);
            }
        },
        error: function(xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
}

// 連動測試
$('.cascade-select').on('change', function() {
    var level = parseInt($(this).data('level'));
    var valueId = $(this).val();

    // 清除後續層級
    for (var i = level + 1; i < groupData.levels.length; i++) {
        var $sel = $('#cascade-select-' + i);
        $sel.html('<option value="">{{ $lang->text_select_value }}</option>');
        $sel.prop('disabled', true);
    }

    if (!valueId) return;

    // 取得子值
    $.getJSON(url_children.replace('__ID__', valueId), function(data) {
        var nextLevel = level + 1;
        if (nextLevel >= groupData.levels.length) return;

        var $nextSel = $('#cascade-select-' + nextLevel);
        var html = '<option value="">{{ $lang->text_select_value }}</option>';

        var children = data.children || [];
        for (var i = 0; i < children.length; i++) {
            html += '<option value="' + children[i].id + '">' + children[i].name + '</option>';
        }

        $nextSel.html(html);
        $nextSel.prop('disabled', false);
    });
});
@endif
</script>
@endsection
