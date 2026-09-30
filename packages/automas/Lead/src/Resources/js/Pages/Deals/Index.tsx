import { useState, useMemo } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Plus, Edit as EditIcon, Trash2, Eye, DollarSign as DollarSignIcon, Download, FileImage, Tag, MoreVertical, Calendar, Kanban, List, ShoppingCart, Globe, CheckSquare, Users as UsersIcon, Phone } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";

import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import KanbanBoard from '@/components/kanban-board';
import Create from './Create';
import EditDeal from './Edit';
import View from './View';
import LabelView from './LabelView';
import NoRecordsFound from '@/components/no-records-found';
import { Deal, DealsIndexProps, DealFilters, DealModalState } from './types';
import { formatDate, formatTime, formatDateTime, formatCurrency, getImagePath, formatTimeFromDate } from '@/utils/helpers';
import { usePageButtons } from '@/hooks/usePageButtons';


export default function Index() {
    const { t } = useTranslation();
    const { deals, auth, pipelines, stages, groups, users, sources, products, permissions, pbxModuleActive } = usePage<DealsIndexProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState<DealFilters>({
        name: urlParams.get('name') || '',
        phone: urlParams.get('phone') || '',
        notes: urlParams.get('notes') || '',
        pipeline_id: urlParams.get('pipeline_id') || '',
        stage_id: urlParams.get('stage_id') || '',
        status: urlParams.get('status') || '',
        is_active: urlParams.get('is_active') || '',

    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [viewMode, setViewMode] = useState<'list' | 'kanban'>(urlParams.get('view') as 'list' | 'kanban' || 'list');
    const [modalState, setModalState] = useState<DealModalState>({
        isOpen: false,
        mode: '',
        data: null
    });
    const [viewingItem, setViewingItem] = useState<Deal | null>(null);
    const [labelingItem, setLabelingItem] = useState<Deal | null>(null);
    const [callItem, setCallItem] = useState<Deal | null>(null);

    const [topPipeline, setTopPipeline] = useState<string>('all');
    const [showFilters, setShowFilters] = useState(false);

    const filterStages = useMemo(() => {
        if (!filters.pipeline_id) return [];
        return stages?.filter((item: any) => item.pipeline_id?.toString() === filters.pipeline_id) || [];
    }, [stages, filters.pipeline_id]);



    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'lead.deals.destroy',
        defaultMessage: t('Are you sure you want to delete this deal?')
    });

    const handleFilter = () => {
        router.get(route('lead.deals.index'), { ...filters, per_page: perPage, sort: sortField, direction: sortDirection, view: viewMode }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('lead.deals.index'), { ...filters, per_page: perPage, sort: field, direction, view: viewMode }, {
            preserveState: true,
            replace: true
        });
    };
    const googleDriveButtons = usePageButtons('googleDriveBtn', { module: 'Deal', settingKey: 'GoogleDrive Deal' });
    const oneDriveButtons = usePageButtons('oneDriveBtn', { module: 'Deal', settingKey: 'OneDrive Deal' });
    const dropboxButtons = usePageButtons('dropboxBtn', { module: 'Deal', settingKey: 'Dropbox Deal' });
    const boxButtons = usePageButtons('boxBtn', { module: 'Deal', settingKey: 'Box Deal' });
    const hubspotButtons = usePageButtons('hubspotBtn', { module: 'Deal', settingKey: 'HubSpot Deal' });

    const clearFilters = () => {
        setFilters({
            name: '',
            phone: '',
            notes: '',
            pipeline_id: '',
            stage_id: '',
            status: '',
            is_active: '',

        });
        router.get(route('lead.deals.index'), { per_page: perPage, view: viewMode });
    };


    const openModal = (mode: 'add' | 'edit', data: Deal | null = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const stageColors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#f97316', '#84cc16', '#ec4899', '#6366f1'];

    const getStageColor = (stageId: number | string) => {
        const index = stages?.findIndex(s => s.id.toString() === stageId?.toString()) ?? -1;
        return index >= 0 ? stageColors[index % stageColors.length] : '#6b7280';
    };

    const handleMove = (dealId: number, fromStage: string, toStage: string) => {
        router.post(route('lead.deals.order'), {
            deal_id: dealId,
            stage_id: toStage,
            order: [dealId]
        }, {
            preserveState: true,
            onSuccess: () => {
                router.reload({ only: ['deals'] });
            }
        });
    };

    const handleDialerCall = (deal: Deal) => {
        let phone = deal.phone?.toString().trim();

        if (phone) {
            phone = phone.replace(/\D/g, '');

            if (phone.length === 10 && phone.startsWith('1')) {
                phone = `0${phone}`;
            }
        }

        if (!phone) {
            window.alert(t('No phone number available for this deal.'));
            return;
        }

        if (!pbxModuleActive) {
            window.alert(t('PBX is not available for this workspace.'));
            return;
        }

        const dialerCaller = (window as Window & typeof globalThis & {
            CTI_PHONE_CALL?: (
                number: string,
                context?: {
                    module?: string;
                    record_id?: number | string;
                }
            ) => void;
        }).CTI_PHONE_CALL;

        if (typeof dialerCaller === 'function') {
            dialerCaller(phone, {
                module: 'Deal',
                record_id: deal.id,
            });
            return;
        }

        window.dispatchEvent(
            new CustomEvent('cti:make-call', {
                detail: { number: phone },
            })
        );
    };

    const getKanbanData = () => {
        const colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#f97316', '#84cc16', '#ec4899', '#6366f1'];

        const filteredStages = filters.pipeline_id && filters.pipeline_id !== ''
            ? stages?.filter(stage => stage.pipeline_id?.toString() === filters.pipeline_id) || []
            : stages || [];

        const columns = filteredStages.map((stage, index) => ({
            id: stage.id.toString(),
            title: stage.name,
            color: colors[index % colors.length]
        }));

        const tasksByStage = {};
        columns.forEach(col => {
            tasksByStage[col.id] = [];
        });

        const filteredDeals = deals?.data?.filter(deal => {
            let isValid = true;



            if (filters.pipeline_id && filters.pipeline_id !== '') {
                isValid = isValid && deal.pipeline_id?.toString() === filters.pipeline_id;
            }

            return isValid;
        }) || [];

        filteredDeals.forEach(deal => {
            const stageId = deal.stage_id?.toString();
            if (stageId && tasksByStage[stageId]) {
                tasksByStage[stageId].push({
                    id: deal.id,
                    title: deal.name,
                    description: deal.phone,
                    status: stageId,
                    due_date: null,
                    assigned_to: deal.user_deals || [],
                    priority: null,
                    deal: deal
                });
            }
        });

        return { columns, tasks: tasksByStage };
    };

    const DealCard = ({ task }: { task: any }) => {
        const deal = task.deal;

        const handleDragStart = (e: React.DragEvent) => {
            e.dataTransfer.setData('application/json', JSON.stringify({ taskId: task.id, fromStatus: task.status }));
            e.dataTransfer.effectAllowed = 'move';
        };

        return (
            <div
                className="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-2 hover:shadow-md transition-all cursor-move select-none group"
                draggable={true}
                onDragStart={handleDragStart}
            >
                <div className="flex items-start justify-between mb-2">
                    <h4
                        className="font-medium text-sm text-gray-900 leading-tight pr-2 cursor-pointer hover:text-primary hover:underline"
                        onClick={(e) => {
                            e.stopPropagation();
                            router.get(route('lead.deals.show', deal.id));
                        }}
                    >
                        {task.title}
                    </h4>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0 opacity-0 group-hover:opacity-100">
                                <MoreVertical className="h-3 w-3" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            {auth.user?.permissions?.includes('view-deals') && (
                                <DropdownMenuItem onClick={() => router.get(route('lead.deals.show', deal.id))}>
                                    <Eye className="h-3 w-3 mr-2" />
                                    {t('View')}
                                </DropdownMenuItem>
                            )}
                            {auth.user?.permissions?.includes('edit-deals') && (
                                <DropdownMenuItem onClick={() => openModal('edit', deal)}>
                                    <EditIcon className="h-3 w-3 mr-2" />
                                    {t('Edit')}
                                </DropdownMenuItem>
                            )}
                            {auth.user?.permissions?.includes('delete-deals') && (
                                <DropdownMenuItem onClick={() => openDeleteDialog(deal.id)} className="text-red-600 hover:!text-red-600 focus:text-red-600">
                                    <Trash2 className="h-3 w-3 mr-2" />
                                    {t('Delete')}
                                </DropdownMenuItem>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>



                <div className="flex items-center justify-between mb-3">
                    <Tooltip>
                        <TooltipTrigger>
                            <div className={`flex items-center space-x-1 text-sm font-medium px-2 py-1 rounded ${deal.tasks_count > 0 && deal.complete_tasks_count === deal.tasks_count ? 'text-green-600 bg-green-50' : 'text-gray-600 bg-gray-50'
                                }`}>
                                <CheckSquare className="h-3 w-3" />
                                <span>{deal.complete_tasks_count || 0}/{deal.tasks_count || 0}</span>
                            </div>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{t('Tasks')}</p>
                        </TooltipContent>
                    </Tooltip>

                    <div className="flex items-center gap-1">
                        <Tooltip>
                            <TooltipTrigger>
                                <div className="flex items-center space-x-1 text-xs text-blue-600 font-medium bg-blue-50 px-2 py-1 rounded">
                                    <ShoppingCart className="h-3 w-3" />
                                    <span>{deal.products ? (Array.isArray(deal.products) ? deal.products.length : deal.products.split(',').filter(Boolean).length) : 0}</span>
                                </div>
                            </TooltipTrigger>
                            <TooltipContent>
                                <div>
                                    <p className="font-medium">{t('Products')}</p>
                                    {deal.products && (Array.isArray(deal.products) ? deal.products : deal.products.split(',')).filter(Boolean).length > 0 ? (
                                        (Array.isArray(deal.products) ? deal.products : deal.products.split(',')).filter(Boolean).map((productId: string, index: number) => {
                                            const product = products?.find((p: any) => p.id.toString() === String(productId).trim());
                                            return <p key={index} className="text-sm">{product?.name || `Product ${productId}`}</p>;
                                        })
                                    ) : ''}
                                </div>
                            </TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger>
                                <div className="flex items-center space-x-1 text-xs text-purple-600 font-medium bg-purple-50 px-2 py-1 rounded">
                                    <Globe className="h-3 w-3" />
                                    <span>{deal.sources ? (Array.isArray(deal.sources) ? deal.sources.length : deal.sources.split(',').filter(Boolean).length) : 0}</span>
                                </div>
                            </TooltipTrigger>
                            <TooltipContent>
                                <div>
                                    <p className="font-medium">{t('Sources')}</p>
                                    {deal.sources && (Array.isArray(deal.sources) ? deal.sources : deal.sources.split(',')).filter(Boolean).length > 0 ? (
                                        (Array.isArray(deal.sources) ? deal.sources : deal.sources.split(',')).filter(Boolean).map((sourceId: string, index: number) => {
                                            const source = sources?.find((s: any) => s.id.toString() === String(sourceId).trim());
                                            return <p key={index} className="text-sm">{source?.name || `Source ${sourceId}`}</p>;
                                        })
                                    ) : ''}
                                </div>
                            </TooltipContent>
                        </Tooltip>


                    </div>
                </div>

                <div className="flex items-center justify-between">
                    <div className="flex -space-x-2">
                        <TooltipProvider>
                            {(() => {
                                if (!deal.users || deal.users.length === 0) {
                                    return (
                                        <div className="h-8 w-8 rounded-full bg-gray-200 border-2 border-background flex items-center justify-center text-sm font-medium">
                                            -
                                        </div>
                                    );
                                }

                                return deal.users.slice(0, 3).map((user: any, index: number) => (
                                    <Tooltip key={user.id}>
                                        <TooltipTrigger>
                                            <div className="h-8 w-8 rounded-full border-2 border-background overflow-hidden">
                                                {user.avatar ? (
                                                    <img
                                                        src={getImagePath(user.avatar)}
                                                        alt={user.name}
                                                        className="h-full w-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="h-full w-full bg-primary/10 flex items-center justify-center text-sm font-medium">
                                                        {user.name.charAt(0).toUpperCase()}
                                                    </div>
                                                )}
                                            </div>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{user.name}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                ));
                            })()}
                            {deal.users?.length > 3 && (
                                <Tooltip>
                                    <TooltipTrigger>
                                        <div className="h-8 w-8 rounded-full bg-gray-100 border-2 border-background flex items-center justify-center text-xs font-medium">
                                            +{deal.users.length - 3}
                                        </div>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <div className="space-y-1">
                                            {deal.users.slice(3).map((user: any, index: number) => (
                                                <p key={user.id}>{user.name}</p>
                                            ))}
                                        </div>
                                    </TooltipContent>
                                </Tooltip>
                            )}
                        </TooltipProvider>
                    </div>

                    {deal.price && (
                        <div className="flex items-center space-x-1 text-xs text-green-600 font-medium">
                            <span>{formatCurrency(deal.price)}</span>
                        </div>
                    )}
                </div>
            </div>
        );
    };

    const tableColumns = [
        {
            key: 'name',
            header: t('Name'),
            sortable: true
        },
        {
            key: 'phone',
            header: t('Phone'),
            sortable: false,
        },
        {
            key: 'price',
            header: t('Price'),
            sortable: true,
            render: (value: number) => value ? formatCurrency(value) : '-'
        },
        // {
        //     key: 'tasks',
        //     header: t('Tasks'),
        //     sortable: false,
        //     render: (value: any, row: any) => {
        //         const totalTasks = row.tasks_count || 0;
        //         const completedTasks = row.complete_tasks_count || 0;
        //         return (
        //             <span className={`text-sm font-medium ${totalTasks === 0 ? 'text-gray-400' :
        //                 completedTasks === totalTasks ? 'text-green-600' : ''
        //                 }`}>
        //                 {completedTasks}/{totalTasks}
        //             </span>
        //         );
        //     }
        // },
        // {
        //     key: 'clients',
        //     header: t('Clients'),
        //     sortable: false,
        //     render: (_: any, row: any) => (
        //         <div className="flex items-center">
        //             <TooltipProvider>
        //                 <div className="flex -space-x-1">
        //                     {row.client_deals?.length > 0 ? row.client_deals.slice(0, 3).map((cd: any) => (
        //                         <Tooltip key={cd.client_id} delayDuration={0}>
        //                             <TooltipTrigger>
        //                                 <Avatar className="h-8 w-8 border-2 border-white">
        //                                     {cd.client?.avatar ? (
        //                                         <img
        //                                             src={getImagePath(cd.client.avatar)}
        //                                             alt={cd.client.name}
        //                                             className="h-full w-full object-cover"
        //                                         />
        //                                     ) : (
        //                                         <AvatarFallback className="text-xs bg-primary/10">
        //                                             {cd.client?.name?.charAt(0).toUpperCase()}
        //                                         </AvatarFallback>
        //                                     )}
        //                                 </Avatar>
        //                             </TooltipTrigger>
        //                             <TooltipContent><p>{cd.client?.name}</p></TooltipContent>
        //                         </Tooltip>
        //                     )) : (
        //                         <Avatar className="h-8 w-8 border-2 border-white">
        //                             <AvatarFallback className="text-xs bg-gray-200">
        //                                 <UsersIcon className="h-3 w-3" />
        //                             </AvatarFallback>
        //                         </Avatar>
        //                     )}
        //                     {row.client_deals?.length > 3 && (
        //                         <Tooltip delayDuration={0}>
        //                             <TooltipTrigger>
        //                                 <Avatar className="h-8 w-8 border-2 border-white">
        //                                     <AvatarFallback className="text-xs bg-gray-100">
        //                                         +{row.client_deals.length - 3}
        //                                     </AvatarFallback>
        //                                 </Avatar>
        //                             </TooltipTrigger>
        //                             <TooltipContent>
        //                                 <div className="space-y-1">
        //                                     {row.client_deals.slice(3).map((cd: any) => (
        //                                         <p key={cd.client_id}>{cd.client?.name}</p>
        //                                     ))}
        //                                 </div>
        //                             </TooltipContent>
        //                         </Tooltip>
        //                     )}
        //                 </div>
        //             </TooltipProvider>
        //         </div>
        //     )
        // },
        {
            key: 'stage',
            header: t('Stage'),
            sortable: false,
            render: (value: any, row: any) => {
                const stageName = row.stage?.name || stages?.find(item => item.id.toString() === row.stage_id?.toString())?.name;
                const color = getStageColor(row.stage_id);
                return (
                    <span className="inline-block max-w-[120px] leading-snug break-words px-2 py-1 text-xs font-medium rounded-md" style={{ backgroundColor: `${color}20`, color }}>
                        {stageName || 'No Stage'}
                    </span>
                );
            }
        },
        {
            key: 'status',
            header: t('Status'),
            sortable: false,
            render: (value: string) => (
                <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${value === 'Won' ? 'bg-green-100 text-green-700' :
                    value === 'Loss' ? 'bg-red-100 text-red-700' :
                        'bg-blue-100 text-blue-700'
                    }`}>
                    {value || '-'}
                </span>
            )
        },
        {
            key: 'created_at',
            header: t('Created'),
            sortable: false,
            render: (value: string) => (
                <>
                    <div className="whitespace-nowrap text-sm">
                        <div className="text-foreground">
                            {formatDate(value)}
                        </div>

                        <div className="text-xs text-muted-foreground">
                            {formatTimeFromDate(value)}
                        </div>
                    </div>
                </>
            )
        },
        {
            key: 'note',
            header: t('Note'),
            sortable: false,
            render: (value: any, row: any) => {
                const note = row.notes
                    ? row.notes
                        .replace(/<[^>]*>/g, ' ')
                        .replace(/&nbsp;/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim()
                    : '';

                const shortNote = note
                    ? note.split(' ').slice(0, 12).join(' ') + (note.split(' ').length > 12 ? '...' : '')
                    : '';

                return (
                    <span
                        className="block max-w-[250px] text-xs text-gray-600 line-clamp-2"
                        title={note}
                    >
                        {shortNote || '-'}
                    </span>
                );
            }
        },
        ...(auth.user?.permissions?.some((p: string) => ['view-deals', 'edit-deals', 'delete-deals', 'use dialer'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, deal: Deal) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth.user?.permissions?.includes('use dialer') && pbxModuleActive && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => void handleDialerCall(deal)} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                        <Phone className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Click to Call')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {/* {auth.user?.permissions?.includes('edit-deals') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => setLabelingItem(deal)} className="h-8 w-8 p-0 text-purple-600 hover:text-purple-700">
                                        <Tag className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Label')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )} */}
                        {auth.user?.permissions?.includes('view-deals') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => router.get(route('lead.deals.show', deal.id))} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('edit-deals') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openModal('edit', deal)} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                        <EditIcon className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Edit')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('delete-deals') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteDialog(deal.id)}
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
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('CRM'), url: route('lead.index') },
                { label: t('Deals') }
            ]}
            pageTitle={t('Manage Deals')}
            pageActions={
                <div className="flex items-center gap-2">
                    <TooltipProvider>
                        <Select value={topPipeline} onValueChange={(value) => {
                            const pipelineId = value === 'all' ? '' : value;
                            setTopPipeline(value);

                            // Save default pipeline to user table
                            if (pipelineId) {
                                fetch(route('lead.deals.save-default-pipeline'), {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    },
                                    body: JSON.stringify({ pipeline_id: pipelineId })
                                });
                            }

                            router.get(route('lead.deals.index'), { ...filters, pipeline_id: pipelineId, per_page: perPage, sort: sortField, direction: sortDirection, view: viewMode }, {
                                preserveState: true,
                                replace: true
                            });
                        }}>
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder={t('Select Pipeline')} />
                            </SelectTrigger>
                            <SelectContent searchable>
                                <SelectItem value="all">{t('All Pipelines')}</SelectItem>
                                {pipelines?.map((pipeline: any) => (
                                    <SelectItem key={pipeline.id} value={pipeline.id.toString()}>
                                        {pipeline.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {googleDriveButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {oneDriveButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {dropboxButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {boxButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {hubspotButtons.map((button) => (
                            <div key={button.id}>{button.component}</div>
                        ))}
                        {auth.user?.permissions?.includes('view-deals') && (
                            <>

                                <Tooltip delayDuration={0}>
                                    <TooltipTrigger asChild>
                                        <Button size="sm" variant="outline" className="bg-white" onClick={() => setViewMode(viewMode === 'kanban' ? 'list' : 'kanban')}>
                                            {viewMode === 'kanban' ? <List className="h-4 w-4" /> : <Kanban className="h-4 w-4" />}
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{viewMode === 'kanban' ? t('List View') : t('Kanban View')}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </>
                        )}
                        {auth.user?.permissions?.includes('create-deals') && (
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
            <Head title={t('Deals')} />

            {viewMode === 'kanban' ? (
                (() => {
                    const { columns, tasks } = getKanbanData();
                    return (
                        <KanbanBoard
                            tasks={tasks}
                            columns={columns}
                            onMove={handleMove}
                            taskCard={DealCard}
                            kanbanActions={null}
                        />
                    );
                })()
            ) : (
                <Card className="shadow-sm">
                    {/* Search & Controls Header */}
                    <CardContent className="p-6 border-b bg-gray-50/50">
                        <div className="flex items-center justify-between gap-4">
                            <div className="flex-1 max-w-md">
                                <SearchInput
                                    value={filters.name}
                                    onChange={(value) => setFilters({ ...filters, name: value })}
                                    onSearch={handleFilter}
                                    placeholder={t('Search Deals...')}
                                />
                            </div>
                            <div className="flex items-center gap-3">

                                <PerPageSelector
                                    routeName="lead.deals.index"
                                    filters={{ ...filters, view: viewMode }}
                                />
                                <div className="relative">
                                    <FilterButton
                                        showFilters={showFilters}
                                        onToggle={() => setShowFilters(!showFilters)}
                                    />
                                    {(() => {
                                        const activeFilters = [filters.pipeline_id, filters.stage_id, filters.status].filter(f => f !== '' && f !== null && f !== undefined).length;
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
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Pipeline')}</label>
                                    <Select
                                        value={filters.pipeline_id}
                                        onValueChange={(value) => {
                                            const pipelineId = value === 'all' ? '' : value;
                                            setFilters(prev => ({ ...prev, pipeline_id: pipelineId, stage_id: '' }));
                                        }}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Filter by Pipeline')} />
                                        </SelectTrigger>
                                        <SelectContent searchable>
                                            <SelectItem value="all">{t('All Pipelines')}</SelectItem>
                                            {pipelines?.map((item: any) => (
                                                <SelectItem key={item.id} value={item.id.toString()}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Stage')}</label>
                                    <Select
                                        value={filters.stage_id}
                                        onValueChange={(value) => setFilters({ ...filters, stage_id: value === 'all' ? '' : value })}
                                        disabled={!filters.pipeline_id}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder={filters.pipeline_id ? t('Filter by Stage') : t('Select Pipeline first')} />
                                        </SelectTrigger>
                                        <SelectContent searchable>
                                            <SelectItem value="all">{t('All Stages')}</SelectItem>
                                            {filterStages?.map((item: any) => (
                                                <SelectItem key={item.id} value={item.id.toString()}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                    <Select value={filters.status} onValueChange={(value) => setFilters({ ...filters, status: value })}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Filter by Status')} />
                                        </SelectTrigger>
                                        <SelectContent searchable>
                                            <SelectItem value="Won">{t('Won')}</SelectItem>
                                            <SelectItem value="Loss">{t('Loss')}</SelectItem>
                                            <SelectItem value="Active">{t('Active')}</SelectItem>
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
                        <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[70vh] rounded-none w-full">
                            <div className="min-w-[800px]">
                                <DataTable
                                    data={deals?.data || []}
                                    columns={tableColumns}
                                    onSort={handleSort}
                                    sortKey={sortField}
                                    sortDirection={sortDirection as 'asc' | 'desc'}
                                    className="rounded-none"
                                    emptyState={
                                        <NoRecordsFound
                                            icon={DollarSignIcon}
                                            title={t('No Deals found')}
                                            description={t('Get started by creating your first Deal.')}
                                            hasFilters={!!(filters.name || filters.notes || filters.pipeline_id || filters.stage_id || filters.status)}
                                            onClearFilters={clearFilters}
                                            createPermission="create-deals"
                                            onCreateClick={() => openModal('add')}
                                            createButtonText={t('Create Deal')}
                                            className="h-auto"
                                        />
                                    }
                                />
                            </div>
                        </div>
                    </CardContent>

                    {/* Pagination Footer */}
                    <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                        <Pagination
                            data={deals || { data: [], links: [], meta: {} }}
                            routeName="lead.deals.index"
                            filters={{ ...filters, per_page: perPage, view: viewMode }}
                        />
                    </CardContent>
                </Card>
            )}

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'add' && (
                    <Create onSuccess={closeModal} />
                )}
                {modalState.mode === 'edit' && modalState.data && (
                    <EditDeal
                        deal={modalState.data}
                        onSuccess={closeModal}
                    />
                )}
            </Dialog>



            <Dialog open={!!labelingItem} onOpenChange={() => setLabelingItem(null)}>
                {labelingItem && <LabelView deal={labelingItem} onSuccess={() => setLabelingItem(null)} />}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Deal')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}
