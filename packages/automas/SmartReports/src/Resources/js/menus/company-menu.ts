import { FileBarChart } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const smartreportsCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Smart Reports'),
        icon: FileBarChart,
        href: route('smart-reports.index'),
        permission: 'manage-smart-reports',
        parent: '',
        order: 462,
    },
];