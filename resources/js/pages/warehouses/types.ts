import { ModalState, AuthContext, CreateProps, EditProps } from '@/types/common';

export interface Warehouse {
    id: number;
    name: string;
    address: string;
    city: string;
    zip_code: string;
    phone?: string;
    email?: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    stock_quantity?: number;
    product_count?: number;
    stock_value?: number;
    out_of_stock_count?: number;
    has_stranded_stock?: boolean;
    is_never_stocked?: boolean;
}

export interface WarehouseStats {
    total: number;
    active: number;
    inactive: number;
}

export interface CreateWarehouseFormData {
    name: string;
    address: string;
    city: string;
    zip_code: string;
    phone: string;
    email: string;
    is_active: boolean;
    [key: string]: any;
}

export interface EditWarehouseFormData {
    name: string;
    address: string;
    city: string;
    zip_code: string;
    phone?: string;
    email?: string;
    is_active: boolean;
    [key: string]: any;
}

export interface CreateWarehouseProps extends CreateProps {}

export interface EditWarehouseProps extends EditProps<Warehouse> {
    warehouse: Warehouse;
}

export interface WarehouseFilters {
    search: string;
}

export type WarehouseModalState = ModalState<Warehouse>;

export interface WarehousesIndexProps {
    warehouses: Warehouse[];
    auth: AuthContext;
    stats: WarehouseStats;
    [key: string]: unknown;
}

export interface WarehouseFormErrors {
    name?: string;
    address?: string;
    city?: string;
    zip_code?: string;
    phone?: string;
    email?: string;
    is_active?: string;
}