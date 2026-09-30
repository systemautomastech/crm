export interface ProposalItem {
    id?: number | string;
    proposal_id?: number;
    invoice_id?: number;
    product_id: number;
    section?: string;
    product_type?: string;
    product_description?: string;
    description?: string;
    quantity: number;
    unit_price: number;
    discount_type?: 'percentage' | 'fixed';
    discount_percentage: number;
    discount_amount: number;
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

export type SalesProposalItem = ProposalItem;

export interface ProposalCustomer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
}

export interface ProposalWarehouse {
    id: number;
    name: string;
    address?: string;
}

export interface SalesProposal {
    id: number;
    proposal_number: string;
    reference?: string;
    subject: string;
    proposal_date: string;
    due_date: string;
    customer_id?: number | null;
    customer_name?: string | null;
    customer_email?: string | null;
    customer_phone?: string | null;
    customer_address?: string | null;
    warehouse_id?: number;
    type?: string;
    is_recurring?: boolean | number;
    is_prepaid?: boolean | number;
    is_tax_enabled?: boolean | number;
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
    payment_terms?: string | null;
    notes?: string;
    created_at?: string;
    updated_at?: string;
    items?: ProposalItem[];
    [key: string]: any;
}
