<?php

namespace App\Models\Hrm;

use App\Enums\Common\Gender;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $table = 'hrm_employees';

    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'employee_no',
        'first_name',
        'last_name',
        'email',
        'phone',
        'hire_date',
        'birth_date',
        'gender',
        'job_title',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
