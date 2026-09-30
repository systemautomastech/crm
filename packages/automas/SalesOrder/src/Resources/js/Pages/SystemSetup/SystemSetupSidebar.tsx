import { router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import { cn } from '@/lib/utils';
import {    Building, Factory, Target, Truck , FileText , File , Folder } from "lucide-react";

interface SidebarItem {
    key: string;
    label: string;
    icon: React.ComponentType<{ className?: string }>;
    route: string;
    permission: string;
}

interface SystemSetupSidebarProps {
    activeItem?: string;
    onSectionChange?: (section: string) => void;
}

export default function SystemSetupSidebar({ activeItem, onSectionChange }: SystemSetupSidebarProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const currentRoute = route().current();

    const sidebarItems: SidebarItem[] = [
        {
            key: 'account-types',
            label: t('Account Types'),
            icon: Building,
            route: 'sales.account-types.index',
            permission: 'manage-sales-account-types'
        },
        {
            key: 'account-industries',
            label: t('Account Industries'),
            icon: Factory,
            route: 'sales.account-industries.index',
            permission: 'manage-sales-account-industries'
        },
        {
            key: 'opportunity-stages',
            label: t('Opportunity Stages'),
            icon: Target,
            route: 'sales.opportunity-stages.index',
            permission: 'manage-sales-opportunity-stages'
        },
        {
            key: 'shipping-providers',
            label: t('Shipping Providers'),
            icon: Truck,
            route: 'sales.shipping-providers.index',
            permission: 'manage-shipping-providers'
        },
        {
            key: 'case-types',
            label: t('Case Types'),
            icon: FileText,
            route: 'sales.case-types.index',
            permission: 'manage-sales-case-types'
        },
        {
            key: 'sales-document-types',
            label: t('Document Types'),
            icon: File,
            route: 'sales.sales-document-types.index',
            permission: 'manage-sales-document-types'
        },
        {
            key: 'sales-document-folders',
            label: t('Document Folders'),
            icon: Folder,
            route: 'sales.sales-document-folders.index',
            permission: 'manage-sales-document-folders'
        },
    ];

    const filteredItems = sidebarItems.filter(item =>
        auth.user?.permissions?.includes(item.permission)
    );

    return (
        <div className="sticky top-4">
            <ScrollArea className="h-[calc(100vh-8rem)]">
                <div className="pr-4 space-y-1">
                    {filteredItems.map((item) => {
                        const Icon = item.icon;
                        const isActive = activeItem === item.key || currentRoute === item.route;

                        return (
                            <Button
                                key={item.key}
                                variant="ghost"
                                className={cn('w-full justify-start', {
                                    'bg-muted font-medium': isActive,
                                })}
                                onClick={() => {
                                    router.get(route(item.route));
                                    onSectionChange?.(item.key);
                                }}
                            >
                                <Icon className="h-4 w-4 mr-2" />
                                {item.label}
                            </Button>
                        );
                    })}
                </div>
            </ScrollArea>
        </div>
    );
}