import React from 'react';
import { ScrollArea } from "@/components/ui/scroll-area";
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
    Settings,
    Image as ImageIcon,
    Layers,
    FileText,
} from 'lucide-react';
import { useTranslation } from 'react-i18next';

export interface SetupSidebarItem {
    id: string;
    label: string;
    icon: React.ElementType;
}

interface SetupSidebarProps {
    activeSection: string;
    onNavClick: (id: string) => void;
}

export default function Sidebar({ activeSection, onNavClick }: SetupSidebarProps) {
    const { t } = useTranslation();

    const sidebarItems: SetupSidebarItem[] = [
        {
            id: 'general-settings',
            label: t('General Settings'),
            icon: Settings,
        },
        {
            id: 'logo-template',
            label: t('Logo & Template'),
            icon: ImageIcon,
        },
        {
            id: 'default-pages',
            label: t('Default Pages'),
            icon: Layers,
        },
        {
            id: 'subjects',
            label: t('Subjects'),
            icon: FileText,
        },
    ];

    return (
        <div className="md:w-64 flex-shrink-0">
            <div className="sticky top-4">
                <ScrollArea className="h-[calc(100vh-8rem)]">
                    <div className="pr-4 space-y-1">
                        {sidebarItems.map((item) => {
                            const Icon = item.icon;
                            const isActive = activeSection === item.id;
                            return (
                                <Button
                                    key={item.id}
                                    variant="ghost"
                                    className={cn('w-full justify-start', {
                                        'bg-muted font-medium': isActive,
                                    })}
                                    onClick={() => onNavClick(item.id)}
                                >
                                    <Icon className="h-4 w-4 mr-2" />
                                    {item.label}
                                </Button>
                            );
                        })}
                    </div>
                </ScrollArea>
            </div>
        </div>
    );
}
