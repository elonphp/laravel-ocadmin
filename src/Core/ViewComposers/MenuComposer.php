<?php

namespace Elonphp\LaravelOcadminModules\Core\ViewComposers;

use Illuminate\View\View;
use Elonphp\LaravelOcadminModules\Core\Support\ModuleLoader;

class MenuComposer
{
    public function __construct(
        protected ModuleLoader $moduleLoader
    ) {}

    public function compose(View $view): void
    {
        $view->with('menus', $this->buildMenus());
    }

    protected function buildMenus(): array
    {
        $menus = [];

        // Dashboard
        $menus[] = [
            'id'       => 'menu-dashboard',
            'icon'     => 'fa-solid fa-home',
            'name'     => __('ocadmin::menu.dashboard'),
            'href'     => ocadmin_route('dashboard'),
            'children' => []
        ];

        // Module menus - merge items with same title
        $moduleMenus = $this->moduleLoader->getMenuItems();
        $mergedMenus = $this->mergeMenusByTitle($moduleMenus);

        // Sort by config menu_order
        $mergedMenus = $this->sortMenusByOrder($mergedMenus);

        foreach ($mergedMenus as $menu) {
            $menus[] = $this->processMenu($menu);
        }

        return $menus;
    }

    /**
     * 根據 config 的 menu_order 排序選單
     */
    protected function sortMenusByOrder(array $menus): array
    {
        $order = config('ocadmin.menu_order', []);

        usort($menus, function ($a, $b) use ($order) {
            $aTitle = $a['title'] ?? '';
            $bTitle = $b['title'] ?? '';

            $aIndex = array_search($aTitle, $order);
            $bIndex = array_search($bTitle, $order);

            // 不在 order 中的排到最後
            $aIndex = $aIndex === false ? 999 : $aIndex;
            $bIndex = $bIndex === false ? 999 : $bIndex;

            return $aIndex <=> $bIndex;
        });

        return $menus;
    }

    /**
     * 合併相同 title 的選單項目
     */
    protected function mergeMenusByTitle(array $menus): array
    {
        $merged = [];

        foreach ($menus as $menu) {
            $title = $menu['title'] ?? '';

            if (empty($title)) {
                $merged[] = $menu;
                continue;
            }

            // 尋找是否有相同 title 的選單
            $found = false;
            foreach ($merged as &$existing) {
                if (($existing['title'] ?? '') === $title) {
                    // 合併 children
                    $existing['children'] = array_merge(
                        $existing['children'] ?? [],
                        $menu['children'] ?? []
                    );
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $merged[] = $menu;
            }
        }

        return $merged;
    }

    /**
     * 處理選單項目，轉換路由名稱為 URL
     */
    protected function processMenu(array $menu): array
    {
        // 處理 title（翻譯並轉為 name）
        if (!empty($menu['title'])) {
            $menu['name'] = __($menu['title']);
            unset($menu['title']);
        }

        // 處理 href（如果是路由名稱則轉換）
        if (!empty($menu['route'])) {
            $menu['href'] = ocadmin_route($menu['route']);
            unset($menu['route']);
        }

        // 處理 icon（加上 fa-solid 前綴）
        if (!empty($menu['icon']) && !str_starts_with($menu['icon'], 'fa-')) {
            $menu['icon'] = 'fa-solid fa-' . $menu['icon'];
        }

        // 處理子選單
        if (!empty($menu['children'])) {
            $menu['children'] = array_map(
                fn($child) => $this->processMenu($child),
                $menu['children']
            );
        }

        // 確保必要欄位存在
        $menu['id'] = $menu['id'] ?? 'menu-' . uniqid();
        $menu['icon'] = $menu['icon'] ?? '';
        $menu['name'] = $menu['name'] ?? '';
        $menu['href'] = $menu['href'] ?? '';
        $menu['children'] = $menu['children'] ?? [];

        return $menu;
    }
}
