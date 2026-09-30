import { lazy } from 'react';

// Core settings components
const coreComponents = {
  'brand-settings': lazy(() => import('@/pages/settings/components/brand-settings')),
  'company-settings': lazy(() => import('@/pages/settings/components/company-settings')),
  'system-settings': lazy(() => import('@/pages/settings/components/system-settings')),
  'currency-settings': lazy(() => import('@/pages/settings/components/currency-settings')),
  'seo-settings': lazy(() => import('@/pages/settings/components/seo-settings')),
  'storage-settings': lazy(() => import('@/pages/settings/components/storage-settings')),
  'email-settings': lazy(() => import('@/pages/settings/components/email-settings')),
  'pusher-settings': lazy(() => import('@/pages/settings/components/pusher-settings')),
  'email-notification-settings': lazy(() => import('@/pages/settings/components/email-notification-settings')),
  'cookie-settings': lazy(() => import('@/pages/settings/components/cookie-settings')),
  'bank-transfer-settings': lazy(() => import('@/pages/settings/components/bank-transfer-settings')),
  'cache-settings': lazy(() => import('@/pages/settings/components/cache-settings')),
  'ai-agent-settings': lazy(() => import('@/pages/settings/components/ai-agent-settings')),
};

let packageComponentsCache: Record<string, any> | null = null;
let cachedPackages: string = '';

// Auto-load package components
const getPackageComponents = (activatedPackages: string[]) => {
  const packagesKey = [...activatedPackages].sort().join(',');
  
  if (packageComponentsCache && cachedPackages === packagesKey) {
    return packageComponentsCache;
  }

  try {
    const modules = import.meta.glob('../../../packages/automas/*/src/Resources/js/settings/components/*.tsx');
    const packageComponents: Record<string, any> = {};

    activatedPackages.forEach(packageName => {
      Object.entries(modules).forEach(([path, moduleLoader]) => {
        if (path.includes(`/packages/automas/${packageName}/`)) {
          const match = path.match(/\/([^/]+)\.tsx$/);
          if (match) {
            const componentName = match[1];
            packageComponents[componentName] = lazy(() => moduleLoader() as any);
          }
        }
      });
    });

    packageComponentsCache = packageComponents;
    cachedPackages = packagesKey;
    return packageComponents;
  } catch (error) {
    return {};
  }
};

let allComponentsCache: Record<string, any> | null = null;
let allComponentsCacheKey: string = '';

// Combined components registry
export const getSettingsComponent = (componentName: string, activatedPackages: string[]) => {
  const cacheKey = [...activatedPackages].sort().join(',');
  
  if (!allComponentsCache || allComponentsCacheKey !== cacheKey) {
    allComponentsCache = { ...coreComponents, ...getPackageComponents(activatedPackages) };
    allComponentsCacheKey = cacheKey;
  }
  
  return allComponentsCache[componentName as keyof typeof allComponentsCache] || null;
};
