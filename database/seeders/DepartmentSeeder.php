<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $companyIds = Company::pluck('id', 'code')->toArray();

        // 天行集團（母公司）— 管理部門
        $this->createDepartments($companyIds['TX'], [
            ['name' => '管理部',   'code' => 'ADM', 'sort_order' => 1],
            ['name' => '財務部',   'code' => 'FIN', 'sort_order' => 2],
        ]);

        // 星河科技（子公司 A）— 研發 + 業務
        $rdDept = Department::create([
            'company_id' => $companyIds['XH'],
            'name'       => '研發部',
            'code'       => 'RD',
            'sort_order' => 1,
        ]);
        Department::create([
            'company_id' => $companyIds['XH'],
            'parent_id'  => $rdDept->id,
            'name'       => '前端組',
            'code'       => 'RD-FE',
            'sort_order' => 1,
        ]);
        Department::create([
            'company_id' => $companyIds['XH'],
            'parent_id'  => $rdDept->id,
            'name'       => '後端組',
            'code'       => 'RD-BE',
            'sort_order' => 2,
        ]);
        $this->createDepartments($companyIds['XH'], [
            ['name' => '業務部',   'code' => 'SAL', 'sort_order' => 2],
            ['name' => '人資部',   'code' => 'HR',  'sort_order' => 3],
        ]);

        // 雲端數位（子公司 B）— 營運 + 客服
        $this->createDepartments($companyIds['YD'], [
            ['name' => '營運部',   'code' => 'OPS', 'sort_order' => 1],
            ['name' => '客服部',   'code' => 'CS',  'sort_order' => 2],
        ]);

        // 晨光創意（獨立公司）— 行銷 + 業務
        $this->createDepartments($companyIds['CG'], [
            ['name' => '行銷部',   'code' => 'MKT', 'sort_order' => 1],
            ['name' => '業務部',   'code' => 'SAL', 'sort_order' => 2],
            ['name' => '資訊部',   'code' => 'IT',  'sort_order' => 3],
        ]);
    }

    private function createDepartments(int $companyId, array $departments): void
    {
        foreach ($departments as $dept) {
            Department::create(array_merge($dept, ['company_id' => $companyId]));
        }
    }
}
