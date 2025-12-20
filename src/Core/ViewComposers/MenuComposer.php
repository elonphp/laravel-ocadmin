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

        // 從模組載入選單
        $moduleMenus = $this->moduleLoader->getMenuItems();
        foreach ($moduleMenus as $menu) {
            $menus[] = $this->processMenu($menu);
        }

        return $menus;
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
