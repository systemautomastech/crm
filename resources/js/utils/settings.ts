import { SettingMenuItem } from './menus/superadmin-setting';
import { getSuperAdminSettings } from './menus/superadmin-setting';
import { getCompanySettings } from './menus/company-setting';

let packageSettingsCache: Record<string, SettingMenuItem[]> = {};

// Get role-based core settings items
const getCoreSettingsItems = (userRoles: string[], t: (key: string) => string): SettingMenuItem[] => {
    if (userRoles.includes('superadmin')) {
        return getSuperAdminSettings(t);
    }
    return getCompanySettings(t);
};

// Auto-load package settings based on activated packages
const getPackageSettingsItems = (userRoles: string[], activatedPackages: string[], t: (key: string) => string): SettingMenuItem[] => {
    const cacheKey = `${userRoles.join(',')}-${[...activatedPackages].sort().join(',')}`;
    
    if (packageSettingsCache[cacheKey]) {
        return packageSettingsCache[cacheKey];
    }

    const menuItems: SettingMenuItem[] = [];
    const settingType = userRoles.includes('superadmin') ? 'superadmin-setting' : 'company-setting';

    const allModules = userRoles.includes('superadmin')
        ? import.meta.glob('../../../packages/automas/*/src/Resources/js/settings/superadmin-setting.ts', { eager: true })
        : import.meta.glob('../../../packages/automas/*/src/Resources/js/settings/company-setting.ts', { eager: true });

    (Array.isArray(activatedPackages) ? activatedPackages : []).forEach(packageName => {
        const settingPath = `../../../packages/automas/${packageName}/src/Resources/js/settings/${settingType}.ts`;
        const module = allModules[settingPath] as any;

        if (module) {
            Object.values(module).forEach((item: any) => {
                const result = typeof item === 'function' ? item(t) : item;
                const items = Array.isArray(result) ? result : [result];
                menuItems.push(...items);
            });
        }
    });

    packageSettingsCache[cacheKey] = menuItems;
    return menuItems;
};

// Main function to get filtered settings items
export const allSettingsItems = (userPermissions: string[], userRoles: string[], activatedPackages: string[], t: (key: string) => string): SettingMenuItem[] => {
    const coreSettingsItems = getCoreSettingsItems(userRoles, t);
    const packageSettingsItems = getPackageSettingsItems(userRoles, activatedPackages, t);

    const allItems = [...coreSettingsItems, ...packageSettingsItems];

    // Sort by order and filter by permission
    return allItems
        .sort((a, b) => (a.order || 999) - (b.order || 999))
        .filter(item => userPermissions.includes(item.permission));
};