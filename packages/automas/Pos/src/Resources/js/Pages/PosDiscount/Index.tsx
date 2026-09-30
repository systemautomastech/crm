import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { SearchInput } from '@/components/ui/search-input';
import { ListGridToggle } from '@/components/ui/list-grid-toggle';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PerPageSelector } from '@/components/ui/per-page-selector'; 
import { Pagination } from '@/components/ui/pagination';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Plus, Edit as EditIcon, Trash2, Tag, Eye } from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import { PosDiscount } from './types';
import { formatDate } from '@/utils/helpers';
import { Dialog } from '@/components/ui/dialog';
import { FilterButton } from '@/components/ui/filter-button';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import ViewDiscount from './View';

interface IndexProps {
    discounts: {
        data: PosDiscount[];
        links: any[];
        meta: any;
    };
    products: Array<{ id: number; name: string }>;
    categories: Array<{ id: number; name: string }>;
    auth: {
        user: {
            permissions: string[];
        };
    };
}

interface ModalState {
    isOpen: boolean;
    mode: string;
    data: PosDiscount | null;
}

export default function Index() {
    const { t } = useTranslation();
    const { discounts, products, categories, auth } = usePage<IndexProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState({
        search: urlParams.get('search') || '',
        discount_type: urlParams.get('discount_type') || '',
        status: urlParams.get('status') || '',
        date_range: (() => {
            const fromDate = urlParams.get('date_from');
            const toDate = urlParams.get('date_to');
            return (fromDate && toDate) ? `${fromDate} - ${toDate}` : '';
        })()
    });
    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'desc');
    const [viewMode, setViewMode] = useState<'list' | 'grid'>(urlParams.get('view') as 'list' | 'grid' || 'list');
    const [showFilters, setShowFilters] = useState(false);
    const [modalState, setModalState] = useState<ModalState>({ isOpen: false, mode: '', data: null });

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'pos.discounts.destroy',
        defaultMessage: t('Are you sure you want to delete this discount?'),
    });

    const handleFilter = () => {
        const filterParams: any = {
            search: filters.search,
            discount_type: filters.discount_type,
            status: filters.status,
            per_page: perPage,
            sort: sortField,
            direction: sortDirection,
            view: viewMode
        };

        // Convert date_range to date_from and date_to for backend
        if (filters.date_range) {
            const [fromDate, toDate] = filters.date_range.split(' - ');
            filterParams.date_from = fromDate;
            filterParams.date_to = toDate;
        }

        router.get(route('pos.discounts.index'), filterParams, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        setFilters({ search: '', discount_type: '', status: '', date_range: '' });
        router.get(route('pos.discounts.index'), { per_page: perPage, view: viewMode });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('pos.discounts.index'), { ...filters, per_page: perPage, sort: field, direction, view: viewMode }, {
            preserveState: true,
            replace: true,
        });
    };

    const openModal = (mode: 'add' | 'edit' | 'view', data: PosDiscount | null = null) => {
        if (mode === 'add') {
            router.visit(route('pos.discounts.create'));
        } else if (mode === 'edit' && data) {
            router.visit(route('pos.discounts.edit', data.id));
        } else if (mode === 'view' && data) {
            setModalState({ isOpen: true, mode: 'view', data });
        }
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const getApplyOnLabel = (discount: PosDiscount) => {
        // Check if category is set
        if (discount.category_id && discount.category) {
            return discount.category.name;
        }
        
        // Check if products are set
        if (discount.products && discount.products.length > 0) {
            if (discount.products.length === 1) {
                return discount.products[0].name;
            }
            return `${discount.products.length} Products`;
        }
        
        return t('All');
    };

    const tableColumns = [
        {
            key: 'name',
            header: t('Name'),
            sortable: true,
        },
        {
            key: 'discount_type',
            header: t('Type'),
            render: (value: string) => (
                <span className={`px-2 py-1 rounded-full text-sm ${
                    value === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
                }`}>
                    {value === 'percentage' ? t('Percentage') : t('Fixed')}
                </span>
            ),
        },
        {
            key: 'discount_value',
            header: t('Value'),
            render: (value: number, item: PosDiscount) => (
                <span>
                    {item.discount_type === 'percentage' ? `${value}%` : `₹${value}`}
                </span>
            ),
        },
        {
            key: 'apply_on',
            header: t('Apply On'),
            render: (_: any, item: PosDiscount) => (
                <span className="text-sm">{getApplyOnLabel(item)}</span>
            ),
        },
        {
            key: 'min_quantity',
            header: t('Min Qty'),
            render: (value: number) => <span>{value}</span>,
        },
        {
            key: 'start_date',
            header: t('Date Range'),
            render: (_: any, item: PosDiscount) => (
                <span className="text-sm">
                    {item.start_date && item.end_date 
                        ? `${formatDate(item.start_date)} - ${formatDate(item.end_date)}`
                        : item.start_date 
                            ? formatDate(item.start_date)
                            : '-'
                    }
                </span>
            ),
        },
        {
            key: 'is_active',
            header: t('Status'),
            render: (value: boolean) => (
                <span className={`px-2 py-1 rounded-full text-sm ${
                    value === true ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`}>
                    {value === true ? t('Active') : t('Inactive')}
                </span>
            ),
        },
        ...(auth.user?.permissions?.some((p: string) => ['view-pos-discounts', 'edit-pos-discounts', 'delete-pos-discounts'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, item: PosDiscount) => (
                <div className="flex gap-1">
                    {auth.user?.permissions?.includes('view-pos-discounts') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => openModal('view', item)}
                                    className="h-8 w-8 p-0 text-green-600 hover:text-green-700"
                                >
                                    <Eye className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('View')}</p></TooltipContent>
                        </Tooltip>
                    )}
                    {auth.user?.permissions?.includes('edit-pos-discounts') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => openModal('edit', item)}
                                    className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                >
                                    <EditIcon className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                        </Tooltip>
                    )}
                    {auth.user?.permissions?.includes('delete-pos-discounts') && (
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
                            <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                        </Tooltip>
                    )}
                </div>
            ),
        }] : []),
    ];

    return (
        <TooltipProvider>
            <AuthenticatedLayout
                breadcrumbs={[
                    { label: t('POS'), url: route('pos.index') },
                    { label: t('Discounts') },
                ]}
                pageTitle={t('Manage Discounts')}
                pageActions={
                    <div className="flex gap-2">
                        <TooltipProvider>
                            {auth.user?.permissions?.includes('create-pos-discounts') && (
                                <Tooltip delayDuration={0}>
                                    <TooltipTrigger asChild>
                                        <Button size="sm" onClick={() => openModal('add')}>
                                            <Plus className="h-4 w-4" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{t('Create')}</p>
                                    </TooltipContent>
                                </Tooltip>
                            )}
                        </TooltipProvider>
                    </div>
                }
            >
                <Head title={t('Discounts')} />

                <Card className="shadow-sm">
                    <CardContent className="p-6 border-b bg-gray-50/50">
                        <div className="flex items-center justify-between gap-4">
                            <div className="flex-1 max-w-md">
                                <SearchInput
                                    value={filters.search}
                                    onChange={(value) => setFilters({ ...filters, search: value })}
                                    onSearch={handleFilter}
                                    placeholder={t('Search by name...')}
                                />
                            </div>
                            <div className="flex items-center gap-3">
                                <ListGridToggle
                                    currentView={viewMode}
                                    routeName="pos.discounts.index"
                                    filters={{...filters, per_page: perPage}}
                                />
                                <PerPageSelector
                                    routeName="pos.discounts.index"
                                    filters={{ ...filters, view: viewMode }}
                                />
                                <div className="relative">
                                    <FilterButton
                                        showFilters={showFilters}
                                        onToggle={() => setShowFilters(!showFilters)}
                                    />
                                    {(() => {
                                        const activeFilters = [filters.discount_type, filters.status, filters.date_range].filter(Boolean).length;
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
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Discount Type')}</label>
                                    <Select value={filters.discount_type || 'all'} onValueChange={(value) => setFilters({...filters, discount_type: value === 'all' ? '' : value})}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('All Types')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">{t('All Types')}</SelectItem>
                                            <SelectItem value="percentage">{t('Percentage')}</SelectItem>
                                            <SelectItem value="fixed">{t('Fixed')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                    <Select value={filters.status || 'all'} onValueChange={(value) => setFilters({...filters, status: value === 'all' ? '' : value})}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('All Statuses')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">{t('All Statuses')}</SelectItem>
                                            <SelectItem value="active">{t('Active')}</SelectItem>
                                            <SelectItem value="inactive">{t('Inactive')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Date Range')}</label>
                                    <DateRangePicker
                                        value={filters.date_range}
                                        onChange={(value) => setFilters({...filters, date_range: value})}
                                        placeholder={t('Select date range')}
                                    />
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
                                <div className="min-w-[1000px]">
                                    <DataTable
                                        data={discounts.data}
                                        columns={tableColumns}
                                        onSort={handleSort}
                                        sortKey={sortField}
                                        sortDirection={sortDirection as 'asc' | 'desc'}
                                        className="rounded-none"
                                        emptyState={
                                            <NoRecordsFound
                                                icon={Tag}
                                                title={t('No discount found')}
                                                description={t('Get started by creating your first discount.')}
                                                hasFilters={!!(filters.search || filters.discount_type || filters.status || filters.date_range)}
                                                onClearFilters={clearFilters}
                                                createPermission="create-pos-discounts"
                                                onCreateClick={() => openModal('add')}
                                                createButtonText={t('Create Discount')}
                                                className="h-auto"
                                            />
                                        }
                                    />
                                </div>
                            </div>
                        ) : (
                            <div className="overflow-auto max-h-[70vh] p-6">
                                {discounts.data.length > 0 ? (
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                                        {discounts.data.map((discount) => (
                                            <Card key={discount.id} className="p-0 hover:shadow-lg transition-all duration-200 relative overflow-hidden flex flex-col h-full">
                                                {/* Header */}
                                                <div className="p-4 bg-gradient-to-r from-primary/5 to-transparent border-b flex-shrink-0">
                                                    
                                                    <h3 className="font-semibold text-sm text-gray-900 truncate">{discount.name}</h3>
                                                </div>

                                                {/* Body */}
                                                <div className="p-4 flex-1 space-y-3">
                                                    <div className="bg-blue-50 rounded-lg p-3 text-center">
                                                        <p className="text-xs text-gray-600 mb-1">{t('Discount Value')}</p>
                                                        <p className="text-2xl font-bold text-blue-600">
                                                            {discount.discount_type === 'percentage' ? `${discount.discount_value}%` : `₹${discount.discount_value}`}
                                                        </p>
                                                        <span className={`inline-block mt-1 px-2 py-0.5 rounded-full text-xs ${
                                                            discount.discount_type === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
                                                        }`}>
                                                            {discount.discount_type === 'percentage' ? t('Percentage') : t('Fixed')}
                                                        </span>
                                                    </div>

                                                    <div className="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <p className="text-xs text-gray-600 mb-1">{t('Min Qty')}</p>
                                                            <p className="text-sm font-medium text-gray-900">{discount.min_quantity}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-xs text-gray-600 mb-1">{t('Apply On')}</p>
                                                            <p className="text-sm font-medium text-gray-900 truncate">{getApplyOnLabel(discount)}</p>
                                                        </div>
                                                    </div>

                                                    {(discount.start_date || discount.end_date) && (
                                                        <div>
                                                            <p className="text-xs text-gray-600 mb-1">{t('Date Range')}</p>
                                                            <p className="text-xs text-gray-900">
                                                                {discount.start_date && discount.end_date 
                                                                    ? `${formatDate(discount.start_date)} - ${formatDate(discount.end_date)}`
                                                                    : discount.start_date 
                                                                        ? formatDate(discount.start_date)
                                                                        : '-'
                                                                }
                                                            </p>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Actions Footer */}
                                                <div className="flex justify-between items-center p-3 border-t bg-gray-50/50 flex-shrink-0">
                                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                        discount.is_active === true ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                    }`}>
                                                        {discount.is_active === true ? t('Active') : t('Inactive')}
                                                    </span>

                                                    <div className="flex gap-1">
                                                    <TooltipProvider>
                                                        {auth.user?.permissions?.includes('view-pos-discounts') && (
                                                            <Tooltip delayDuration={300}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => openModal('view', discount)} className="h-9 w-9 p-0 text-green-600 hover:text-green-700">
                                                                        <Eye className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('View')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                        {auth.user?.permissions?.includes('edit-pos-discounts') && (
                                                            <Tooltip delayDuration={300}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => openModal('edit', discount)} className="h-9 w-9 p-0 text-blue-600 hover:text-blue-700">
                                                                        <EditIcon className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                        {auth.user?.permissions?.includes('delete-pos-discounts') && (
                                                            <Tooltip delayDuration={300}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(discount.id)} className="h-9 w-9 p-0 text-red-600 hover:text-red-700">
                                                                        <Trash2 className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                    </TooltipProvider>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>
                                ) : (
                                    <NoRecordsFound
                                        icon={Tag}
                                        title={t('No discount found')}
                                        description={t('Get started by creating your first discount.')}
                                        hasFilters={!!(filters.search || filters.discount_type || filters.status || filters.date_range)}
                                        onClearFilters={clearFilters}
                                        createPermission="create-pos-discounts"
                                        onCreateClick={() => openModal('add')}
                                        createButtonText={t('Create Discount')}
                                    />
                                )}
                            </div>
                        )}
                    </CardContent>

                    <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                        <Pagination
                            data={discounts}
                            routeName="pos.discounts.index"
                            filters={{ ...filters, per_page: perPage, view: viewMode }}
                        />
                    </CardContent>
                </Card>

                <ConfirmationDialog
                    open={deleteState.isOpen}
                    onOpenChange={closeDeleteDialog}
                    title={t('Delete Discount')}
                    message={deleteState.message}
                    confirmText={t('Delete')}
                    onConfirm={confirmDelete}
                    variant="destructive"
                />

                <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                    {modalState.mode === 'view' && modalState.data && (
                        <ViewDiscount discount={modalState.data} />
                    )}
                </Dialog>
            </AuthenticatedLayout>
        </TooltipProvider>
    );
}
