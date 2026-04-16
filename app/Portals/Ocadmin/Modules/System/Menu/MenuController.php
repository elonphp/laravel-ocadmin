<?php

namespace App\Portals\Ocadmin\Modules\System\Menu;

use App\Helpers\Classes\OrmHelper;
use App\Models\Menu;
use App\Models\Acl\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class MenuController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['system/menu'];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['list'] = $this->getList($request);

        $data['list_url']         = route('lang.ocadmin.system.menus.list');
        $data['index_url']        = route('lang.ocadmin.system.menus.index');
        $data['add_url']          = route('lang.ocadmin.system.menus.create');
        $data['batch_delete_url'] = route('lang.ocadmin.system.menus.batch-delete');
        $data['tree_url']         = route('lang.ocadmin.system.menu-tree.index');

        return view('ocadmin::system.menu.index', $data);
    }

    /**
     * AJAX 入口（列表刷新）
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯
     */
    protected function getList(Request $request): string
    {
        $query = Menu::with(['parent.translation', 'translation'])
            ->where('portal', 'ocadmin');

        $filter_data = $this->filterData($request, ['equal_is_active']);

        $filter_data['sort'] = $request->query('sort', 'sort_order');
        $filter_data['order'] = $request->query('order', 'asc');

        // search 關鍵字 — 搜尋翻譯名稱
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('translations', function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_display_name', $search);
            });
            unset($filter_data['search']);
        }

        OrmHelper::prepare($query, $filter_data);

        $menus = OrmHelper::getResult($query, $filter_data);
        $menus->withPath(route('lang.ocadmin.system.menus.list'))->withQueryString();

        $data['lang'] = $this->lang;
        $data['menus'] = $menus;
        $data['pagination'] = $menus->links('ocadmin::pagination.default');

        $url = $this->buildUrlParams($request);
        $data['urlParams'] = $this->buildEditUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.menus.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_display_name'] = $baseUrl . "?sort=id&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_sort_order'] = $baseUrl . "?sort=sort_order&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::system.menu.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;

        $data['menu'] = new Menu(['portal' => 'ocadmin', 'group' => 'main']);
        $data['portals'] = $this->getPortalOptions();
        $data['parents'] = $this->getParentOptions();
        $data['permissions'] = Permission::orderBy('name')->pluck('name', 'name');

        $data['save_url'] = route('lang.ocadmin.system.menus.store');
        $data['back_url'] = route('lang.ocadmin.system.menus.index');

        return view('ocadmin::system.menu.form', $data);
    }

    /**
     * 儲存新增
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateMenu($request);

        $menu = Menu::create($validated);
        $this->saveTranslations($menu, $request);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.system.menus.edit', $menu),
            'form_action' => route('lang.ocadmin.system.menus.update', $menu),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(Menu $menu): View
    {
        $data['lang'] = $this->lang;

        $data['menu'] = $menu->load('translations');
        $data['portals'] = $this->getPortalOptions();
        $data['parents'] = $this->getParentOptions($menu->id);
        $data['permissions'] = Permission::orderBy('name')->pluck('name', 'name');

        $data['save_url'] = route('lang.ocadmin.system.menus.update', $menu);
        $data['back_url'] = route('lang.ocadmin.system.menus.index');

        return view('ocadmin::system.menu.form', $data);
    }

    /**
     * 儲存編輯
     */
    public function update(Request $request, Menu $menu): JsonResponse
    {
        $validated = $this->validateMenu($request, $menu);

        $menu->update($validated);
        $this->saveTranslations($menu, $request);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_delete]);
        }

        Menu::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    // ========== Private ==========

    private function validateMenu(Request $request, ?Menu $menu = null): array
    {
        return $request->validate([
            'portal'          => 'required|string|max:20',
            'group'           => 'required|string|max:50',
            'parent_id'       => 'nullable|integer|exists:sys_menus,id',
            'permission_name' => 'nullable|string|max:255',
            'route_name'      => 'nullable|string|max:255',
            'href'            => 'nullable|string|max:255',
            'icon'            => 'nullable|string|max:255',
            'sort_order'      => 'nullable|integer|min:0',
            'is_active'       => 'nullable|boolean',
        ]);
    }

    private function saveTranslations(Menu $menu, Request $request): void
    {
        $translations = $request->input('translations', []);

        foreach ($translations as $locale => $fields) {
            if (!empty($fields['display_name'])) {
                $menu->saveTranslation($locale, ['display_name' => $fields['display_name']]);
            }
        }
    }

    private function getPortalOptions(): array
    {
        $portals = config('portals', []);
        unset($portals['global']);

        return array_keys($portals);
    }

    private function getParentOptions(?int $excludeId = null): \Illuminate\Support\Collection
    {
        $query = Menu::with('translation')
            ->where('portal', 'ocadmin')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // 取 L1 + L2 做為可選父層
        $roots = $query->get();
        $options = collect();

        foreach ($roots as $root) {
            $options->push((object)['id' => $root->id, 'name' => $root->display_name]);

            $children = Menu::with('translation')
                ->where('parent_id', $root->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->get();

            foreach ($children as $child) {
                $options->push((object)['id' => $child->id, 'name' => '　└ ' . $child->display_name]);
            }
        }

        return $options;
    }
}
