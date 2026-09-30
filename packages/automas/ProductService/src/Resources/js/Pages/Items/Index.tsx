import { useState, useMemo, useEffect } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Plus, Package, Edit, Trash2, Eye, Image, Download } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { ListGridToggle } from '@/components/ui/list-grid-toggle';
import NoRecordsFound from '@/components/no-records-found';
import { formatCurrency, getImagePath } from '@/utils/helpers';
import { Item, ItemsIndexProps, ItemFilters } from './types';
import { usePageButtons } from '@/hooks/usePageButtons';

export default function Index() {
    const { t } = useTranslation();
    const { items, categories, auth } = usePage<ItemsIndexProps>().props;
    const urlParams = useMemo(() => new URLSearchParams(window.location.search), []);

    // Item types same as Create page
    const itemTypes = ['product', 'service', 'part'];

    const [filters, setFilters] = useState<ItemFilters>({
        name: urlParams.get('name') || '',
        type: urlParams.get('type') || '',
        category_id: urlParams.get('category_id') || ''
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');


    const [viewMode, setViewMode] = useState<'list' | 'grid'>(urlParams.get('view') as 'list' | 'grid' || 'grid');
    const [showFilters, setShowFilters] = useState(false);

    const googleDriveButtons = usePageButtons('googleDriveBtn', { module: 'Products', settingKey: 'GoogleDrive Products' });
    const oneDriveButtons = usePageButtons('oneDriveBtn', { module: 'Products', settingKey: 'OneDrive Products' });
    const hubspotButtons = usePageButtons('hubspotBtn', { module: 'Products', settingKey: 'HubSpot Products' });
    const dropboxBtn = usePageButtons('dropboxBtn', { module: 'Products', settingKey: 'Dropbox Products' });
    const boxBtn = usePageButtons('boxBtn', { module: 'Products', settingKey: 'Box Products' });
    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'product-service.items.destroy',
        defaultMessage: t('Are you sure you want to delete this item?')
    });
    const handleFilter = () => {
        router.get(route('product-service.items.index'), {...filters, per_page: perPage, sort: sortField, direction: sortDirection, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('product-service.items.index'), {...filters, per_page: perPage, sort: field, direction, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ name: '', type: '', category_id: '' });
        router.get(route('product-service.items.index'), {per_page: perPage, view: viewMode});
    };



    const tableColumns = [
        {
            key: 'image',
            header: t('Image'),
            render: (value: string) => {
                if (!value) {
                    return (
                        <div className="w-12 h-12 bg-gray-100 rounded-md border flex items-center justify-center">
                            <Image className="w-6 h-6 text-gray-400" />
                        </div>
                    );
                }
                const isImage = /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(value);
                let imageUrl = getImagePath(value);
                return isImage ? (
                    <div className="relative w-12 h-12">
                        <img
                            src={imageUrl}
                            alt="Image"
                            className="w-12 h-12 object-cover rounded-md border hover:scale-110 transition-transform cursor-pointer"
                            onClick={() => window.open(imageUrl, '_blank')}
                            onError={(e) => {
                                const target = e.target as HTMLImageElement;
                                target.style.display = 'none';
                                const fallback = target.nextElementSibling as HTMLElement;
                                if (fallback) fallback.classList.remove('hidden');
                            }}
                        />
                        <div className="hidden w-12 h-12 bg-gray-100 rounded-md border flex items-center justify-center">
                            <Image className="w-6 h-6 text-gray-400" />
                        </div>
                    </div>
                ) : (
                    <div className="w-12 h-12 bg-gray-100 rounded-md border flex items-center justify-center cursor-pointer hover:bg-gray-200 transition-colors" onClick={() => {
                        const link = document.createElement('a');
                        link.href = getImagePath(value);
                        link.download = value.split('/').pop() || 'file';
                        link.click();
                    }}>
                        <Download className="w-6 h-6 text-gray-600" />
                    </div>
                );
            }
        },
        {
            key: 'name',
            header: t('Name'),
            sortable: true
        },
        {
            key: 'sku',
            header: t('SKU'),
            sortable: true
        },
        {
            key: 'sale_price',
            header: t('Sale Price'),
            sortable: true,
            render: (value: number) => value ? formatCurrency(value) : '-'
        },
        {
            key: 'purchase_price',
            header: t('Purchase Price'),
            sortable: true,
            render: (value: number) => value ? formatCurrency(value) : '-'
        },
        {
            key: 'category_id',
            header: t('Category'),
            render: (value: number, item: Item) => item.category?.name || '-'
        },
        {
            key: 'unit',
            header: t('Unit'),
            render: (value: string, item: Item) => item.unit_relation?.unit_name || '-'
        },
        {
            key: 'total_quantity',
            header: t('Quantity'),
            sortable: false,
            render: (value: number) => Math.floor(value) || 0
        },
        {
            key: 'type',
            header: t('Type'),
            sortable: true,
            render: (value: string) => {
                const typeBadge =
                    value === 'product' ? 'bg-blue-100 text-blue-700' :
                    value === 'service' ? 'bg-violet-100 text-violet-700' :
                    value === 'part'    ? 'bg-amber-100 text-amber-700' :
                                         'bg-gray-100 text-gray-700';
                return (
                    <span className={`px-2 py-1 rounded-full text-xs font-medium capitalize ${typeBadge}`}>
                        {t(value.replace(/_/g, ' '))}
                    </span>
                );
            }
        },
        ...(auth.user?.permissions?.some((p: string) => ['view-product-service-item', 'edit-product-service-item', 'delete-product-service-item'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, item: Item) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth.user?.permissions?.includes('view-product-service-item') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => router.visit(route('product-service.items.show', item.id))} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('edit-product-service-item') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => router.visit(route('product-service.items.edit', item.id))} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                        <Edit className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Edit')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('delete-product-service-item') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteDialog(item.id)}
                                        className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Delete')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                    </TooltipProvider>
                </div>
            )
        }] : [])
    ];

    return (
        <TooltipProvider>
            <AuthenticatedLayout
                breadcrumbs={[
                    {label: t('Product & Service')},
                    {label: t('Items')}
                ]}
                pageTitle={t('Manage Items')}
                pageActions={
                    <div className="flex gap-2">
                        {googleDriveButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {oneDriveButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {dropboxBtn.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {boxBtn.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {hubspotButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {auth.user?.permissions?.includes('manage-stock') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="outline" size="sm" onClick={() => router.visit(route('product-service.stock.index'))}>
                                        <Package className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Add Stock')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('create-product-service-item') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button size="sm" onClick={() => router.visit(route('product-service.items.create'))}>
                                        <Plus className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Create')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                    </div>
                }
            >
            <Head title={t('Items')} />

            <Card className="shadow-sm">
                {/* Search & Controls Header */}
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.name}
                                onChange={(value) => setFilters({...filters, name: value})}
                                onSearch={handleFilter}
                                placeholder={t('Search items...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <ListGridToggle
                                currentView={viewMode}
                                routeName="product-service.items.index"
                                filters={{...filters, per_page: perPage}}
                            />
                            <PerPageSelector
                                routeName="product-service.items.index"
                                filters={{...filters, view: viewMode}}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {(() => {
                                    const activeFilters = [filters.type, filters.category_id].filter(Boolean).length;
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

                {/* Advanced Filters */}
                {showFilters && (
                    <CardContent className="p-6 bg-blue-50/30 border-b">
                        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Item Type')}</label>
                                <Select value={filters.type} onValueChange={(value) => setFilters({...filters, type: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by item type')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {itemTypes.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {t(type)}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Category')}</label>
                                <Select value={filters.category_id} onValueChange={(value) => setFilters({...filters, category_id: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by category')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {categories.map((category) => (
                                            <SelectItem key={category.id} value={category.id.toString()}>
                                                {category.name}
                                            </SelectItem>
                                        ))}
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

                {/* Table Content */}
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
                                            title={t('No items found')}
                                            description={t('Get started by creating your first item.')}
                                            hasFilters={!!(filters.name || filters.type || filters.category_id)}
                                            onClearFilters={clearFilters}
                                            createPermission="create-product-service-item"
                                            onCreateClick={() => router.visit(route('product-service.items.create'))}
                                            createButtonText={t('Create Item')}
                                            className="h-auto"
                                        />
                                    }
                                />
                            </div>
                        </div>
                    ) : (
                        <div className="overflow-auto max-h-[70vh] p-3 sm:p-4 lg:p-6">
                            {items.data.length > 0 ? (
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-5">
                                    {items.data.map((item) => {
                                        const typeBadge: Record<string, string> = {
                                            product: 'bg-blue-500/90 text-white',
                                            service: 'bg-violet-500/90 text-white',
                                            part:    'bg-amber-500/90 text-white',
                                        };
                                        const badgeClass = typeBadge[item.type ?? ''] ?? 'bg-slate-500/90 text-white';
                                        const canView = auth.user?.permissions?.includes('view-product-service-item');

                                        return (
                                            <Card key={item.id} className="group border border-gray-200 flex flex-col overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                                                {/* Image area */}
                                                <div
                                                    className={`relative w-full h-32 flex-shrink-0 overflow-hidden bg-gray-100 ${canView ? 'cursor-pointer' : ''}`}
                                                    onClick={() => canView && router.visit(route('product-service.items.show', item.id))}
                                                >
                                                    {item.image ? (
                                                        <>
                                                            <img
                                                                src={getImagePath(item.image)}
                                                                alt={item.name}
                                                                className="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300"
                                                                onError={(e) => {
                                                                    const target = e.target as HTMLImageElement;
                                                                    target.style.display = 'none';
                                                                    const fallback = target.nextElementSibling as HTMLElement;
                                                                    if (fallback) fallback.classList.remove('hidden');
                                                                }}
                                                            />
                                                            <div className="hidden w-full h-32 bg-gray-100 flex items-center justify-center">
                                                                <Image className="w-8 h-8 text-gray-300" />
                                                            </div>
                                                        </>
                                                    ) : (
                                                        <div className="w-full h-32 bg-gray-100 flex items-center justify-center">
                                                            <Image className="w-8 h-8 text-gray-300" />
                                                        </div>
                                                    )}

                                                    {/* Bottom gradient for readability */}
                                                    <div className="absolute inset-x-0 bottom-0 h-8 bg-gradient-to-t from-black/25 to-transparent pointer-events-none" />

                                                    {/* Type badge — top left */}
                                                    <span className={`absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold capitalize backdrop-blur-sm shadow-sm ${badgeClass}`}>
                                                        {t(item.type?.replace(/_/g, ' ') ?? '')}
                                                    </span>

                                                    {/* Quantity badge — top right */}
                                                    <span className="absolute top-1.5 right-1.5 bg-white/90 backdrop-blur-sm text-gray-700 text-[10px] font-semibold px-1.5 py-0.5 rounded-full border border-white/60 shadow-sm">
                                                        {Math.floor(item.total_quantity) || 0} {t('in stock')}
                                                    </span>
                                                </div>

                                                {/* Card body */}
                                                <div className="p-2.5 flex flex-col flex-1">
                                                    {/* Name + SKU */}
                                                    <h3 className="font-semibold text-sm text-gray-900 truncate leading-snug" title={item.name}>
                                                        {item.name}
                                                    </h3>
                                                    {item.sku && (
                                                        <p className="text-[11px] text-gray-400 mt-0.5 mb-1.5 font-mono">{item.sku}</p>
                                                    )}

                                                    {/* Description preview */}
                                                    {item.description && (
                                                        <p className="text-[11px] text-gray-500 mb-1.5 line-clamp-1 leading-relaxed">
                                                            {item.description}
                                                        </p>
                                                    )}

                                                    {/* Prices */}
                                                    {(item.sale_price || item.purchase_price) && (
                                                        <div className="flex gap-1 mb-2">
                                                            {item.sale_price ? (
                                                                <div className="flex-1 bg-emerald-50 border border-emerald-100 rounded-md px-2 py-1 min-w-0">
                                                                    <p className="text-[10px] text-emerald-600 font-medium leading-none">{t('Sale')}</p>
                                                                    <p className="text-[11px] font-bold text-emerald-800 mt-0.5 truncate">{formatCurrency(item.sale_price)}</p>
                                                                </div>
                                                            ) : null}
                                                            {item.purchase_price ? (
                                                                <div className="flex-1 bg-sky-50 border border-sky-100 rounded-md px-2 py-1 min-w-0">
                                                                    <p className="text-[10px] text-sky-600 font-medium leading-none">{t('Purchase')}</p>
                                                                    <p className="text-[11px] font-bold text-sky-800 mt-0.5 truncate">{formatCurrency(item.purchase_price)}</p>
                                                                </div>
                                                            ) : null}
                                                        </div>
                                                    )}

                                                    {/* Category & Unit tags */}
                                                    <div className="flex flex-wrap gap-1 flex-1 content-start mb-2">
                                                        {item.category && (
                                                            <span className="inline-flex items-center gap-1 text-[10px] text-gray-600 bg-gray-100 rounded px-1.5 py-0.5 truncate max-w-full" title={item.category.name}>
                                                                <span className="text-gray-400">📁</span>
                                                                {item.category.name}
                                                            </span>
                                                        )}
                                                        {item.unit_relation && (
                                                            <span className="inline-flex items-center gap-1 text-[10px] text-gray-600 bg-gray-100 rounded px-1.5 py-0.5">
                                                                <span className="text-gray-400">⚖️</span>
                                                                {item.unit_relation.unit_name}
                                                            </span>
                                                        )}
                                                    </div>

                                                    {/* Actions */}
                                                    <div className="flex items-center justify-end gap-0.5 pt-1.5 border-t border-gray-100">
                                                        <TooltipProvider>
                                                            {auth.user?.permissions?.includes('view-product-service-item') && (
                                                                <Tooltip delayDuration={300}>
                                                                    <TooltipTrigger asChild>
                                                                        <Button variant="ghost" size="sm" onClick={() => router.visit(route('product-service.items.show', item.id))} className="h-7 w-7 p-0 text-green-600 hover:text-green-700">
                                                                            <Eye className="h-3.5 w-3.5" />
                                                                        </Button>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent><p>{t('View')}</p></TooltipContent>
                                                                </Tooltip>
                                                            )}
                                                            {auth.user?.permissions?.includes('edit-product-service-item') && (
                                                                <Tooltip delayDuration={300}>
                                                                    <TooltipTrigger asChild>
                                                                        <Button variant="ghost" size="sm" onClick={() => router.visit(route('product-service.items.edit', item.id))} className="h-7 w-7 p-0 text-blue-600 hover:text-blue-700">
                                                                            <Edit className="h-3.5 w-3.5" />
                                                                        </Button>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                                                                </Tooltip>
                                                            )}
                                                            {auth.user?.permissions?.includes('delete-product-service-item') && (
                                                                <Tooltip delayDuration={300}>
                                                                    <TooltipTrigger asChild>
                                                                        <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(item.id)} className="h-7 w-7 p-0 text-destructive hover:text-destructive">
                                                                            <Trash2 className="h-3.5 w-3.5" />
                                                                        </Button>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                                                                </Tooltip>
                                                            )}
                                                        </TooltipProvider>
                                                    </div>
                                                </div>
                                            </Card>
                                        );
                                    })}
                                </div>
                            ) : (
                                <NoRecordsFound
                                    icon={Package}
                                    title={t('No items found')}
                                    description={t('Get started by creating your first item.')}
                                    hasFilters={!!(filters.name || filters.type || filters.category_id)}
                                    onClearFilters={clearFilters}
                                    createPermission="create-product-service-item"
                                    onCreateClick={() => router.visit(route('product-service.items.create'))}
                                    createButtonText={t('Create Item')}
                                    className="h-auto"
                                />
                            )}
                        </div>
                    )}
                </CardContent>

                {/* Pagination Footer */}
                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={items}
                        routeName="product-service.items.index"
                        filters={{...filters, per_page: perPage, view: viewMode}}
                    />
                </CardContent>
            </Card>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Item')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />

            </AuthenticatedLayout>
        </TooltipProvider>
    );
}
