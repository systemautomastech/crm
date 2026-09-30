import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Input } from '@/components/ui/input';
import { Save, Package, Settings as SettingsIcon, Search, Edit3, Plus, Trash2, Shield, Zap, Users, Headphones, Award, RefreshCw, Building2, Calculator, CreditCard, UserCheck, FolderOpen, ArrowUpDown, GripVertical, Layout, Image } from 'lucide-react';
import MediaPicker from '@/components/MediaPicker';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Repeater } from '@/components/ui/repeater';
import { useState } from 'react';
import React from 'react';
import { router, useForm } from '@inertiajs/react';
import { toast } from 'sonner';

interface MarketplaceSetting {
    id?: number;
    title?: string;
    config_sections?: any;
}

interface Module {
    name: string;
    status: string;
    version: string;
}

interface SettingsProps {
    settings: MarketplaceSetting;
    activeModules: Module[];
    selectedModule?: string;
}

export default function Settings({ settings, activeModules, selectedModule }: SettingsProps) {
    const { t } = useTranslation();
    const { auth } = usePage<{auth: {user: any}}>().props;

    if (!auth.user?.permissions?.includes('manage-marketplace-settings')) {
        return (
            <AuthenticatedLayout
                breadcrumbs={[{ label: t('Marketplace Settings') }]}
                pageTitle={t('Marketplace Settings')}
            >
                <Head title={t('Marketplace Settings')} />
                <div className="text-center py-12">
                    <p className="text-gray-500">{t('You do not have permission to access this page.')}</p>
                </div>
            </AuthenticatedLayout>
        );
    }
    const [isLoading, setIsLoading] = useState(false);
    const [activeModule, setActiveModule] = useState('');
    const [activeModuleName, setActiveModuleName] = useState('');
    const [moduleSearchTerm, setModuleSearchTerm] = useState('');
    const [dataInitialized, setDataInitialized] = useState(false);
    const TAB_SECTIONS: Record<string, { key: string; label: string; icon: any }[]> = {
        setup: [
            { key: 'hero', label: t('Hero'), icon: Layout },
            { key: 'order', label: t('Order'), icon: ArrowUpDown },
        ],
        content: [
            { key: 'modules', label: t('Modules'), icon: Package },
            { key: 'dedication', label: t('Dedication'), icon: Users },
        ],
        social: [
            { key: 'screenshots', label: t('Screenshots'), icon: Image },
            { key: 'why_choose', label: t('Why Choose'), icon: Shield },
        ],
    };

    const [activeTab, setActiveTab] = useState<'setup' | 'layout' | 'content' | 'social' | 'engagement'>('setup');
    const [activeSection, setActiveSection] = useState('hero');

    const { data, setData, post, processing, reset } = useForm({
        module: activeModule,
        title: settings?.title || 'Marketplace',
        config_sections: settings?.config_sections || {
            sections: {},
            section_visibility: {
                header: true,
                hero: true,
                modules: true,
                dedication: true,
                screenshots: true,
                why_choose: true,
                cta: true,
                footer: true
            },
            section_order: ['header', 'hero', 'modules', 'dedication', 'screenshots', 'why_choose', 'cta', 'footer']
        }
    });

    const getSectionData = (key: string) => {
        const sectionData = data.config_sections?.sections?.[key] || {};
        // Ensure screenshots section has images array initialized
        if (key === 'screenshots' && !sectionData.images) {
            sectionData.images = [];
        }
        return sectionData;
    };

    const updateSectionData = (key: string, field: string, value: string | any[]) => {
        const currentSections = { ...data.config_sections?.sections };
        currentSections[key] = { ...currentSections[key], [field]: value };

        setData('config_sections', {
            ...data.config_sections,
            sections: currentSections
        });
    };

    const updateSectionVisibility = (sectionKey: string, visible: boolean) => {
        setData('config_sections', {
            ...data.config_sections,
            section_visibility: { ...data.config_sections?.section_visibility, [sectionKey]: visible }
        });
    };

    // Initialize with first module if no module selected
    React.useEffect(() => {
        if (!selectedModule && activeModules.length > 0 && !activeModule) {
            const firstModule = activeModules[0];
            setActiveModule(firstModule.module);
            setActiveModuleName(firstModule.name);
            // Don't make API call here - let the component load with default state
        } else if (selectedModule) {
            setActiveModule(selectedModule);
            const moduleData = activeModules.find(m => m.module === selectedModule);
            setActiveModuleName(moduleData?.name || '');
        }
    }, [selectedModule, activeModules]);

    // Update form data when settings change (only on module switch, not on every save)
    React.useEffect(() => {
        if (settings && activeModule) {
            setData({
                module: activeModule,
                title: settings.title || 'Marketplace',
                config_sections: settings.config_sections || {
                    sections: {},
                    section_visibility: {
                        header: true,
                        hero: true,
                        modules: true,
                        dedication: true,
                        screenshots: true,
                        why_choose: true,
                        cta: true,
                        footer: true
                    },
                    section_order: ['header', 'hero', 'modules', 'dedication', 'screenshots', 'why_choose', 'cta', 'footer']
                }
            });
            setDataInitialized(true);
        }
    }, [settings, activeModule]);

    const saveSettings = () => {
        setIsLoading(true);

        router.post(route('marketplace.settings.store'), data as any, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                setIsLoading(false);
                if ((page.props as any).flash?.success) {
                    toast.success((page.props as any).flash.success);
                }
            },
            onError: (errors) => {
                setIsLoading(false);
                if (errors.message) {
                    toast.error(errors.message);
                } else {
                    toast.error(t('Failed to save settings'));
                }
            }
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Marketplace Settings') }
            ]}
            pageTitle={t('Marketplace Settings')}
            pageActions={
                auth.user?.permissions?.includes('manage-marketplace-settings') && (
                    <Button
                        onClick={saveSettings}
                        disabled={isLoading}
                        className="text-white"
                        style={{ backgroundColor: 'hsl(var(--primary))' }}
                    >
                        <Save className="h-4 w-4 mr-2" />
                        {isLoading ? t('Saving...') : t('Save Changes')}
                    </Button>
                )
            }
        >
            <Head title={t('Marketplace Settings')} />

            <div className="space-y-6">

                <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    {/* Module Sidebar */}
                    <Card className="lg:col-span-1">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                                <Package className="h-4 w-4" />
                                {t('Active Add-Ons')}
                            </CardTitle>
                            <CardDescription>
                                {t('Select addon to configure marketplace settings')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2 max-h-[calc(100vh-15rem)] overflow-y-auto">
                            <div className="relative mb-3">
                                <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder={t('Search addons...')}
                                    value={moduleSearchTerm}
                                    onChange={(e) => setModuleSearchTerm(e.target.value)}
                                    className="pl-8"
                                />
                            </div>

                            {activeModules
                                .filter(module =>
                                    module.name.toLowerCase().includes(moduleSearchTerm.toLowerCase())
                                )
                                .map((module, index) => (
                                <Button
                                    key={index}
                                    variant={activeModule === module.module ? "default" : "ghost"}
                                    className="w-full justify-start gap-2"
                                    onClick={() => {
                                        if (activeModule !== module.module) {
                                            setActiveModule(module.module);
                                            setActiveModuleName(module.name);
                                            setDataInitialized(false);
                                            router.visit(route('marketplace.settings') + '?module=' + module.module, {
                                                preserveScroll: true,
                                                preserveState: true,
                                            });
                                        }
                                    }}
                                >
                                    <div className="flex flex-col items-start">
                                        <span className="text-sm">{module.name}</span>
                                    </div>
                                    {activeModule === module.module && (
                                        <Edit3 className="h-3 w-3 ml-auto" />
                                    )}
                                </Button>
                            ))}
                        </CardContent>
                    </Card>

                    {/* Settings Editor */}
                    <Card className="lg:col-span-3">
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center gap-2 text-lg font-semibold">
                                <SettingsIcon className="h-4 w-4" />
                                {activeModuleName ? `${activeModuleName} ${t('Marketplace Settings')}` : t('Marketplace Settings')}
                            </CardTitle>
                            <CardDescription>
                                {activeModuleName
                                    ? t('Configure marketplace settings for') + ` ${activeModuleName}`
                                    : t('Select a module to configure marketplace settings')
                                }
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {activeModule && (
                                <div className="space-y-6">
                                    {/* Tab Navigation */}
                                    <Tabs
                                        value={activeTab}
                                        onValueChange={(val) => {
                                            const first: Record<string, string> = {
                                                setup: 'hero',
                                                content: 'modules',
                                                social: 'screenshots'
                                            };
                                            setActiveTab(val as any);
                                            setActiveSection(first[val]);
                                        }}
                                    >
                                        <TabsList className="w-full h-auto flex">
                                            <TabsTrigger value="setup" className="flex-1">{t('Setup')}</TabsTrigger>
                                            <TabsTrigger value="content" className="flex-1">{t('Content')}</TabsTrigger>
                                            <TabsTrigger value="social" className="flex-1">{t('Social')}</TabsTrigger>
                                        </TabsList>
                                    </Tabs>

                                    {/* Section Navigation within Tab */}
                                    <div className="border-b border-gray-200 mb-6">
                                        <nav className="flex">
                                            {(TAB_SECTIONS[activeTab] || []).map(section => {
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
                                                        {section.label}
                                                    </button>
                                                );
                                            })}
                                        </nav>
                                    </div>

                                    {/* Section Components */}
                                    {activeSection === 'hero' && (
                                        <div className="space-y-4">
                                            <div>
                                                <label className="text-sm font-medium">{t('Title')}</label>
                                                <Input
                                                    value={data.title}
                                                    onChange={(e) => setData('title', e.target.value)}
                                                    placeholder={t('Marketplace')}
                                                />
                                            </div>
                                            <div>
                                                <label className="text-sm font-medium">{t('Hero Variant')}</label>
                                                <Select
                                                    value={getSectionData('hero').variant || 'hero1'}
                                                    onValueChange={(value) => updateSectionData('hero', 'variant', value)}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('Select Hero Style')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="hero1">{t('Image Left Split')}</SelectItem>
                                                        <SelectItem value="hero2">{t('Image Right Split')}</SelectItem>
                                                        <SelectItem value="hero3">{t('Background Image')}</SelectItem>
                                                        <SelectItem value="hero4">{t('Minimal')}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label className="text-sm font-medium">{t('Hero Title')}</label>
                                                    <Input
                                                        value={getSectionData('hero').title || ''}
                                                        onChange={(e) => updateSectionData('hero', 'title', e.target.value)}
                                                        placeholder={t('Discover Premium Business Packages')}
                                                    />
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">{t('Hero Subtitle')}</label>
                                                    <Input
                                                        value={getSectionData('hero').subtitle || ''}
                                                        onChange={(e) => updateSectionData('hero', 'subtitle', e.target.value)}
                                                        placeholder={t('Extend your Automas ERP with powerful premium modules...')}
                                                    />
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-1 gap-4">
                                                <div className="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label className="text-sm font-medium">{t('Primary Button Text')}</label>
                                                        <Input
                                                            value={getSectionData('hero').primary_button_text || ''}
                                                            onChange={(e) => updateSectionData('hero', 'primary_button_text', e.target.value)}
                                                            placeholder={t('Browse Packages')}
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-sm font-medium">{t('Primary Button Link')}</label>
                                                        <Input
                                                            value={getSectionData('hero').primary_button_link || ''}
                                                            onChange={(e) => updateSectionData('hero', 'primary_button_link', e.target.value)}
                                                            placeholder={t('#packages')}
                                                        />
                                                    </div>
                                                </div>
                                                <div className="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label className="text-sm font-medium">{t('Secondary Button Text')}</label>
                                                        <Input
                                                            value={getSectionData('hero').secondary_button_text || ''}
                                                            onChange={(e) => updateSectionData('hero', 'secondary_button_text', e.target.value)}
                                                            placeholder={t('View Categories')}
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-sm font-medium">{t('Secondary Button Link')}</label>
                                                        <Input
                                                            value={getSectionData('hero').secondary_button_link || ''}
                                                            onChange={(e) => updateSectionData('hero', 'secondary_button_link', e.target.value)}
                                                            placeholder={t('#categories')}
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label className="text-sm font-medium">{t('Hero Image')}</label>
                                                <MediaPicker
                                                    value={getSectionData('hero').image || ''}
                                                    onChange={(value) => updateSectionData('hero', 'image', value)}
                                                    placeholder={t('Select hero image')}
                                                />
                                            </div>
                                        </div>
                                    )}

                                    {activeSection === 'modules' && (
                                        <div className="space-y-4">
                                            <div>
                                                <label className="text-sm font-medium">{t('Modules Variant')}</label>
                                                <Select
                                                    value={getSectionData('modules').variant || 'modules1'}
                                                    onValueChange={(value) => updateSectionData('modules', 'variant', value)}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('Select Modules Style')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="modules1">{t('Grid')}</SelectItem>
                                                        <SelectItem value="modules2">{t('List')}</SelectItem>
                                                        <SelectItem value="modules3">{t('Cards')}</SelectItem>
                                                        <SelectItem value="modules4">{t('Slider')}</SelectItem>
                                                        <SelectItem value="modules5">{t('Masonry')}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label className="text-sm font-medium">{t('Section Title')}</label>
                                                    <Input
                                                value={getSectionData('modules').title || ''}
                                                onChange={(e) => updateSectionData('modules', 'title', e.target.value)}
                                                placeholder={t('Premium Packages')}
                                            />
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">{t('Section Subtitle')}</label>
                                                    <Input
                                                value={getSectionData('modules').subtitle || ''}
                                                onChange={(e) => updateSectionData('modules', 'subtitle', e.target.value)}
                                                placeholder={t('Discover powerful extensions...')}
                                            />
                                                </div>
                                            </div>
                                            <div>
                                                <Label htmlFor="modules-card-variant">{t('Card Variant')}</Label>
                                                <Select
                                                    value={getSectionData('modules').card_variant || 'card1'}
                                                    onValueChange={(value) => updateSectionData('modules', 'card_variant', value)}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="card1">{t('Overlapping')}</SelectItem>
                                                        <SelectItem value="card2">{t('Modern Gradient')}</SelectItem>
                                                        <SelectItem value="card3">{t('Premium Glass')}</SelectItem>
                                                        <SelectItem value="card4">{t('Horizontal')}</SelectItem>
                                                        <SelectItem value="card5">{t('Colorful Floating')}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                    )}

                                    {activeSection === 'dedication' && (
                                        <div className="space-y-6">
                                            <div>
                                                <label className="text-sm font-medium">{t('Dedication Variant')}</label>
                                                <Select
                                                    value={getSectionData('dedication').variant || 'dedication1'}
                                                    onValueChange={(value) => updateSectionData('dedication', 'variant', value)}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('Select Dedication Style')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="dedication1">{t('Alternating')}</SelectItem>
                                                        <SelectItem value="dedication2">{t('Cards')}</SelectItem>
                                                        <SelectItem value="dedication3">{t('Tabs')}</SelectItem>
                                                        <SelectItem value="dedication4">{t('Timeline')}</SelectItem>
                                                        <SelectItem value="dedication5">{t('Carousel')}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="grid grid-cols-1 gap-4">
                                                <div>
                                                    <label className="text-sm font-medium">{t('Section Title')}</label>
                                                    <Input
                                                        value={getSectionData('dedication').title || ''}
                                                        onChange={(e) => updateSectionData('dedication', 'title', e.target.value)}
                                                        placeholder={t('Dedicated to Excellence')}
                                                    />
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">{t('Description')}</label>
                                                    <Input
                                                        value={getSectionData('dedication').description || ''}
                                                        onChange={(e) => updateSectionData('dedication', 'description', e.target.value)}
                                                        placeholder={t('Our premium packages are crafted...')}
                                                    />
                                                </div>
                                            </div>

                                            <div>
                                                <label className="text-sm font-medium">{t('Sub Sections')}</label>
                                                <Repeater
                                                    fields={[
                                                        {
                                                            name: 'title',
                                                            label: t('Sub Section Title'),
                                                            type: 'text',
                                                            placeholder: t('Advanced CRM Dashboard'),
                                                            required: true
                                                        },
                                                        {
                                                            name: 'description',
                                                            label: t('Description'),
                                                            type: 'textarea',
                                                            placeholder: t('Comprehensive customer relationship management...'),
                                                            required: true
                                                        },
                                                        {
                                                            name: 'keyPoints',
                                                            label: t('Key Points'),
                                                            type: 'tags',
                                                            placeholder: t('Real-time Analytics, Automated Lead Scoring')
                                                        },
                                                        {
                                                            name: 'screenshot',
                                                            label: t('Screenshot'),
                                                            type: 'image',
                                                            placeholder: t('Select screenshot image')
                                                        }
                                                    ]}
                                                    value={(getSectionData('dedication').subSections || []).map((item: any, index: number) => ({
                                                        id: item.id || `subsection-${index}`,
                                                        ...item
                                                    }))}
                                                    onChange={(items) => {
                                                        const subSections = items.map(({ id, ...item }) => item);
                                                        updateSectionData('dedication', 'subSections', subSections);
                                                    }}
                                                    addButtonText={t('Add Sub Section')}
                                                    deleteTooltipText={t('Delete Sub Section')}
                                                />
                                            </div>
                                        </div>
                                    )}

                                    {activeSection === 'screenshots' && (
                                        <div className="space-y-6">
                                            <div>
                                                <label className="text-sm font-medium">{t('Screenshots Variant')}</label>
                                                <Select
                                                    value={getSectionData('screenshots').variant || 'screenshots1'}
                                                    onValueChange={(value) => updateSectionData('screenshots', 'variant', value)}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('Select Screenshots Style')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="screenshots1">{t('Grid')}</SelectItem>
                                                        <SelectItem value="screenshots2">{t('Masonry')}</SelectItem>
                                                        <SelectItem value="screenshots3">{t('Slider')}</SelectItem>
                                                        <SelectItem value="screenshots4">{t('Lightbox')}</SelectItem>
                                                        <SelectItem value="screenshots5">{t('Gallery')}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label className="text-sm font-medium">{t('Section Title')}</label>
                                                    <Input
                                                        value={getSectionData('screenshots').title || ''}
                                                        onChange={(e) => updateSectionData('screenshots', 'title', e.target.value)}
                                                        placeholder={t('Screenshots')}
                                                    />
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">{t('Section Subtitle')}</label>
                                                    <Input
                                                        value={getSectionData('screenshots').subtitle || ''}
                                                        onChange={(e) => updateSectionData('screenshots', 'subtitle', e.target.value)}
                                                        placeholder={t('Explore our premium packages in action')}
                                                    />
                                                </div>
                                            </div>

                                            <div>
                                                <label className="text-sm font-medium">{t('Screenshots')}</label>
                                                <Repeater
                                                    fields={[
                                                        {
                                                            name: 'image',
                                                            label: t('Screenshot Image'),
                                                            type: 'image',
                                                            placeholder: t('Select screenshot image'),
                                                            required: true
                                                        }
                                                    ]}
                                                    value={(getSectionData('screenshots').images || []).map((image: string, index: number) => ({
                                                        id: `screenshot-${index}`,
                                                        image: image || ''
                                                    }))}
                                                    onChange={(items) => {
                                                        const images = items.map(item => item.image || '');
                                                        updateSectionData('screenshots', 'images', images);
                                                    }}
                                                    addButtonText={t('Add Screenshot')}
                                                    deleteTooltipText={t('Delete Screenshot')}
                                                />
                                            </div>
                                        </div>
                                    )}

                                    {activeSection === 'why_choose' && (
                                        <div className="space-y-6">
                                            <div>
                                                <label className="text-sm font-medium">{t('Why Choose Variant')}</label>
                                                <Select
                                                    value={getSectionData('why_choose').variant || 'whychoose1'}
                                                    onValueChange={(value) => updateSectionData('why_choose', 'variant', value)}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('Select Why Choose Style')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="whychoose1">{t('Grid')}</SelectItem>
                                                        <SelectItem value="whychoose2">{t('List')}</SelectItem>
                                                        <SelectItem value="whychoose3">{t('Cards')}</SelectItem>
                                                        <SelectItem value="whychoose4">{t('Timeline')}</SelectItem>
                                                        <SelectItem value="whychoose5">{t('Accordion')}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label className="text-sm font-medium">{t('Section Title')}</label>
                                                    <Input
                                                        value={getSectionData('why_choose').title || ''}
                                                        onChange={(e) => updateSectionData('why_choose', 'title', e.target.value)}
                                                        placeholder={t('Why Choose Our Marketplace?')}
                                                    />
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">{t('Section Subtitle')}</label>
                                                    <Input
                                                        value={getSectionData('why_choose').subtitle || ''}
                                                        onChange={(e) => updateSectionData('why_choose', 'subtitle', e.target.value)}
                                                        placeholder={t('We provide the most comprehensive...')}
                                                    />
                                                </div>
                                            </div>

                                            <div>
                                                <label className="text-sm font-medium">{t('Benefits')}</label>
                                                <Repeater
                                                    fields={[
                                                        {
                                                            name: 'title',
                                                            label: t('Benefit Title'),
                                                            type: 'text',
                                                            placeholder: t('Secure & Reliable'),
                                                            required: true
                                                        },
                                                        {
                                                            name: 'description',
                                                            label: t('Description'),
                                                            type: 'textarea',
                                                            placeholder: t('All packages are thoroughly tested...'),
                                                            required: true
                                                        },
                                                        {
                                                            name: 'icon',
                                                            label: t('Icon'),
                                                            type: 'select',
                                                            options: [
                                                                { value: 'Shield', label: t('Shield') },
                                                                { value: 'Zap', label: t('Zap') },
                                                                { value: 'Users', label: t('Users') },
                                                                { value: 'Headphones', label: 'Headphones' },
                                                                { value: 'Award', label: t('Award') },
                                                                { value: 'RefreshCw', label: t('Refresh') },
                                                                { value: 'Building2', label: t('Building') },
                                                                { value: 'Calculator', label: t('Calculator') },
                                                                { value: 'CreditCard', label: t('Credit Card') },
                                                                { value: 'UserCheck', label: t('User Check') },
                                                                { value: 'FolderOpen', label: t('Folder') }
                                                            ]
                                                        },
                                                        {
                                                            name: 'color',
                                                            label: t('Color'),
                                                            type: 'select',
                                                            options: [
                                                                { value: 'blue', label: t('Blue') },
                                                                { value: 'green', label: t('Green') },
                                                                { value: 'yellow', label: t('Yellow') },
                                                                { value: 'purple', label: t('Purple') },
                                                                { value: 'red', label: t('Red') },
                                                                { value: 'indigo', label: t('Indigo') },
                                                                { value: 'pink', label: t('Pink') },
                                                                { value: 'gray', label: t('Gray') }
                                                            ]
                                                        }
                                                    ]}
                                                    value={(getSectionData('why_choose').benefits || []).map((item: any, index: number) => ({
                                                        id: item.id || `benefit-${index}`,
                                                        ...item
                                                    }))}
                                                    onChange={(items) => {
                                                        const benefits = items.map(({ id, ...item }) => item);
                                                        updateSectionData('why_choose', 'benefits', benefits);
                                                    }}
                                                    addButtonText={t('Add Benefit')}
                                                    deleteTooltipText={t('Delete Benefit')}
                                                />
                                            </div>
                                        </div>
                                    )}

                                    {activeSection === 'order' && (
                                        <div className="space-y-6">
                                            <Card>
                                                <CardHeader>
                                                    <div className="flex items-center gap-3">
                                                        <div className="p-2 bg-indigo-100 rounded-lg">
                                                            <ArrowUpDown className="h-5 w-5 text-indigo-600" />
                                                        </div>
                                                        <div>
                                                            <CardTitle>{t('Section Order')}</CardTitle>
                                                            <CardDescription>{t('Drag and drop to reorder sections on your marketplace page')}</CardDescription>
                                                        </div>
                                                    </div>
                                                </CardHeader>
                                                <CardContent>
                                                    <div className="space-y-3">
                                                        {(data.config_sections?.section_order || []).map((sectionKey, index) => {
                                                            const sectionNames = {
                                                                header: t('Header'),
                                                                hero: t('Hero'),
                                                                modules: t('Modules'),
                                                                dedication: t('Dedication'),
                                                                screenshots: t('Screenshots'),
                                                                why_choose: t('Why Choose'),
                                                                cta: t('CTA'),
                                                                footer: t('Footer')
                                                            };

                                                            const isEnabled = data.config_sections?.section_visibility?.[sectionKey] !== false;

                                                            return (
                                                                <div
                                                                    key={sectionKey}
                                                                    draggable
                                                                    onDragStart={(e) => {
                                                                        e.dataTransfer.setData('text/plain', index.toString());
                                                                    }}
                                                                    onDragOver={(e) => {
                                                                        e.preventDefault();
                                                                    }}
                                                                    onDrop={(e) => {
                                                                        e.preventDefault();
                                                                        const dragIndex = parseInt(e.dataTransfer.getData('text/plain'));
                                                                        const currentOrder = [...(data.config_sections?.section_order || [])];
                                                                        const draggedItem = currentOrder[dragIndex];
                                                                        currentOrder.splice(dragIndex, 1);
                                                                        currentOrder.splice(index, 0, draggedItem);
                                                                        setData('config_sections', {
                                                                            ...data.config_sections,
                                                                            section_order: currentOrder
                                                                        });
                                                                    }}
                                                                    className={`flex items-center gap-3 p-4 border rounded-lg transition-all cursor-move ${
                                                                        isEnabled ? 'bg-white border-gray-200 hover:shadow-md' : 'bg-gray-50 border-gray-300 opacity-60'
                                                                    }`}
                                                                >
                                                                    <GripVertical className="h-5 w-5 text-gray-400" />
                                                                    <div className="flex-1 flex items-center justify-between">
                                                                        <div className="flex items-center gap-3">
                                                                            <span className="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold">
                                                                                {index + 1}
                                                                            </span>
                                                                            <div>
                                                                                <h4 className="font-medium text-gray-900">{sectionNames[sectionKey] || sectionKey}</h4>
                                                                                <p className="text-sm text-gray-500">
                                                                                    {isEnabled ? t('Enabled') : t('Disabled')}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                        <div className="flex items-center gap-2">
                                                                            <Label className="text-sm">{t('Enable')}</Label>
                                                                            <Switch
                                                                                checked={isEnabled}
                                                                                onCheckedChange={(checked) => updateSectionVisibility(sectionKey, checked)}
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            );
                                                        })}
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        </div>
                                    )}
                                </div>
                            )}

                            {!activeModule && (
                                <div className="text-center py-8 text-muted-foreground">
                                    <Package className="h-12 w-12 mx-auto mb-4 opacity-50" />
                                    {t('Select a module to configure marketplace settings')}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}