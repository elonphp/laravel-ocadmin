<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasTranslation;

    protected $fillable = [
        'parent_id', 'code', 'business_no', 'phone', 'address',
        'is_active', 'sort_order',
    ];

    protected array $translatedAttributes = ['name', 'short_name'];

    protected string $translationModel = CompanyTranslation::class;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ── 自引用階層 ──

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_id');
    }

    // ── 下屬資料 ──

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(\App\Models\Hrm\Employee::class);
    }

    // ── 使用者存取 ──

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
