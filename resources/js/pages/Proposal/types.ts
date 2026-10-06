export interface ProposalItem {
    id?: number | string;
    proposal_id?: number;
    invoice_id?: number;
    product_id: number;
    product_name?: string;
    name?: string;
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

export interface Proposal {
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

export interface ProposalPreviewSection {
    id?: string;
    title: string;
    content: string;
    page_type?: string;
    background_image?: string;
    order?: number;
}

export interface ProposalTotals {
    subtotal: number;
    tax_amount?: number;
    taxAmount?: number;
    discount_amount?: number;
    discountAmount?: number;
    total_amount?: number;
    total?: number;
}

export interface ProposalFormData {
    id?: string | number;
    proposal_id?: string | number;
    proposal_number?: string;
    invoice_date?: string;
    due_date?: string;
    customer_id?: string | number;
    warehouse_id?: string | number;
    type?: string;
    payment_terms?: string;
    notes?: string;
    subject?: string;
    other_details?: string;
    items?: ProposalItem[];
    creator_name?: string;
}

export interface ProposalSettingsConfig {
    logo_image?: string;
    company_logo?: string;
    show_logo?: boolean | string | number;
    background_image?: string;
    template_color?: string;
    company_name?: string;
    company_email?: string;
    company_phone?: string;
    company_telephone?: string;
    company_address?: string;
    company_website?: string;
    [key: string]: any;
}

