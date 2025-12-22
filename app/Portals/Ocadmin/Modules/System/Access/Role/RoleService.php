<?php

namespace App\Portals\Ocadmin\Modules\System\Access\Role;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

class RoleService
{
    /**
     * 建立角色
     * 注意：不包含 Transaction，由 Controller 控制
     */
    public function create(array $data): Role
    {
        $data = $this->withDefaults($data);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        // 同步權限（直接用 id 陣列）
        $role->permissions()->sync($data['permissions'] ?? []);

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /**
     * 更新角色
     */
    public function update(Role $role, array $data): Role
    {
        $data = $this->withDefaults($data);

        $role->update([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        // 同步權限（直接用 id 陣列）
        $role->permissions()->sync($data['permissions'] ?? []);

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /**
     * 刪除角色
     */
    public function delete(Role $role): void
    {
        // 移除所有權限關聯
        $role->permissions()->sync([]);

        $role->delete();

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        // 移除權限關聯
        foreach ($ids as $id) {
            $role = Role::find($id);
            if ($role) {
                $role->permissions()->sync([]);
            }
        }

        $count = Role::whereIn('id', $ids)->delete();

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $count;
    }

    /**
     * 取得權限（按父層分組）
     */
    public function getPermissionsGrouped(): Collection
    {
        // 取得所有頂層權限（選單類型）
        $topLevelPermissions = Permission::whereNull('parent_id')
            ->where('type', 'menu')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $topLevelPermissions->map(function ($parent) {
            return [
                'id' => $parent->id,
                'name' => $parent->name,
                'title' => $parent->title ?: $parent->name,
                'type' => $parent->type,
                'children' => $this->getChildPermissions($parent->id),
            ];
        });
    }

    /**
     * 遞迴取得子權限
     */
    protected function getChildPermissions(int $parentId): array
    {
        $children = Permission::where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $children->map(function ($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'title' => $child->title ?: $child->name,
                'type' => $child->type,
                'children' => $this->getChildPermissions($child->id),
            ];
        })->toArray();
    }

    /**
     * 設定預設值
     */
    protected function withDefaults(array $data): array
    {
        $defaults = [
            'guard_name' => 'web',
        ];

        return array_merge($defaults, $data);
    }
}
