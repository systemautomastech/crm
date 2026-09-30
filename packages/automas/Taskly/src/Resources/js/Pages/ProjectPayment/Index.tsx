import { useState, useMemo } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { SearchInput } from '@/components/ui/search-input';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { FilterButton } from '@/components/ui/filter-button';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { Plus, Receipt, Download, Eye, FileText, Edit, Trash2, User, BarChart3, Calendar, Clock, CheckCircle2 } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { ListGridToggle } from '@/components/ui/list-grid-toggle';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import NoRecordsFound from '@/components/no-records-found';
import type { ProjectPayment, ProjectPaymentFilters, PaginatedData } from './types';

interface IndexProps {
    payments: PaginatedData<ProjectPayment>;
    projects: Array<{ id: number; name: string }>;
    customers: Array<{ id: number; name: string; email: string }>;
    filters: ProjectPaymentFilters;
    stats: {
        yearly: number;
        monthly: number;
        quarterly: number;
        today: number;
    };
    auth: {
        user: {
            permissions?: string[];
        };
    };
}

export default function Index() {
    const { t } = useTranslation();
    const { payments, projects, customers, auth, stats } = usePage<IndexProps>().props;
    const urlParams = new URLSearchParams(window.location.search);
    useFlashMessages();

    const [filters, setFilters] = useState<ProjectPaymentFilters>({
        search: urlParams.get('search') || '',
        project_id: urlParams.get('project_id') || '',
        customer_id: urlParams.get('customer_id') || '',
        status: urlParams.get('status') || '',
        date_range: urlParams.get('date_range') || ''
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || 'created_at');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'desc');
    const [viewMode, setViewMode] = useState<'list' | 'grid'>(urlParams.get('view') as 'list' | 'grid' || 'grid');
    const [showFilters, setShowFilters] = useState(false);

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'project-payments.destroy',
        defaultMessage: t('Are you sure you want to delete this project payment?')
    });

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('project-payments.index'), {...filters, per_page: perPage, sort: field, direction, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const handleFilter = () => {
        router.get(route('project-payments.index'), {...filters, per_page: perPage, sort: sortField, direction: sortDirection, view: viewMode}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ search: '', project_id: '', customer_id: '', status: '', date_range: '' });
        router.get(route('project-payments.index'), {per_page: perPage, view: viewMode});
    };

    const activeFilterCount = [filters.project_id, filters.customer_id, filters.status, filters.date_range].filter(Boolean).length;

    const tableColumns = [
        {
            key: 'payment_number',
            header: t('Payment Number'),
            sortable: true,
            render: (value: string, payment: ProjectPayment) =>
                auth.user?.permissions?.includes('view-project-payments') ? (
                    <span className="text-blue-600 hover:text-blue-700 cursor-pointer font-medium" onClick={() => router.get(route('project-payments.show', payment.id))}>{value}</span>
                ) : (
                    <span className="font-medium">{value}</span>
                )
        },
        {
            key: 'payment_date',
            header: t('Payment Date'),
            sortable: true,
            render: (value: string) => formatDate(value),
        },
        {
            key: 'due_date',
            header: t('Due Date'),
            sortable: true,
            render: (value: string) => formatDate(value),
        },
        {
            key: 'project',
            header: t('Project'),
            sortable: false,
            render: (_: any, row: ProjectPayment) => row.project?.name || '-',
        },
        {
            key: 'customer',
            header: t('Customer'),
            sortable: false,
            render: (_: any, row: ProjectPayment) => row.customer?.name || '-',
        },
        {
            key: 'total_amount',
            header: t('Total'),
            sortable: true,
            render: (value: number) => formatCurrency(value),
        },
        {
            key: 'status',
            header: t('Status'),
            sortable: true,
            render: (_: any, row: ProjectPayment) => {
                const statusColors: Record<string, string> = {
                    draft: 'bg-gray-100 text-gray-800',
                    posted: 'bg-blue-100 text-blue-800',
                };
                return (
                    <span className={`px-2 py-1 rounded-full text-xs font-medium capitalize ${statusColors[row.status] || statusColors.draft}`}>
                        {t(row.status)}
                    </span>
                );
            },
        },
        {
            key: 'actions',
            header: t('Actions'),
            sortable: false,
            render: (_: any, payment: ProjectPayment) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth.user?.permissions?.includes('print-project-payments') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => window.open(route('project-payments.print', payment.id) + '?download=pdf', '_blank')}
                                        className="h-8 w-8 p-0 text-orange-600 hover:text-orange-700"
                                    >
                                        <Download className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Download PDF')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('view-project-payments') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => router.get(route('project-payments.show', payment.id))}
                                        className="h-8 w-8 p-0 text-green-600 hover:text-green-700"
                                    >
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {payment.status === 'draft' && (
                            <>
                                {auth.user?.permissions?.includes('post-project-payments') && (
                                    <Tooltip delayDuration={0}>
                                        <TooltipTrigger asChild>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => router.post(route('project-payments.post', payment.id))}
                                                className="h-8 w-8 p-0 text-purple-600 hover:text-purple-700"
                                            >
                                                <FileText className="h-4 w-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{t('Post payment to finalize')}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                )}
                                {auth.user?.permissions?.includes('edit-project-payments') && (
                                    <Tooltip delayDuration={0}>
                                        <TooltipTrigger asChild>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => router.visit(route('project-payments.edit', payment.id))}
                                                className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                            >
                                                <Edit className="h-4 w-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{t('Edit')}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                )}
                                {auth.user?.permissions?.includes('delete-project-payments') && (
                                    <Tooltip delayDuration={0}>
                                        <TooltipTrigger asChild>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => openDeleteDialog(payment.id)}
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
                            </>
                        )}
                    </TooltipProvider>
                </div>
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Project'), url: route('project.dashboard.index') },
                { label: t('Project Payments') },
            ]}
            pageTitle={t('Manage Project Payments')}
            pageActions={
                auth.user?.permissions?.includes('create-project-payments') ? (
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={() => router.get(route('project-payments.create'))}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Create')}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                ) : null
            }
        >
            <Head title={t('Project Payments')} />

            <div className="space-y-5">
                {/* Summary Cards */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <Card className="border shadow-sm">
                        <CardContent className="p-3.5 flex items-center gap-3">
                            <div className="p-2.5 bg-blue-50 rounded-xl">
                                <Calendar className="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium">{t('Yearly Payment')}</p>
                                <p className="text-sm font-bold text-gray-800">{formatCurrency(stats.yearly)}</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border shadow-sm">
                        <CardContent className="p-3.5 flex items-center gap-3">
                            <div className="p-2.5 bg-emerald-50 rounded-xl">
                                <BarChart3 className="h-5 w-5 text-emerald-600" />
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium">{t('Quarterly Payment')}</p>
                                <p className="text-sm font-bold text-gray-800">{formatCurrency(stats.quarterly)}</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border shadow-sm">
                        <CardContent className="p-3.5 flex items-center gap-3">
                            <div className="p-2.5 bg-amber-50 rounded-xl">
                                <Receipt className="h-5 w-5 text-amber-600" />
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium">{t('Monthly Payment')}</p>
                                <p className="text-sm font-bold text-gray-800">{formatCurrency(stats.monthly)}</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border shadow-sm">
                        <CardContent className="p-3.5 flex items-center gap-3">
                            <div className="p-2.5 bg-purple-50 rounded-xl">
                                <Clock className="h-5 w-5 text-purple-600" />
                            </div>
                            <div>
                                <p className="text-xs text-gray-500 font-medium">{t("Today's Payment")}</p>
                                <p className="text-sm font-bold text-gray-800">{formatCurrency(stats.today)}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.search || ''}
                                onChange={(value) => setFilters({...filters, search: value})}
                                onSearch={handleFilter}
                                placeholder={t('Search by payment number...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <ListGridToggle
                                currentView={viewMode}
                                routeName="project-payments.index"
                                filters={{...filters, per_page: perPage}}
                            />
                            <PerPageSelector
                                routeName="project-payments.index"
                                filters={{...filters, view: viewMode}}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {activeFilterCount > 0 && (
                                    <span className="absolute -top-2 -right-2 bg-primary text-primary-foreground text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                        {activeFilterCount}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </CardContent>

                {showFilters && (
                    <CardContent className="p-6 bg-blue-50/30 border-b">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Project')}</label>
                                <Select value={filters.project_id} onValueChange={(value) => setFilters({ ...filters, project_id: value })}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by project')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {projects.map((project) => (
                                            <SelectItem key={project.id} value={project.id.toString()}>
                                                {project.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            {auth.user?.permissions?.includes('manage-users') && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Customer')}</label>
                                    <Select value={filters.customer_id} onValueChange={(value) => setFilters({ ...filters, customer_id: value })}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Filter by customer')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {customers.map((customer) => (
                                                <SelectItem key={customer.id} value={customer.id.toString()}>
                                                    {customer.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                             )}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                <Select value={filters.status} onValueChange={(value) => setFilters({ ...filters, status: value })}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by status')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">{t('Draft')}</SelectItem>
                                        <SelectItem value="posted">{t('Posted')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Date Range')}</label>
                                <DateRangePicker
                                    value={filters.date_range}
                                    onChange={(value) => setFilters({ ...filters, date_range: value })}
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
                            <div className="min-w-[800px]">
                                <DataTable
                                    data={payments.data}
                                    columns={tableColumns}
                                    onSort={handleSort}
                                    sortKey={sortField}
                                    sortDirection={sortDirection}
                                    className="rounded-none"
                                    emptyState={
                                        <NoRecordsFound
                                            icon={Receipt}
                                            title={t('No project payments found')}
                                            description={t('Get started by creating your first project payment.')}
                                            hasFilters={!!(filters.search || filters.project_id || filters.customer_id || filters.status || filters.date_range)}
                                            onClearFilters={clearFilters}
                                            createPermission="create-project-payments"
                                            onCreateClick={() => router.get(route('project-payments.create'))}
                                            createButtonText={t('Create Project Payment')}
                                            className="h-auto"
                                        />
                                    }
                                />
                            </div>
                        </div>
                    ) : (
                        <div className="overflow-auto max-h-[70vh] p-4">
                            {payments.data.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                                    {payments.data.map((payment) => (
                                        <Card key={payment.id} className="border border-gray-200 flex flex-col">
                                            <div className="p-4 flex-1">
                                                <div className="flex items-center justify-between mb-3">
                                                    {auth.user?.permissions?.includes('view-project-payments') ? (
                                                        <h3 className="font-semibold text-base text-blue-600 hover:text-blue-700 cursor-pointer" onClick={() => router.get(route('project-payments.show', payment.id))}>{payment.payment_number}</h3>
                                                    ) : (
                                                        <h3 className="font-semibold text-base text-gray-900">{payment.payment_number}</h3>
                                                    )}
                                                    <span className={`px-2 py-1 rounded-full text-xs font-medium capitalize ${payment.status === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'}`}>
                                                        {t(payment.status)}
                                                    </span>
                                                </div>

                                                <div className="space-y-3 mb-0">
                                                    <div>
                                                        <p className="text-xs font-medium text-gray-600 mb-1">{t('Project')}</p>
                                                        <p className="text-sm text-gray-900 truncate font-medium">{payment.project?.name}</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-xs font-medium text-gray-600 mb-1">{t('Customer')}</p>
                                                        <p className="text-sm text-gray-900 truncate font-medium">{payment.customer?.name}</p>
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-3">
                                                        <div>
                                                            <p className="text-xs font-medium text-gray-600 mb-1">{t('Payment Date')}</p>
                                                            <p className="text-xs text-gray-900">{formatDate(payment.payment_date)}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-xs font-medium text-gray-600 mb-1">{t('Due Date')}</p>
                                                            <p className="text-xs text-gray-900">{formatDate(payment.due_date)}</p>
                                                        </div>
                                                    </div>

                                                    <div className="bg-gray-50 rounded-lg p-3">
                                                        <div className="grid grid-cols-2 gap-2 text-xs">
                                                            <div className="flex justify-between">
                                                                <span className="text-gray-600">{t('Subtotal')}:</span>
                                                                <span className="font-medium">{formatCurrency(payment.subtotal)}</span>
                                                            </div>
                                                            <div className="flex justify-between">
                                                                <span className="text-gray-600">{t('Discount')}:</span>
                                                                <span className="font-medium">{formatCurrency(payment.discount_amount)}</span>
                                                            </div>
                                                        </div>
                                                        <div className="border-t mt-2 pt-2">
                                                            <div className="flex justify-between items-center">
                                                                <span className="text-sm font-semibold text-gray-900">{t('Total Amount')}</span>
                                                                <span className="text-lg font-bold text-gray-900">{formatCurrency(payment.total_amount)}</span>
                                                            </div>
                                                            <div className="flex justify-between items-center mt-1">
                                                                <span className="text-xs text-gray-600">{t('Balance Due')}</span>
                                                                <span className="text-sm font-semibold text-blue-600">{formatCurrency(payment.balance_amount)}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center justify-between p-3 border-t bg-gray-50/50 mt-0">
                                                <div className="flex gap-1">
                                                    <TooltipProvider>
                                                        {auth.user?.permissions?.includes('print-project-payments') && (
                                                            <Tooltip delayDuration={0}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => window.open(route('project-payments.print', payment.id) + '?download=pdf', '_blank')} className="h-8 w-8 p-0 text-orange-600 hover:text-orange-700">
                                                                        <Download className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('Download PDF')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                        {auth.user?.permissions?.includes('view-project-payments') && (
                                                            <Tooltip delayDuration={0}>
                                                                <TooltipTrigger asChild>
                                                                    <Button variant="ghost" size="sm" onClick={() => router.get(route('project-payments.show', payment.id))} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                                                        <Eye className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent><p>{t('View')}</p></TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                    </TooltipProvider>
                                                </div>
                                                <div className="flex gap-1">
                                                    <TooltipProvider>
                                                        {payment.status === 'draft' && (
                                                            <>
                                                                {auth.user?.permissions?.includes('post-project-payments') && (
                                                                    <Tooltip delayDuration={0}>
                                                                        <TooltipTrigger asChild>
                                                                            <Button variant="ghost" size="sm" onClick={() => router.post(route('project-payments.post', payment.id))} className="h-8 w-8 p-0 text-purple-600 hover:text-purple-700">
                                                                                <FileText className="h-4 w-4" />
                                                                            </Button>
                                                                        </TooltipTrigger>
                                                                        <TooltipContent><p>{t('Post payment to finalize')}</p></TooltipContent>
                                                                    </Tooltip>
                                                                )}
                                                                {auth.user?.permissions?.includes('edit-project-payments') && (
                                                                    <Tooltip delayDuration={0}>
                                                                        <TooltipTrigger asChild>
                                                                            <Button variant="ghost" size="sm" onClick={() => router.visit(route('project-payments.edit', payment.id))} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                                                                <Edit className="h-4 w-4" />
                                                                            </Button>
                                                                        </TooltipTrigger>
                                                                        <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                                                                    </Tooltip>
                                                                )}
                                                                {auth.user?.permissions?.includes('delete-project-payments') && (
                                                                    <Tooltip delayDuration={0}>
                                                                        <TooltipTrigger asChild>
                                                                            <Button variant="ghost" size="sm" onClick={() => openDeleteDialog(payment.id)} className="h-8 w-8 p-0 text-destructive hover:text-destructive">
                                                                                <Trash2 className="h-4 w-4" />
                                                                            </Button>
                                                                        </TooltipTrigger>
                                                                        <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                                                                    </Tooltip>
                                                                )}
                                                            </>
                                                        )}
                                                    </TooltipProvider>
                                                </div>
                                            </div>
                                        </Card>
                                    ))}
                                </div>
                            ) : (
                                <NoRecordsFound
                                    icon={Receipt}
                                    title={t('No project payments found')}
                                    description={t('Get started by creating your first project payment.')}
                                    hasFilters={!!(filters.search || filters.project_id || filters.customer_id || filters.status || filters.date_range)}
                                    onClearFilters={clearFilters}
                                    createPermission="create-project-payments"
                                    onCreateClick={() => router.get(route('project-payments.create'))}
                                    createButtonText={t('Create Project Payment')}
                                />
                            )}
                        </div>
                    )}
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={{...payments, ...payments.meta}}
                        routeName="project-payments.index"
                        filters={{ ...filters, per_page: perPage, view: viewMode }}
                    />
                </CardContent>
            </Card>
            </div>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Project Payment')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}
