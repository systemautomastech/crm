export interface PosDiscount {
    id: number;
    name: string;
    discount_type: 'percentage' | 'fixed';
    discount_value: number;
    apply_on: 'product' | 'category';
    product_id: number | null;
    category_id: number | null;
    min_quantity: number;
    start_date: string | null;
    end_date: string | null;
    is_active: boolean;
    priority: number;
    product?: { id: number; name: string; sku?: string } | null;
    category?: { id: number; name: string } | null;
    products?: Array<{ id: number; name: string; sku?: string }>;
    product_ids?: number[];
    discount_ids?: number[];
}

export interface PosDiscountFormData {
    name: string;
    discount_type: 'percentage' | 'fixed';
    discount_value: number | string;
    apply_on: 'product' | 'category';
    product_id: number | string | null;
    category_id: number | string | null;
    min_quantity: number | string;
    start_date: string | null;
    end_date: string | null;
    is_active: boolean;
    priority: number | string;
}
