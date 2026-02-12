<?php

namespace App\Models\Hrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Department extends Model
{
    protected $table = 'hrm_departments';

    protected $fillable = [
        'company_id', 'parent_id', 'name', 'code',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    // ── HRM 出勤設定 ──

    public function attendanceSetting(): MorphOne
    {
        return $this->morphOne(AttendanceSetting::class, 'settingable');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
