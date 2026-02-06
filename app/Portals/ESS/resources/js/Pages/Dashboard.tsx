import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '../Components/Layout/AuthenticatedLayout';

export default function Dashboard() {
    return (
        <AuthenticatedLayout>
            <Head title="儀表板" />
            <h1 className="text-2xl font-bold mb-4">儀表板</h1>
            <div className="card bg-base-100 shadow">
                <div className="card-body">
                    <p className="text-gray-500">歡迎使用 ESS 員工自助服務系統。</p>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
