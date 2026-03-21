<?php

namespace App\Models\Acl;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalUser extends Model
{
    protected $table = 'acl_portal_users';

    protected $fillable = [
        'user_id',
        'portal',
        'enrolled_at',
        'revoked_at',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 使用者目前是否擁有有效角色（已 enrolled 且未 revoked）
     */
    public function isActive(): bool
    {
        return $this->enrolled_at !== null && $this->revoked_at === null;
    }

    /**
     * 角色連動：依據使用者目前的角色狀態，同步更新 acl_portal_users 記錄。
     *
     * - portal 前綴角色（如 admin.xxx）→ portal = 'admin'
     * - 無前綴角色（如 super_admin）→ portal = 'global'
     *
     * 由 UserController 儲存角色後呼叫。
     */
    public static function syncFromRoles(User $user, string $portal = 'admin'): void
    {
        if ($portal === 'global') {
            // 無前綴角色（角色名稱不含 dot）
            $hasRoles = $user->roles->contains(
                fn ($role) => !str_contains($role->name, '.')
            );
        } else {
            $hasRoles = $user->roles->contains(
                fn ($role) => str_starts_with($role->name, $portal . '.')
            );
        }

        static::syncPortal($user, $portal, $hasRoles);
    }

    /**
     * 針對單一 portal 群組同步記錄（upsert 模式，每個 user+portal 只保留一筆）。
     */
    protected static function syncPortal(User $user, string $portal, bool $hasRoles): void
    {
        if ($hasRoles) {
            // 有角色 → 啟用（新增或重新啟用）
            static::updateOrCreate(
                ['user_id' => $user->id, 'portal' => $portal],
                ['enrolled_at' => now(), 'revoked_at' => null],
            );
        } else {
            // 無角色 → 撤銷（若有記錄才更新）
            static::where('user_id', $user->id)
                ->where('portal', $portal)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }
    }
}
