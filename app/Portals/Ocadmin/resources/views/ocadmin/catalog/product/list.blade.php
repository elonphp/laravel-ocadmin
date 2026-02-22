<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_name }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                        {{ $lang->column_name }}
                    </a>
                </th>
                <th>
                    <a href="{{ $sort_model }}" @class([request('order', 'asc') => request('sort') === 'model'])>
                        {{ $lang->column_model }}
                    </a>
                </th>
                <th class="text-end">
                    <a href="{{ $sort_price }}" @class([request('order', 'asc') => request('sort') === 'price'])>
                        {{ $lang->column_price }}
                    </a>
                </th>
                <th class="text-center">
                    <a href="{{ $sort_quantity }}" @class([request('order', 'asc') => request('sort') === 'quantity'])>
                        {{ $lang->column_quantity }}
                    </a>
                </th>
                <th class="text-center">{{ $lang->column_status }}</th>
                <th class="text-center">
                    <a href="{{ $sort_sort_order }}" @class([request('order', 'asc') => request('sort') === 'sort_order'])>
                        {{ $lang->column_sort_order }}
                    </a>
                </th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr @if(!$product->status) style="opacity: 0.5;" @endif>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $product->id }}" class="form-check-input">
                </td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->model }}</td>
                <td class="text-end">{{ number_format($product->price, 0) }}</td>
                <td class="text-center">
                    @if($product->quantity <= 0)
                    <span class="badge bg-warning">{{ $product->quantity }}</span>
                    @elseif($product->quantity <= 5)
                    <span class="badge bg-danger">{{ $product->quantity }}</span>
                    @else
                    <span class="badge bg-success">{{ $product->quantity }}</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($product->status)
                    <span class="badge bg-success">{{ $lang->text_enabled }}</span>
                    @else
                    <span class="badge bg-secondary">{{ $lang->text_disabled }}</span>
                    @endif
                </td>
                <td class="text-center">{{ $product->sort_order }}</td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.catalog.product.edit', $product) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">顯示 {{ $products->firstItem() ?? 0 }} 到 {{ $products->lastItem() ?? 0 }}，共 {{ $products->total() }} 筆</div>
</div>
