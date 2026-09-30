import { FileCheck } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const quotationCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Quotation'),
        icon: FileCheck,
        permission: 'manage-quotations',
        order: 30,
        children: [
            {
                title: t('Quotation'),
                href: route('quotations.index'),
                permission: 'manage-quotations',
            },
            {
                title: t('System Setup'),
                href: route('quotation-setup.index'),
                permission: 'manage-quotation-system-setup',
            },
        ],
    },
];
