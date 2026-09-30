import { ArrowRight } from 'lucide-react';
import { getImagePath } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';

interface MarketplaceHeroProps {
    settings?: any;
    matchedPackage?: any;
    title?: string;
    subtitle?: string;
    primaryButton?: string;
    secondaryButton?: string;
}

export default function MarketplaceHero({ settings, matchedPackage, title: propTitle, subtitle: propSubtitle, primaryButton, secondaryButton }: MarketplaceHeroProps) {
    const { t } = useTranslation();
    const sectionData = settings?.config_sections?.sections?.hero || {};

    const packageName = matchedPackage?.alias || matchedPackage?.name || propTitle || sectionData.title || 'AI Business Advisor';
    const packageDesc = matchedPackage?.description || propSubtitle || sectionData.subtitle || 'Unlock powerful AI-driven insights to make data-driven business decisions. The AI Business Advisor analyzes your organizational metrics across financial, team, sales, project, and operational dimensions to provide actionable recommendations for continuous business improvement.';
    const packageImage = matchedPackage?.image || sectionData.image;
    const packageImageUrl = packageImage ? getImagePath(packageImage) : null;

    const colors = {
        primary: 'var(--color-primary, #130774)',
        secondary: 'var(--color-secondary, #0b55b7)',
        accent: 'var(--color-accent, #130674)'
    };

    return (
        <section className="relative flex items-center justify-center overflow-hidden pt-28 pb-16 lg:pt-32 lg:pb-20 bg-slate-50/70 font-['Plus_Jakarta_Sans',sans-serif]">
            <div className="relative z-10 max-w-7xl mx-auto px-6 lg:px-8">
                <div className="grid lg:grid-cols-2 gap-12 lg:gap-14 items-center">

                    {/* Left Column Content */}
                    <div className="text-center lg:text-left order-2 lg:order-1">

                        {/* Category Badge */}
                        <div className="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full border border-slate-200 mb-6 bg-white shadow-2xs">
                            <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                            <span className="text-slate-700">{matchedPackage?.module ? `${matchedPackage.module} Module` : t('Addon Module')}</span>
                        </div>

                        {/* Package Title */}
                        <h1 className="text-4xl sm:text-5xl lg:text-5xl font-extrabold text-slate-900 mb-6 tracking-tight leading-[1.15]">
                            {t(packageName)}
                        </h1>

                        {/* Package Subtitle */}
                        <p className="text-base sm:text-lg text-slate-600 leading-relaxed mb-10 max-w-xl mx-auto lg:mx-0 font-normal">
                            {t(packageDesc)}
                        </p>

                        {/* CTA Buttons */}
                        <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-6">
                            <a
                                href={route('register')}
                                style={{ backgroundColor: colors.primary }}
                                className="px-8 py-4 rounded-full text-base font-semibold text-white inline-flex items-center justify-center gap-2.5 shadow-lg shadow-emerald-500/20 transition-all hover:-translate-y-0.5 active:scale-95 hover:opacity-95"
                            >
                                <span>{t(`Install ${packageName}`)}</span>
                                <ArrowRight className="w-4 h-4" />
                            </a>

                            <a
                                href="#details"
                                className="px-8 py-4 rounded-full text-base font-semibold text-slate-700 bg-white border border-slate-200 hover:border-slate-300 hover:shadow-lg transition-all inline-flex items-center justify-center gap-2"
                            >
                                <span>{t('Learn More')}</span>
                            </a>
                        </div>

                    </div>

                    {/* Right Column Dashboard Visual Frame */}
                    <div className="relative order-1 lg:order-2">
                        <div className="relative z-10 bg-white rounded-2xl shadow-xl shadow-slate-300/50 border border-slate-100 overflow-hidden">
                            <div className="bg-slate-50 rounded-xl overflow-hidden border border-slate-100">

                                {/* Browser Chrome Header */}
                                <div className="flex items-center gap-2 px-4 py-3 border-b border-slate-200 bg-white">
                                    <div className="flex gap-1.5">
                                        <div className="w-3 h-3 rounded-full bg-rose-400" />
                                        <div className="w-3 h-3 rounded-full bg-amber-400" />
                                        <div className="w-3 h-3 rounded-full bg-emerald-400" />
                                    </div>
                                    <div className="flex-1 mx-4">
                                        <div className="bg-slate-100 rounded-md px-3 py-1 text-xs text-slate-400 text-center font-mono">
                                            {import.meta.env.VITE_APP_URL || 'automas.com.bd'}/marketplace
                                        </div>
                                    </div>
                                </div>

                                {/* Dashboard Image or Visual Placeholder */}
                                {packageImageUrl ? (
                                    <div className="relative overflow-hidden bg-slate-100">
                                        <img
                                            src={packageImageUrl}
                                            alt={packageName}
                                            className="w-full h-auto object-cover max-h-[440px] rounded-b-xl shadow-inner transition-transform duration-500 hover:scale-[1.01]"
                                        />
                                    </div>
                                ) : (
                                    <div className="p-8 sm:p-10 text-center bg-gradient-to-b from-slate-50 to-white min-h-[320px] flex flex-col items-center justify-center">
                                        <div className="w-20 h-20 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-5 font-bold text-3xl shadow-sm">
                                            ⚡
                                        </div>
                                        <h3 className="text-2xl font-bold text-slate-900 mb-2">{packageName}</h3>
                                        <p className="text-sm text-slate-500 max-w-md mx-auto leading-relaxed">{t('Official Automas ERP Enterprise Module. Built for high reliability and seamless workflow integration.')}</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Floating Compatibility Badge Top Right */}
                        <div className="absolute -top-4 right-2 sm:-top-5 sm:-right-4 bg-white rounded-xl p-3 sm:p-4 shadow-xl shadow-slate-200/60 border border-slate-100 z-20 flex items-center gap-3">
                            <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-lg">
                                ⚡
                            </div>
                            <div>
                                <div className="text-[10px] sm:text-xs text-slate-400 font-medium">{t('Addon Modules')}</div>
                                <div className="text-xs sm:text-sm font-bold text-slate-900">100% Compatible</div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </section>
    );
}
