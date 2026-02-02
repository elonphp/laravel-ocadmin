<?php

namespace App\Portals\Ocadmin\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected array $breadcrumbs = [];

    public function __construct()
    {
        if (basename($_SERVER['SCRIPT_NAME'] ?? '') == 'artisan') {
            return;
        }

        $this->middleware(function ($request, $next) {
            $this->setBreadcrumbs();
            return $next($request);
        });
    }

    protected function setBreadcrumbs(): void
    {
        // 由子類別覆寫
    }

    protected function buildUrlParams(Request $request): string
    {
        $params = [];

        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') || str_starts_with($key, 'equal_')) {
                if ($value !== null && $value !== '') {
                    $params[] = $key . '=' . urlencode($value);
                }
            }
        }

        if ($request->has('search') && $request->search) {
            $params[] = 'search=' . urlencode($request->search);
        }

        if ($request->has('limit') && $request->limit) {
            $params[] = 'limit=' . $request->limit;
        }

        if ($request->has('page') && $request->page) {
            $params[] = 'page=' . $request->page;
        }

        if ($request->has('sort') && $request->sort) {
            $params[] = 'sort=' . urlencode($request->sort);
        }

        if ($request->has('order') && $request->order) {
            $params[] = 'order=' . urlencode($request->order);
        }

        return $params ? '?' . implode('&', $params) : '';
    }
}
