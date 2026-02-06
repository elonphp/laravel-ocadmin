<?php

namespace App\Models\Hrm;

use App\Enums\Common\Gender;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $table = 'hrm_employees';

    protected $fillable = [
        'user_id',
        'organization_id',
        'employee_no',
        'first_name',
        'last_name',
        'email',
        'phone',
        'hire_date',
        'birth_date',
        'gender',
        'job_title',
        'department',
        'address',
        'note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hire_date'  => 'date',
            'birth_date' => 'date',
            'gender'     => Gender::class,
            'is_active'  => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
