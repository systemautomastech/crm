import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Save, Eye, Settings2, ArrowUpDown, Palette, Layout, Image, Star, Layers, Zap, Monitor, CreditCard, AlignLeft, Package, CheckCircle } from 'lucide-react';
import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';

import General from './components/settings/General';
import Hero from './components/settings/Hero';
import Header from './components/settings/Header';
import Features from './components/settings/Features';
import Stats from './components/settings/Stats';
import Modules from './components/settings/Modules';
import Benefits from './components/settings/Benefits';
import Gallery from './components/settings/Gallery';
import CTA from './components/settings/CTA';
import Footer from './components/settings/Footer';
import Order from './components/settings/Order';
import Colors from './components/settings/Colors';
import Addon from './components/settings/Addon';
import Pricing from './components/settings/Pricing';
import { LandingPreview } from './components/LandingPreview';

interface LandingPageSetting {
    id?: number;
    company_name?: string;
    contact_email?: string;
    contact_phone?: string;
    contact_address?: string;
    config_sections?: any;
}

interface CustomPage {
    id: number;
    title: string;
    slug: string;
}

interface SettingsProps {
    settings: LandingPageSetting;
    customPages: CustomPage[];
}

const TAB_SECTIONS: Record<string, { key: string; label: string; icon: any }[]> = {
    setup: [
        { key: 'general', label: 'General', icon: Settings2 },
        { key: 'order', label: 'Order', icon: ArrowUpDown },
    ],
    layout: [
        { key: 'header', label: 'Header', icon: AlignLeft },
        { key: 'hero', label: 'Hero', icon: Layout },
        { key: 'footer', label: 'Footer', icon: Layers },
    ],
    content: [
        { key: 'features', label: 'Features', icon: Star },
        { key: 'modules', label: 'Modules', icon: Monitor },
        { key: 'benefits', label: 'Benefits', icon: CheckCircle },
    ],
    social: [
        { key: 'stats', label: 'Stats', icon: Layers },
        { key: 'gallery', label: 'Gallery', icon: Image },
    ],
    engagement: [
        { key: 'cta', label: 'CTA', icon: Zap },
    ],
    themecolor: [
        { key: 'colors', label: 'Colors', icon: Palette },
    ],
    page: [
        { key: 'addon', label: 'Addon', icon: Package },
        { key: 'pricing', label: 'Pricing', icon: CreditCard },
    ],
};

export default function Settings({ settings, customPages }: SettingsProps) {
    const { t } = useTranslation();
    const { auth } = usePage<{auth: {user: any}}>().props;

    if (!auth.user?.permissions?.includes('manage-landing-page')) {
        return (
            <AuthenticatedLayout
                breadcrumbs={[{ label: t('Landing Page Settings') }]}
                pageTitle={t('Landing Page Settings')}
            >
                <Head title={t('Landing Page Settings')} />
                <div className="text-center py-12">
                    <p className="text-gray-500">{t('You do not have permission to access this page.')}</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    const [activeTab, setActiveTab] = useState<'setup' | 'layout' | 'content' | 'social' | 'engagement' | 'themecolor' | 'page'>('setup');
    const [activeSection, setActiveSection] = useState<string>('general');

    const { data, setData, post, processing } = useForm({
        company_name: settings.company_name || '',
        contact_email: settings.contact_email || '',
        contact_phone: settings.contact_phone || '',
        contact_address: settings.contact_address || '',
        config_sections: settings.config_sections || {
            sections: {},
            section_visibility: {
                header: true, hero: true, stats: true, features: true,
                modules: true, benefits: true, gallery: true, cta: true,
                footer: true, addons: true, pricing: true
            },
            section_order: ['header', 'hero', 'stats', 'features', 'modules', 'benefits', 'gallery', 'cta', 'footer']
        }
    });

    const getSectionData = (key: string) => data.config_sections?.sections?.[key] || {};

    const updateSectionData = (key: string, updates: any) => {
        const currentSections = { ...data.config_sections?.sections };
        currentSections[key] = { ...currentSections[key], ...updates };
        setData('config_sections', { ...data.config_sections, sections: currentSections });
    };

    const updateSectionVisibility = (sectionKey: string, visible: boolean) => {
        setData('config_sections', {
            ...data.config_sections,
            section_visibility: { ...data.config_sections?.section_visibility, [sectionKey]: visible }
        });
    };

    const saveSettings = () => {
        post(route('landing-page.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.success) toast.success(page.props.flash.success);
            },
            onError: (errors) => {
                toast.error(errors.message || t('Failed to save settings'));
            }
        });
    };

    const sections = TAB_SECTIONS[activeTab] || [];

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Landing Page Settings') }]}
            pageTitle={t('Landing Page Settings')}
            pageActions={
                <div className="flex gap-2">
                    <Button variant="outline" onClick={() => window.open(route('landing.page'), '_blank')}>
                        <Eye className="h-4 w-4 mr-2" />
                        {t('View Landing Page')}
                    </Button>
                    {auth.user?.permissions?.includes('edit-landing-page') && (
                        <Button
                            onClick={saveSettings}
                            disabled={processing}
                            className="text-white"
                            style={{ backgroundColor: 'hsl(var(--primary))' }}
                        >
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? t('Saving...') : t('Save Changes')}
                        </Button>
                    )}
                </div>
            }
        >
            <Head title={t('Landing Page Settings')} />

            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div className="lg:col-span-8 space-y-4">

                    {/* Main Tabs */}
                    <Tabs
                        value={activeTab}
                        onValueChange={(val) => {
                            const first: Record<string, string> = {
                                setup: 'general', layout: 'header', content: 'features',
                                social: 'stats', engagement: 'cta', themecolor: 'colors', page: 'addon'
                            };
                            setActiveTab(val as any);
                            setActiveSection(first[val]);
                        }}
                    >
                        <TabsList className="w-full h-auto flex">
                            <TabsTrigger value="setup" className="flex-1">{t('Setup')}</TabsTrigger>
                            <TabsTrigger value="layout" className="flex-1">{t('Layout')}</TabsTrigger>
                            <TabsTrigger value="content" className="flex-1">{t('Content')}</TabsTrigger>
                            <TabsTrigger value="social" className="flex-1">{t('Social')}</TabsTrigger>
                            <TabsTrigger value="engagement" className="flex-1">{t('Engagement')}</TabsTrigger>
                            <TabsTrigger value="themecolor" className="flex-1">{t('Theme Color')}</TabsTrigger>
                            <TabsTrigger value="page" className="flex-1">{t('Page')}</TabsTrigger>
                        </TabsList>
                    </Tabs>

                    {/* Section Tabs */}
                    <div className="border-b border-gray-200">
                        <nav className="flex">
                            {sections.map(section => {
                                const Icon = section.icon;
                                const isActive = activeSection === section.key;
                                return (
                                    <button
                                        key={section.key}
                                        onClick={() => setActiveSection(section.key)}
                                        className={`flex items-center gap-2 px-5 py-2.5 text-sm font-medium border-b-2 -mb-px transition-all ${
                                            isActive
                                                ? 'border-primary text-primary'
                                                : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
                                        }`}
                                    >
                                        <Icon className="h-4 w-4" />
                                        {t(section.label)}
                                    </button>
                                );
                            })}
                        </nav>
                    </div>

                    {/* Section Content */}
                    <div>
                        {activeSection === 'general' && <General data={data} updateSectionData={(field, value) => setData(field, value)} />}
                        {activeSection === 'hero' && <Hero data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'features' && <Features data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'header' && <Header data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} customPages={customPages || []} />}
                        {activeSection === 'stats' && <Stats data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'modules' && <Modules data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'benefits' && <Benefits data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'gallery' && <Gallery data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'cta' && <CTA data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'footer' && <Footer data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} customPages={customPages || []} />}
                        {activeSection === 'order' && <Order data={data} setData={setData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'colors' && <Colors data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} setData={setData} />}
                        {activeSection === 'addon' && <Addon data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                        {activeSection === 'pricing' && <Pricing data={data} getSectionData={getSectionData} updateSectionData={updateSectionData} updateSectionVisibility={updateSectionVisibility} />}
                    </div>
                </div>

                {/* Live Preview */}
                <div className="lg:col-span-4">
                    <div className="sticky top-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">{t('Live Preview')}</CardTitle>
                            </CardHeader>
                            <CardContent className="p-2">
                                <LandingPreview settings={data} />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
