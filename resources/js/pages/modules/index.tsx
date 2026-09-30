import { useState, useMemo, useRef, useCallback, useEffect } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Package, Plus, Power, PowerOff, Eye, ExternalLink, Sparkles, Search, ArrowRight, X } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { SearchInput } from "@/components/ui/search-input";
import { ScrollArea } from '@/components/ui/scroll-area';
import NoRecordsFound from '@/components/no-records-found';
import { ModulesIndexProps, Module } from './types';
import { getPackageFavicon, getPackageAlias } from '@/utils/helpers';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

interface AddOn {
    name: string;
    image: string;
    url: string;
}

interface Category {
    name: string;
    icon: string;
    description: string;
    add_ons: AddOn[];
}

const slugify = (name: string) => name.toLowerCase().replace(/[^a-z0-9]+/g, '-');

export default function Index() {
    const { modules, auth, addOns = [], exploreUrl = '', systemVersion = '' } = usePage<ModulesIndexProps & { addOns: Category[], exploreUrl: string, systemVersion: string }>().props;
    const { t } = useTranslation();

    const [searchTerm, setSearchTerm] = useState('');
    const [selectedModule, setSelectedModule] = useState<Module | null>(null);
    const [isDetailsOpen, setIsDetailsOpen] = useState(false);
    const [activeCategory, setActiveCategory] = useState<string>(addOns[0]?.name ?? '');
    const [exploreSearch, setExploreSearch] = useState('');
    const sidebarRef = useRef<HTMLDivElement>(null);
    const sectionRefs = useRef<Record<string, HTMLElement | null>>({});
    const isScrollingTo = useRef(false);
    const scrollContainerRef = useRef<HTMLDivElement>(null);

    useFlashMessages();

    const filteredModules = modules.filter(module =>
        module.display !== false &&
        (module.alias.toLowerCase().includes(searchTerm.toLowerCase()) ||
        module.description.toLowerCase().includes(searchTerm.toLowerCase()))
    );

    const filteredAddOns = useMemo(() => {
        if (!exploreSearch.trim()) return null;
        const q = exploreSearch.toLowerCase();
        return addOns
            .map(cat => ({ ...cat, add_ons: cat.add_ons.filter(a => a.name.toLowerCase().includes(q)) }))
            .filter(cat => cat.add_ons.length > 0);
    }, [exploreSearch, addOns]);

    const updateActive = useCallback(() => {
        if (isScrollingTo.current) return;
        const container = scrollContainerRef.current?.querySelector<HTMLDivElement>('[data-radix-scroll-area-viewport]');
        if (!container) return;
        const scrollTop = container.scrollTop + container.clientHeight * 0.3;
        let current = addOns[0]?.name ?? '';
        for (const category of addOns) {
            const el = sectionRefs.current[category.name];
            if (el && el.offsetTop <= scrollTop) {
                current = category.name;
            }
        }
        setActiveCategory(current);
    }, [addOns]);

    useEffect(() => {
        const container = scrollContainerRef.current?.querySelector<HTMLDivElement>('[data-radix-scroll-area-viewport]');
        if (!container) return;
        container.addEventListener('scroll', updateActive, { passive: true });
        updateActive();
        return () => container.removeEventListener('scroll', updateActive);
    }, [updateActive]);

    useEffect(() => {
        if (!sidebarRef.current) return;
        const activeBtn = sidebarRef.current.querySelector<HTMLElement>('[data-active="true"]');
        if (activeBtn) {
            activeBtn.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }, [activeCategory]);

    const scrollToCategory = (name: string) => {
        const el = sectionRefs.current[name];
        const container = scrollContainerRef.current?.querySelector<HTMLDivElement>('[data-radix-scroll-area-viewport]');
        if (!el || !container) return;
        isScrollingTo.current = true;
        setActiveCategory(name);
        container.scrollTo({ top: el.offsetTop - 16, behavior: 'smooth' });
        setTimeout(() => { isScrollingTo.current = false; }, 800);
    };

    const displayCategories = filteredAddOns ?? addOns;

    const handleToggleModule = (moduleName: string, isEnabled: boolean) => {
        router.post(route('add-on.enable', moduleName), {}, {
            preserveState: true,
        });
    };

    const handleViewDetails = (module: Module) => {
        setSelectedModule(module);
        setIsDetailsOpen(true);
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{label: t('Add-ons')}]}
            pageTitle={t('Add-ons Manager')}
            pageActions={
                <TooltipProvider>
                    {auth.user?.permissions?.includes('manage-add-on') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={() => router.visit(route('add-on.upload'))}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Upload Add-ons')}</p>
                            </TooltipContent>
                        </Tooltip>
                    )}
                </TooltipProvider>
            }
        >
            <Head title={t('Add-ons')} />

            <Card>
                <CardHeader className="pb-3">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="font-semibold text-lg">{t('Installed Add-ons')}</h3>
                        {systemVersion && (
                            <div className="flex items-center gap-2 px-3 py-1.5  rounded-lg border border-primary/20">
                                <span className="text-sm font-medium text-primary">
                                    {t('System Version')}: <span className="font-semibold">v{systemVersion}</span>
                                </span>
                            </div>
                        )}
                    </div>
                    <SearchInput
                        value={searchTerm}
                        onChange={setSearchTerm}
                        onSearch={() => {}}
                        placeholder={t('Search installed add-ons...')}
                        className="w-full"
                    />
                </CardHeader>

                <CardContent>
                    {filteredModules.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-4">
                            {filteredModules.map((module) => (
                                <Card key={module.name} className="relative hover:shadow-lg transition-all duration-200 border border-gray-200 flex flex-col">
                                    <div className="p-4 flex-1 flex flex-col">
                                        <div className="flex items-start justify-between mb-3">
                                            <div className="flex items-center gap-3">
                                                <div className="relative">
                                                    <img
                                                        src={getPackageFavicon(module.name)}
                                                        alt={getPackageAlias(module.name)}
                                                        className="h-10 w-10 object-contain rounded-lg"
                                                        onError={(e) => {
                                                            const target = e.target as HTMLImageElement;
                                                            target.style.display = 'none';
                                                            target.nextElementSibling?.classList.remove('hidden');
                                                        }}
                                                    />
                                                    <Package className="h-10 w-10 text-primary hidden" />
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-1 flex-shrink-0">
                                                <span className="text-xs text-green-600 font-medium whitespace-nowrap">v{parseFloat(module.version).toFixed(1)}</span>
                                                <span className={`px-2 py-1 rounded-md text-xs font-medium whitespace-nowrap ${
                                                    module.is_enabled
                                                        ? 'bg-green-500 text-white'
                                                        : 'bg-gray-500 text-white'
                                                }`}>
                                                    {module.is_enabled ? t('Active') : t('Inactive')}
                                                </span>
                                            </div>
                                        </div>

                                        <div className="mb-4">
                                            <h3 className="font-semibold text-gray-900 text-sm mb-1 line-clamp-2">{module.alias}</h3>
                                            <p className="text-xs text-gray-500 line-clamp-2">{module.description}</p>
                                        </div>

                                        <div className="mt-auto flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handleViewDetails(module)}
                                                className="flex-1 h-8 text-xs"
                                            >
                                                <Eye className="mr-1 h-3 w-3" />
                                                {t('Details')}
                                            </Button>
                                            {auth.user?.permissions?.includes('manage-actions') && (
                                                <TooltipProvider>
                                                    <Tooltip delayDuration={0}>
                                                        <TooltipTrigger asChild>
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleToggleModule(module.name, module.is_enabled)}
                                                                className={`h-8 px-2 ${module.is_enabled ? 'bg-red-50 hover:bg-red-100 border-red-200' : 'bg-green-50 hover:bg-green-100 border-green-200'}`}
                                                            >
                                                                {module.is_enabled ? (
                                                                    <PowerOff className="h-3 w-3 text-red-600" />
                                                                ) : (
                                                                    <Power className="h-3 w-3 text-green-600" />
                                                                )}
                                                            </Button>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>{module.is_enabled ? t('Disable Module') : t('Enable Module')}</p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                            )}
                                        </div>
                                    </div>
                                </Card>
                            ))}
                        </div>
                    ) : (
                        <NoRecordsFound
                            icon={Package}
                            title={t('No add-ons found')}
                            description={searchTerm ? t('No add-ons match your search criteria.') : t('No add-ons are available.')}
                            hasFilters={!!searchTerm}
                            onClearFilters={() => setSearchTerm('')}
                        />
                    )}
                </CardContent>
            </Card>

            {addOns.length > 0 && (
                <Card className="mt-6">
                    <CardHeader>
                        <h3 className="font-semibold text-lg">{t('Explore Add-ons')}</h3>
                    </CardHeader>
                    <CardContent>                        
                        <div className="flex gap-4">
                            <div className="w-60 shrink-0">
                                <Card className="sticky top-4 border border-gray-100 shadow-sm">
                                    <div className="px-3 pt-3 pb-2 border-b border-gray-100">
                                        <div className="relative">
                                            <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400" />
                                            <input
                                                type="text"
                                                value={exploreSearch}
                                                onChange={e => setExploreSearch(e.target.value)}
                                                placeholder={t('Search add-ons...')}
                                                className="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                                            />
                                        </div>
                                    </div>
                                    <CardContent className="p-2 max-h-[70vh] overflow-y-auto" ref={sidebarRef}>
                                        <ul className="space-y-0.5">
                                            {addOns.map(category => {
                                                const isActive = activeCategory === category.name && !exploreSearch;
                                                return (
                                                    <li key={category.name}>
                                                        <button
                                                            data-active={isActive}
                                                            onClick={() => { setExploreSearch(''); scrollToCategory(category.name); }}
                                                            className={`w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg text-sm text-left transition-all duration-150 ${
                                                                isActive
                                                                    ? 'bg-primary text-primary-foreground shadow-sm'
                                                                    : 'hover:bg-gray-50 text-gray-600 hover:text-gray-900'
                                                            }`}
                                                        >
                                                            <span className="flex items-center gap-2.5 truncate">
                                                                <i className={`${category.icon} text-base shrink-0`} />
                                                                <span className="truncate font-medium">{category.name}</span>
                                                            </span>
                                                            <span className={`shrink-0 text-xs font-semibold px-1.5 py-0.5 rounded-md ${
                                                                isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'
                                                            }`}>
                                                                {category.add_ons.length}
                                                            </span>
                                                        </button>
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    </CardContent>
                                </Card>
                            </div>

                            <ScrollArea className="flex-1 min-w-0 h-[calc(100vh-280px)]" ref={scrollContainerRef}>
                                <div className="space-y-8 pr-4">
                                    {exploreSearch && filteredAddOns?.length === 0 && (
                                        <div className="flex flex-col items-center justify-center py-16 text-center">
                                            <Search className="h-10 w-10 text-gray-300 mb-3" />
                                            <p className="font-medium text-gray-500">{t('No add-ons match')} "{exploreSearch}"</p>
                                            <div className="flex items-center gap-2 mt-3">
                                                <button
                                                    onClick={() => setExploreSearch('')}
                                                    className="inline-flex items-center gap-1.5 text-sm font-semibold border border-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors"
                                                >
                                                    <X className="h-3.5 w-3.5" />
                                                    {t('Clear Search')}
                                                </button>
                                                <a
                                                    href={exploreUrl}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-1.5 text-sm font-semibold bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors"
                                                >
                                                    <ExternalLink className="h-3.5 w-3.5" />
                                                    {t('Explore All Add-ons')}
                                                </a>
                                            </div>
                                        </div>
                                    )}

                                    {displayCategories.map(category => (
                                        <section
                                            key={category.name}
                                            id={slugify(category.name)}
                                            ref={el => { sectionRefs.current[category.name] = el; }}
                                        >
                                            <div className="flex items-center gap-3 mb-3">
                                                <div className="p-2 bg-primary/10 rounded-lg">
                                                    <i className={`${category.icon} text-xl text-primary`} />
                                                </div>
                                                <div>
                                                    <h3 className="font-bold text-gray-900 text-base">{category.name}</h3>
                                                    <p className="text-xs text-gray-400">{category.add_ons.length} {t('add-ons')}</p>
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3">
                                                {category.add_ons.map(addon => (
                                                    <a
                                                        key={addon.name}
                                                        href={addon.url}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="group"
                                                    >
                                                        <Card className="h-full border border-gray-300 hover:border-primary transition-colors duration-200 overflow-hidden">
                                                            <div className="h-0.5 w-full bg-primary scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left" />
                                                            <div className="p-4 flex flex-col items-center text-center gap-2.5">
                                                                {addon.image ? (
                                                                    <img
                                                                        src={addon.image}
                                                                        alt={addon.name}
                                                                        className="w-12 h-12 object-contain rounded-lg"
                                                                        onError={(e) => {
                                                                            const el = e.target as HTMLImageElement;
                                                                            el.style.display = 'none';
                                                                            el.nextElementSibling?.classList.remove('hidden');
                                                                        }}
                                                                    />
                                                                ) : null}
                                                                <Package className={`h-10 w-10 text-primary/40 ${addon.image ? 'hidden' : ''}`} />
                                                                <p className="text-sm font-semibold text-gray-800 line-clamp-2 leading-snug">
                                                                    {addon.name}
                                                                </p>
                                                                <span className="inline-flex items-center gap-1 text-xs font-semibold text-primary/70 group-hover:text-primary transition-colors">
                                                                    {t('View Details')} <ExternalLink className="h-2.5 w-2.5" />
                                                                </span>
                                                            </div>
                                                        </Card>
                                                    </a>
                                                ))}
                                            </div>
                                        </section>
                                    ))}

                                    {!exploreSearch && (
                                        <div className="rounded-xl border border-dashed border-primary/30 bg-primary/5 p-5 flex items-center justify-between gap-4">
                                            <div>
                                                <p className="font-semibold text-gray-800 text-sm">{t("Can't find what you need?")}</p>
                                                <p className="text-xs text-gray-500 mt-0.5">{t('Browse our full marketplace with 300+ add-ons across all categories.')}</p>
                                            </div>
                                            <a
                                                href={exploreUrl}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="shrink-0 inline-flex items-center gap-2 bg-primary text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors"
                                            >
                                                {t('View All')} <ArrowRight className="h-3.5 w-3.5" />
                                            </a>
                                        </div>
                                    )}
                                </div>
                            </ScrollArea>
                        </div>
                    </CardContent>
                </Card>
            )}

            <Dialog open={isDetailsOpen} onOpenChange={setIsDetailsOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-3">
                            <img
                                src={selectedModule?.image}
                                alt={selectedModule?.alias}
                                className="h-8 w-8 object-contain rounded"
                                onError={(e) => {
                                    const target = e.target as HTMLImageElement;
                                    target.style.display = 'none';
                                    target.nextElementSibling?.classList.remove('hidden');
                                }}
                            />
                            <Package className="h-8 w-8 text-primary hidden" />
                            {selectedModule?.alias}
                        </DialogTitle>
                        <DialogDescription>
                            {selectedModule?.description}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span className="font-medium text-gray-600">{t('Version')}:</span>
                                <p className="text-green-600 font-medium">v{selectedModule?.version}</p>
                            </div>
                            <div>
                                <span className="font-medium text-gray-600">{t('Status')}:</span>
                                <p className={`font-medium ${
                                    selectedModule?.is_enabled ? 'text-green-600' : 'text-gray-500'
                                }`}>
                                    {selectedModule?.is_enabled ? t('Active') : t('Inactive')}
                                </p>
                            </div>
                        </div>
                        {selectedModule?.package_name && (
                            <div>
                                <span className="font-medium text-gray-600">{t('Package')}:</span>
                                <p className="text-sm text-gray-800">{selectedModule.package_name}</p>
                            </div>
                        )}
                        {auth.user?.permissions?.includes('manage-actions') && (
                            <div className="flex gap-2 pt-4 border-t">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        if (selectedModule) {
                                            handleToggleModule(selectedModule.name, selectedModule.is_enabled);
                                            setIsDetailsOpen(false);
                                        }
                                    }}
                                    className={`flex-1 ${selectedModule?.is_enabled ? 'bg-red-50 hover:bg-red-100 border-red-200 text-red-600' : 'bg-green-50 hover:bg-green-100 border-green-200 text-green-600'}`}
                                >
                                    {selectedModule?.is_enabled ? (
                                        <>
                                            <PowerOff className="mr-2 h-4 w-4" />
                                            {t('Disable')}
                                        </>
                                    ) : (
                                        <>
                                            <Power className="mr-2 h-4 w-4" />
                                            {t('Enable')}
                                        </>
                                    )}
                                </Button>
                            </div>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
