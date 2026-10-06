import { LucideIcon } from 'lucide-react';

export interface User {
    id: number;
    name: string;
    email: string;
    type: string;
    email_verified_at?: string;
    lang?: string;
    permissions?: string[];
}

export interface NavItem {
    id?: string;
    title: string;
    href?: string;
    icon?: LucideIcon;
    permission?: string;
    children?: NavItem[];
    isActive?: boolean;
    parent?: string;
    name?: string;
    order?: number;
    module?: string;
    activeRoutes?: string[];
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        permissions?: string[];
        roles?: string[];
        impersonating?: boolean;
    };
};