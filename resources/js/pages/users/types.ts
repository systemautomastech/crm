import { PaginatedData, ModalState, AuthContext, CreateProps, EditProps } from '@/types/common';

export interface Plan {
    id: number;
    name: string;
    description: string;
    number_of_users: number;
    custom_plan: boolean;
    status: boolean;
    free_plan: boolean;
    modules: string[];
    package_price_yearly: number;
    package_price_monthly: number;
    price_per_user_monthly: number;
    price_per_user_yearly: number;
    storage_limit: number;
    price_per_storage_monthly: number;
    price_per_storage_yearly: number;
    trial: boolean;
    trial_days: number;
}

export interface RoleItem {
    id: number;
    name: string;
    label?: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    mobile_no: string;
    role: string;
    type: string;
    roles?: RoleItem[];
    is_enable_login: boolean;
    is_disable?: number;
    is_online?: number;
    avatar?: string;
    lang?: string;
    active_plan?: number;
    plan_expire_date?: string;
    is_trial_done?: number;
    total_user?: number;
    storage_limit?: number;
    created_at: string;
}

export interface CreateUserFormData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    mobile_no: string;
    type: string;
    is_enable_login: boolean;
}

export interface EditUserFormData {
    name: string;
    email: string;
    mobile_no: string;
    type?: string;
    is_enable_login: boolean;
}

export interface ChangePasswordFormData {
    password: string;
    password_confirmation: string;
}

export interface CreateUserProps extends CreateProps {
    roles?: Record<string, string>;
}

export interface EditUserProps {
    user: User;
    onSuccess: () => void;
    roles?: Record<string, string>;
}

export interface ChangePasswordProps {
    user: User;
    onSuccess: () => void;
}

export interface UserFilters {
    name: string;
    email: string;
    role: string;
    is_enable_login: string;
}

export type PaginatedUsers = PaginatedData<User>;
export interface UserModalState {
    isOpen: boolean;
    mode: '' | 'add' | 'edit' | 'change-password' | 'upgrade-plan';
    data: User | null;
}

export interface ActiveModule {
    module: string;
    name: string;
    image: string;
}

export interface UsersIndexProps {
    users: PaginatedUsers;
    roles: Record<string, string>;
    plans: Plan[];
    activeModules: ActiveModule[];
    auth: AuthContext;
    [key: string]: unknown;
}

export interface AdminHubProps {
    company: User;
    companyUsers: User[];
    totalActiveUsers: number;
    totalInactiveUsers: number;
    roles: Record<string, string>;
    activePlanName: string;
    auth: AuthContext;
    [key: string]: unknown;
}

export interface UserFormErrors {
    name?: string;
    email?: string;
    password?: string;
    password_confirmation?: string;
    mobile_no?: string;
    type?: string;
    is_enable_login?: string;
}