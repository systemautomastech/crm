import { Layers } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const productserviceCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Product & Service'),
        icon: Layers,
        permission: 'manage-product-service-item',
        parent: 'settings',
        order: 15,
        children: [
            {
                title: t('Items'),
                href: route('product-service.items.index'),
                permission: 'manage-product-service-item',
            },
            {
                title: t('Stocks'),
                href: route('product-service.stock.index'),
                permission: 'manage-stock',
            },
            {
                title: t('System Setup'),
                href: route('product-service.item-categories.index'),
                permission: 'manage-product-service-item',
            },
        ],
    },
];
