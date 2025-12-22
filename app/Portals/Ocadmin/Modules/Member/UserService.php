<?php

namespace App\Portals\Ocadmin\Modules\Member;

use App\Models\Identity\User;

class UserService
{
    /**
     * 建立會員
     * 注意：不包含 Transaction，由 Controller 控制
     */
    public function create(array $data): User
    {
        $userData = User::withDefaults($data);

        $user = User::create($userData);

        // 處理 meta 欄位
        if (isset($data['metas']) && is_array($data['metas'])) {
            $user->setMetas($data['metas']);
        }

        return $user;
    }

    /**
     * 更新會員
     */
    public function update(User $user, array $data): User
    {
        $userData = User::withDefaults($data);

        // 處理密碼（空值時不更新）
        if (empty($userData['password'])) {
            unset($userData['password']);
        }

        $user->update($userData);

        // 處理 meta 欄位
        if (isset($data['metas']) && is_array($data['metas'])) {
            $user->setMetas($data['metas']);
        }

        return $user;
    }

    /**
     * 刪除會員
     */
    public function delete(User $user): void
    {
        $user->delete();
    }

    /**
     * 批次刪除
     */
    public function batchDelete(array $ids): int
    {
        return User::whereIn('id', $ids)->delete();
    }
}
