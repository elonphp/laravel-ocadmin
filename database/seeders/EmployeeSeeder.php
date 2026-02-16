<?php

namespace Database\Seeders;

use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();
        $users = User::all();

        $titles = ['工程師', '資深工程師', '主管', '經理', '副理', '專員', '總監'];

        foreach ($users as $index => $user) {
            $department = $departments->random();

            Employee::create([
                'user_id'       => $user->id,
                'company_id'    => $department->company_id,
                'department_id' => $department->id,
                'employee_no'   => 'EMP' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'first_name'    => $user->first_name ?: $user->name,
                'last_name'     => $user->last_name,
                'email'         => $user->email,
                'phone'         => '09' . rand(10000000, 99999999),
                'hire_date'     => now()->subDays(rand(30, 1800))->format('Y-m-d'),
                'gender'        => ['male', 'female', 'other'][array_rand(['male', 'female', 'other'])],
                'job_title'     => $titles[array_rand($titles)],
                'is_active'     => true,
            ]);

        }
    }
}
