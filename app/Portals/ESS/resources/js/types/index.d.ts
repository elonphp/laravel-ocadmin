export interface User {
    id: number;
    name: string;
    email: string;
    username: string;
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
