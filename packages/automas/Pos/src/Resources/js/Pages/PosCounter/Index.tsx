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
import { Dialog } from '@/components/ui/dialog';
import { SearchInput } from '@/components/ui/search-input';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from '@/components/ui/pagination';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Plus, Edit, Trash2, Monitor, Eye } from 'lucide-react';
import NoRecordsFound from '@/components/no-records-found';
import Create from './Create';
import EditCounter from './Edit';
import ViewCounter from './View';

interface PosCounter {
    id: number;
    name: string;
    code: string;
    status: boolean;
    description?: string;
    created_at: string;
}

interface IndexProps {
    counters: {
        data: PosCounter[];
        links: any[];
        meta: any;
    };
    auth: {
        user: {
            permissions: string[];
        };
    };
}

interface ModalState {
    isOpen: boolean;
    mode: string;
    data: PosCounter | null;
}

export default function Index() {
    const { t } = useTranslation();
    const { counters, auth } = usePage<IndexProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState({
        search: urlParams.get('search') || '',
        status: urlParams.get('status') || '',
    });
    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'desc');
    const [showFilters, setShowFilters] = useState(false);
    const [modalState, setModalState] = useState<ModalState>({ isOpen: false, mode: '', data: null });

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'pos.billing-counters.destroy',
        defaultMessage: t('Are you sure you want to delete this POS Counter?'),
    });

    const handleFilter = () => {
        router.get(route('pos.billing-counters'), { ...filters, per_page: perPage, sort: sortField, direction: sortDirection }, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        setFilters({ search: '', status: '' });
        router.get(route('pos.billing-counters'), { per_page: perPage });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('pos.billing-counters'), { ...filters, per_page: perPage, sort: field, direction }, {
            preserveState: true,
            replace: true,
        });
    };

    const openModal = (mode: 'add' | 'edit' | 'view', data: PosCounter | null = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const tableColumns = [
        {
            key: 'name',
            header: t('Counter Name'),
            sortable: true,
        },
        {
            key: 'code',
            header: t('Counter Code'),
            sortable: true,
            render: (value: string) => (value),
        },
        {
            key: 'status',
            header: t('Status'),
            sortable: true,
            render: (value: boolean) => (
                <span className={`px-2 py-1 rounded-full text-sm ${
                    value === true ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }`}>
                    {value === true ? t('Active') : t('Inactive')}
                </span>
            ),
        },
        ...(auth.user?.permissions?.some((p: string) => ['view-pos-billing-counters', 'edit-pos-billing-counters', 'delete-pos-billing-counters'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, item: PosCounter) => (
                <div className="flex gap-1">
                    {auth.user?.permissions?.includes('view-pos-billing-counters') && (
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
                    {auth.user?.permissions?.includes('edit-pos-billing-counters') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => openModal('edit', item)}
                                    className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                >
                                    <Edit className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                        </Tooltip>
                    )}
                    {auth.user?.permissions?.includes('delete-pos-billing-counters') && (
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
                    { label: t('POS Billing Counters') },
                ]}
                pageTitle={t('Manage POS Billing Counters')}
                pageActions={
                    <div className="flex gap-2">
                        <TooltipProvider>
                            {auth.user?.permissions?.includes('create-pos-billing-counters') && (
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
                <Head title={t('POS Billing Counters')} />

                <Card className="shadow-sm">
                    <CardContent className="p-6 border-b bg-gray-50/50">
                        <div className="flex items-center justify-between gap-4">
                            <div className="flex-1 max-w-md">
                                <SearchInput
                                    value={filters.search}
                                    onChange={(value) => setFilters({ ...filters, search: value })}
                                    onSearch={handleFilter}
                                    placeholder={t('Search by name or code...')}
                                />
                            </div>
                            <div className="flex items-center gap-3">
                                <PerPageSelector
                                    routeName="pos.billing-counters"
                                    filters={{ ...filters }}
                                />
                                <div className="relative">
                                    <FilterButton
                                        showFilters={showFilters}
                                        onToggle={() => setShowFilters(!showFilters)}
                                    />
                                    {filters.status && (
                                        <span className="absolute -top-2 -right-2 bg-primary text-primary-foreground text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                            1
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    {showFilters && (
                        <CardContent className="p-6 bg-blue-50/30 border-b">
                            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                    <Select
                                        value={filters.status || 'all'}
                                        onValueChange={(value) => setFilters({ ...filters, status: value === 'all' ? '' : value })}
                                    >
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
                                <div className="flex items-end gap-2">
                                    <Button onClick={handleFilter} size="sm">{t('Apply')}</Button>
                                    <Button variant="outline" onClick={clearFilters} size="sm">{t('Clear')}</Button>
                                </div>
                            </div>
                        </CardContent>
                    )}

                    <CardContent className="p-0">
                        <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[70vh] rounded-none w-full">
                            <div className="min-w-[700px]">
                                <DataTable
                                    data={counters.data}
                                    columns={tableColumns}
                                    onSort={handleSort}
                                    sortKey={sortField}
                                    sortDirection={sortDirection as 'asc' | 'desc'}
                                    className="rounded-none"
                                    emptyState={
                                        <NoRecordsFound
                                            icon={Monitor}
                                            title={t('No POS Billing Counters found')}
                                            description={t('Get started by creating your first POS Billing Counter.')}
                                            hasFilters={!!(filters.search || filters.status)}
                                            onClearFilters={clearFilters}
                                            createPermission="create-pos-billing-counters"
                                            onCreateClick={() => openModal('add')}
                                            createButtonText={t('Create Billing Counter')}
                                            className="h-auto"
                                        />
                                    }
                                />
                            </div>
                        </div>
                    </CardContent>

                    <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                        <Pagination
                            data={counters}
                            routeName="pos.billing-counters"
                            filters={{ ...filters, per_page: perPage }}
                        />
                    </CardContent>
                </Card>

                <ConfirmationDialog
                    open={deleteState.isOpen}
                    onOpenChange={closeDeleteDialog}
                    title={t('Delete POS Billing Counter')}
                    message={deleteState.message}
                    confirmText={t('Delete')}
                    onConfirm={confirmDelete}
                    variant="destructive"
                />

                <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                    {modalState.mode === 'add' && (
                        <Create onSuccess={closeModal} />
                    )}
                    {modalState.mode === 'edit' && modalState.data && (
                        <EditCounter counter={modalState.data} onSuccess={closeModal} />
                    )}
                    {modalState.mode === 'view' && modalState.data && (
                        <ViewCounter counter={modalState.data} />
                    )}
                </Dialog>
            </AuthenticatedLayout>
        </TooltipProvider>
    );
}
