<?php

namespace App\Portals\Ocadmin\Modules\System\Menu;

use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class MenuTreeController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['system/menu'];
    }

    /**
     * 樹狀結構頁面
     */
    public function index(Request $request): View
    {
        $portals = $this->getPortalOptions();
        $portal = $request->query('portal', $portals[0] ?? 'ocadmin');

        // 取得該 portal 下所有已存在的 group 值
        $groups = Menu::where('portal', $portal)
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->all();

        if (empty($groups)) {
            $groups = ['main'];
        }

        $group = $request->query('group', $groups[0] ?? 'main');

        $menus = Menu::with([
                'allChildren.allChildren.allChildren', // 支援最多 4 層
            ])
            ->where('portal', $portal)
            ->where('group', $group)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $data['lang'] = $this->lang;
        $data['menus'] = $menus;
        $data['portals'] = $portals;
        $data['groups'] = $groups;
        $data['current_portal'] = $portal;
        $data['current_group'] = $group;
        $data['tree_url'] = route('lang.ocadmin.system.menu-tree.index');
        $data['list_url'] = route('lang.ocadmin.system.menus.index');
        $data['reorder_url'] = route('lang.ocadmin.system.menu-tree.reorder');

        return view('ocadmin::system.menu.tree', $data);
    }

    private function getPortalOptions(): array
    {
        $portals = config('portals', []);
        unset($portals['global']);

        return array_keys($portals);
    }

    /**
     * 儲存拖曳排序結果
     */
    public function reorder(Request $request): JsonResponse
    {
        $items = $request->input('items', []);

        $this->saveTree($items, null);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_reorder,
        ]);
    }

    /**
     * 遞迴更新 parent_id + sort_order
     */
    private function saveTree(array $items, ?int $parentId): void
    {
        foreach ($items as $index => $item) {
            Menu::where('id', $item['id'])->update([
                'parent_id'  => $parentId,
                'sort_order' => $index,
            ]);

            if (!empty($item['children'])) {
                $this->saveTree($item['children'], (int) $item['id']);
            }
        }
    }
}
