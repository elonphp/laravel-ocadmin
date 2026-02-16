<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Hrm\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
     * 是否擁有後台角色（admin.* 開頭）
     */
    public function hasBackendRole(): bool
    {
        return $this->roles->contains(fn($r) => $r->name === 'super_admin' || str_starts_with($r->name, 'admin.'));
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
