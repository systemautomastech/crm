export interface PosCounter {
    id: number;
    name: string;
    code: string;
    status: boolean;
    description?: string;
    created_at: string;
}

export interface PosCounterFormData {
    name: string;
    code: string;
    status: boolean;
    description: string;
}
