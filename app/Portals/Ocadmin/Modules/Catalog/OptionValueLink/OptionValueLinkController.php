<?php

namespace App\Portals\Ocadmin\Modules\Catalog\OptionValueLink;

use App\Models\Catalog\OptionValueGroup;
use App\Models\Catalog\OptionValueLink;
use App\Models\Catalog\OptionValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class OptionValueLinkController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'catalog/option-value-link'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_catalog,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.catalog.option-value-link.index'),
            ],
        ];
    }

    /**
     * 連動設定主頁
     */
    public function index(Request $request): View
    {
        $groups = OptionValueGroup::with('translations')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['groups'] = $groups;
        $data['selectedGroupId'] = $request->get('group_id');
        $data['url_index'] = route('lang.ocadmin.catalog.option-value-link.index');
        $data['url_links'] = route('lang.ocadmin.catalog.option-value-link.links', ['parentValueId' => '__ID__']);
        $data['url_save_links'] = route('lang.ocadmin.catalog.option-value-link.save-links');
        $data['url_children'] = route('lang.ocadmin.catalog.option-value-link.children', ['optionValueId' => '__ID__']);

        // 若選了群組，載入完整資料
        $data['groupData'] = null;
        if ($request->filled('group_id')) {
            $group = OptionValueGroup::with([
                'levels.option.translations',
                'levels.option.optionValues.translations',
            ])->find($request->group_id);

            if ($group) {
                $data['groupData'] = $this->buildGroupData($group);
            }
        }

        return view('ocadmin.catalog.option-value-link::index', $data);
    }

    /**
     * 取得指定父值的已連動子值 ID
     */
    public function links(int $parentValueId): JsonResponse
    {
        $childIds = OptionValueLink::where('parent_option_value_id', $parentValueId)
            ->pluck('child_option_value_id');

        return response()->json(['child_ids' => $childIds]);
    }

    /**
     * 儲存連動關係
     */
    public function saveLinks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_option_value_id' => 'required|integer|exists:clg_option_values,id',
            'child_option_value_ids' => 'nullable|array',
            'child_option_value_ids.*' => 'integer|exists:clg_option_values,id',
        ]);

        $parentId = $validated['parent_option_value_id'];
        $childIds = $validated['child_option_value_ids'] ?? [];

        // 刪除舊連動
        OptionValueLink::where('parent_option_value_id', $parentId)->delete();

        // 建立新連動
        foreach ($childIds as $childId) {
            OptionValueLink::create([
                'parent_option_value_id' => $parentId,
                'child_option_value_id' => $childId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_save,
        ]);
    }

    /**
     * 前端連動測試：取得子選項值
     */
    public function children(int $optionValueId): JsonResponse
    {
        $childValues = OptionValueLink::where('parent_option_value_id', $optionValueId)
            ->with('childValue.translations')
            ->get()
            ->map(function ($link) {
                return [
                    'id' => $link->childValue->id,
                    'name' => $link->childValue->name,
                    'code' => $link->childValue->code,
                    'option_id' => $link->childValue->option_id,
                ];
            });

        return response()->json(['children' => $childValues]);
    }

    /**
     * 建構群組資料供前端使用
     */
    protected function buildGroupData(OptionValueGroup $group): array
    {
        $levels = [];

        foreach ($group->levels as $level) {
            $option = $level->option;
            $values = $option->optionValues->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    'code' => $v->code,
                ];
            })->toArray();

            $levels[] = [
                'level' => $level->level,
                'option_id' => $option->id,
                'option_name' => $option->name,
                'option_code' => $option->code,
                'values' => $values,
            ];
        }

        return [
            'id' => $group->id,
            'name' => $group->name,
            'code' => $group->code,
            'levels' => $levels,
        ];
    }
}
