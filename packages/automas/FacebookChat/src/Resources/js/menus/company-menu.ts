import { MessageSquare } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const facebookchatCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Facebook Chat'),
        icon: MessageSquare,
        permission: 'manage-facebook-chat',
        order: 535,
        href: route('facebook-chat.index'),     
    },    
];




