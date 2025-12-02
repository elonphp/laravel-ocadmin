<?php

namespace App\Models\Identity;

use App\Traits\HasMetas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasMetas, HasRoles;

    protected $fillable = [
        'username',
        'email',
        'mobile',
        'password',
        'name',
        'display_name',
        'avatar',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * 填充預設值
     */
    public static function withDefaults(array $data): array
    {
        return array_merge([
            'is_active' => true,
        ], $data);
    }

    /**
     * 關聯：Metas
     */
    public function metas()
    {
        return $this->hasMany(UserMeta::class);
    }

    /**
     * 關聯：地址
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * 取得預設配送地址
     */
    public function defaultShippingAddress()
    {
        return $this->hasOne(Address::class)
            ->where('type', Address::TYPE_SHIPPING)
            ->where('is_default', true);
    }

    /**
     * 取得預設帳單地址
     */
    public function defaultBillingAddress()
    {
        return $this->hasOne(Address::class)
            ->where('type', Address::TYPE_BILLING)
            ->where('is_default', true);
    }

    /**
     * 是否為超級管理員（繞過所有權限檢查）
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('admin.super_admin');
    }

    /**
     * 是否有後台權限（必須有 admin.staff 角色）
     */
    public function canAccessBackend(): bool
    {
        return $this->hasRole('admin.staff');
    }
}
