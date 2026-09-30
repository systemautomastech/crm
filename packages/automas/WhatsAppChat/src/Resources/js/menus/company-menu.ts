import { MessageSquare, Users } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const whatsappchatCompanyMenu = (t: (key: string) => string) => [  
    {
        title: t('WhatsApp Chat'),
        icon: MessageSquare,
        permission: 'manage-whatsapp-chat',
        order: 930,
        href: route('whatsapp-chat.index'),       
    },
];