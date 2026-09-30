import { MessageSquare } from 'lucide-react';

export interface SettingMenuItem {
  order: number;
  title: string;
  href: string;
  icon: any;
  permission: string;
  component: string;
}

export const getWhatsAppChatCompanySettings = (t: (key: string) => string): SettingMenuItem[] => [
  {
    order: 590,
    title: t('WhatsApp Chat Settings'),
    href: '#whatsapp-chat-settings',
    icon: MessageSquare,
    permission: 'manage-whatsapp-chat-settings',
    component: 'whatsapp-chat-settings'
  }
];