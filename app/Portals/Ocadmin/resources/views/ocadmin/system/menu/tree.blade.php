@extends('ocadmin::layouts.app')

@section('title', $lang->text_tree)

@section('styles')
<style>
.menu-tree {
    list-style: none;
    padding-left: 0;
    margin: 0;
}
.menu-tree .menu-tree {
    padding-left: 30px;
}
.menu-tree-item {
    margin-bottom: 4px;
}
.menu-tree-handle {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: grab;
    user-select: none;
    transition: background-color 0.15s;
}
.menu-tree-handle:hover {
    background: #f8f9fa;
}
.menu-tree-handle:active {
    cursor: grabbing;
}
.menu-tree-handle .drag-icon {
    color: #adb5bd;
    margin-right: 10px;
    flex-shrink: 0;
}
.menu-tree-handle .menu-icon {
    margin-right: 8px;
    width: 20px;
    text-align: center;
    color: #6c757d;
    flex-shrink: 0;
}
.menu-tree-handle .menu-name {
    flex: 1;
    font-weight: 500;
}
.menu-tree-handle .menu-meta {
    font-size: 0.8em;
    color: #6c757d;
    margin-left: 12px;
    flex-shrink: 0;
}
.menu-tree-handle .menu-actions {
    margin-left: 12px;
    flex-shrink: 0;
}
.menu-tree-handle .badge-inactive {
    background: #6c757d;
    font-size: 0.75em;
    margin-left: 8px;
}
.menu-tree-toggle {
    width: 20px;
    text-align: center;
    margin-right: 6px;
    cursor: pointer;
    color: #6c757d;
    flex-shrink: 0;
    border: none;
    background: none;
    padding: 0;
}
.menu-tree-toggle:hover {
    color: #333;
}
.sortable-ghost {
    opacity: 0.4;
}
.sortable-drag {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.menu-tree.nested-empty {
    min-height: 10px;
    padding: 4px 0;
}
</style>
@endsection

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <select id="select-portal" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    @foreach($portals as $portal)
                    <option value="{{ $portal }}" @selected($portal === $current_portal)>{{ $portal }}</option>
                    @endforeach
                </select>
                <select id="select-group" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    @foreach($groups as $group)
                    <option value="{{ $group }}" @selected($group === $current_group)>{{ $group }}</option>
                    @endforeach
                </select>
                <a href="{{ $list_url }}" data-bs-toggle="tooltip" title="{{ $lang->text_list }}" class="btn btn-light">
                    <i class="fa-solid fa-list"></i>
                </a>
                <button type="button" id="btn-expand-all" data-bs-toggle="tooltip" title="{{ $lang->text_expand_all }}" class="btn btn-light">
                    <i class="fa-solid fa-angles-down"></i>
                </button>
                <button type="button" id="btn-collapse-all" data-bs-toggle="tooltip" title="{{ $lang->text_collapse_all }}" class="btn btn-light">
                    <i class="fa-solid fa-angles-up"></i>
                </button>
                <button type="button" id="btn-save-order" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
            </div>
            <h1>{{ $lang->text_tree }}</h1>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-sitemap"></i> {{ $lang->text_tree }}
                <small class="text-muted ms-2">{{ $lang->text_drag_hint }}</small>
            </div>
            <div class="card-body">
                <ul class="menu-tree" id="menu-tree-root">
                    @foreach($menus as $node)
                        @include('ocadmin::system.menu.tree-node', ['node' => $node])
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    var reorderUrl = '{{ $reorder_url }}';
    var treeUrl = '{{ $tree_url }}';

    // 切換 Portal（group 回歸預設）
    $('#select-portal').on('change', function() {
        window.location.href = treeUrl + '?portal=' + $(this).val();
    });

    // 切換 Group
    $('#select-group').on('change', function() {
        window.location.href = treeUrl + '?portal=' + $('#select-portal').val() + '&group=' + $(this).val();
    });

    // 初始化所有 sortable 列表
    function initSortable(el) {
        new Sortable(el, {
            group: 'menu-tree',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.drag-icon',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function() {
                // 可在此標記 dirty 狀態
            }
        });
    }

    document.querySelectorAll('.menu-tree').forEach(function(el) {
        initSortable(el);
    });

    // 展開/收合子選單
    $(document).on('click', '.menu-tree-toggle', function(e) {
        e.stopPropagation();
        var $item = $(this).closest('.menu-tree-item');
        var $children = $item.children('.menu-tree');
        $children.toggleClass('d-none');
        var icon = $children.hasClass('d-none') ? 'fa-caret-right' : 'fa-caret-down';
        $(this).find('i').attr('class', 'fa-solid ' + icon);
    });

    // 全部展開
    $('#btn-expand-all').on('click', function() {
        $('.menu-tree-item > .menu-tree').removeClass('d-none');
        $('.menu-tree-toggle i').attr('class', 'fa-solid fa-caret-down');
    });

    // 全部收合
    $('#btn-collapse-all').on('click', function() {
        $('.menu-tree-item > .menu-tree').addClass('d-none');
        $('.menu-tree-toggle i').attr('class', 'fa-solid fa-caret-right');
    });

    // 從 DOM 遞迴收集樹狀結構
    function serializeTree(ul) {
        var items = [];
        $(ul).children('.menu-tree-item').each(function() {
            var node = {
                id: $(this).data('id'),
                children: []
            };
            var childUl = $(this).children('.menu-tree');
            if (childUl.length) {
                node.children = serializeTree(childUl);
            }
            items.push(node);
        });
        return items;
    }

    // 儲存排序
    $('#btn-save-order').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);

        var tree = serializeTree('#menu-tree-root');

        $.ajax({
            url: reorderUrl,
            type: 'POST',
            data: JSON.stringify({ items: tree }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    $('#alert').html('<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-check-circle"></i> ' + json.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.statusText);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
