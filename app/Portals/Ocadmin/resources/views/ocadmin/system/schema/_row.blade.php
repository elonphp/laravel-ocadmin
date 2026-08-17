{{-- 單欄編輯列 partial，由 form.blade.php 引入 --}}
<tr>
    {{-- 原名（read-only）--}}
    <td>
        <input type="text" name="columns[{{ $idx }}][original_name]"
               value="{{ $originalName }}"
               class="form-control form-control-sm input-original-name"
               readonly
               placeholder="{{ $lang->text_new_column }}">
    </td>

    {{-- 欄位名 --}}
    <td>
        <input type="text" name="columns[{{ $idx }}][name]"
               value="{{ $name }}"
               class="form-control form-control-sm input-name"
               {{ $primary ? 'readonly' : '' }}>
    </td>

    {{-- 類型 --}}
    <td>
        <select name="columns[{{ $idx }}][type]" class="form-select form-select-sm input-type" {{ $primary ? 'disabled' : '' }}>
            @foreach($supportedTypes as $group => $types)
            <optgroup label="{{ $group }}">
                @foreach($types as $t)
                <option value="{{ $t }}" @selected($type === $t)>{{ $t }}</option>
                @endforeach
            </optgroup>
            @endforeach
        </select>
        @if($primary)
        <input type="hidden" name="columns[{{ $idx }}][type]" value="{{ $type }}">
        @endif
    </td>

    {{-- 長度 --}}
    <td>
        <input type="text" name="columns[{{ $idx }}][length]"
               value="{{ $length }}"
               class="form-control form-control-sm"
               placeholder="—">
    </td>

    {{-- Unsigned --}}
    <td class="text-center">
        <input type="hidden" name="columns[{{ $idx }}][unsigned]" value="0">
        <input type="checkbox" name="columns[{{ $idx }}][unsigned]" value="1"
               class="form-check-input"
               @checked($unsigned)>
    </td>

    {{-- Nullable --}}
    <td class="text-center">
        <input type="hidden" name="columns[{{ $idx }}][nullable]" value="0">
        <input type="checkbox" name="columns[{{ $idx }}][nullable]" value="1"
               class="form-check-input"
               @checked($nullable)
               {{ $primary ? 'disabled' : '' }}>
    </td>

    {{-- Default --}}
    <td>
        <input type="text" name="columns[{{ $idx }}][default]"
               value="{{ $default === null ? '' : $default }}"
               class="form-control form-control-sm"
               placeholder="—">
    </td>

    {{-- Auto Increment --}}
    <td class="text-center">
        <input type="hidden" name="columns[{{ $idx }}][auto_increment]" value="0">
        <input type="checkbox" name="columns[{{ $idx }}][auto_increment]" value="1"
               class="form-check-input"
               @checked($auto_increment)
               {{ $primary ? 'disabled' : '' }}>
        @if($primary && $auto_increment)
        <input type="hidden" name="columns[{{ $idx }}][auto_increment]" value="1">
        @endif
    </td>

    {{-- Primary Key (read-only 顯示) --}}
    <td class="text-center">
        @if($primary)
            <i class="fa-solid fa-key text-warning" title="PK"></i>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>

    {{-- Comment --}}
    <td>
        <input type="text" name="columns[{{ $idx }}][comment]"
               value="{{ $comment }}"
               class="form-control form-control-sm"
               placeholder="—">
    </td>

    {{-- 刪除按鈕 + hidden _delete --}}
    <td class="text-center">
        <input type="hidden" name="columns[{{ $idx }}][_delete]" value="0" class="input-delete">
        @if(!$primary)
        <button type="button" class="btn btn-danger btn-sm btn-remove-row" title="{{ $lang->button_remove_row }}">
            <i class="fa-solid fa-xmark"></i>
        </button>
        @endif
    </td>
</tr>
