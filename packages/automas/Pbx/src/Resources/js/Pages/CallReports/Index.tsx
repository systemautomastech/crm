import { useMemo, useRef, useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import {
    BarChart3,
    Headphones,
    Phone,
    PhoneIncoming,
    PhoneOutgoing,
    RefreshCw,
    Search,
    X,
    XCircle,
} from 'lucide-react';

import AuthenticatedLayout from '@/layouts/authenticated-layout';

import {
    Card,
    CardContent,
} from '@/components/ui/card';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import { DataTable } from '@/components/ui/data-table';
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from '@/components/ui/pagination';
import { SearchInput } from '@/components/ui/search-input';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { DateRangePicker } from '@/components/ui/date-range-picker';

import NoRecordsFound from '@/components/no-records-found';

import { formatDate, formatTimeFromDate } from '@/utils/helpers';

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

interface CallLog {
    calldate: string;

    extension: string;

    direction:
    | 'inbound'
    | 'outbound';

    number: string | null;

    did: string | null;

    status: string;

    duration: number;

    talk_time: number;

    recordingfile?: string | null;

    has_recording: boolean;

    recording_size?: number;

    linkedid: string;

    uniqueid: string;

    user_id?: number | null;

    user_name?: string | null;

    recording_url?: string | null;
}

interface ExtensionOption {
    id: number;

    extension: string;

    user_id?: number | null;

    user_name?: string | null;
}

interface CallFilters {
    search: string;

    extension: string;

    call_direction: string;

    status: string;

    period?: string;

    from?: string;

    to?: string;

    date_range: string;
}

const PERIOD_OPTIONS = [
    { value: 'today', label: 'Today' },
    { value: 'this_week', label: 'This Week' },
    { value: 'this_month', label: 'This Month' },
    { value: 'previous_month', label: 'Previous Month' },
    { value: 'all_time', label: 'All Time' },
] as const;

interface CallPaginator {
    data: CallLog[];

    current_page?: number;

    first_page_url?: string;

    from?: number | null;

    last_page?: number;

    last_page_url?: string;

    links?: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;

    next_page_url?: string | null;

    path?: string;

    per_page?: number;

    prev_page_url?: string | null;

    to?: number | null;

    total?: number;

    meta?: {
        current_page?: number;
        from?: number | null;
        last_page?: number;
        links?: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        path?: string;
        per_page?: number;
        to?: number | null;
        total?: number;
    };
}

interface CallReportPermissions {
    view_all: boolean;

    view_own: boolean;
}

interface Props {
    calls: CallPaginator;

    extensions: ExtensionOption[];

    filters: CallFilters;

    callReportPermissions: CallReportPermissions;
}

/*
|--------------------------------------------------------------------------
| Duration
|--------------------------------------------------------------------------
*/

const formatDuration = (
    seconds?: number,
) => {
    const value = Math.max(
        Number(seconds || 0),
        0,
    );

    const hours = Math.floor(
        value / 3600,
    );

    const minutes = Math.floor(
        (value % 3600) / 60,
    );

    const secs = Math.floor(
        value % 60,
    );

    if (hours > 0) {
        return [
            hours
                .toString()
                .padStart(2, '0'),

            minutes
                .toString()
                .padStart(2, '0'),

            secs
                .toString()
                .padStart(2, '0'),
        ].join(':');
    }

    return [
        minutes
            .toString()
            .padStart(2, '0'),

        secs
            .toString()
            .padStart(2, '0'),
    ].join(':');
};

/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const getStatusVariant = (
    status?: string,
):
    | 'default'
    | 'secondary'
    | 'destructive'
    | 'success'
    | 'outline' => {
    switch (
    status
        ?.toUpperCase()
        .trim()
    ) {
        case 'ANSWERED':
            return 'success';

        case 'NO ANSWER':
        case 'NOANSWER':
            return 'secondary';

        case 'FAILED':
        case 'BUSY':
        case 'CONGESTION':
            return 'destructive';

        default:
            return 'outline';
    }
};

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

export default function Index({
    calls,
    extensions,
    filters: serverFilters,
    callReportPermissions,
}: Props) {
    const { t } = useTranslation();

    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    */

    const urlParams =
        new URLSearchParams(
            window.location.search,
        );

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    const [
        filters,
        setFilters,
    ] = useState<CallFilters>({
        search:
            urlParams.get('search')
            ?? serverFilters.search
            ?? '',

        extension:
            (urlParams.get('extension') && urlParams.get('extension') !== 'all')
                ? urlParams.get('extension')!
                : (serverFilters.extension && serverFilters.extension !== 'all' ? serverFilters.extension : ''),

        call_direction:
            (urlParams.get('call_direction') || urlParams.get('direction'))
                && (urlParams.get('call_direction') || urlParams.get('direction')) !== 'all'
                ? (urlParams.get('call_direction') || urlParams.get('direction'))!
                : (serverFilters.call_direction && serverFilters.call_direction !== 'all' ? serverFilters.call_direction : ''),

        status:
            (urlParams.get('status') && urlParams.get('status') !== 'all')
                ? urlParams.get('status')!
                : (serverFilters.status && serverFilters.status !== 'all' ? serverFilters.status : ''),

        period:
            urlParams.get('period')
            ?? serverFilters.period
            ?? 'today',

        from:
            urlParams.get('from')
            ?? serverFilters.from
            ?? '',

        to:
            urlParams.get('to')
            ?? serverFilters.to
            ?? '',

        date_range:
            urlParams.get('date_range')
            ?? serverFilters.date_range
            ?? '',
    });

    useEffect(() => {
        setFilters({
            search: serverFilters?.search || '',
            extension: (serverFilters?.extension && serverFilters.extension !== 'all') ? serverFilters.extension : '',
            call_direction: (serverFilters?.call_direction && serverFilters.call_direction !== 'all') ? serverFilters.call_direction : '',
            status: (serverFilters?.status && serverFilters.status !== 'all') ? serverFilters.status : '',
            period: serverFilters?.period || 'today',
            from: serverFilters?.from || '',
            to: serverFilters?.to || '',
            date_range: serverFilters?.date_range || '',
        });
    }, [
        serverFilters?.search,
        serverFilters?.extension,
        serverFilters?.call_direction,
        serverFilters?.status,
        serverFilters?.period,
        serverFilters?.from,
        serverFilters?.to,
        serverFilters?.date_range,
    ]);

    /*
    |--------------------------------------------------------------------------
    | UI
    |--------------------------------------------------------------------------
    */

    const [
        showFilters,
        setShowFilters,
    ] = useState(false);

    const [
        loading,
        setLoading,
    ] = useState(false);

    /*
    |--------------------------------------------------------------------------
    | Lazy Audio Playback State (One active player at a time)
    |--------------------------------------------------------------------------
    */
    const [activeAudioCall, setActiveAudioCall] = useState<{
        linkedid: string;
        extension: string;
        url: string;
    } | null>(null);

    const [loadingAudioId, setLoadingAudioId] = useState<string | null>(null);
    const [audioErrorId, setAudioErrorId] = useState<string | null>(null);
    const activeAbortControllerRef = useRef<AbortController | null>(null);

    const handlePlayRecording = async (row: CallLog) => {
        if (!row.linkedid || !row.extension) return;

        // Cancel previous pending request if practical
        if (activeAbortControllerRef.current) {
            activeAbortControllerRef.current.abort();
        }
        const controller = new AbortController();
        activeAbortControllerRef.current = controller;

        // Stop current audio & reset error states
        if (activeAudioCall?.url) {
            URL.revokeObjectURL(activeAudioCall.url);
        }
        setActiveAudioCall(null);
        setAudioErrorId(null);
        setLoadingAudioId(row.linkedid);

        const recUrl = row.recording_url || route('pbx.call-reports.recording', {
            linkedid: row.linkedid,
            extension: row.extension,
        });

        try {
            const response = await fetch(recUrl, { signal: controller.signal });
            if (controller.signal.aborted) return;

            if (response.status === 404 || !response.ok) {
                setAudioErrorId(row.linkedid);
                setLoadingAudioId(null);
                return;
            }

            const blob = await response.blob();
            if (controller.signal.aborted) return;

            const objectUrl = URL.createObjectURL(blob);
            setActiveAudioCall({
                linkedid: row.linkedid,
                extension: row.extension,
                url: objectUrl,
            });
            setLoadingAudioId(null);
        } catch (err: any) {
            if (err.name === 'AbortError') {
                return;
            }
            setAudioErrorId(row.linkedid);
            setLoadingAudioId(null);
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Per Page
    |--------------------------------------------------------------------------
    */

    const perPage =
        urlParams.get(
            'per_page',
        ) || '10';

    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    const statusOptions =
        useMemo(
            () => {
                const values =
                    new Set<string>([
                        'ANSWERED',
                        'NO ANSWER',
                        'BUSY',
                        'FAILED',
                        'CONGESTION',
                    ]);

                calls?.data?.forEach(
                    (call) => {
                        if (
                            call.status
                        ) {
                            const upper = call.status.toUpperCase().trim();
                            const normalized = upper === 'NOANSWER' ? 'NO ANSWER' : upper;
                            values.add(
                                normalized,
                            );
                        }
                    },
                );

                if (
                    filters.status
                ) {
                    const upperF = filters.status.toUpperCase().trim();
                    const normalizedF = upperF === 'NOANSWER' ? 'NO ANSWER' : upperF;
                    values.add(
                        normalizedF,
                    );
                }

                return Array.from(
                    values,
                );
            },
            [
                calls?.data,
                filters.status,
            ],
        );

    /*
    |--------------------------------------------------------------------------
    | Active Filter Count
    |--------------------------------------------------------------------------
    */

    const activeFilters =
        [
            filters.extension,
            filters.call_direction,
            filters.status,
            (filters.period && filters.period !== 'today') ? filters.period : '',
            filters.date_range,
        ].filter(
            (value) =>
                value !== ''
                && value !== 'all'
                && value !== null
                && value !== undefined,
        ).length;

    /*
    |--------------------------------------------------------------------------
    | Request Filters
    |--------------------------------------------------------------------------
    */

    const buildRequestFilters = () => {
        const req: Record<string, any> = {
            search: filters.search || undefined,
            extension: (filters.extension && filters.extension !== 'all') ? filters.extension : undefined,
            call_direction: (filters.call_direction && filters.call_direction !== 'all') ? filters.call_direction : undefined,
            status: (filters.status && filters.status !== 'all') ? filters.status : undefined,
            period: filters.period || 'today',
            per_page: perPage,
        };

        if (filters.period === 'custom') {
            if (filters.date_range) req.date_range = filters.date_range;
            if (filters.from) req.from = filters.from;
            if (filters.to) req.to = filters.to;
        }

        return req;
    };

    /*
    |--------------------------------------------------------------------------
    | Quick Period Switch
    |--------------------------------------------------------------------------
    */

    const handlePeriodChange = (newPeriod: string) => {
        setFilters((prev) => ({
            ...prev,
            period: newPeriod,
            date_range: '',
            from: '',
            to: '',
        }));

        const reqFilters: Record<string, any> = {
            search: filters.search || undefined,
            extension: (filters.extension && filters.extension !== 'all') ? filters.extension : undefined,
            call_direction: (filters.call_direction && filters.call_direction !== 'all') ? filters.call_direction : undefined,
            status: (filters.status && filters.status !== 'all') ? filters.status : undefined,
            period: newPeriod,
            per_page: perPage,
        };

        router.get(
            route('pbx.call-reports.index'),
            reqFilters,
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => setLoading(true),
                onFinish: () => setLoading(false),
            }
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Apply Filter
    |--------------------------------------------------------------------------
    */

    const handleFilter = () => {
        router.get(
            route(
                'pbx.call-reports.index',
            ),

            buildRequestFilters(),

            {
                preserveState: true,

                preserveScroll: true,

                replace: true,

                onStart: () =>
                    setLoading(
                        true,
                    ),

                onFinish: () =>
                    setLoading(
                        false,
                    ),
            },
        );
    };

    const handleClearSearch = () => {
        setFilters((prev) => ({
            ...prev,
            search: '',
        }));

        const reqFilters: Record<string, any> = {
            ...buildRequestFilters(),
            search: undefined,
        };

        router.get(
            route('pbx.call-reports.index'),
            reqFilters,
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => setLoading(true),
                onFinish: () => setLoading(false),
            }
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Clear Filter
    |--------------------------------------------------------------------------
    */

    const clearFilters = () => {
        const emptyFilters: CallFilters = {
            search: '',
            extension: '',
            call_direction: '',
            status: '',
            period: 'today',
            date_range: '',
            from: '',
            to: '',
        };

        setFilters(emptyFilters);

        router.get(
            route('pbx.call-reports.index'),
            {
                period: 'today',
                per_page: perPage,
            },
            {
                preserveState: false,
                preserveScroll: true,
                replace: true,
                onStart: () => setLoading(true),
                onFinish: () => setLoading(false),
            }
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    const tableColumns = [
        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        {
            key: 'user_name',

            header: t('User'),

            sortable: false,

            render: (
                value: string,
                row: CallLog,
            ) => (
                <div>
                    <div className="font-medium text-foreground">
                        {value || '-'}
                    </div>

                    <div className="text-xs text-muted-foreground">
                        {row.extension || '-'}
                    </div>
                </div>
            ),
        },


        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */
        {
            key: 'calldate',

            header:
                t('Date & Time'),

            sortable: false,

            render: (
                value: string,
            ) => (
                <div className="whitespace-nowrap text-sm">
                    <div className="text-foreground">
                        {formatDate(value)}
                    </div>

                    <div className="text-xs text-muted-foreground">
                        {formatTimeFromDate(value)}
                    </div>
                </div>
            ),
        },

        /*
        |--------------------------------------------------------------------------
        | Direction
        |--------------------------------------------------------------------------
        */
        {
            key: 'direction',

            header:
                t('Direction'),

            sortable: false,

            render: (
                value: string,
            ) => {
                const lowerVal = (value || '').toLowerCase().trim();
                const inbound =
                    lowerVal === 'inbound' || lowerVal === 'incoming';

                return (
                    <span
                        className={
                            inbound
                                ? 'inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700'
                                : 'inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700'
                        }
                    >
                        {inbound ? (
                            <PhoneIncoming className="h-3.5 w-3.5" />
                        ) : (
                            <PhoneOutgoing className="h-3.5 w-3.5" />
                        )}

                        {inbound
                            ? t(
                                'Incoming',
                            )
                            : t(
                                'Outgoing',
                            )}
                    </span>
                );
            },
        },

        /*
        |--------------------------------------------------------------------------
        | Number
        |--------------------------------------------------------------------------
        */
        {
            key: 'number',

            header:
                t('Number'),

            sortable: false,

            render: (
                value: string,
                row: CallLog,
            ) => (
                <div>
                    <div className="font-medium">
                        {value || '-'}
                    </div>

                    {row.did ? (
                        <div className="text-xs text-muted-foreground">
                            DID: {row.did}
                        </div>
                    ) : null}
                </div>
            ),
        },

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */
        {
            key: 'status',

            header:
                t('Status'),

            sortable: false,

            render: (
                value: string,
            ) => {
                const s = (value || '').toUpperCase().trim();
                if (s === 'ANSWERED') {
                    return (
                        <span className="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                            {t('ANSWERED')}
                        </span>
                    );
                }
                if (s === 'NO ANSWER' || s === 'NOANSWER') {
                    return (
                        <span className="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                            {t('NO ANSWER')}
                        </span>
                    );
                }
                if (s === 'BUSY') {
                    return (
                        <span className="inline-flex items-center gap-1 rounded-full border border-purple-200 bg-purple-50 px-2.5 py-0.5 text-xs font-semibold text-purple-700 dark:border-purple-800 dark:bg-purple-950/40 dark:text-purple-300">
                            {t('BUSY')}
                        </span>
                    );
                }
                if (s === 'FAILED') {
                    return (
                        <span className="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-300">
                            {t('FAILED')}
                        </span>
                    );
                }
                if (s === 'CONGESTION') {
                    return (
                        <span className="inline-flex items-center gap-1 rounded-full border border-orange-200 bg-orange-50 px-2.5 py-0.5 text-xs font-semibold text-orange-700 dark:border-orange-800 dark:bg-orange-950/40 dark:text-orange-300">
                            {t('CONGESTION')}
                        </span>
                    );
                }
                return (
                    <Badge variant="outline">
                        {value || '-'}
                    </Badge>
                );
            },
        },

        /*
        |--------------------------------------------------------------------------
        | Duration
        |--------------------------------------------------------------------------
        */

        {
            key: 'duration',

            header:
                t('Duration'),

            sortable: false,

            render: (
                value: number,
            ) => (
                <span className="whitespace-nowrap font-medium">
                    {formatDuration(
                        value,
                    )}
                </span>
            ),
        },

        /*
        |--------------------------------------------------------------------------
        | Talk Time
        |--------------------------------------------------------------------------
        */

        {
            key: 'talk_time',

            header:
                t('Talk Time'),

            sortable: false,

            render: (
                value: number,
            ) => (
                <span className="whitespace-nowrap font-medium">
                    {formatDuration(
                        value,
                    )}
                </span>
            ),
        },

        /*
        |--------------------------------------------------------------------------
        | Recording
        |--------------------------------------------------------------------------
        */

        {
            key: 'recording_url',

            header: t('Recording'),

            sortable: false,

            render: (value: string, row: CallLog) => {
                if (!row.has_recording && !row.recordingfile) {
                    return <span className="text-muted-foreground text-xs">{t('No Recording')}</span>;
                }

                const isCurrentPlaying = activeAudioCall?.linkedid === row.linkedid;
                const isLoadingThis = loadingAudioId === row.linkedid;
                const isErrorThis = audioErrorId === row.linkedid;

                if (isLoadingThis) {
                    return (
                        <Button variant="outline" size="sm" disabled className="gap-1.5 h-8 text-xs">
                            <RefreshCw className="h-3.5 w-3.5 animate-spin" />
                            {t('Loading...')}
                        </Button>
                    );
                }

                if (isErrorThis) {
                    return (
                        <span className="text-rose-600 dark:text-rose-400 text-xs font-medium">
                            {t('Recording unavailable')}
                        </span>
                    );
                }

                if (isCurrentPlaying && activeAudioCall) {
                    return (
                        <div className="flex min-w-[240px] items-center gap-2 bg-slate-50 dark:bg-slate-800 p-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                            <audio
                                controls
                                autoPlay
                                src={activeAudioCall.url}
                                className="h-7 w-full"
                            />
                            <Button
                                variant="ghost"
                                size="icon"
                                className="h-7 w-7 text-slate-500 hover:text-red-600"
                                title={t('Stop')}
                                onClick={() => {
                                    if (activeAudioCall?.url) {
                                        URL.revokeObjectURL(activeAudioCall.url);
                                    }
                                    setActiveAudioCall(null);
                                }}
                            >
                                <XCircle className="h-4 w-4" />
                            </Button>
                        </div>
                    );
                }

                return (
                    <Button
                        variant="outline"
                        size="sm"
                        className="gap-1.5 h-8 text-xs font-medium border-slate-300 dark:border-slate-700 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-600 transition-colors"
                        onClick={() => handlePlayRecording(row)}
                    >
                        <Headphones className="h-3.5 w-3.5" />
                        {t('Play')}
                    </Button>
                );
            },
        },
    ];

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {
                    label:
                        t('PBX'),
                },
                {
                    label:
                        t(
                            'Call Reports',
                        ),
                },
            ]}
        >

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            {t('Call Reports')}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {t('Detailed call history, search, audio playback, and exports.')}
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button
                            variant="outline"
                            className="gap-2"
                            onClick={() => router.get(route('pbx.call-reports.summary'))}
                        >
                            <BarChart3 className="h-4 w-4" />
                            {t('View Call Analytics & Charts')}
                        </Button>
                    </div>
                </div>

                {/* ========================================================= */}
                {/* Standard System Table */}
                {/* ========================================================= */}

                <Card className="shadow-sm">

                    {/* Toolbar */}

                    <CardContent className="border-b bg-gray-50/50 p-6 space-y-4">
                        <div className="flex items-end justify-between gap-4">

                            <div className="flex gap-2">
                                <div className="block gap-2">
                                    <label className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        {t('Period')}:
                                    </label>
                                    <div className="flex flex-wrap items-center gap-1 rounded-lg border border-slate-200/60 bg-slate-100 p-1 dark:border-slate-800 dark:bg-slate-800/60">
                                        {PERIOD_OPTIONS.map((opt) => (
                                            <Button
                                                key={opt.value}
                                                type="button"
                                                size="sm"
                                                variant={filters.period === opt.value ? 'default' : 'ghost'}
                                                onClick={() => handlePeriodChange(opt.value)}
                                                disabled={loading}
                                                className={
                                                    filters.period === opt.value
                                                        ? 'h-7 bg-blue-600 px-3 text-xs text-white shadow-xs hover:bg-blue-700'
                                                        : 'h-7 px-3 text-xs text-slate-600 hover:text-slate-900 dark:text-slate-300'
                                                }
                                            >
                                                {t(opt.label)}
                                            </Button>
                                        ))}
                                    </div>
                                </div>

                                <div className="flex flex-wrap items-end gap-2">
                                    <div className="flex items-center gap-1 rounded-lg border border-slate-200/60 bg-slate-100 p-1 dark:border-slate-800 dark:bg-slate-800/60">
                                        <Search className="ml-1.5 h-3.5 w-3.5 shrink-0 text-slate-400" />
                                        <input
                                            type="text"
                                            value={filters.search || ''}
                                            onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                                            onKeyDown={(e) => {
                                                if (e.key === 'Enter') {
                                                    e.preventDefault();
                                                    handleFilter();
                                                }
                                            }}
                                            className="h-7 w-64 md:w-80 border-none bg-transparent px-2 text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none dark:text-slate-100"
                                            placeholder={t('search by caller id, called id or agent name')}
                                        />
                                        {filters.search && (
                                            <button
                                                type="button"
                                                onClick={handleClearSearch}
                                                className="flex h-5 w-5 items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-200 transition-colors"
                                                title={t('Clear search')}
                                            >
                                                <X className="h-3 w-3" />
                                            </button>
                                        )}
                                        <Button
                                            type="button"
                                            size="sm"
                                            onClick={handleFilter}
                                            disabled={loading}
                                            className="h-7 bg-blue-600 px-3 text-xs text-white shadow-xs hover:bg-blue-700"
                                        >
                                            {t('Search')}
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div className="flex items-center gap-3">

                                <PerPageSelector
                                    routeName="pbx.call-reports.index"
                                    filters={buildRequestFilters()}
                                />

                                <div className="relative">

                                    <FilterButton
                                        showFilters={
                                            showFilters
                                        }
                                        onToggle={() =>
                                            setShowFilters(
                                                !showFilters,
                                            )
                                        }
                                    />

                                    {activeFilters >
                                        0 && (

                                            <span className="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-xs font-medium text-primary-foreground">

                                                {
                                                    activeFilters
                                                }

                                            </span>

                                        )}

                                </div>

                            </div>

                        </div>

                    </CardContent>

                    {/* Filters */}

                    {showFilters && (

                        <CardContent className="border-b bg-blue-50/30 p-6">

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">

                                {/* Period */}

                                <div>

                                    <label className="mb-2 block text-sm font-medium text-gray-700">
                                        {t('Period')}
                                    </label>

                                    <Select
                                        value={
                                            filters.period
                                            || 'today'
                                        }
                                        onValueChange={(
                                            value,
                                        ) => {
                                            if (value === 'custom') {
                                                setFilters({
                                                    ...filters,
                                                    period: 'custom',
                                                });
                                            } else {
                                                setFilters({
                                                    ...filters,
                                                    period: value,
                                                    date_range: '',
                                                    from: '',
                                                    to: '',
                                                });
                                            }
                                        }}
                                    >

                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder={t(
                                                    'Today',
                                                )}
                                            />
                                        </SelectTrigger>

                                        <SelectContent>

                                            {PERIOD_OPTIONS.map((opt) => (
                                                <SelectItem key={opt.value} value={opt.value}>
                                                    {t(opt.label)}
                                                </SelectItem>
                                            ))}

                                            {filters.period === 'custom' && (
                                                <SelectItem value="custom">
                                                    {t('Custom')}
                                                </SelectItem>
                                            )}

                                        </SelectContent>

                                    </Select>

                                </div>

                                {/* Extension */}

                                <div>

                                    <label className="mb-2 block text-sm font-medium text-gray-700">

                                        {callReportPermissions.view_all
                                            ? t(
                                                'User / Extension',
                                            )
                                            : t(
                                                'My Extension',
                                            )}

                                    </label>

                                    <Select
                                        value={
                                            filters.extension
                                            || 'all'
                                        }
                                        onValueChange={(
                                            value,
                                        ) =>
                                            setFilters(
                                                {
                                                    ...filters,

                                                    extension:
                                                        value ===
                                                            'all'
                                                            ? ''
                                                            : value,
                                                },
                                            )
                                        }
                                    >

                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder={t(
                                                    'All Extensions',
                                                )}
                                            />
                                        </SelectTrigger>

                                        <SelectContent searchable>

                                            <SelectItem value="all">
                                                {callReportPermissions.view_all
                                                    ? t(
                                                        'All Extensions',
                                                    )
                                                    : t(
                                                        'All My Extensions',
                                                    )}
                                            </SelectItem>

                                            {extensions.map(
                                                (
                                                    item,
                                                ) => (

                                                    <SelectItem
                                                        key={
                                                            item.id
                                                        }
                                                        value={
                                                            item.extension
                                                        }
                                                    >
                                                        {item.user_name
                                                            ? `${item.user_name} (${item.extension})`
                                                            : item.extension}
                                                    </SelectItem>

                                                ),
                                            )}

                                        </SelectContent>

                                    </Select>

                                </div>

                                {/* Direction */}

                                <div>

                                    <label className="mb-2 block text-sm font-medium text-gray-700">
                                        {t(
                                            'Direction',
                                        )}
                                    </label>

                                    <Select
                                        value={
                                            filters.call_direction
                                            || 'all'
                                        }
                                        onValueChange={(
                                            value,
                                        ) =>
                                            setFilters(
                                                {
                                                    ...filters,

                                                    call_direction:
                                                        value ===
                                                            'all'
                                                            ? ''
                                                            : value,
                                                },
                                            )
                                        }
                                    >

                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder={t(
                                                    'All Directions',
                                                )}
                                            />
                                        </SelectTrigger>

                                        <SelectContent>

                                            <SelectItem value="all">
                                                {t(
                                                    'All Directions',
                                                )}
                                            </SelectItem>

                                            <SelectItem value="inbound">
                                                {t(
                                                    'Incoming',
                                                )}
                                            </SelectItem>

                                            <SelectItem value="outbound">
                                                {t(
                                                    'Outgoing',
                                                )}
                                            </SelectItem>

                                        </SelectContent>

                                    </Select>

                                </div>

                                {/* Status */}

                                <div>

                                    <label className="mb-2 block text-sm font-medium text-gray-700">
                                        {t(
                                            'Status',
                                        )}
                                    </label>

                                    <Select
                                        value={
                                            filters.status
                                            || 'all'
                                        }
                                        onValueChange={(
                                            value,
                                        ) =>
                                            setFilters(
                                                {
                                                    ...filters,

                                                    status:
                                                        value ===
                                                            'all'
                                                            ? ''
                                                            : value,
                                                },
                                            )
                                        }
                                    >

                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder={t(
                                                    'All Statuses',
                                                )}
                                            />
                                        </SelectTrigger>

                                        <SelectContent>

                                            <SelectItem value="all">
                                                {t(
                                                    'All Statuses',
                                                )}
                                            </SelectItem>

                                            {statusOptions.map(
                                                (
                                                    status,
                                                ) => (

                                                    <SelectItem
                                                        key={
                                                            status
                                                        }
                                                        value={
                                                            status
                                                        }
                                                    >
                                                        {
                                                            status
                                                        }
                                                    </SelectItem>

                                                ),
                                            )}

                                        </SelectContent>

                                    </Select>

                                </div>

                                {/* Date */}

                                <div>

                                    <label className="mb-2 block text-sm font-medium text-gray-700">
                                        {t(
                                            'Call Date Range',
                                        )}
                                    </label>

                                    <DateRangePicker
                                        value={
                                            filters.date_range
                                        }
                                        onChange={(
                                            value,
                                        ) =>
                                            setFilters(
                                                {
                                                    ...filters,
                                                    period: 'custom',
                                                    date_range:
                                                        value,
                                                },
                                            )
                                        }
                                        placeholder={t(
                                            'Select date range',
                                        )}
                                    />

                                </div>

                            </div>

                            <div className="mt-4 flex gap-2">

                                <Button
                                    size="sm"
                                    onClick={
                                        handleFilter
                                    }
                                    disabled={
                                        loading
                                    }
                                >
                                    {loading && (
                                        <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                                    )}

                                    {t(
                                        'Apply',
                                    )}
                                </Button>

                                <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={
                                        clearFilters
                                    }
                                    disabled={
                                        loading
                                    }
                                >
                                    {t(
                                        'Clear',
                                    )}
                                </Button>

                            </div>

                        </CardContent>

                    )}

                    {/* Table */}

                    <CardContent className="p-0">

                        <div className="max-h-[70vh] w-full overflow-y-auto rounded-none scrollbar-thin scrollbar-track-gray-100 scrollbar-thumb-gray-400">

                            <div className="min-w-[1200px]">

                                <DataTable
                                    data={
                                        calls?.data
                                        || []
                                    }
                                    columns={
                                        tableColumns
                                    }
                                    className="rounded-none"
                                    emptyState={
                                        <NoRecordsFound
                                            icon={
                                                Phone
                                            }
                                            title={t(
                                                'No call logs found',
                                            )}
                                            description={t(
                                                'No calls match the selected filters.',
                                            )}
                                            hasFilters={
                                                !!(
                                                    filters.search
                                                    || filters.extension
                                                    || filters.call_direction
                                                    || filters.status
                                                    || filters.date_range
                                                )
                                            }
                                            onClearFilters={
                                                clearFilters
                                            }
                                            className="h-auto"
                                        />
                                    }
                                />

                            </div>

                        </div>

                    </CardContent>

                    {/* Pagination */}

                    <CardContent className="border-t bg-gray-50/30 px-4 py-2">

                        <Pagination
                            data={
                                calls || {
                                    data: [],
                                    links: [],
                                    meta: {},
                                }
                            }
                            routeName="pbx.call-reports.index"
                            filters={
                                buildRequestFilters()
                            }
                        />

                    </CardContent>

                </Card>

            </div>

        </AuthenticatedLayout>
    );
}