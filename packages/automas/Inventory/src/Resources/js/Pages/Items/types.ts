export interface InventoryItem {
    id: number;
    product_id: number;
    reorder_level: string;
    maximum_level: string;
    inventory_account_id: number;
    cogs_account_id: number;
    sales_account_id: number;
    valuation_method: 'fifo' | 'lifo' | 'weighted_average';
    is_tracked: boolean;
    created_at: string;
    updated_at: string;
    product?: {
        id: number;
        name: string;
        sku: string;
        type: string;
    };
    inventory_account?: {
        id: number;
        account_code: string;
        account_name: string;
    };
    cogs_account?: {
        id: number;
        account_code: string;
        account_name: string;
    };
    sales_account?: {
        id: number;
        account_code: string;
        account_name: string;
    };
}

export interface Product {
    id: number;
    name: string;
    sku: string;
    type: string;
}

export interface ChartOfAccount {
    id: number;
    account_code: string;
    account_name: string;
}

export interface InventoryItemsIndexProps {
    items: {
        data: InventoryItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        product_name?: string;
        valuation_method?: string;
        is_tracked?: string;
    };
    auth: {
        user: {
            permissions: string[];
        };
    };
}

export interface InventoryItemFilters {
    product_name: string;
    valuation_method: string;
    is_tracked: string;
}

export interface InventoryItemModalState {
    isOpen: boolean;
    mode: 'add' | 'edit' | 'view' | '';
    data: InventoryItem | null;
}

export interface CreateEditProps {
    onSuccess: () => void;
    item?: InventoryItem;
    data?: InventoryItem;
}

export interface ViewProps {
    onSuccess: () => void;
    item?: InventoryItem;
    data?: InventoryItem;
}
