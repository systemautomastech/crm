import { Brain } from 'lucide-react';

declare global {
    function route(name: string, params?: any): string;
}

export const aibusinessadvisorCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('AI Business Advisor'),
        icon: Brain,
        href: route('ai-advisor.dashboard'),
        permission: 'manage-ai-business-advisor',
        order: 461,
    },
];
