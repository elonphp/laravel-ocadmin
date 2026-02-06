import { useForm, Head } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('lang.ess.login.submit'));
    };

    return (
        <>
            <Head title="登入" />
            <div className="min-h-screen flex items-center justify-center bg-base-200">
                <div className="card w-full max-w-sm bg-base-100 shadow-xl">
                    <div className="card-body">
                        <h2 className="card-title justify-center text-2xl mb-4">ESS 登入</h2>
                        <form onSubmit={submit}>
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">Email</span>
                                </label>
                                <input
                                    type="email"
                                    className="input input-bordered"
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    autoFocus
                                />
                                {errors.email && (
                                    <span className="text-error text-sm mt-1">{errors.email}</span>
                                )}
                            </div>
                            <div className="form-control mt-4">
                                <label className="label">
                                    <span className="label-text">密碼</span>
                                </label>
                                <input
                                    type="password"
                                    className="input input-bordered"
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                />
                                {errors.password && (
                                    <span className="text-error text-sm mt-1">{errors.password}</span>
                                )}
                            </div>
                            <div className="form-control mt-2">
                                <label className="label cursor-pointer justify-start gap-2">
                                    <input
                                        type="checkbox"
                                        className="checkbox checkbox-sm"
                                        checked={data.remember}
                                        onChange={e => setData('remember', e.target.checked)}
                                    />
                                    <span className="label-text">記住我</span>
                                </label>
                            </div>
                            <div className="form-control mt-6">
                                <button
                                    type="submit"
                                    className="btn btn-primary"
                                    disabled={processing}
                                >
                                    {processing && <span className="loading loading-spinner loading-sm" />}
                                    登入
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
