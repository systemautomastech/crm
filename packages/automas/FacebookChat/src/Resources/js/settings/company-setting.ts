import { MessageSquare } from 'lucide-react';

export interface SettingMenuItem {
  order: number;
  title: string;
  href: string;
  icon: any;
  permission: string;
  component: string;
}

export const getFacebookChatCompanySettings = (t: (key: string) => string): SettingMenuItem[] => [
  {
    order: 595,
    title: t('Facebook Chat Settings'),
    href: '#facebook-chat-settings',
    icon: MessageSquare,
    permission: 'manage-facebook-chat-settings',
    component: 'facebook-chat-settings'
  }
];