import { PaginatedData, ModalState, AuthContext } from '@/types/common';

export interface Branch {
    id: number;
    branch_name: string;
}

export interface CompanyPolicy {
    id: number;
    branch_id?: number;
    branch?: Branch;
    title: string;
    description?: string;
    attachment?: string;
    created_at: string;
}

export interface CreateCompanyPolicyFormData {
    branch_id: string;
    title: string;
    description: string;
    attachment: string;
}

export interface EditCompanyPolicyFormData {
    branch_id: string;
    title: string;
    description: string;
    attachment: string;
}

export interface CompanyPolicyFilters {
    title: string;
    branch_id: string;
}

export type PaginatedCompanyPolicies = PaginatedData<CompanyPolicy>;
export type CompanyPolicyModalState = ModalState<CompanyPolicy>;

export interface CompanyPoliciesIndexProps {
    companyPolicies: PaginatedCompanyPolicies;
    auth: AuthContext;
    branches: Branch[];
    [key: string]: unknown;
}

export interface CreateCompanyPolicyProps {
    onSuccess: () => void;
}

export interface EditCompanyPolicyProps {
    companyPolicy: CompanyPolicy;
    onSuccess: () => void;
}

export interface CompanyPolicyShowProps {
    companyPolicy: CompanyPolicy;
    [key: string]: unknown;
}
