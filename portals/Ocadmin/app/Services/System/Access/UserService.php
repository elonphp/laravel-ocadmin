<?php

namespace Portals\Ocadmin\Services\System\Access;

use App\Models\Identity\User;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * 將使用者加入 ocadmin 角色（賦予後台訪問權限）
     * 注意：不包含 Transaction，由 Controller 控制
     *
     * @param User $user 使用者
     * @param array $roleIds 額外角色 ID 陣列（不含 ocadmin）
     */
    public function addOcadminUser(User $user, array $roleIds = []): User
    {
        // 取得 ocadmin 角色
        $ocadminRole = Role::where('name', 'ocadmin')->first();

        if (!$ocadminRole) {
            throw new \RuntimeException('ocadmin 角色不存在，請先建立');
        }

        // 合併 ocadmin 角色和其他選擇的角色
        $allRoleIds = array_unique(array_merge([$ocadminRole->id], $roleIds));

        // 同步角色
        $user->roles()->sync($allRoleIds);

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    /**
     * 更新使用者的功能角色（保留 ocadmin 角色）
     *
     * @param User $user 使用者
     * @param array $roleIds 功能角色 ID 陣列（不含 ocadmin）
     */
    public function updateOcadminRoles(User $user, array $roleIds = []): User
    {
        // 取得 ocadmin 角色
        $ocadminRole = Role::where('name', 'ocadmin')->first();

        if (!$ocadminRole) {
            throw new \RuntimeException('ocadmin 角色不存在');
        }

        // 確保 ocadmin 角色一定在內
        $allRoleIds = array_unique(array_merge([$ocadminRole->id], $roleIds));

        // 同步角色
        $user->roles()->sync($allRoleIds);

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    /**
     * 移除使用者的所有後台角色（包含 ocadmin）
     *
     * @param User $user 使用者
     */
    public function removeOcadminUser(User $user): void
    {
        // 取得所有後台相關角色（有 ocadmin 的）
        // 移除所有角色
        $user->roles()->detach();

        // 清除 Spatie Permission 快取
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * 批次移除使用者的後台訪問權限
     *
     * @param array $userIds 使用者 ID 陣列
     */
    public function batchRemoveOcadminUsers(array $userIds): int
    {
        $count = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->hasRole('ocadmin')) {
                $this->removeOcadminUser($user);
                $count++;
            }
        }

        return $count;
    }
}
