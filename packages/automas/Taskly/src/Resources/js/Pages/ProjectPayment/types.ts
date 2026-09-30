export interface ProjectPaymentItem {
    id?: number;
    payment_id?: number;
    milestone_id: number | string;
    milestone?: {
        id: number;
        title: string;
    };
    price: number;
    discount_percentage: number;
    discount_amount: number;
    total_amount: number;
}

export interface ProjectPayment {
    id: number;
    payment_number: string;
    payment_date: string;
    due_date: string;
    project_id: number;
    project?: {
        id: number;
        name: string;
    };
    customer_id: number;
    customer?: {
        id: number;
        name: string;
        email: string;
    };
    subtotal: number;
    discount_amount: number;
    total_amount: number;
    paid_amount: number;
    balance_amount: number;
    status: 'draft' | 'posted';
    display_status?: string;
    payment_terms: string;
    notes: string;
    items: ProjectPaymentItem[];
    creator_id?: number;
    created_by?: number;
    created_at?: string;
}

export interface ProjectPaymentFilters {
    search?: string;
    project_id?: string;
    customer_id?: string;
    status?: string;
    date_range?: string;
    sort?: string;
    direction?: string;
    per_page?: number;
    view?: string;
}

export interface PaginatedData<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    meta?: {
        current_page: number;
        from: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}
