import { Link, Head, usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';
import { LanguageSwitcher } from '@/components/language-switcher';
import { useBrand } from '@/contexts/brand-context';
import { useFavicon } from '@/hooks/use-favicon';
import { getImagePath } from '@/utils/helpers';
import ApplicationLogo from '@/components/application-logo';
import CookieConsent from '@/components/cookie-consent';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    const { settings, getPrimaryColor, getLogoSrc } = useBrand();
    const { adminAllSetting } = usePage().props as any;
    useFavicon();

    const logoSrc = getLogoSrc();
    const primaryColor = getPrimaryColor();

    return (
        <>
        <Head title={adminAllSetting?.metaTitle}>
            <meta name="keywords" content={adminAllSetting?.metaKeywords || ''} />
            <meta name="description" content={adminAllSetting?.metaDescription || ''} />
            <meta property="og:image" content={adminAllSetting?.metaImage ? getImagePath(adminAllSetting.metaImage) : ''} />
        </Head>
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 p-6 md:p-10 relative overflow-hidden">
            <style>{`
                .dark .bg-primary {
                    background-color: ${primaryColor} !important;
                    color: white !important;
                }
                .dark .bg-primary:hover {
                    background-color: ${primaryColor}dd !important;
                }
            `}</style>
            {/* Dotted background pattern */}
            <div className="absolute inset-0 opacity-20 dark:opacity-15" style={{
                backgroundImage: 'radial-gradient(circle, #94a3b8 2px, transparent 1px)',
                backgroundSize: '30px 30px',
                backgroundPosition: '0 0, 15px 15px'
            }}></div>

            {/* Decorative shapes */}
            <div className="absolute top-10 left-10 w-32 h-32 bg-primary/10 dark:bg-primary/20 rounded-full blur-xl"></div>
            <div className="absolute bottom-10 right-10 w-40 h-40 bg-primary/5 dark:bg-primary/15 rounded-full blur-2xl"></div>
            <div className="absolute top-1/3 right-20 w-24 h-24 bg-primary/8 dark:bg-primary/18 rounded-full blur-lg"></div>

            <div className="absolute top-4 right-4 z-10">
                <LanguageSwitcher />
            </div>

            <div className="w-full max-w-md relative z-10 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md rounded-2xl shadow-2xl border border-white/30 dark:border-slate-700/50 p-8">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4">
                        <Link
                            href={route('dashboard')}
                            className="flex flex-col items-center gap-2 font-medium"
                        >
                            <div className="flex w-auto items-center justify-center rounded-md">
                                {logoSrc ? (
                                    <img
                                        src={getImagePath(logoSrc)}
                                        alt={settings.titleText || 'Logo'}
                                        className="w-auto max-w-28 object-contain"
                                    />
                                ) : (
                                    <ApplicationLogo className="h-16 w-16 text-primary" />
                                )}
                            </div>
                            <span className="sr-only">{title}</span>
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-xl font-semibold text-gray-900 dark:text-white">{title}</h1>
                            <p className="text-center text-sm text-gray-600 dark:text-gray-400">
                                {description}
                            </p>
                        </div>
                    </div>
                    {children}
                </div>
            </div>
            <CookieConsent settings={adminAllSetting || {}} />
        </div>
        </>
    );
}
