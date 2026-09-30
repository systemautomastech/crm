import { Package2 } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const inventoryCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Inventory'),
        icon: Package2,
        permission: 'manage-inventory-items',
        order: 850,
        children: [
            {
                title: t('Items'),
                href: route('inventory.items.index'),
                permission: 'manage-inventory-items',
            },
            {
                title: t('Adjustments'),
                href: route('inventory.adjustments.index'),
                permission: 'manage-inventory-adjustments',
            },
            {
                title: t('Reports'),
                permission: 'manage-inventory-reports',
                children: [
                    {
                        title: t('Stock Valuation Report'),
                        href: route('inventory.reports.stock-valuation'),
                        permission: 'view-inventory-reports',
                    },
                    {
                        title: t('COGS Report'),
                        href: route('inventory.reports.cogs'),
                        permission: 'view-inventory-reports',
                    },
                    {
                        title: t('Stock Movement Report'),
                        href: route('inventory.reports.stock-movement'),
                        permission: 'view-inventory-reports',
                    },
                ]
            },
        ],
    }
];
