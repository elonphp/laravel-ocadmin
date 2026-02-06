import { PropsWithChildren } from 'react';
import { Link, usePage } from '@inertiajs/react';
import type { PageProps, MenuItem } from '../../types';

export default function AuthenticatedLayout({ children }: PropsWithChildren) {
    const { auth, menu, flash } = usePage<PageProps>().props;

    return (
        <div className="drawer lg:drawer-open">
            <input id="ess-drawer" type="checkbox" className="drawer-toggle" />

            {/* 主內容區 */}
            <div className="drawer-content flex flex-col min-h-screen">
                {/* Header (mobile) */}
                <div className="navbar bg-base-100 shadow-sm lg:hidden">
                    <div className="flex-none">
                        <label htmlFor="ess-drawer" className="btn btn-ghost drawer-button">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                      d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </label>
                    </div>
                    <div className="flex-1">
                        <span className="text-lg font-bold">ESS</span>
                    </div>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="alert alert-success mx-6 mt-4">
                        <span>{flash.success}</span>
                    </div>
                )}
                {flash?.error && (
                    <div className="alert alert-error mx-6 mt-4">
                        <span>{flash.error}</span>
                    </div>
                )}

                {/* Page Content */}
                <main className="flex-1 p-6">
                    {children}
                </main>
            </div>

            {/* 側邊欄 */}
            <div className="drawer-side">
                <label htmlFor="ess-drawer" aria-label="close sidebar" className="drawer-overlay" />
                <aside className="bg-base-200 w-64 min-h-full flex flex-col">
                    <div className="p-4 border-b border-base-300">
                        <span className="text-xl font-bold">ESS Portal</span>
                    </div>
                    <ul className="menu flex-1 p-4">
                        {menu.map((item: MenuItem) => (
                            <li key={item.href}>
                                <Link href={item.href}>{item.name}</Link>
                            </li>
                        ))}
                    </ul>
                    <div className="p-4 border-t border-base-300">
                        <div className="text-sm text-gray-500 mb-2">{auth.user.name}</div>
                        <Link
                            href={route('lang.ess.logout')}
                            method="post"
                            as="button"
                            className="btn btn-ghost btn-sm w-full"
                        >
                            登出
                        </Link>
                    </div>
                </aside>
            </div>
        </div>
    );
}
