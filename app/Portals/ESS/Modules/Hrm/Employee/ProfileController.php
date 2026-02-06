<?php

namespace App\Portals\ESS\Modules\Hrm\Employee;

use App\Enums\Common\Gender;
use App\Models\Hrm\Employee;
use App\Portals\ESS\Core\Controllers\EssController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends EssController
{
    public function edit(Request $request): Response
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        return Inertia::render('Hrm/Employee/Edit', [
            'employee' => $employee->only([
                'id', 'employee_no', 'first_name', 'last_name',
                'email', 'phone', 'birth_date', 'gender',
                'job_title', 'department', 'address',
            ]),
            'genderOptions' => Gender::options(__('enums.gender_placeholder')),
        ]);
    }

    public function update(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'phone'      => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'gender'     => ['nullable', Rule::enum(Gender::class)],
            'address'    => 'nullable|string',
        ]);

        $employee->update($validated);

        return back()->with('success', '個人資料更新成功！');
    }
}
