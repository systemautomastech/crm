import { Menu, X, ShieldCheck, Layers } from 'lucide-react';
import { useState, useEffect } from 'react';
import { Link, router } from '@inertiajs/react';
import { getAdminSetting, getImagePath } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { LanguageSwitcher } from '@/components/language-switcher';

interface HeaderProps {
    settings?: any;
}

export default function Header({ settings }: HeaderProps) {
    const sectionData = settings?.config_sections?.sections?.header || {};
    const colors = settings?.config_sections?.colors || {
        primary: 'var(--color-primary, #130774)',
        secondary: 'var(--color-secondary, #0b55b7)',
        accent: 'var(--color-accent, #130674)'
    };
    const primaryColor = colors.primary || 'var(--color-primary)';
    const { t } = useTranslation();

    const companyName = sectionData.company_name || settings?.company_name || 'Automas';
    const ctaText = sectionData.cta_text || 'Start Free Trial';
    const isAuthenticated = settings?.is_authenticated;

    const [scrolled, setScrolled] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const logoKey = 'logo_dark';
    const logoPath = getAdminSetting(logoKey);
    const logoUrl = logoPath ? getImagePath(logoPath) : null;

    useEffect(() => {
        const handleScroll = () => setScrolled(window.scrollY > 20);
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const customNavItems = sectionData.navigation_items || [
        { text: 'Features', href: '#features' },
        { text: 'Modules', href: '#modules' },
        { text: 'Pricing', href: '#pricing' },
        { text: 'Demo', href: '#demo' },
        { text: 'About', href: '#about' },
    ];

    const extraNav = [
        { text: 'Home', href: '/' },
        { text: 'Call Center', href: route('call-center.page') },
        { text: 'CRM', href: '#modules' },
    ];

    // Filter out duplicates if present in customNavItems
    const filteredCustom = customNavItems.filter((item: any) =>
        !['home', 'call center', 'crm'].includes(String(item.text).toLowerCase())
    );

    const navigationItems = [...extraNav, ...filteredCustom];

    const renderNavItems = (isMobile = false) => {
        return navigationItems.map((item: any, idx: number) => {
            const href = item.href?.startsWith('/page/')
                ? route('custom-page.show', item.href.replace('/page/', ''))
                : item.href;

            const className = isMobile
                ? 'block py-2.5 text-base font-medium text-slate-600 hover:text-primary transition-colors'
                : 'text-sm font-medium text-slate-600 hover:text-primary transition-colors';

            return item.target === '_blank' ? (
                <a key={idx} href={href} target="_blank" rel="noopener noreferrer" className={className}>
                    {t(item.text)}
                </a>
            ) : (
                <Link key={idx} href={href} className={className} onClick={() => isMobile && setMobileMenuOpen(false)}>
                    {t(item.text)}
                </Link>
            );
        });
    };

    return (
        <nav id="navbar" className={`absolute md:fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${scrolled ? 'bg-white/80 md:backdrop-blur-xl border-b border-slate-200/80 shadow-xs' : 'bg-transparent'
            }`}>
            <div className="max-w-7xl mx-auto px-6 lg:px-8">
                <div className="flex items-center justify-between h-20">

                    {/* Logo */}
                    <Link href={route('landing.page')} className="flex items-center gap-3 group">
                        {logoUrl ? (
                            <img src={logoUrl} alt={companyName} className="h-10 w-auto max-w-40 object-contain" />
                        ) : (
                            <>
                                <div className="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg transition-all duration-300" style={{ backgroundColor: primaryColor }}>
                                    <Layers className="w-5 h-5 text-white" />
                                </div>
                                <span className="font-bold text-xl text-slate-900 tracking-tight">
                                    {companyName}
                                </span>
                            </>
                        )}
                    </Link>

                    {/* Navigation Links from settings - Desktop only (>=1024px) */}
                    <div className="hidden lg:flex items-center gap-8">
                        {renderNavItems()}

                        {sectionData?.enable_pricing_link !== false && (
                            <Link href={route('pricing.page')} className="text-sm font-medium text-slate-600 hover:opacity-80 transition-colors">
                                {t('Pricing')}
                            </Link>
                        )}
                    </div>

                    {/* Action Buttons - Desktop only (>=1024px) */}
                    <div className="hidden lg:flex items-center gap-4">
                        {isAuthenticated ? (
                            <button
                                onClick={() => router.visit(route('dashboard'))}
                                style={{ backgroundColor: primaryColor }}
                                className="px-5 py-2.5 rounded-full text-sm font-semibold text-white transition-all shadow-md flex items-center gap-2 hover:opacity-90"
                            >
                                <ShieldCheck className="w-4 h-4" />
                                {t('Dashboard')}
                            </button>
                        ) : (
                            <>
                                <a href={route('login')} className="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                                    {t('Login')}
                                </a>
                                <a href={route('register')} style={{ backgroundColor: primaryColor }} className="px-5 py-2.5 rounded-full text-sm font-semibold text-white transition-all shadow-md hover:opacity-90">
                                    {t(ctaText)}
                                </a>
                            </>
                        )}
                        <LanguageSwitcher />
                    </div>

                    {/* Hamburger Toggle - Mobile & Tablet (<1024px) */}
                    <button
                        className="lg:hidden p-2 text-slate-600 hover:text-slate-900 cursor-pointer"
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        aria-label={t('Toggle navigation')}
                    >
                        {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                    </button>
                </div>
            </div>

            {/* Mobile & Tablet Toggle Menu Drawer */}
            {mobileMenuOpen && (
                <div className="lg:hidden bg-white border-t border-slate-200/90 shadow-2xl px-6 py-6 space-y-4">
                    <div className="space-y-1">
                        {renderNavItems(true)}

                        {sectionData?.enable_pricing_link !== false && (
                            <Link
                                href={route('pricing.page')}
                                className="block py-2.5 text-base font-medium text-slate-700 hover:text-slate-900 transition-colors"
                                onClick={() => setMobileMenuOpen(false)}
                            >
                                {t('Pricing')}
                            </Link>
                        )}
                    </div>

                    <div className="pt-4 border-t border-slate-100 flex flex-col gap-3">
                        {isAuthenticated ? (
                            <button
                                onClick={() => {
                                    setMobileMenuOpen(false);
                                    router.visit(route('dashboard'));
                                }}
                                style={{ backgroundColor: primaryColor }}
                                className="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all shadow-md flex items-center justify-center gap-2"
                            >
                                <ShieldCheck className="w-4 h-4" />
                                {t('Dashboard')}
                            </button>
                        ) : (
                            <>
                                <a
                                    href={route('login')}
                                    className="w-full py-2.5 rounded-xl text-center text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    {t('Login')}
                                </a>
                                <a
                                    href={route('register')}
                                    style={{ backgroundColor: primaryColor }}
                                    className="w-full py-3 rounded-xl text-center text-sm font-semibold text-white transition-all shadow-md"
                                    onClick={() => setMobileMenuOpen(false)}
                                >
                                    {t(ctaText)}
                                </a>
                            </>
                        )}

                        <div className="pt-2 flex items-center justify-between border-t border-slate-100">
                            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">{t('Language')}</span>
                            <LanguageSwitcher />
                        </div>
                    </div>
                </div>
            )}
        </nav>
    );
}
