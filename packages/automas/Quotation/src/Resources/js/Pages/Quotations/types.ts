export interface QuotationItem {
    id?: number | string;
    quotation_id?: number;
    invoice_id?: number;
    product_id: number;
    section?: string;
    product_type?: string;
    product_description?: string;
    description?: string;
    quantity: number;
    unit_price: number;
    discount_percentage: number;
    discount_amount: number;
    discount_type?: 'percentage' | 'fixed' | string;
    tax_percentage: number;
    tax_amount: number;
    total_amount: number;
    taxes?: Array<{ id?: number; tax_name: string; tax_rate?: number; rate?: number }>;
    product?: {
        id?: number;
        name?: string;
        sku?: string;
        sale_price?: number;
        description?: string;
        tax?: Array<{ id?: number; name?: string; rate?: number }>;
    };
}

export type SalesQuotationItem = QuotationItem;
// Backward compatibility alias if needed by shared components
export type ProposalItem = QuotationItem;

export interface QuotationCustomer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
}

export interface QuotationWarehouse {
    id: number;
    name: string;
    address?: string;
}

export interface QuotationDefaultPage {
    id: number;
    title: string;
    content: string;
    page_type?: string;
    background_image?: string;
    sort_order: number;
}

export interface SalesQuotation {
    id: number;
    quotation_number: string;
    revision_number?: number;
    parent_quotation_id?: number;
    quotation_date: string;
    due_date: string;
    customer_id?: number | null;
    customer_name?: string | null;
    customer_email?: string | null;
    customer_phone?: string | null;
    customer_address?: string | null;
    warehouse_id?: number;
    type?: string;
    is_tax_enabled?: boolean | number;
    is_prepaid?: boolean | number;
    otc_discount_type?: 'percentage' | 'fixed';
    otc_discount_value?: number;
    mrc_discount_type?: 'percentage' | 'fixed';
    mrc_discount_value?: number;
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    status: string;
    display_status?: string;
    converted_to_invoice: boolean;
    invoice_id?: number;
    payment_terms?: string | null;
    notes?: string;
    created_at?: string;
    updated_at?: string;
    customer?: QuotationCustomer | null;
    warehouse?: QuotationWarehouse | null;
    items?: QuotationItem[];
    contents?: any[];
}