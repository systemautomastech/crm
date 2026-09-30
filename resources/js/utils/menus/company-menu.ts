import { LayoutGrid, Users, Warehouse, ArrowRightLeft, Package, Tag, Tags, Shield, Settings, Image, CreditCard, Headphones, ShoppingCart, Kanban, Calendar, MessageCircle, Replace, Receipt, Bot, FileCheck } from 'lucide-react';
import { NavItem } from '@/types';

export const getCompanyMenu = (t: (key: string) => string): NavItem[] => [
    {
        title: t('Dashboard'),
        icon: LayoutGrid,
        permission: 'manage-dashboard',
        name: 'dashboard',
        order: 1,
    },
    {
        title: t('User Management'),
        icon: Users,
        permission: 'manage-users',
        order: 10,
        children: [
            {
                title: t('Roles'),
                href: route('roles.index'),
                permission: 'manage-roles',
            },
            {
                title: t('Users'),
                href: route('users.index'),
                permission: 'manage-users',
            },
            {
                title: t('Clients & Vendors'),
                href: route('users.clients-vendors'),
                permission: 'manage-client-vendors',
            },
            {
                title: t('User Groups'),
                href: route('user-groups.index'),
                permission: 'manage-user-groups',
            },
        ],
    },
    {
        title: t('Purchase'),
        icon: ShoppingCart,
        permission: 'manage-purchase-invoices',
        module: 'ProductService',
        order: 20,
        children: [
            {
                title: t('Purchase Invoice'),
                href: route('purchase-invoices.index'),
                permission: 'manage-purchase-invoices',
            },
            {
                title: t('Purchase Returns'),
                href: route('purchase-returns.index'),
                permission: 'manage-purchase-return-invoices',
            },
            {
                title: t('Warehouses'),
                href: route('warehouses.index'),
                permission: 'manage-warehouses',
            },
            {
                title: t('Transfers'),
                href: route('transfers.index'),
                permission: 'manage-transfers',
            },
            {
                title: t('System Setup'),
                href: route('purchase-invoice-setup.index'),
                permission: 'manage-purchase-invoice-setup',
            },
        ],
    },
    {
        title: t('Proposal'),
        icon: Replace,
        permission: 'manage-sales-proposals',
        module: 'ProductService',
        order: 25,
        children: [
            {
                title: t('Proposal'),
                href: route('sales-proposals.index'),
                permission: 'manage-sales-proposals',
            },
            {
                title: t('System Setup'),
                href: route('proposal-setup.index'),
                permission: 'manage-proposal-system-setup',
            },
        ],
    },
    {
        title: t('Invoice'),
        icon: Receipt,
        permission: 'manage-sales-invoices',
        module: 'ProductService',
        order: 40,
        children: [
            {
                title: t('Invoice'),
                href: route('sales-invoices.index'),
                permission: 'manage-sales-invoices',
            },
            {
                title: t('Invoice Returns'),
                href: route('sales-returns.index'),
                permission: 'manage-sales-return-invoices',
            },
            {
                title: t('System Setup'),
                href: route('sales-invoice-setup.index'),
                permission: 'manage-sales-invoice-setup',
            },
        ],
    },
    {
        title: t('Media Library'),
        href: route('media-library'),
        icon: Image,
        permission: 'manage-media',
        order: 2900,
    },
    {
        title: t('Messenger'),
        href: route('messenger.index'),
        icon: MessageCircle,
        permission: 'manage-messenger',
        order: 2940,
    },
    {
        title: t('AI Agent'),
        href: route('ai-agent.chat.page'),
        icon: Bot,
        permission: 'manage-ai-agent',
        order: 2945,
    },
    {
        title: t('Helpdesk'),
        href: route('helpdesk-tickets.index'),
        icon: Headphones,
        permission: 'manage-helpdesk-tickets',
        order: 2950,
    },
    {
        title: t('Plan'),
        icon: CreditCard,
        permission: 'manage-plans',
        order: 2980,
        children: [
            {
                title: t('Setup Subscription Plan'),
                href: route('plans.index'),
                permission: 'manage-plans',
            },
            {
                title: t('Bank Transfer Requests'),
                href: route('bank-transfer.index'),
                permission: 'manage-bank-transfer-requests',
            },
            {
                title: t('Orders'),
                href: route('orders.index'),
                permission: 'manage-orders',
            }
        ]
    },
    {
        title: t('Settings'),
        href: route('settings.index'),
        icon: Settings,
        permission: 'manage-settings',
        order: 3000,
    },
];
