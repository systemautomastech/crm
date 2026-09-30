import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import { cn } from '@/lib/utils';
import { Info, CheckSquare, Users, Package, Database, File, Phone, Activity } from "lucide-react";

interface SidebarItem {
    key: string;
    label: string;
    icon: React.ComponentType<{ className?: string }>;
    permission: string;
}

interface DealShowSidebarProps {
    activeItem?: string;
    onSectionChange?: (section: string) => void;
}

export default function DealShowSidebar({ activeItem, onSectionChange }: DealShowSidebarProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;

    const sidebarItems: SidebarItem[] = [
        {
            key: 'general',
            label: t('General'),
            icon: Info,
            permission: 'view-deals'
        },
        {
            key: 'tasks',
            label: t('Tasks'),
            icon: CheckSquare,
            permission: 'manage-deal-tasks'
        },
        {
            key: 'users',
            label: t('Users'),
            icon: Users,
            permission: 'manage-deal-users'
        },
        {
            key: 'products',
            label: t('Products'),
            icon: Package,
            permission: 'manage-deal-products'
        },
        {
            key: 'sources',
            label: t('Sources'),
            icon: Database,
            permission: 'manage-deal-sources'
        },
        {
            key: 'files',
            label: t('Files'),
            icon: File,
            permission: 'manage-deal-files'
        },
        {
            key: 'calls',
            label: t('Calls'),
            icon: Phone,
            permission: 'manage-deal-calls'
        },
        {
            key: 'clients',
            label: t('Clients'),
            icon: Users,
            permission: 'manage-deal-clients'
        },
        {
            key: 'activity',
            label: t('Activity'),
            icon: Activity,
            permission: 'manage-deal-activity'
        }
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
                        const isActive = activeItem === item.key;

                        return (
                            <Button
                                key={item.key}
                                variant="ghost"
                                className={cn('w-full justify-start', {
                                    'bg-muted font-medium': isActive,
                                })}
                                onClick={() => {
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