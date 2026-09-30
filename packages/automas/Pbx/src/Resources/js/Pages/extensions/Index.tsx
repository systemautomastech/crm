import { useState, useEffect, useMemo, useCallback } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import axios from 'axios';
import {
    Activity,
    CheckCircle2,
    Edit,
    Phone,
    PhoneCall,
    PhoneIncoming,
    PhoneOff,
    Plus,
    Radio,
    RefreshCw,
    Settings,
    ShieldCheck,
    Trash2,
    Users,
} from 'lucide-react';
import {
    Cell,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip as RechartsTooltip,
} from 'recharts';

import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { DataTable } from '@/components/ui/data-table';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { SearchInput } from '@/components/ui/search-input';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

import NoRecordsFound from '@/components/no-records-found';

interface ExtensionUser {
    id: number;
    name: string;
    email?: string | null;
}

interface PbxExtension {
    id: number;
    user_id: number;
    extension: string;
    caller_id?: string | null;
    is_active: boolean | number;
    created_at?: string;
    user?: ExtensionUser | null;
}

interface LiveStatusInfo {
    extension_id?: number;
    extension?: string;
    status: 'available' | 'ringing' | 'on_call' | 'offline' | 'unknown';
    registered?: boolean;
    in_call?: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedExtensions {
    data: PbxExtension[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
    meta?: {
        current_page?: number;
        last_page?: number;
        per_page?: number;
        total?: number;
        from?: number | null;
        to?: number | null;
    };
}

interface PbxSetting {
    id: number;
    max_extensions: number;
    extension_start: number;
    extension_end: number;
    is_enabled: boolean | number;
}

interface AuthUser {
    id?: number;
    permissions?: string[];
}

interface IndexProps {
    extensions: PaginatedExtensions;
    setting: PbxSetting | null;
    canCreateExtension: boolean;
    auth?: {
        user?: AuthUser;
    };
    [key: string]: any;
}

export default function Index() {
    const { t } = useTranslation();

    const {
        extensions,
        setting,
        canCreateExtension,
        auth,
    } = usePage<IndexProps>().props;

    const permissions = auth?.user?.permissions ?? [];
    const userId = auth?.user?.id;

    const urlParams = new URLSearchParams(window.location.search);

    const [search, setSearch] = useState(
        urlParams.get('search') ?? '',
    );

    const [perPage] = useState(
        urlParams.get('per_page') ?? '10',
    );

    const [sortField, setSortField] = useState(
        urlParams.get('sort') ?? 'created_at',
    );

    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>(
        urlParams.get('direction') === 'asc' ? 'asc' : 'desc',
    );

    useFlashMessages();

    const canViewAll =
        permissions.includes('view all extensions') ||
        permissions.length === 0;

    const canCreate =
        permissions.includes('create extensions') ||
        permissions.length === 0;

    const canEdit =
        permissions.includes('edit extensions') ||
        permissions.length === 0;

    const canDelete =
        permissions.includes('delete extensions') ||
        permissions.length === 0;

    const canEditExt = (ext: PbxExtension) => {
        if (!canEdit) return false;
        if (canViewAll) return true;
        return userId !== undefined && ext.user_id === userId;
    };

    const canDeleteExt = (ext: PbxExtension) => {
        if (!canDelete) return false;
        if (canViewAll) return true;
        return userId !== undefined && ext.user_id === userId;
    };

    const {
        deleteState,
        openDeleteDialog,
        closeDeleteDialog,
        confirmDelete,
    } = useDeleteHandler({
        routeName: 'pbx.extensions.destroy',
        defaultMessage: t(
            'Are you sure you want to delete this extension?',
        ),
    });

    const handleSearch = (): void => {
        router.get(
            route('pbx.extensions.index'),
            {
                search,
                per_page: perPage,
                sort: sortField,
                direction: sortDirection,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const clearSearch = (): void => {
        setSearch('');

        router.get(
            route('pbx.extensions.index'),
            {
                per_page: perPage,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleSort = (field: string): void => {
        const direction: 'asc' | 'desc' =
            sortField === field && sortDirection === 'asc'
                ? 'desc'
                : 'asc';

        setSortField(field);
        setSortDirection(direction);

        router.get(
            route('pbx.extensions.index'),
            {
                search,
                per_page: perPage,
                sort: field,
                direction,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const [liveStatuses, setLiveStatuses] = useState<Record<number, LiveStatusInfo>>({});
    const [isLoadingStatus, setIsLoadingStatus] = useState<boolean>(true);
    const [isRefreshingStatus, setIsRefreshingStatus] = useState<boolean>(false);
    const [lastUpdatedTime, setLastUpdatedTime] = useState<string | null>(null);

    const fetchLiveStatuses = useCallback(async () => {
        setIsRefreshingStatus(true);

        try {
            const response = await axios.get(route('pbx.extensions.live-status'));

            if (response.data?.success && response.data?.extensions) {
                setLiveStatuses(response.data.extensions);
                setLastUpdatedTime(
                    new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    })
                );
            }
        } catch (error) {
            console.error('Error fetching live statuses:', error);
        } finally {
            setIsLoadingStatus(false);
            setIsRefreshingStatus(false);
        }
    }, []);

    useEffect(() => {
        let isMounted = true;
        let timeoutId: ReturnType<typeof setTimeout> | null = null;

        const runPoll = async () => {
            if (!isMounted) return;
            await fetchLiveStatuses();
            if (isMounted && document.visibilityState === 'visible') {
                timeoutId = setTimeout(runPoll, 5000);
            }
        };

        runPoll();

        const handleVisibilityChange = () => {
            if (document.visibilityState === 'visible') {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
                runPoll();
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);

        return () => {
            isMounted = false;
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        };
    }, [fetchLiveStatuses]);

    const renderLiveStatusBadge = (extensionId: number) => {
        if (isLoadingStatus && !liveStatuses[extensionId]) {
            return (
                <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-slate-400" />
                    {t('Checking...')}
                </span>
            );
        }

        const info = liveStatuses[extensionId];
        const status = info?.status || 'unknown';

        switch (status) {
            case 'available':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400 dark:ring-emerald-500/30">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        {t('Available')}
                    </span>
                );

            case 'ringing':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-400 dark:ring-amber-500/30">
                        <span className="h-1.5 w-1.5 animate-ping rounded-full bg-amber-500" />
                        {t('Ringing')}
                    </span>
                );

            case 'on_call':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-500/30">
                        <span className="h-1.5 w-1.5 rounded-full bg-blue-500" />
                        {t('On Call')}
                    </span>
                );

            case 'offline':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-500/20 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700">
                        <span className="h-1.5 w-1.5 rounded-full bg-slate-500" />
                        {t('Offline')}
                    </span>
                );

            case 'unknown':
            default:
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500 ring-1 ring-inset ring-gray-400/20 dark:bg-gray-800 dark:text-gray-400">
                        <span className="h-1.5 w-1.5 rounded-full bg-gray-400" />
                        {t('Unknown')}
                    </span>
                );
        }
    };

    const stats = useMemo(() => {
        const liveValues = Object.values(liveStatuses);
        const total = extensions.total || liveValues.length || extensions.data.length || 0;

        let activeConfig = 0;
        let inactiveConfig = 0;
        let availableCount = 0;
        let ringingCount = 0;
        let onCallCount = 0;
        let offlineCount = 0;
        let unknownCount = 0;

        if (liveValues.length > 0) {
            liveValues.forEach((info) => {
                if (info.is_active) activeConfig++;
                else inactiveConfig++;

                const status = info.status || 'unknown';
                if (status === 'available') availableCount++;
                else if (status === 'ringing') ringingCount++;
                else if (status === 'on_call') onCallCount++;
                else if (status === 'offline') offlineCount++;
                else unknownCount++;
            });
        } else {
            activeConfig = extensions.data.filter((e) => Boolean(e.is_active)).length;
            inactiveConfig = extensions.data.filter((e) => !Boolean(e.is_active)).length;
            unknownCount = total;
        }

        const registeredCount = availableCount + ringingCount + onCallCount;
        const checkedTotal = liveValues.length || total || 1;

        return {
            total,
            activeConfig,
            inactiveConfig,
            available: availableCount,
            ringing: ringingCount,
            onCall: onCallCount,
            offline: offlineCount,
            unknown: unknownCount,
            registered: registeredCount,
            registeredRate: Math.round((registeredCount / checkedTotal) * 100),
        };
    }, [extensions.total, extensions.data, liveStatuses]);

    const chartData = useMemo(() => {
        const data = [
            { name: t('Available'), value: stats.available, color: '#10b981' },
            { name: t('Ringing'), value: stats.ringing, color: '#f59e0b' },
            { name: t('On Call'), value: stats.onCall, color: '#3b82f6' },
            { name: t('Offline'), value: stats.offline, color: '#64748b' },
            { name: t('Unknown'), value: stats.unknown, color: '#94a3b8' },
        ];
        const totalVal = data.reduce((acc, curr) => acc + curr.value, 0);
        if (totalVal === 0) {
            return [{ name: t('Checking...'), value: 1, color: '#cbd5e1' }];
        }
        return data.filter((item) => item.value > 0);
    }, [stats, t]);

    const liveBreakdownList = useMemo(() => {
        const liveValuesCount = Object.keys(liveStatuses).length || extensions.total || 1;
        const calcPct = (cnt: number) => Math.round((cnt / liveValuesCount) * 100);

        return [
            { key: 'available', label: t('Available / Idle'), count: stats.available, percent: calcPct(stats.available), color: '#10b981' },
            { key: 'on_call', label: t('Ongoing Call'), count: stats.onCall, percent: calcPct(stats.onCall), color: '#3b82f6' },
            { key: 'ringing', label: t('Ringing'), count: stats.ringing, percent: calcPct(stats.ringing), color: '#f59e0b' },
            { key: 'offline', label: t('Offline'), count: stats.offline, percent: calcPct(stats.offline), color: '#64748b' },
            { key: 'unknown', label: t('Unknown'), count: stats.unknown, percent: calcPct(stats.unknown), color: '#94a3b8' },
        ];
    }, [stats, liveStatuses, extensions.total, t]);

    const columns = [
        {
            key: 'serial',
            header: t('SL'),
            render: (_value: unknown, _extension: PbxExtension, index: number) => {
                return (
                    (extensions.current_page - 1) *
                    extensions.per_page +
                    index +
                    1
                );
            },
        },
        {
            key: 'user',
            header: t('User'),
            render: (_value: unknown, extension: PbxExtension) => (
                <div>
                    <div className="font-medium text-foreground">
                        {extension.user?.name ?? t('Unknown User')}
                    </div>

                    {extension.user?.email && (
                        <div className="text-xs text-muted-foreground">
                            {extension.user.email}
                        </div>
                    )}
                </div>
            ),
        },
        {
            key: 'extension',
            header: t('Extension'),
            sortable: true,
            render: (value: string) => (
                <span className="font-semibold">
                    {value}
                </span>
            ),
        },
        {
            key: 'caller_id',
            header: t('Caller ID'),
            sortable: true,
            render: (value: string | null) => value || '—',
        },
        {
            key: 'is_active',
            header: t('Status'),
            sortable: true,
            render: (value: boolean | number) =>
                Boolean(value) ? (
                    <span className="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                        {t('Active')}
                    </span>
                ) : (
                    <span className="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                        {t('Inactive')}
                    </span>
                ),
        },
        {
            key: 'live_status',
            header: t('Live Activity'),
            render: (_value: unknown, extension: PbxExtension) => renderLiveStatusBadge(extension.id),
        },
        ...(canEdit || canDelete
            ? [
                {
                    key: 'actions',
                    header: t('Actions'),
                    render: (
                        _value: unknown,
                        extension: PbxExtension,
                    ) => {
                        const showEdit = canEditExt(extension);
                        const showDelete = canDeleteExt(extension);

                        if (!showEdit && !showDelete) return null;

                        return (
                            <div className="flex items-center gap-1">
                                <TooltipProvider>
                                    {showEdit && (
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                                    onClick={() => router.visit(route('pbx.extensions.edit', extension.id))}
                                                >
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                            </TooltipTrigger>

                                            <TooltipContent>
                                                <p>{t('Edit')}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    )}

                                    {showDelete && (
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                                    onClick={() => openDeleteDialog(extension.id)}
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
                        );
                    },
                },
            ]
            : []),
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {
                    label: t('PBX'),
                },
                {
                    label: t('Extensions'),
                },
            ]}
            pageTitle={t('Manage Extensions')}
            pageActions={
                <div className="flex items-center gap-2">
                    {setting && (canCreate) && (
                        <>
                            <span className="hidden rounded-md border bg-muted px-3 py-1.5 text-xs font-medium text-muted-foreground sm:inline-flex">
                                {t('Range')}: {setting.extension_start}–
                                {setting.extension_end}
                            </span>

                            <span className="hidden rounded-md border bg-muted px-3 py-1.5 text-xs font-medium text-muted-foreground sm:inline-flex">
                                {t('Used')}: {extensions.total}/
                                {setting.max_extensions}
                            </span>
                        </>
                    )}

                    {!setting && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() =>
                                            router.visit(
                                                route(
                                                    'pbx.settings.index',
                                                ),
                                            )
                                        }
                                    >
                                        <Settings className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>

                                <TooltipContent>
                                    <p>{t('Configure PBX')}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}

                    {canCreateExtension && (canCreate) && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        type="button"
                                        size="sm"
                                        onClick={() =>
                                            router.visit(
                                                route(
                                                    'pbx.extensions.create',
                                                ),
                                            )
                                        }
                                    >
                                        <Plus className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>

                                <TooltipContent>
                                    <p>{t('Create Extension')}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                </div>
            }
        >
            <Head title={t('Manage Extensions')} />

            {!setting && (
                <Card className="mb-4 border-amber-200 bg-amber-50">
                    <CardContent className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="font-medium text-amber-900">
                                {t('PBX settings are not configured')}
                            </p>

                            <p className="text-sm text-amber-700">
                                {t(
                                    'Configure PBX settings before managing extensions.',
                                )}
                            </p>
                        </div>

                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                router.visit(
                                    route('pbx.settings.index'),
                                )
                            }
                        >
                            <Settings className="h-4 w-4" />
                            {t('Configure PBX')}
                        </Button>
                    </CardContent>
                </Card>
            )}

            {setting && !canCreateExtension && (
                <Card className="mb-4 border-blue-200 bg-blue-50">
                    <CardContent className="p-4">
                        <p className="text-sm font-medium text-blue-800">
                            {t(
                                'The maximum extension limit for this workspace has been reached.',
                            )}
                        </p>
                    </CardContent>
                </Card>
            )}

            {/* Real-time Extension Statistics Overview & Chart Diagram */}
            <div className="mb-6 space-y-4">
                {/* 6 KPI Overview Cards */}
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    {/* Card 1: Total Extensions */}
                    <Card className="border border-slate-200/80 bg-white/80 shadow-xs backdrop-blur-xs dark:border-slate-800 dark:bg-slate-900/80">
                        <CardContent className="p-3.5">
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {t('Total Exts')}
                                </span>
                                <div className="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                                    <Users className="h-3.5 w-3.5" />
                                </div>
                            </div>
                            <div className="mt-2 flex items-baseline justify-between">
                                <span className="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {stats.total}
                                </span>
                                {setting && (
                                    <span className="text-[11px] font-medium text-slate-400">
                                        / {setting.max_extensions}
                                    </span>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 2: Config Active */}
                    <Card className="border border-slate-200/80 bg-white/80 shadow-xs backdrop-blur-xs dark:border-slate-800 dark:bg-slate-900/80">
                        <CardContent className="p-3.5">
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {t('Config Active')}
                                </span>
                                <div className="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                </div>
                            </div>
                            <div className="mt-2 flex items-baseline justify-between">
                                <span className="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                    {stats.activeConfig}
                                </span>
                                <span className="text-[11px] font-medium text-slate-400">
                                    {stats.inactiveConfig} {t('Inactive')}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 3: Available (Live) */}
                    <Card className="border border-slate-200/80 bg-white/80 shadow-xs backdrop-blur-xs dark:border-slate-800 dark:bg-slate-900/80">
                        <CardContent className="p-3.5">
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {t('Available')}
                                </span>
                                <div className="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    <Phone className="h-3.5 w-3.5" />
                                </div>
                            </div>
                            <div className="mt-2 flex items-baseline justify-between">
                                <span className="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                    {stats.available}
                                </span>
                                <span className="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600">
                                    <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                    {t('Idle')}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 4: Ringing */}
                    <Card className="border border-slate-200/80 bg-white/80 shadow-xs backdrop-blur-xs dark:border-slate-800 dark:bg-slate-900/80">
                        <CardContent className="p-3.5">
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {t('Ringing')}
                                </span>
                                <div className="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                                    <PhoneIncoming className="h-3.5 w-3.5 animate-bounce" />
                                </div>
                            </div>
                            <div className="mt-2 flex items-baseline justify-between">
                                <span className="text-xl font-bold text-amber-600 dark:text-amber-400">
                                    {stats.ringing}
                                </span>
                                <span className="inline-flex items-center gap-1 text-[11px] font-medium text-amber-600">
                                    <span className="h-1.5 w-1.5 animate-ping rounded-full bg-amber-500" />
                                    {t('Incoming')}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 5: On Call */}
                    <Card className="border border-slate-200/80 bg-white/80 shadow-xs backdrop-blur-xs dark:border-slate-800 dark:bg-slate-900/80">
                        <CardContent className="p-3.5">
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {t('On Call')}
                                </span>
                                <div className="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                                    <PhoneCall className="h-3.5 w-3.5" />
                                </div>
                            </div>
                            <div className="mt-2 flex items-baseline justify-between">
                                <span className="text-xl font-bold text-blue-600 dark:text-blue-400">
                                    {stats.onCall}
                                </span>
                                <span className="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600">
                                    <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-500" />
                                    {t('Active Call')}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 6: Offline */}
                    <Card className="border border-slate-200/80 bg-white/80 shadow-xs backdrop-blur-xs dark:border-slate-800 dark:bg-slate-900/80">
                        <CardContent className="p-3.5">
                            <div className="flex items-center justify-between gap-2">
                                <span className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                    {t('Offline')}
                                </span>
                                <div className="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    <PhoneOff className="h-3.5 w-3.5" />
                                </div>
                            </div>
                            <div className="mt-2 flex items-baseline justify-between">
                                <span className="text-xl font-bold text-slate-700 dark:text-slate-300">
                                    {stats.offline}
                                </span>
                                <span className="text-[11px] font-medium text-slate-400">
                                    {stats.unknown > 0 ? `+${stats.unknown} ${t('Unknown')}` : t('Unreachable')}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Split Section: Chart Diagram & Workspace Overview */}
                <div className="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    {/* Left: Recharts Donut Activity Diagram */}
                    <Card className="border border-slate-200/80 shadow-xs lg:col-span-7 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader className="pb-2 pt-4 px-5 flex flex-row items-center justify-between space-y-0">
                            <div>
                                <CardTitle className="text-base font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    <Activity className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    {t('Live Activity Breakdown')}
                                </CardTitle>
                                <CardDescription className="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                                    <span>{t('Real-time Asterisk state distribution')}</span>
                                    {lastUpdatedTime && (
                                        <span className="text-[11px] text-slate-400 dark:text-slate-500">
                                            • {t('Updated')}: {lastUpdatedTime}
                                        </span>
                                    )}
                                </CardDescription>
                            </div>
                            <div className="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => fetchLiveStatuses()}
                                    disabled={isRefreshingStatus}
                                    className="h-7 px-2 text-xs text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white"
                                >
                                    <RefreshCw className={`h-3.5 w-3.5 mr-1 ${isRefreshingStatus ? 'animate-spin text-blue-600' : ''}`} />
                                    {t('Refresh')}
                                </Button>
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    <span className="h-1.5 w-1.5 animate-ping rounded-full bg-emerald-500" />
                                    {t('Auto 5s')}
                                </span>
                            </div>
                        </CardHeader>

                        <CardContent className="p-5 pt-2">
                            <div className="flex flex-col items-center justify-between gap-6 sm:flex-row">
                                {/* Donut Chart */}
                                <div className="relative h-48 w-48 shrink-0">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <PieChart>
                                            <Pie
                                                data={chartData}
                                                cx="50%"
                                                cy="50%"
                                                innerRadius={55}
                                                outerRadius={78}
                                                paddingAngle={4}
                                                dataKey="value"
                                            >
                                                {chartData.map((entry, idx) => (
                                                    <Cell key={`cell-${idx}`} fill={entry.color} stroke="none" />
                                                ))}
                                            </Pie>
                                            <RechartsTooltip
                                                formatter={(val: number) => [`${val} ${t('extensions')}`, t('Count')]}
                                                contentStyle={{
                                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                                    borderRadius: '8px',
                                                    color: '#fff',
                                                    fontSize: '12px',
                                                    border: 'none',
                                                }}
                                            />
                                        </PieChart>
                                    </ResponsiveContainer>

                                    {/* Center Text */}
                                    <div className="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                                        <span className="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                            {extensions.data.length}
                                        </span>
                                        <span className="text-[10px] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400">
                                            {t('Checked')}
                                        </span>
                                    </div>
                                </div>

                                {/* Custom Legend List */}
                                <div className="w-full space-y-2 sm:max-w-xs">
                                    {liveBreakdownList.map((item) => (
                                        <div
                                            key={item.key}
                                            className="flex items-center justify-between rounded-lg border border-slate-100 p-2 text-xs dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/30"
                                        >
                                            <div className="flex items-center gap-2">
                                                <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: item.color }} />
                                                <span className="font-medium text-slate-700 dark:text-slate-300">
                                                    {item.label}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-2 font-semibold">
                                                <span className="text-slate-900 dark:text-slate-100">{item.count}</span>
                                                <span className="text-[11px] text-slate-400">({item.percent}%)</span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Right: Capacity & Health Overview */}
                    <Card className="border border-slate-200/80 shadow-xs lg:col-span-5 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader className="pb-2 pt-4 px-5">
                            <CardTitle className="text-base font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <ShieldCheck className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                {t('Workspace Capacity & Health')}
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500">
                                {t('Subscription limits & PBX connectivity overview')}
                            </CardDescription>
                        </CardHeader>

                        <CardContent className="p-5 pt-2 space-y-4">
                            {/* License Allocation */}
                            {setting && (
                                <div className="space-y-1.5">
                                    <div className="flex justify-between text-xs font-medium">
                                        <span className="text-slate-600 dark:text-slate-400">{t('License Usage')}</span>
                                        <span className="font-semibold text-slate-900 dark:text-slate-100">
                                            {extensions.total} / {setting.max_extensions} ({Math.round((extensions.total / setting.max_extensions) * 100)}%)
                                        </span>
                                    </div>
                                    <Progress
                                        value={Math.round((extensions.total / setting.max_extensions) * 100)}
                                        className="h-2 bg-slate-100 dark:bg-slate-800"
                                    />
                                </div>
                            )}

                            {/* Registered / Reachable Rate */}
                            <div className="space-y-1.5">
                                <div className="flex justify-between text-xs font-medium">
                                    <span className="text-slate-600 dark:text-slate-400">{t('Live Connectivity Rate')}</span>
                                    <span className="font-semibold text-emerald-600 dark:text-emerald-400">
                                        {stats.registeredRate}% {t('Registered')}
                                    </span>
                                </div>
                                <Progress
                                    value={stats.registeredRate}
                                    className="h-2 bg-slate-100 dark:bg-slate-800 [&>div]:bg-emerald-500"
                                />
                            </div>

                            {/* Active vs Inactive Configuration */}
                            <div className="space-y-1.5">
                                <div className="flex justify-between text-xs font-medium">
                                    <span className="text-slate-600 dark:text-slate-400">{t('Enabled Status')}</span>
                                    <span className="font-semibold text-blue-600 dark:text-blue-400">
                                        {stats.activeConfig} {t('Active')} / {stats.inactiveConfig} {t('Disabled')}
                                    </span>
                                </div>
                                <Progress
                                    value={extensions.data.length > 0 ? Math.round((stats.activeConfig / extensions.data.length) * 100) : 100}
                                    className="h-2 bg-slate-100 dark:bg-slate-800 [&>div]:bg-blue-500"
                                />
                            </div>

                            {/* PBX Server Connection Status Badge */}
                            <div className="mt-3 flex items-center justify-between rounded-lg border border-slate-200/70 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                                <div className="flex items-center gap-2">
                                    <Radio className="h-4 w-4 text-emerald-500 animate-pulse" />
                                    <div>
                                        <div className="text-xs font-semibold text-slate-900 dark:text-slate-100">
                                            {setting ? (setting.pbx_name || 'Asterisk / amarSIP PBX') : t('No PBX Configured')}
                                        </div>
                                        <div className="text-[11px] text-slate-500">
                                            {setting ? `AMI Host: ${setting.ami_host}:${setting.ami_port}` : t('Please setup PBX credentials')}
                                        </div>
                                    </div>
                                </div>
                                <span className="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    {t('Connected')}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <Card className="shadow-sm">
                <CardContent className="border-b bg-muted/30 p-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="w-full max-w-md">
                            <SearchInput
                                value={search}
                                onChange={setSearch}
                                onSearch={handleSearch}
                                placeholder={t(
                                    'Search by extension, caller ID or user...',
                                )}
                            />
                        </div>

                        <div className="flex items-center gap-2">
                            <PerPageSelector
                                routeName="pbx.extensions.index"
                                filters={{
                                    search,
                                    sort: sortField,
                                    direction: sortDirection,
                                }}
                            />

                            {search && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={clearSearch}
                                >
                                    {t('Clear')}
                                </Button>
                            )}
                        </div>
                    </div>
                </CardContent>

                <CardContent className="p-0">
                    <div className="max-h-[70vh] w-full overflow-auto">
                        <div className="min-w-[750px]">
                            <DataTable
                                data={extensions.data}
                                columns={columns}
                                onSort={handleSort}
                                sortKey={sortField}
                                sortDirection={sortDirection}
                                className="rounded-none"
                                emptyState={
                                    <NoRecordsFound
                                        icon={Phone}
                                        title={t(
                                            'No extensions found',
                                        )}
                                        description={t(
                                            'Get started by creating your first PBX extension.',
                                        )}
                                        hasFilters={Boolean(search)}
                                        onClearFilters={clearSearch}
                                        onCreateClick={() =>
                                            router.visit(
                                                route(
                                                    'pbx.extensions.create',
                                                ),
                                            )
                                        }
                                        createButtonText={t(
                                            'Create Extension',
                                        )}
                                        className="h-auto"
                                    />
                                }
                            />
                        </div>
                    </div>
                </CardContent>

                <CardContent className="border-t bg-muted/20 px-4 py-2">
                    <Pagination
                        data={{
                            ...extensions,
                            ...(extensions.meta ?? {}),
                        }}
                        routeName="pbx.extensions.index"
                        filters={{
                            search,
                            per_page: perPage,
                            sort: sortField,
                            direction: sortDirection,
                        }}
                    />
                </CardContent>
            </Card>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Extension')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}