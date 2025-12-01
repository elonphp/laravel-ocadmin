<?php

namespace Portals\Ocadmin\Services\System\Access;

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * 建立權限
     * 注意：不包含 Transaction，由 Controller 控制
     */
    public function create(array $data): Permission
    {
        $data = $this->withDefaults($data);

        return Permission::create($data);
    }

    /**
     * 更新權限
     */
    public function update(Permission $permission, array $data): Permission
    {
        $data = $this->withDefaults($data);

        $permission->update($data);

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $permission;
    }

    /**
     * 刪除權限
     */
    public function delete(Permission $permission): void
    {
        $permission->delete();

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        $count = Permission::whereIn('id', $ids)->delete();

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $count;
    }

    /**
     * 取得父層選項（排除自己和子孫）
     */
    public function getParentOptions(?int $excludeId = null): Collection
    {
        $query = Permission::query()
            ->where('type', 'menu')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($excludeId) {
            // 排除自己
            $query->where('id', '!=', $excludeId);

            // 排除所有子孫（遞迴查詢）
            $descendantIds = $this->getDescendantIds($excludeId);
            if (!empty($descendantIds)) {
                $query->whereNotIn('id', $descendantIds);
            }
        }

        return $query->get(['id', 'name', 'title', 'parent_id']);
    }

    /**
     * 取得所有子孫 ID
     */
    protected function getDescendantIds(int $parentId): array
    {
        $ids = [];
        $children = Permission::where('parent_id', $parentId)->pluck('id');

        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getDescendantIds($childId));
        }

        return $ids;
    }

    /**
     * 取得樹狀結構
     */
    public function getTree(?string $type = null): Collection
    {
        $query = Permission::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($type) {
            $query->where('type', $type);
        }

        $permissions = $query->get();

        return $permissions->map(function ($permission) use ($type) {
            return $this->buildTreeNode($permission, $type);
        });
    }

    /**
     * 建立樹狀節點
     */
    protected function buildTreeNode(Permission $permission, ?string $type = null): array
    {
        $query = Permission::where('parent_id', $permission->id)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($type) {
            $query->where('type', $type);
        }

        $children = $query->get();

        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'title' => $permission->title,
            'type' => $permission->type,
            'children' => $children->map(function ($child) use ($type) {
                return $this->buildTreeNode($child, $type);
            })->toArray(),
        ];
    }

    /**
     * 設定預設值
     */
    protected function withDefaults(array $data): array
    {
        $defaults = [
            'guard_name' => 'web',
            'sort_order' => 0,
            'type' => 'menu',
        ];

        // 處理空的 parent_id
        if (isset($data['parent_id']) && $data['parent_id'] === '') {
            $data['parent_id'] = null;
        }

        return array_merge($defaults, $data);
    }
}
