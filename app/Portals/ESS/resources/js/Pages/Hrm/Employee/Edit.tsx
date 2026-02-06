import { useForm, Head } from '@inertiajs/react';
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/react';
import { FormEvent } from 'react';
import AuthenticatedLayout from '../../../Components/Layout/AuthenticatedLayout';
import type { Employee } from '../../../types';

interface SelectOption {
    value: string;
    label: string;
}

interface Props {
    employee: Employee;
    genderOptions: SelectOption[];
}

export default function Edit({ employee, genderOptions }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        phone:      employee.phone ?? '',
        birth_date: employee.birth_date ?? '',
        gender:     employee.gender ?? '',
        address:    employee.address ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('lang.ess.profile.update'));
    };

    const selectedGender = genderOptions.find(g => g.value === data.gender) ?? genderOptions[0];

    return (
        <AuthenticatedLayout>
            <Head title="個人資料" />

            <div className="max-w-2xl">
                <h1 className="text-2xl font-bold mb-6">個人資料</h1>

                {/* 唯讀區塊 */}
                <div className="card bg-base-100 shadow mb-6">
                    <div className="card-body">
                        <h2 className="card-title text-sm text-gray-500">基本資訊（由管理員維護）</h2>
                        <div className="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <span className="text-sm text-gray-500">員工編號</span>
                                <p className="font-medium">{employee.employee_no || '-'}</p>
                            </div>
                            <div>
                                <span className="text-sm text-gray-500">姓名</span>
                                <p className="font-medium">
                                    {employee.first_name} {employee.last_name}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-gray-500">Email</span>
                                <p className="font-medium">{employee.email || '-'}</p>
                            </div>
                            <div>
                                <span className="text-sm text-gray-500">職稱</span>
                                <p className="font-medium">{employee.job_title ?? '-'}</p>
                            </div>
                            <div>
                                <span className="text-sm text-gray-500">部門</span>
                                <p className="font-medium">{employee.department ?? '-'}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 可編輯表單 */}
                <form onSubmit={submit}>
                    <div className="card bg-base-100 shadow">
                        <div className="card-body space-y-4">
                            <h2 className="card-title text-sm text-gray-500">可編輯資訊</h2>

                            {/* 電話 */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">電話</span>
                                </label>
                                <input
                                    type="text"
                                    className={`input input-bordered ${errors.phone ? 'input-error' : ''}`}
                                    value={data.phone}
                                    onChange={e => setData('phone', e.target.value)}
                                />
                                {errors.phone && (
                                    <span className="text-error text-sm mt-1">{errors.phone}</span>
                                )}
                            </div>

                            {/* 生日 */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">生日</span>
                                </label>
                                <input
                                    type="date"
                                    className={`input input-bordered ${errors.birth_date ? 'input-error' : ''}`}
                                    value={data.birth_date}
                                    onChange={e => setData('birth_date', e.target.value)}
                                />
                                {errors.birth_date && (
                                    <span className="text-error text-sm mt-1">{errors.birth_date}</span>
                                )}
                            </div>

                            {/* 性別 — Headless UI Listbox */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">性別</span>
                                </label>
                                <Listbox value={data.gender} onChange={val => setData('gender', val)}>
                                    <div className="relative">
                                        <ListboxButton className="select select-bordered w-full text-left">
                                            {selectedGender.label}
                                        </ListboxButton>
                                        <ListboxOptions className="menu bg-base-100 shadow-lg rounded-box absolute z-10 mt-1 w-full">
                                            {genderOptions.map(option => (
                                                <ListboxOption
                                                    key={option.value}
                                                    value={option.value}
                                                    className={({ focus }) =>
                                                        `cursor-pointer px-4 py-2 ${focus ? 'bg-primary text-primary-content' : ''}`
                                                    }
                                                >
                                                    {option.label}
                                                </ListboxOption>
                                            ))}
                                        </ListboxOptions>
                                    </div>
                                </Listbox>
                            </div>

                            {/* 地址 */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">地址</span>
                                </label>
                                <textarea
                                    className={`textarea textarea-bordered ${errors.address ? 'textarea-error' : ''}`}
                                    rows={3}
                                    value={data.address}
                                    onChange={e => setData('address', e.target.value)}
                                />
                                {errors.address && (
                                    <span className="text-error text-sm mt-1">{errors.address}</span>
                                )}
                            </div>

                            <div className="card-actions justify-end pt-4">
                                <button
                                    type="submit"
                                    className="btn btn-primary"
                                    disabled={processing}
                                >
                                    {processing && <span className="loading loading-spinner loading-sm" />}
                                    儲存
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
