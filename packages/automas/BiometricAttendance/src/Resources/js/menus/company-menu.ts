import { Fingerprint } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const biometricattendanceCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Biometric Attendance'),
        icon: Fingerprint,
        permission: 'manage-biometric-attendance',
        order: 455,
        children: [
            {
                title: t('Attendance'),
                href: route('biometric-attendance.index'),
                permission: 'manage-biometric-attendance',
            },
            {
                title: t('Settings'),
                href: route('biometric-attendance.settings'),
                permission: 'manage-biometric-settings',
            },
        ],
    },
];