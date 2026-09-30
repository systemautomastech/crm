import { PaginatedData, ModalState, AuthContext, CreateProps } from '@/types/common';

export interface WhatsappContact {
    id: number;
    contact_no: string;
    name?: string;
    user_id?: number;
    user?: {
        id: number;
        name: string;
        mobile_no: string;
    };
    last_message?: string;
    unseen_count?: number;
    created_at: string;
}

export interface WhatsappChat {
    id: number;
    whatsapp_contact_id: number;
    is_send: boolean;
    is_seen: boolean;
    message: string;
    image_path?: string;
    created_at: string;
}

export interface User {
    id: number;
    name: string;
    mobile_no: string;
}

export interface ChatInitializeFormData {
    type: 'choose_from_users' | 'custom';
    user_id?: number;
    name?: string;
    contact_no: string;
    message: string;
}

export interface ChatInitializeProps extends CreateProps {
    users: Record<string, string>;
}

export interface WhatsAppChatIndexProps {
    contacts: WhatsappContact[];
    auth: AuthContext;
}

export type PaginatedContacts = PaginatedData<WhatsappContact>;
export type ContactModalState = ModalState<WhatsappContact>;