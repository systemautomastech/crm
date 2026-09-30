import { Head, router, usePage } from '@inertiajs/react';
import Header from './components/Header';
import Footer from './components/Footer';
import AddonCard from './components/AddonCard';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { getAdminSetting, getImagePath, formatAdminCurrency } from '@/utils/helpers';
import { useState, useEffect } from 'react';
import { Search, Filter } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import CookieConsent from "@/components/cookie-consent";

interface Addon {
    id: number;
    name: string;
    description?: string;
    image?: string;
    monthly_price?: number;
    yearly_price?: number;
    package_name: string;
}

interface AddonsProps {
    addons?: {
        data: Addon[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    settings?: any;
    categories?: string[];
    filters?: {
        search?: string;
        category?: string;
        price?: string;
        price_type?: string;
        sort?: string;
    };
}

export default function Addons(props: AddonsProps) {
    const { t } = useTranslation();
    const favicon = getAdminSetting('favicon');
    const faviconUrl = favicon ? getImagePath(favicon) : null;
    const { adminAllSetting, auth } = usePage().props as any;
    const addons = props.addons || { data: [], current_page: 1, last_page: 1, per_page: 20, total: 0 };
    const settings = { ...props.settings, is_authenticated: (auth?.user?.id !== undefined && auth?.user?.id !== null) };
    const categories = props.categories || [];
    const filters = props.filters || {};

    const colors = settings?.config_sections?.colors || { primary: '#10b981', secondary: '#059669', accent: '#f59e0b' };
    const addonSettings = settings?.config_sections?.sections?.addons || {};

    const [searchTerm, setSearchTerm] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('');
    const [priceFilter, setPriceFilter] = useState('');
    const [priceType, setPriceType] = useState(addonSettings.default_price_type || 'monthly');
    const [sortBy, setSortBy] = useState('name');

    useEffect(() => {
        if (filters.search) setSearchTerm(filters.search);
        if (filters.category) setSelectedCategory(filters.category);
        if (filters.price) setPriceFilter(filters.price);
        if (filters.price_type) setPriceType(filters.price_type);
        if (filters.sort && typeof filters.sort === 'string') setSortBy(filters.sort);
    }, [filters]);

    const handleFilter = () => {
        const params = {
            search: searchTerm || undefined,
            category: selectedCategory === 'all' ? undefined : selectedCategory,
            price: priceFilter === 'all' ? undefined : priceFilter,
            price_type: priceType,
            sort: sortBy,
            page: 1
        };

        router.get(route('addons.page'), params, { preserveState: true });
    };

    const handlePageChange = (page: number) => {
        const params = {
            search: searchTerm || undefined,
            category: selectedCategory === 'all' ? undefined : selectedCategory,
            price: priceFilter === 'all' ? undefined : priceFilter,
            price_type: priceType,
            sort: sortBy,
            page
        };

        router.get(route('addons.page'), params, { preserveState: true });
    };

    return (
        <>
            <Head title="Addons" >
                {faviconUrl && <link rel="icon" type="image/x-icon" href={faviconUrl} />}
            </Head>

            <Header settings={settings} />

            <main className="min-h-screen bg-slate-50/70 pt-28 pb-20 font-['Plus_Jakarta_Sans',sans-serif]">
                <div className="max-w-7xl mx-auto px-6 lg:px-8">
                    {/* Header */}
                    <div className="text-center max-w-3xl mx-auto mb-14">
                        <span 
                            className="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full border border-slate-200 mb-4 shadow-2xs"
                            style={{ backgroundColor: `${colors.primary}12`, color: colors.primary }}
                        >
                            {t('Enterprise Ecosystem')}
                        </span>
                        <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 tracking-tight leading-[1.08] mb-5">
                            {addonSettings.title || t('Premium Addons')}
                        </h1>
                        <p className="text-base sm:text-lg text-slate-500 font-normal leading-relaxed mb-6">
                            {addonSettings.subtitle || t('Extend your Automas ERP with powerful premium modules designed to enhance your business operations')}
                        </p>
                        <div className="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 bg-white px-4 py-1.5 rounded-full border border-slate-200 shadow-2xs">
                            <span className="w-2 h-2 rounded-full bg-emerald-500" />
                            <span>{t('Showing')} <strong className="text-slate-900">{addons.data.length}</strong> {t('of')} <strong className="text-slate-900">{addons.total}</strong> {t('addons')}</span>
                        </div>
                    </div>

                    {/* Modern Filters Card */}
                    {(addonSettings.show_search !== false || addonSettings.show_category !== false || addonSettings.show_price !== false || addonSettings.show_sort !== false) && (
                        <div className="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/90 p-6 md:p-8 mb-12">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                                {addonSettings.show_search !== false && (
                                    <div className="lg:col-span-2">
                                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">{t('Search')}</label>
                                        <div className="relative">
                                            <Search className="absolute start-3.5 top-1/2 transform -translate-y-1/2 text-slate-400 h-4 w-4" />
                                            <Input
                                                placeholder={t("Search addons...")}
                                                value={searchTerm}
                                                onChange={(e) => setSearchTerm(e.target.value)}
                                                className="ps-10 h-11 rounded-2xl border-slate-200 focus:border-slate-400 bg-slate-50/50"
                                                onKeyPress={(e) => e.key === 'Enter' && handleFilter()}
                                            />
                                        </div>
                                    </div>
                                )}

                                {addonSettings.show_price !== false && (
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">{t("Price")}</label>
                                        <Select value={priceFilter} onValueChange={setPriceFilter}>
                                            <SelectTrigger className="h-11 rounded-2xl border-slate-200 bg-slate-50/50">
                                                <SelectValue placeholder={t("All Prices")} />
                                            </SelectTrigger>
                                            <SelectContent className="rounded-2xl">
                                                <SelectItem value="all">{t("All Prices")}</SelectItem>
                                                <SelectItem value="free">{t("Free")}</SelectItem>
                                                <SelectItem value="0-50">{formatAdminCurrency(0)} - {formatAdminCurrency(50)}</SelectItem>
                                                <SelectItem value="50-100">{formatAdminCurrency(50)} - {formatAdminCurrency(100)}</SelectItem>
                                                <SelectItem value="100+">{formatAdminCurrency(100)}+</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                )}

                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">{t('Price Type')}</label>
                                    <Select value={priceType} onValueChange={setPriceType}>
                                        <SelectTrigger className="h-11 rounded-2xl border-slate-200 bg-slate-50/50">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent className="rounded-2xl">
                                            <SelectItem value="monthly">{t('Monthly')}</SelectItem>
                                            <SelectItem value="yearly">{t('Yearly')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <Button 
                                        onClick={handleFilter} 
                                        style={{ backgroundColor: colors.primary }} 
                                        className="w-full h-11 rounded-2xl text-white font-bold transition-all shadow-md hover:opacity-90 cursor-pointer"
                                    >
                                        <Filter className="h-4 w-4 me-2" />
                                        {t('Filter')}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Cards Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 lg:gap-8">
                        {addons.data.map((addon) => (
                            <AddonCard
                                key={addon.id}
                                addon={addon}
                                colors={colors}
                                priceType={priceType as 'monthly' | 'yearly'}
                                variant={addonSettings.card_variant as 'card1' | 'card2' | 'card3' | 'card4' | 'card5'}
                                onViewDetails={() => {
                                    router.visit(route('marketplace', { slug: addon.package_name }));
                                }}
                            />
                        ))}
                    </div>

                    {/* Pagination */}
                    {addons.last_page > 1 && (
                        <div className="flex justify-center items-center gap-2 mt-16">
                            <Button
                                variant="outline"
                                disabled={addons.current_page === 1}
                                onClick={() => handlePageChange(addons.current_page - 1)}
                                className="rounded-2xl h-10 px-5 border-slate-200 text-slate-700"
                            >
                                {t('Previous')}
                            </Button>

                            {Array.from({ length: Math.min(5, addons.last_page) }, (_, i) => {
                                const page = addons.current_page <= 3
                                    ? i + 1
                                    : addons.current_page + i - 2;
                                if (page > addons.last_page) return null;

                                return (
                                    <Button
                                        key={page}
                                        variant={page === addons.current_page ? 'default' : 'outline'}
                                        onClick={() => handlePageChange(page)}
                                        style={page === addons.current_page ? { backgroundColor: colors.primary } : {}}
                                        className={`rounded-2xl h-10 w-10 p-0 ${page === addons.current_page ? 'text-white shadow-md font-bold' : 'border-slate-200 text-slate-700'}`}
                                    >
                                        {page}
                                    </Button>
                                );
                            })}

                            <Button
                                variant="outline"
                                disabled={addons.current_page === addons.last_page}
                                onClick={() => handlePageChange(addons.current_page + 1)}
                                className="rounded-2xl h-10 px-5 border-slate-200 text-slate-700"
                            >
                                {t('Next')}
                            </Button>
                        </div>
                    )}

                    {addons.data.length === 0 && (
                        <div className="text-center py-20 bg-white rounded-3xl border border-slate-200/90 shadow-sm max-w-xl mx-auto my-12 p-8">
                            <div className="w-20 h-20 mx-auto mb-5 bg-slate-100 rounded-2xl flex items-center justify-center">
                                <svg className="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-bold text-slate-900 mb-2">{t('No Addons Available')}</h3>
                            <p className="text-slate-500 text-sm leading-relaxed">{addonSettings.empty_message || t('Check back later for new premium addons and modules.')}</p>
                        </div>
                    )}
                </div>
            </main>

            <Footer settings={settings} />

            <CookieConsent settings={adminAllSetting || {}} />
        </>
    );
}
