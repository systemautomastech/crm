import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';

import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog } from "@/components/ui/dialog";

import { Package, Eye } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { ListGridToggle } from '@/components/ui/list-grid-toggle';

import View from './View';
import NoRecordsFound from '@/components/no-records-found';
import { InventoryItem, InventoryItemsIndexProps, InventoryItemFilters, InventoryItemModalState } from './types';

export default function Index() {
    const { t } = useTranslation();
    const { items, auth } = usePage<InventoryItemsIndexProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState<InventoryItemFilters>({
        product_name: urlParams.get('product_name') || '',
        valuation_method: urlParams.get('valuation_method') || '',
        is_tracked: urlParams.get('is_tracked') || ''
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [viewMode, setViewMode] = useState<'list' | 'grid'>(urlParams.get('view') as 'list' | 'grid' || 'list');
    const [modalState, setModalState] = useState<InventoryItemModalState>({
        isOpen: false,
        mode: '',
        data: null
    });
    const [showFilters, setShowFilters] = useState(false);

    useFlashMessages();



    const handleFilter = () => {
        router.get(route('inventory.items.index'), {...filters, per_page: perPage, sort: sortField, direction: sortDirection, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('inventory.items.index'), {...filters, per_page: perPage, sort: field, direction, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ product_name: '', valuation_method: '', is_tracked: '' });
        router.get(route('inventory.items.index'), {per_page: perPage, view: viewMode});
    };

    const openModal = (mode: 'view', data: InventoryItem | null = null) => {
        setModalState({
            isOpen: true,
            mode,
            data
        });
    };

    const closeModal = () => {
        setModalState({
            isOpen: false,
            mode: '',
            data: null
        });
    };

    const tableColumns = [
        {
            key: 'product.name',
            header: t('Product'),
            sortable: true,
            render: (_: any, item: InventoryItem) => item.product?.name || '-'
        },
        {
            key: 'product.sku',
            header: t('SKU'),
            render: (_: any, item: InventoryItem) => item.product?.sku || '-'
        },
        {
            key: 'reorder_level',
            header: t('Reorder Level'),
            sortable: true
        },
        {
            key: 'maximum_level',
            header: t('Maximum Level'),
            sortable: true
        },
        {
            key: 'valuation_method',
            header: t('Valuation Method'),
            sortable: true,
            render: (value: string) => value?.toUpperCase()
        },
        {
            key: 'is_tracked',
            header: t('Tracked'),
            sortable: true,
            render: (value: boolean) => (
                <span className={`px-2 py-1 rounded-full text-sm ${
                    value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`}>
                    {value ? t('Yes') : t('No')}
                </span>
            )
        },
        ...(auth.user?.permissions?.includes('view-inventory-items') ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, item: InventoryItem) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="sm" onClick={() => openModal('view', item)} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                    <Eye className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('View')}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            )
        }] : [])
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[{label: t('Inventory')}, {label: t('Items')}]}
            pageTitle={t('Manage Inventory Items')}
            pageActions={
                <div className="flex gap-2">
                </div>
            }
        >
            <Head title={t('Inventory Items')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.product_name}
                                onChange={(value) => setFilters({...filters, product_name: value})}
                                onSearch={handleFilter}
                                placeholder={t('Search products , sku...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <ListGridToggle
                                currentView={viewMode}
                                routeName="inventory.items.index"
                                filters={{...filters, per_page: perPage}}
                            />
                            <PerPageSelector
                                routeName="inventory.items.index"
                                filters={{...filters, view: viewMode}}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {(() => {
                                    const activeFilters = [filters.valuation_method, filters.is_tracked].filter(Boolean).length;
                                    return activeFilters > 0 && (
                                        <span className="absolute -top-2 -right-2 bg-primary text-primary-foreground text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                            {activeFilters}
                                        </span>
                                    );
                                })()}
                            </div>
                        </div>
                    </div>
                </CardContent>

                {showFilters && (
                    <CardContent className="p-6 bg-blue-50/30 border-b">
                        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Valuation Method')}</label>
                                <Select value={filters.valuation_method} onValueChange={(value) => setFilters({...filters, valuation_method: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by method')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="fifo">{t('FIFO')}</SelectItem>
                                        <SelectItem value="lifo">{t('LIFO')}</SelectItem>
                                        <SelectItem value="weighted_average">{t('Weighted Average')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Tracked')}</label>
                                <Select value={filters.is_tracked} onValueChange={(value) => setFilters({...filters, is_tracked: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by tracking')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">{t('Yes')}</SelectItem>
                                        <SelectItem value="0">{t('No')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-end gap-2">
                                <Button onClick={handleFilter} size="sm">{t('Apply')}</Button>
                                <Button variant="outline" onClick={clearFilters} size="sm">{t('Clear')}</Button>
                            </div>
                        </div>
                    </CardContent>
                )}

                <CardContent className="p-0">
                    {viewMode === 'list' ? (
                        <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[70vh] rounded-none w-full">
                            <div className="min-w-[800px]">
                                <DataTable
                                    data={items.data}
                                    columns={tableColumns}
                                    onSort={handleSort}
                                    sortKey={sortField}
                                    sortDirection={sortDirection as 'asc' | 'desc'}
                                    className="rounded-none"
                                    emptyState={
                                        <NoRecordsFound
                                            icon={Package}
                                            title={t('No inventory items found')}
                                            description={t('No inventory items match your current filters.')}
                                            hasFilters={!!(filters.product_name || filters.valuation_method || filters.is_tracked)}
                                            onClearFilters={clearFilters}
                                            className="h-auto"
                                        />
                                    }
                                />
                            </div>
                        </div>
                    ) : (
                        <div className="overflow-auto max-h-[70vh] p-6">
                            {items.data.length > 0 ? (
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                                    {items.data.map((item) => (
                                        <Card key={item.id} className="p-0 hover:shadow-md hover:border-gray-300 transition-all duration-200 relative overflow-hidden flex flex-col h-full min-w-0">
                                            {/* Header */}
                                            <div className="p-4 bg-gradient-to-r from-primary/5 to-transparent border-b flex-shrink-0">
                                                <div className="flex items-center gap-3">
                                                    <div className="p-2 bg-primary/10 rounded-lg">
                                                        <Package className="h-5 w-5 text-primary" />
                                                    </div>
                                                    <div className="min-w-0 flex-1">
                                                        <h3 className="font-semibold text-sm text-gray-900">{item.product?.name}</h3>
                                                        <p className="text-xs text-gray-500">{item.product?.sku}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Body */}
                                            <div className="p-4 flex-1 min-h-0">
                                                <div className="grid grid-cols-2 gap-4 mb-4">
                                                    <div className="text-xs min-w-0">
                                                        <p className="text-muted-foreground mb-1 text-xs uppercase tracking-wide">{t('Reorder')}</p>
                                                        <p className="font-medium text-xs">{item.reorder_level}</p>
                                                    </div>
                                                    <div className="text-xs min-w-0">
                                                        <p className="text-muted-foreground mb-1 text-xs uppercase tracking-wide">{t('Maximum')}</p>
                                                        <p className="font-medium text-xs">{item.maximum_level}</p>
                                                    </div>
                                                </div>

                                                <div className="grid grid-cols-2 gap-4">
                                                    <div className="text-xs min-w-0">
                                                        <p className="text-muted-foreground mb-1 text-xs uppercase tracking-wide">{t('Method')}</p>
                                                        <p className="font-medium text-xs">{item.valuation_method?.toUpperCase()}</p>
                                                    </div>
                                                    <div className="text-xs min-w-0">
                                                        <p className="text-muted-foreground mb-1 text-xs uppercase tracking-wide">{t('Tracked')}</p>
                                                        <span className={`px-2 py-1 rounded-full text-sm ${
                                                            item.is_tracked ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                        }`}>
                                                            {item.is_tracked ? t('Yes') : t('No')}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Actions Footer */}
                                            <div className="p-3 border-t bg-gray-50/50 flex-shrink-0 mt-auto">
                                                {auth.user?.permissions?.includes('view-inventory-items') && (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => openModal('view', item)}
                                                        className="w-full"
                                                        style={{
                                                            borderColor: '#16a34a',
                                                            color: '#16a34a'
                                                        }}
                                                    >
                                                        <Eye className="h-4 w-4 mr-2" />
                                                        {t('View Details')}
                                                    </Button>
                                                )}
                                            </div>
                                        </Card>
                                    ))}
                                </div>
                            ) : (
                                <NoRecordsFound
                                    icon={Package}
                                    title={t('No inventory items found')}
                                    description={t('No inventory items match your current filters.')}
                                    hasFilters={!!(filters.product_name || filters.valuation_method || filters.is_tracked)}
                                    onClearFilters={clearFilters}
                                />
                            )}
                        </div>
                    )}
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={items}
                        routeName="inventory.items.index"
                        filters={{...filters, per_page: perPage, view: viewMode}}
                    />
                </CardContent>
            </Card>

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'view' && modalState.data && (
                    <View
                        data={modalState.data}
                        item={modalState.data}
                        onSuccess={closeModal}
                    />
                )}
            </Dialog>


        </AuthenticatedLayout>
    );
}
