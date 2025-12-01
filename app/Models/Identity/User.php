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
}
