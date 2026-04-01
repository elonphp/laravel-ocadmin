<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Acl\PortalUser;
use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (empty($user->name)) {
                $user->name = trim($user->first_name . ' ' . $user->last_name)
                    ?: $user->first_name
                    ?: $user->last_name
                    ?: $user->username
                    ?: $user->email;
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'first_name',
        'last_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * 是否擁有指定 Portal 的角色（如 admin.*, ess.*）
     * super_admin 永遠通過任何 Portal 檢查。
     */
    public function hasPortalRole(string $prefix): bool
    {
        return $this->roles->contains(
            fn ($role) => $role->name === 'super_admin'
                || str_starts_with($role->name, $prefix . '.')
        );
    }

    /**
     * 是否擁有後台角色（hasPortalRole('admin') 的便捷方法）
     */
    public function hasBackendRole(): bool
    {
        return $this->hasPortalRole('admin');
    }

    /**
     * 覆寫 can()，改用角色組合快取查詢權限
     *
     * @see docs/md/0104_權限機制.md §11 使用者權限快取
     */
    public function can($ability, $arguments = []): bool
    {
        // Policy 檢查（有 arguments 時走原生 Gate 邏輯）
        if (!empty($arguments)) {
            return parent::can($ability, $arguments);
        }

        // super_admin 跳過快取（Gate::before 也會放行，這裡提前 return 省掉 cache lookup）
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return in_array($ability, $this->getCachedPermissions());
    }

    /**
     * 取得快取的權限名稱陣列
     *
     * Cache key 格式：role:{sorted_role_ids}:v{version}
     * 相同角色組合的使用者共用同一份快取
     */
    protected function getCachedPermissions(): array
    {
        $roleIds = $this->roles->pluck('id')->sort()->implode('-');

        if ($roleIds === '') {
            return [];
        }

        $ver = Cache::get('role_perm_ver', 1);
        $key = "role:{$roleIds}:v{$ver}";

        return Cache::remember($key, now()->addDays(7), function () {
            return $this->getPermissionsViaRoles()
                ->pluck('name')
                ->toArray();
        });
    }

    public function portalUsers(): HasMany
    {
        return $this->hasMany(PortalUser::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
