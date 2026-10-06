import { ShoppingCart } from 'lucide-react';

declare global {
    function route(name: string, params?: any): string;
}

export const salesOrderCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Sales Order Dashboard'),
        href: route('salesorder.dashboard'),
        permission: 'manage-sales-orders',
        parent: 'dashboard',
        order: 35,
    },
    {
        title: t('Sales Orders'),
        icon: ShoppingCart,
        permission: 'manage-sales-orders',
        parent: '',
        order: 50,
        children: [
            {
                title: t('Sales Orders'),
                href: route('salesorder.orders.index'),
                permission: 'manage-sales-orders',
            },
            {
                title: t('Delivery Challans'),
                href: route('salesorder.deliveries.index'),
                permission: 'manage-sales-order-deliveries',
            },
            {
                title: t('System Setup'),
                href: route('salesorder.settings.show'),
                permission: 'manage-sales-order-settings',
            },
        ],
    },
];