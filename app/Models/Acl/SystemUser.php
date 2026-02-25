<?php

namespace App\Models\Acl;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemUser extends Model
{
    protected $table = 'acl_system_users';

    protected $fillable = [
        'user_id',
        'app',
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
     * 角色連動：依據使用者目前的角色狀態，同步更新 acl_system_users 記錄。
     *
     * super_admin 和 prefix 角色各自獨立追蹤，互不干擾。
     * 由 UserController 儲存角色後呼叫。
     */
    public static function syncFromRoles(User $user, string $prefix = 'admin'): void
    {
        // super_admin 獨立追蹤
        static::syncApp($user, 'super_admin', $user->hasRole('super_admin'));

        // prefix 角色獨立追蹤
        $hasPrefixRoles = $user->roles->contains(
            fn ($role) => str_starts_with($role->name, $prefix . '.')
        );
        static::syncApp($user, $prefix, $hasPrefixRoles);
    }

    /**
     * 針對單一 app 群組同步記錄。
     */
    protected static function syncApp(User $user, string $app, bool $hasRoles): void
    {
        $active = static::where('user_id', $user->id)
            ->where('app', $app)
            ->whereNull('revoked_at')
            ->first();

        if ($hasRoles && !$active) {
            // 授予：建立新的授權期間
            static::create([
                'user_id' => $user->id,
                'app' => $app,
                'enrolled_at' => now(),
            ]);
        } elseif (!$hasRoles && $active) {
            // 移除：關閉目前的授權期間
            $active->update(['revoked_at' => now()]);
        }
    }
}
