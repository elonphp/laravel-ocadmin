export interface User {
    id: number;
    name: string;
    email: string;
    username: string;
}

export interface Employee {
    id: number;
    employee_no: string;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    birth_date: string | null;
    gender: string | null;
    job_title: string | null;
    department: string | null;
    address: string | null;
}

export interface MenuItem {
    name: string;
    href: string;
    icon: string;
}

export interface PageProps {
    auth: {
        user: User;
    };
    locale: string;
    locales: Record<string, string>;
    flash: {
        success: string | null;
        error: string | null;
    };
    menu: MenuItem[];
}
