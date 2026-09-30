export interface PosReturn {
    id: number;
    return_number: string;
    return_date: string;
    customer_id: number;
    warehouse_id?: number;
    original_pos_id: number;
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    status: 'draft' | 'approved' | 'completed' | 'cancelled';
    notes?: string;
    reason?: string;
    created_at: string;
    updated_at: string;
    customer?: User;
    customer_details?: CustomerDetails;
    warehouse?: Warehouse;
    original_pos?: Pos;
    items?: PosReturnItem[];
}

export interface PosReturnItem {
    id?: number;
    return_id?: number;
    product_id: number;
    original_pos_item_id: number;
    original_quantity: number;
    return_quantity: number;
    unit_price: number;
    discount_percentage: number;
    discount_amount: number;
    tax_amount: number;
    total_amount: number;
    reason?: string;
    return_discount_amount?: number;
    original_item_discount?: number;
    product?: ProductServiceItem;
}

export interface Pos {
    id: number;
    sale_number: string;
    pos_date: string;
    customer_id: number;
    warehouse_id: number;
    customer?: User;
    warehouse?: Warehouse;
    items?: PosItem[];
    payment?: PosPayment;
}

export interface PosPayment {
    pos_id: number;
    discount: number;
    amount: number;
    discount_amount: number;
}

export interface PosItem {
    id: number;
    pos_id: number;
    product_id: number;
    quantity: number;
    price: number;
    subtotal: number;
    tax_ids?: number[];
    taxes?: Array<{
        id: number;
        name: string;
        rate: number;
    }>;
    tax_amount: number;
    total_amount: number;
    available_quantity?: number;
    item_discount_type?: string | null;
    item_discount_value?: number;
    item_discount_amount?: number;
    item_price_before_discount?: number;
    product?: ProductServiceItem;
}

export interface User {
    id: number;
    name: string;
    email: string;
    type?: string;
}

export interface CustomerDetails {
    id: number;
    user_id: number;
    customer_code: string;
    company_name: string;
    contact_person_name?: string;
    contact_person_email?: string;
    contact_person_mobile?: string;
    tax_number?: string;
    payment_terms?: string;
    billing_address?: Address;
    shipping_address?: Address;
    same_as_billing: boolean;
    notes?: string;
}

export interface Address {
    name: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    zip_code: string;
    country: string;
}

export interface Warehouse {
    id: number;
    name: string;
}

export interface ProductServiceItem {
    id: number;
    name: string;
    sku?: string;
    description?: string;
    price: number;
    tax_rate?: number;
    unit?: string;
}

export interface PosReturnFilters {
    search: string;
    customer_id: string;
    warehouse_id: string;
    status: string;
    date_range: string;
}
