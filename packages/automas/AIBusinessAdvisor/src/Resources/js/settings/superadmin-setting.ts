import { Zap } from 'lucide-react';

export interface SettingMenuItem {
  order: number;
  title: string;
  href: string;
  icon: any;
  permission: string;
  component: string;
}

export const getAIBusinessAdvisorSuperAdminSettings = (t: (key: string) => string): SettingMenuItem[] => [
    {
        order: 540,
        title: t('AI Advisor Settings'),
        href: '#superadmin-ai-advisor-settings',
        icon: Zap,
        permission: 'manage-ai-business-advisor-settings',
        component: 'superadmin-ai-advisor-settings'
    }
];
