import { useMemo, useState, useEffect, useCallback } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import axios from 'axios';
import {
    Activity,
    ArrowDownRight,
    ArrowUpRight,
    BarChart3,
    Bell,
    CheckCircle2,
    ChevronRight,
    Clock,
    Download,
    FileText,
    Filter,
    Headphones,
    Info,
    MoreVertical,
    Percent,
    Phone,
    PhoneCall,
    PhoneIncoming,
    PhoneMissed,
    PhoneOff,
    PhoneOutgoing,
    Radio,
    RefreshCw,
    TrendingUp,
    Users,
    Zap,
} from 'lucide-react';
import {
    AreaChart,
    Area,
    BarChart as RechartsBarChart,
    Bar,
    Cell,
    CartesianGrid,
    Legend,
    PieChart as RechartsPieChart,
    Pie,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Switch } from '@/components/ui/switch';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Progress } from '@/components/ui/progress';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Extension {
    id: number;
    extension: string;
    user_id: number | null;
    user_name: string | null;
}

export interface DurationBrackets {
    under_30s: number;
    from_30s_to_1m: number;
    from_1m_to_5m: number;
    from_5m_to_15m: number;
    over_15m: number;
}

export interface CallSummary {
    total_calls: number;
    answered_calls: number;
    no_answer_calls: number;
    busy_calls: number;
    failed_calls: number;
    congestion_calls: number;
    answer_rate: number;
    miss_rate: number;

    inbound_calls: number;
    inbound_answered: number;
    inbound_missed: number;
    inbound_failed: number;
    inbound_answer_rate: number;
    inbound_duration: number;
    inbound_talk_time: number;
    inbound_ring_time: number;
    inbound_avg_duration: number;
    inbound_avg_talk_time: number;
    inbound_avg_ring_time: number;

    outbound_calls: number;
    outbound_answered: number;
    outbound_unanswered: number;
    outbound_failed: number;
    outbound_answer_rate: number;
    outbound_duration: number;
    outbound_talk_time: number;
    outbound_ring_time: number;
    outbound_avg_duration: number;
    outbound_avg_talk_time: number;
    outbound_avg_ring_time: number;

    total_duration: number;
    total_talk_time: number;
    total_ring_time: number;
    average_duration: number;
    average_talk_time: number;

    duration_brackets: DurationBrackets;

    recording_count: number;

    // Optional legacy aliases for backwards compatibility
    totalCalls?: number;
    incoming?: number;
    outgoing?: number;
    totalDuration?: number;
    totalTalkTime?: number;
    totalRingTime?: number;
    answered?: number;
    noAnswer?: number;
    rejected?: number;
    otherStatuses?: number;
    avgDuration?: number;
    avgTalkTime?: number;
    avgRingTime?: number;
    answerRate?: number;
}

export interface DailySummary {
    date: string;
    total: number;
    answered: number;
    missed: number;
    inbound?: number;
    outbound?: number;
}

export interface HourlySummary {
    hour: number;
    time?: string;
    total: number;
    answered: number;
    answerRate?: number;
    inbound?: number;
    outbound?: number;
}

export interface ExtensionSummary {
    extension: string;
    user_name?: string;
    total: number;
    answered: number;
    missed: number;
    inbound: number;
    outbound: number;
    talk_time: number;
}

export interface CallLogRow {
    calldate: string;
    extension: string;
    direction: 'inbound' | 'outbound';
    number: string | null;
    did: string | null;
    status: 'ANSWERED' | 'NO ANSWER' | 'BUSY' | 'FAILED' | 'CONGESTION' | string;
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

export interface Pagination {
    page: number;
    per_page: number;
    total: number;
    last_page: number;
}

type Summary = CallSummary;

interface ChartItem {
    name: string;
    value: number;
    color: string;
}

interface ExtensionChartItem {
    extension: string;
    total: number;
    answered: number;
    duration: number;
}

interface TrendChartItem {
    date: string;
    total: number;
    answered: number;
    missed: number;
}

interface HourlyTrendItem {
    hour?: number;
    time: string;
    total: number;
    answered: number;
    answerRate: number;
    isPeak?: boolean;
    isPeakAnswer?: boolean;
}

interface DayOfWeekTrendItem {
    day: string;
    short: string;
    total: number;
    answered: number;
    answerRate: number;
}

interface PeaksData {
    peakHour?: string | null;
    peakHourVolume?: number;
    peakAnswerHour?: string | null;
    peakHourAnswerRate?: number;
    peakDayOfWeek?: string | null;
    peakDayVolume?: number;
    peakAnswerDayOfWeek?: string | null;
    peakDayAnswerRate?: number;
}

interface LiveStatus {
    online: number;
    onlinePercent: number;
    onCall: number;
    onCallPercent: number;
    ringing: number;
    ringingPercent: number;
    offline: number;
    offlinePercent: number;
    activeExtensions: number;
    totalExtensions: number;
    utilizationPercent: number;
}

interface ExtensionPerformanceItem {
    extension_id?: number;
    extension: string;
    user_name: string;
    department?: string;
    avatar?: string;
    totalCalls: number;
    answeredCalls: number;
    answerRate: number;
    avgTalkTime: number;
    missed: number;
    missedPercent: number;
    failed: number;
    failedPercent: number;
    status: 'online' | 'onCall' | 'offline' | 'available' | 'ringing' | 'on_call' | 'unknown';
}

interface ChartsData {
    direction: ChartItem[];
    status: ChartItem[];
    extensions: ExtensionChartItem[];
    trend: TrendChartItem[];
    hourlyTrend?: HourlyTrendItem[];
    dayOfWeekTrend?: DayOfWeekTrendItem[];
    extensionPerformance?: ExtensionPerformanceItem[];
    liveStatus?: LiveStatus;
    peaks?: PeaksData;
    durationBrackets?: DurationBrackets;
}

interface CallReportPermissions {
    view_all: boolean;
    view_own: boolean;
}

interface SummaryProps {
    summary: Summary;
    charts: ChartsData;
    extensions: Extension[];
    filters: {
        extension?: string;
        period?: string;
        from?: string;
        to?: string;
        date_range?: string;
    };
    callReportPermissions: CallReportPermissions;
}

// Mini Sparkline SVG helper
const Sparkline = ({ color = '#3b82f6', id = '1' }: { color?: string; id?: string }) => {
    const strokeColor = color;
    const gradientId = `sparkline-grad-${id}`;

    return (
        <div className="h-7 w-full max-w-[140px] opacity-85">
            <svg viewBox="0 0 150 30" className="h-full w-full overflow-visible">
                <defs>
                    <linearGradient id={gradientId} x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor={strokeColor} stopOpacity={0.25} />
                        <stop offset="100%" stopColor={strokeColor} stopOpacity={0.0} />
                    </linearGradient>
                </defs>
                <path
                    d="M0 22 Q 18 10, 36 18 T 72 8 T 108 20 T 150 12 L 150 30 L 0 30 Z"
                    fill={`url(#${gradientId})`}
                />
                <path
                    d="M0 22 Q 18 10, 36 18 T 72 8 T 108 20 T 150 12"
                    fill="none"
                    stroke={strokeColor}
                    strokeWidth="2"
                    strokeLinecap="round"
                />
            </svg>
        </div>
    );
};

export default function SummaryIndex({
    summary,
    charts,
    extensions = [],
    filters,
    callReportPermissions,
}: SummaryProps) {
    const { t } = useTranslation();

    const [selectedPeriod, setSelectedPeriod] = useState<string>(
        filters?.period || 'today'
    );
    const [selectedExtension, setSelectedExtension] = useState<string>(
        filters?.extension || ''
    );
    const [dateRange, setDateRange] = useState<string>(
        filters?.date_range || ''
    );
    const [comparePrevious, setComparePrevious] = useState<boolean>(true);
    const [timeGrain, setTimeGrain] = useState<string>('hourly');
    const [isRefreshing, setIsRefreshing] = useState<boolean>(false);

    const formatDuration = (seconds: number | undefined): string => {
        const val = Math.max(0, Math.round(Number(seconds || 0)));
        if (val === 0) return '0s';
        const mins = Math.floor(val / 60);
        const secs = val % 60;
        if (mins === 0) return `${secs}s`;
        const hours = Math.floor(mins / 60);
        const remMins = mins % 60;
        if (hours === 0) return `${mins}m ${secs}s`;
        return `${hours}h ${remMins}m ${secs}s`;
    };

    useEffect(() => {
        setSelectedPeriod(filters?.period || 'today');
        setSelectedExtension(filters?.extension || '');
        setDateRange(filters?.date_range || '');
    }, [filters?.period, filters?.extension, filters?.date_range]);

    const handlePeriodChange = (newPeriod: string) => {
        setSelectedPeriod(newPeriod);
        setDateRange('');
        setIsRefreshing(true);
        router.get(
            route('pbx.call-reports.summary'),
            {
                period: newPeriod,
                extension: selectedExtension || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setIsRefreshing(false),
            }
        );
    };

    const handleFilterChange = () => {
        setIsRefreshing(true);
        router.get(
            route('pbx.call-reports.summary'),
            {
                period: selectedPeriod,
                extension: selectedExtension || undefined,
                date_range: dateRange || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setIsRefreshing(false),
            }
        );
    };

    const handleReset = () => {
        setSelectedPeriod('today');
        setSelectedExtension('');
        setDateRange('');
        setIsRefreshing(true);
        router.get(
            route('pbx.call-reports.summary'),
            { period: 'today' },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setIsRefreshing(false),
            }
        );
    };

    const calcPercent = (value: number, total: number) => {
        if (!total || total <= 0) return '0%';
        return `${((value / total) * 100).toFixed(1)}%`;
    };

    // Real chart datasets from backend summary
    const hourlyData: HourlyTrendItem[] = useMemo(() => {
        if (charts?.hourlyTrend && charts.hourlyTrend.length > 0) {
            return charts.hourlyTrend;
        }
        const default24Hours: HourlyTrendItem[] = [];
        for (let h = 0; h < 24; h++) {
            const ampm = h >= 12 ? 'PM' : 'AM';
            const displayH = h % 12 === 0 ? 12 : h % 12;
            default24Hours.push({
                hour: h,
                time: `${displayH} ${ampm}`,
                total: 0,
                answered: 0,
                answerRate: 0,
            });
        }
        return default24Hours;
    }, [charts?.hourlyTrend]);

    const [liveStatusesMap, setLiveStatusesMap] = useState<Record<string | number, any>>({});
    const [liveStatusData, setLiveStatusData] = useState<LiveStatus>({
        online: 0,
        onlinePercent: 0,
        onCall: 0,
        onCallPercent: 0,
        ringing: 0,
        ringingPercent: 0,
        offline: 0,
        offlinePercent: 0,
        activeExtensions: 0,
        totalExtensions: extensions ? extensions.length : 0,
        utilizationPercent: 0,
    });
    const [isLiveStatusLoading, setIsLiveStatusLoading] = useState<boolean>(true);

    const fetchLiveStatus = useCallback(async () => {
        try {
            const response = await axios.get(route('pbx.extensions.live-status'));
            if (response.data?.success && response.data?.extensions) {
                const extMap = response.data.extensions as Record<string, any>;
                setLiveStatusesMap(extMap);
                const extList = Object.values(extMap);
                const total = extList.length || (extensions ? extensions.length : 0);

                let availableCount = 0;
                let onCallCount = 0;
                let ringingCount = 0;
                let offlineCount = 0;
                let activeCount = 0;

                extList.forEach((item) => {
                    const status = item.status || 'unknown';
                    if (status === 'available') {
                        availableCount++;
                        activeCount++;
                    } else if (status === 'on_call') {
                        onCallCount++;
                        activeCount++;
                    } else if (status === 'ringing') {
                        ringingCount++;
                        activeCount++;
                    } else if (status === 'offline') {
                        offlineCount++;
                    }
                });

                const safeTotal = total > 0 ? total : 1;
                const onlinePct = Math.round((availableCount / safeTotal) * 100);
                const onCallPct = Math.round((onCallCount / safeTotal) * 100);
                const ringingPct = Math.round((ringingCount / safeTotal) * 100);
                const offlinePct = Math.round((offlineCount / safeTotal) * 100);
                const utilizationPct = Math.round((activeCount / safeTotal) * 100);

                setLiveStatusData({
                    online: availableCount,
                    onlinePercent: onlinePct,
                    onCall: onCallCount,
                    onCallPercent: onCallPct,
                    ringing: ringingCount,
                    ringingPercent: ringingPct,
                    offline: offlineCount,
                    offlinePercent: offlinePct,
                    activeExtensions: activeCount,
                    totalExtensions: total,
                    utilizationPercent: utilizationPct,
                });
            }
        } catch (err) {
            console.error('Error fetching live extension status:', err);
        } finally {
            setIsLiveStatusLoading(false);
        }
    }, [extensions]);

    useEffect(() => {
        let isMounted = true;
        let timeoutId: ReturnType<typeof setTimeout> | null = null;

        const runPoll = async () => {
            if (!isMounted) return;
            await fetchLiveStatus();
            if (isMounted && document.visibilityState === 'visible') {
                timeoutId = setTimeout(runPoll, 10000);
            }
        };

        runPoll();

        const handleVisibilityChange = () => {
            if (document.visibilityState === 'visible') {
                if (timeoutId) clearTimeout(timeoutId);
                runPoll();
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);

        return () => {
            isMounted = false;
            if (timeoutId) clearTimeout(timeoutId);
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        };
    }, [fetchLiveStatus]);

    const extensionPerformanceList: ExtensionPerformanceItem[] = useMemo(() => {
        const extUserMap = new Map<string, string>();
        const extAvatarMap = new Map<string, string>();
        const extIdMap = new Map<string, number>();

        extensions.forEach((ext) => {
            if (ext.extension) {
                const extStr = String(ext.extension);
                if (ext.user_name) extUserMap.set(extStr, ext.user_name);
                if ((ext as any).avatar) extAvatarMap.set(extStr, (ext as any).avatar);
                if (ext.id) extIdMap.set(extStr, ext.id);
            }
        });

        let list: ExtensionPerformanceItem[] = [];

        if (charts?.extensionPerformance && charts.extensionPerformance.length > 0) {
            list = charts.extensionPerformance.map((item) => {
                const extStr = String(item.extension);
                const matchedName = extUserMap.get(extStr);
                const matchedAvatar = extAvatarMap.get(extStr);
                const matchedId = extIdMap.get(extStr);

                return {
                    ...item,
                    extension_id: matchedId,
                    user_name: matchedName || item.user_name || `Extension ${item.extension}`,
                    avatar: item.avatar || matchedAvatar || '',
                };
            });
        } else if (extensions && extensions.length > 0) {
            list = extensions.map((ext) => ({
                extension_id: ext.id,
                extension: String(ext.extension),
                user_name: ext.user_name || `Extension ${ext.extension}`,
                department: 'Team Member',
                avatar: (ext as any).avatar || '',
                totalCalls: 0,
                answeredCalls: 0,
                answerRate: 0,
                avgTalkTime: 0,
                missed: 0,
                missedPercent: 0,
                failed: 0,
                failedPercent: 0,
                status: 'offline',
            }));
        }

        const activeFilterExt = (filters?.extension || selectedExtension || '').trim();
        if (activeFilterExt && activeFilterExt !== 'all') {
            list = list.filter((item) => String(item.extension) === activeFilterExt);
        }

        return list;
    }, [charts?.extensionPerformance, extensions, filters?.extension, selectedExtension]);

    const renderExtensionLiveBadge = (row: ExtensionPerformanceItem) => {
        const statusObj = (row.extension_id ? liveStatusesMap[row.extension_id] : null) || liveStatusesMap[String(row.extension)];
        const status = statusObj?.status || 'unknown';

        if (isLiveStatusLoading && !statusObj) {
            return (
                <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-slate-400" />
                    {t('Checking...')}
                </span>
            );
        }

        switch (status) {
            case 'available':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400 dark:ring-emerald-500/30">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        {t('Available')}
                    </span>
                );

            case 'ringing':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-400 dark:ring-amber-500/30">
                        <span className="h-1.5 w-1.5 animate-ping rounded-full bg-amber-500" />
                        {t('Ringing')}
                    </span>
                );

            case 'on_call':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-500/30">
                        <span className="h-1.5 w-1.5 rounded-full bg-blue-500" />
                        {t('On Call')}
                    </span>
                );

            case 'offline':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-700 ring-1 ring-inset ring-slate-500/20 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700">
                        <span className="h-1.5 w-1.5 rounded-full bg-slate-500" />
                        {t('Offline')}
                    </span>
                );

            case 'unknown':
            default:
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-gray-500 ring-1 ring-inset ring-gray-400/20 dark:bg-gray-800 dark:text-gray-400">
                        <span className="h-1.5 w-1.5 rounded-full bg-gray-400" />
                        {t('Unknown')}
                    </span>
                );
        }
    };

    // KPI Values directly from real summary metrics
    const displayTotalCalls = summary?.total_calls ?? 0;
    const displayAnswered = summary?.answered_calls ?? 0;
    const displayNoAnswer = summary?.no_answer_calls ?? 0;
    const displayBusy = summary?.busy_calls ?? 0;
    const displayFailedOnly = summary?.failed_calls ?? 0;
    const displayCongestion = summary?.congestion_calls ?? 0;
    const displayFailed = displayBusy + displayFailedOnly + displayCongestion;

    const displayTotalDuration = summary?.total_duration ?? 0;
    const displayTotalTalkTime = summary?.total_talk_time ?? 0;
    const displayTotalRingTime = summary?.total_ring_time ?? 0;

    // Inbound (Incoming) Call Metrics
    const displayInboundCalls = summary?.inbound_calls ?? 0;
    const displayInboundAnswered = summary?.inbound_answered ?? 0;
    const displayInboundMissed = summary?.inbound_missed ?? 0;
    const displayInboundFailed = summary?.inbound_failed ?? 0;
    const displayInboundAnswerRate = summary?.inbound_answer_rate ?? 0;
    const displayInboundMissRate = displayInboundCalls > 0 ? Number(((displayInboundMissed / displayInboundCalls) * 100).toFixed(1)) : 0;
    const displayInboundDuration = summary?.inbound_duration ?? 0;
    const displayInboundTalkTime = summary?.inbound_talk_time ?? 0;
    const displayInboundRingTime = summary?.inbound_ring_time ?? 0;
    const displayInboundAvgTalkTime = summary?.inbound_avg_talk_time ?? 0;
    const displayInboundAvgRingTime = summary?.inbound_avg_ring_time ?? 0;

    // Outbound (Outgoing) Call Metrics
    const displayOutboundCalls = summary?.outbound_calls ?? 0;
    const displayOutboundAnswered = summary?.outbound_answered ?? 0;
    const displayOutboundUnanswered = summary?.outbound_unanswered ?? 0;
    const displayOutboundFailed = summary?.outbound_failed ?? 0;
    const displayOutboundAnswerRate = summary?.outbound_answer_rate ?? 0;
    const displayOutboundUnansweredRate = displayOutboundCalls > 0 ? Number(((displayOutboundUnanswered / displayOutboundCalls) * 100).toFixed(1)) : 0;
    const displayOutboundDuration = summary?.outbound_duration ?? 0;
    const displayOutboundTalkTime = summary?.outbound_talk_time ?? 0;
    const displayOutboundRingTime = summary?.outbound_ring_time ?? 0;
    const displayOutboundAvgTalkTime = summary?.outbound_avg_talk_time ?? 0;

    // Duration brackets classification
    const durationBracketsData = summary?.duration_brackets || charts?.durationBrackets || {
        under_30s: 0,
        from_30s_to_1m: 0,
        from_1m_to_5m: 0,
        from_5m_to_15m: 0,
        over_15m: 0,
    };
    const totalBracketCalls = (
        (durationBracketsData.under_30s || 0) +
        (durationBracketsData.from_30s_to_1m || 0) +
        (durationBracketsData.from_1m_to_5m || 0) +
        (durationBracketsData.from_5m_to_15m || 0) +
        (durationBracketsData.over_15m || 0)
    ) || (displayAnswered > 0 ? displayAnswered : 1);

    const durationTiers = [
        { key: 'under_30s', label: '< 30s', tag: t('Brief / Drop'), count: durationBracketsData.under_30s || 0, color: '#94a3b8', bg: 'bg-slate-400' },
        { key: 'from_30s_to_1m', label: '30s - 1m', tag: t('Short Inquiry'), count: durationBracketsData.from_30s_to_1m || 0, color: '#f59e0b', bg: 'bg-amber-500' },
        { key: 'from_1m_to_5m', label: '1m - 5m', tag: t('Standard Call'), count: durationBracketsData.from_1m_to_5m || 0, color: '#3b82f6', bg: 'bg-blue-500' },
        { key: 'from_5m_to_15m', label: '5m - 15m', tag: t('Detailed Support'), count: durationBracketsData.from_5m_to_15m || 0, color: '#10b981', bg: 'bg-emerald-500' },
        { key: 'over_15m', label: '> 15m', tag: t('Extended Call'), count: durationBracketsData.over_15m || 0, color: '#8b5cf6', bg: 'bg-purple-500' },
    ];

    const dispositionData: ChartItem[] = useMemo(() => {
        if (charts?.status && charts.status.length > 0) {
            const hasData = charts.status.some((item) => item.value > 0);
            if (hasData) {
                return charts.status.filter((item) => item.value > 0);
            }
        }

        const data: ChartItem[] = [
            { name: t('Answered'), value: displayAnswered, color: '#10b981' },
            { name: t('No Answer'), value: displayNoAnswer, color: '#f59e0b' },
            { name: t('Busy'), value: displayBusy, color: '#f97316' },
            { name: t('Failed'), value: displayFailedOnly, color: '#f43f5e' },
            { name: t('Congestion'), value: displayCongestion, color: '#6b7280' },
        ];

        const totalVal = data.reduce((acc, curr) => acc + curr.value, 0);
        if (totalVal === 0) {
            return [{ name: t('No Calls'), value: 1, color: '#e2e8f0' }];
        }
        return data.filter((item) => item.value > 0);
    }, [charts?.status, displayAnswered, displayNoAnswer, displayBusy, displayFailedOnly, displayCongestion, t]);

    const displayAvgDuration = summary?.average_duration ?? 0;
    const displayAvgTalkTime = summary?.average_talk_time ?? 0;
    const displayAvgRingTime = displayTotalCalls > 0 ? Math.round(displayTotalRingTime / displayTotalCalls) : 0;

    const answerRateVal = summary?.answer_rate ?? 0;
    const noAnswerRateVal = summary?.miss_rate ?? 0;
    const failedRateVal = displayTotalCalls > 0 ? Number(((displayFailed / displayTotalCalls) * 100).toFixed(1)) : 0;

    const quickInsights = useMemo(() => {
        const peaks = charts?.peaks;
        const peakVolHourText = peaks?.peakHour
            ? `${peaks.peakHour} (${peaks.peakHourVolume || 0} calls)`
            : (hourlyData.reduce((prev, curr) => (curr.total > prev.total ? curr : prev), hourlyData[0])?.total > 0
                ? `${hourlyData.reduce((prev, curr) => (curr.total > prev.total ? curr : prev), hourlyData[0]).time} (${hourlyData.reduce((prev, curr) => (curr.total > prev.total ? curr : prev), hourlyData[0]).total} calls)`
                : 'Traffic Pattern Normal');

        const peakAnsHourText = peaks?.peakAnswerHour
            ? `${peaks.peakAnswerHour} (${peaks.peakHourAnswerRate || 0}% answer rate)`
            : (hourlyData.reduce((prev, curr) => (curr.answerRate > prev.answerRate && curr.total > 0 ? curr : prev), hourlyData[0])?.answerRate > 0
                ? `${hourlyData.reduce((prev, curr) => (curr.answerRate > prev.answerRate && curr.total > 0 ? curr : prev), hourlyData[0]).time} (${hourlyData.reduce((prev, curr) => (curr.answerRate > prev.answerRate && curr.total > 0 ? curr : prev), hourlyData[0]).answerRate}%)`
                : 'N/A');

        const peakDayText = peaks?.peakDayOfWeek
            ? `${peaks.peakDayOfWeek} (${peaks.peakDayVolume || 0} calls)`
            : (charts?.trend && charts.trend.length > 0
                ? `${charts.trend.reduce((prev, curr) => (curr.total > prev.total ? curr : prev), charts.trend[0]).date} (${charts.trend.reduce((prev, curr) => (curr.total > prev.total ? curr : prev), charts.trend[0]).total} calls)`
                : 'N/A');

        const topPerf = extensionPerformanceList.reduce((prev, curr) => (curr.answeredCalls > prev.answeredCalls ? curr : prev), extensionPerformanceList[0] || { user_name: '', extension: '', totalCalls: 0, answeredCalls: 0, answerRate: 0 });

        return [
            {
                title: t('Peak Call Hour'),
                subtitle: peakVolHourText,
                IconComponent: Clock,
                bgClass: 'bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400',
            },
            {
                title: t('Peak Answer Rate'),
                subtitle: peakAnsHourText,
                IconComponent: CheckCircle2,
                bgClass: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400',
            },
            {
                title: t('Peak Call Day'),
                subtitle: peakDayText,
                IconComponent: TrendingUp,
                bgClass: 'bg-purple-100 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400',
            },
            {
                title: topPerf && topPerf.totalCalls > 0 ? `${topPerf.user_name || 'Agent'} (${t('Top Agent')})` : t('Top Performer'),
                subtitle: topPerf && topPerf.totalCalls > 0 ? `${topPerf.answeredCalls} answered call(s) (${topPerf.answerRate}% rate)` : t('Agent activity normal'),
                IconComponent: Users,
                bgClass: 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400',
            },
        ];
    }, [hourlyData, extensionPerformanceList, charts?.peaks, charts?.trend, t]);

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Dashboard') },
                { label: t('PBX') },
                { label: t('Call Summary & Analytics') },
            ]}
        >
            <Head title={t('PBX Call Analytics')} />

            <div className="space-y-6 bg-slate-50/50 dark:bg-slate-950/20 min-h-screen">
                {/* Header Title Section */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl">
                            {t('PBX Call Analytics')}
                        </h1>
                        <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {t('Comprehensive insights into call performance and agent productivity.')}
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Button
                            variant="outline"
                            className="gap-2 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-sm"
                            onClick={() => router.get(route('pbx.call-reports.index'))}
                        >
                            <Radio className="h-4 w-4 text-emerald-500 animate-pulse" />
                            {t('Live Monitor')}
                        </Button>
                        <Button
                            variant="outline"
                            className="gap-2 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-sm"
                            onClick={() => router.get(route('pbx.call-reports.index'))}
                        >
                            <FileText className="h-4 w-4 text-slate-600 dark:text-slate-400" />
                            {t('Call Logs')}
                        </Button>
                        <Button className="gap-2 bg-blue-600 hover:bg-blue-700 text-white shadow-sm">
                            <Download className="h-4 w-4" />
                            {t('Export Report')}
                        </Button>
                    </div>
                </div>

                {/* Filter Control Bar */}
                <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
                    <CardContent className="p-4 sm:p-5 space-y-4">
                        {/* Quick Period Buttons */}
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-slate-100 dark:border-slate-800/80">
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {t('Period')}:
                                </label>
                                <div className="p-1 rounded-lg bg-slate-100 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800 gap-1">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={selectedPeriod === 'today' ? 'default' : 'ghost'}
                                        onClick={() => handlePeriodChange('today')}
                                        disabled={isRefreshing}
                                        className={selectedPeriod === 'today' ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs text-xs h-7 px-3' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 text-xs h-7 px-3'}
                                    >
                                        {t('Today')}
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={selectedPeriod === 'this_week' ? 'default' : 'ghost'}
                                        onClick={() => handlePeriodChange('this_week')}
                                        disabled={isRefreshing}
                                        className={selectedPeriod === 'this_week' ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs text-xs h-7 px-3' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 text-xs h-7 px-3'}
                                    >
                                        {t('This Week')}
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={selectedPeriod === 'this_month' ? 'default' : 'ghost'}
                                        onClick={() => handlePeriodChange('this_month')}
                                        disabled={isRefreshing}
                                        className={selectedPeriod === 'this_month' ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs text-xs h-7 px-3' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 text-xs h-7 px-3'}
                                    >
                                        {t('This Month')}
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={selectedPeriod === 'previous_month' ? 'default' : 'ghost'}
                                        onClick={() => handlePeriodChange('previous_month')}
                                        disabled={isRefreshing}
                                        className={selectedPeriod === 'previous_month' ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs text-xs h-7 px-3' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 text-xs h-7 px-3'}
                                    >
                                        {t('Previous Month')}
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={selectedPeriod === 'all_time' ? 'default' : 'ghost'}
                                        onClick={() => handlePeriodChange('all_time')}
                                        disabled={isRefreshing}
                                        className={selectedPeriod === 'all_time' ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs text-xs h-7 px-3' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 text-xs h-7 px-3'}
                                    >
                                        {t('All Time')}
                                    </Button>
                                </div>
                            </div>


                            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                                <div className="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 flex-1">
                                    {/* Extension Selector */}
                                    <div className="space-y-1.5">
                                        <label className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            {callReportPermissions?.view_all ? t('User / Extension') : t('My Extension')}
                                        </label>
                                        <Select
                                            value={selectedExtension || 'all'}
                                            onValueChange={(value) =>
                                                setSelectedExtension(value === 'all' ? '' : value)
                                            }
                                        >
                                            <SelectTrigger className="w-full bg-slate-50/50 dark:bg-slate-950/40 border-slate-200 dark:border-slate-800">
                                                <SelectValue placeholder={callReportPermissions?.view_all ? t('All Extensions') : t('All My Extensions')} />
                                            </SelectTrigger>
                                            <SelectContent searchable>
                                                <SelectItem value="all">
                                                    {callReportPermissions?.view_all ? t('All Extensions') : t('All My Extensions')}
                                                </SelectItem>
                                                {extensions.map((ext) => (
                                                    <SelectItem key={ext.id} value={ext.extension}>
                                                        {ext.user_name
                                                            ? `${ext.user_name} (${ext.extension})`
                                                            : ext.extension}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    {/* Custom Date Range Input */}
                                    <div className="space-y-1.5 sm:col-span-2">
                                        <label className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            {t('Custom Date Range')}
                                        </label>
                                        <DateRangePicker
                                            value={dateRange}
                                            onChange={(value) => {
                                                setDateRange(value);
                                                if (value && (value.includes(' - ') || value.includes(' to '))) {
                                                    setSelectedPeriod('custom');
                                                    setIsRefreshing(true);
                                                    router.get(
                                                        route('pbx.call-reports.summary'),
                                                        {
                                                            period: 'custom',
                                                            extension: selectedExtension || undefined,
                                                            date_range: value,
                                                        },
                                                        {
                                                            preserveState: true,
                                                            preserveScroll: true,
                                                            onFinish: () => setIsRefreshing(false),
                                                        }
                                                    );
                                                }
                                            }}
                                            placeholder={t('YYYY-MM-DD - YYYY-MM-DD')}
                                            className="bg-slate-50/50 dark:bg-slate-950/40 border-slate-200 dark:border-slate-800"
                                        />
                                    </div>
                                </div>

                                {/* Controls Right */}
                                <div className="flex items-center gap-2 lg:pl-2">
                                    <Button
                                        variant="outline"
                                        onClick={handleReset}
                                        disabled={isRefreshing}
                                        className="border-slate-200 dark:border-slate-800"
                                    >
                                        {t('Reset')}
                                    </Button>
                                    <Button
                                        onClick={handleFilterChange}
                                        disabled={isRefreshing}
                                        className="gap-2 bg-blue-600 hover:bg-blue-700 text-white"
                                    >
                                        <Filter className="h-3.5 w-3.5" />
                                        {t('Apply Filters')}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Call Volumes & Outcomes (4 Cards with Directional Split) */}
                <div className="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-4">
                    {/* 1. Total Calls */}
                    <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 transition-all duration-200 hover:shadow">
                        <CardContent className="p-4">
                            <div className="flex items-start justify-between">
                                <div>
                                    <p className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {t('Total Calls')}
                                    </p>
                                    <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                        {displayTotalCalls}
                                    </p>
                                    <div className="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-blue-600 dark:text-blue-400">
                                        <span>{answerRateVal}% {t('Answered')}</span>
                                    </div>
                                    <div className="mt-1.5 flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span className="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-medium">
                                            {t('In')}: {displayInboundCalls}
                                        </span>
                                        <span className="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-medium">
                                            {t('Out')}: {displayOutboundCalls}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                                    <Headphones className="h-4.5 w-4.5" />
                                </div>
                            </div>
                            <div className="mt-3">
                                <Sparkline color="#3b82f6" id="1" />
                            </div>
                        </CardContent>
                    </Card>

                    {/* 2. Answered */}
                    <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 transition-all duration-200 hover:shadow">
                        <CardContent className="p-4">
                            <div className="flex items-start justify-between">
                                <div>
                                    <p className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {t('Answered')}
                                    </p>
                                    <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                        {displayAnswered}
                                    </p>
                                    <div className="mt-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        <span>{answerRateVal}% {t('Answer Rate')}</span>
                                    </div>
                                    <div className="mt-1.5 flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span className="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-medium">
                                            {t('In')}: {displayInboundAnswered}
                                        </span>
                                        <span className="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-medium">
                                            {t('Out')}: {displayOutboundAnswered}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    <CheckCircle2 className="h-4.5 w-4.5" />
                                </div>
                            </div>
                            <div className="mt-3">
                                <Sparkline color="#10b981" id="2" />
                            </div>
                        </CardContent>
                    </Card>

                    {/* 3. Missed / No Answer */}
                    <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 transition-all duration-200 hover:shadow">
                        <CardContent className="p-4">
                            <div className="flex items-start justify-between">
                                <div>
                                    <p className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {t('Missed / No Answer')}
                                    </p>
                                    <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                        {displayNoAnswer}
                                    </p>
                                    <div className="mt-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                                        <span>{noAnswerRateVal}% {t('Miss Rate')}</span>
                                    </div>
                                    <div className="mt-1.5 flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span className="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-medium">
                                            {t('In')}: {displayInboundMissed}
                                        </span>
                                        <span className="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-medium">
                                            {t('Out')}: {displayOutboundUnanswered}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                                    <PhoneMissed className="h-4.5 w-4.5" />
                                </div>
                            </div>
                            <div className="mt-3">
                                <Sparkline color="#f59e0b" id="3" />
                            </div>
                        </CardContent>
                    </Card>

                    {/* 4. Failed / Unreachable */}
                    <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 transition-all duration-200 hover:shadow">
                        <CardContent className="p-4">
                            <div className="flex items-start justify-between">
                                <div>
                                    <p className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {t('Failed / Busy')}
                                    </p>
                                    <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                        {displayFailed}
                                    </p>
                                    <div className="mt-1 text-[11px] font-semibold text-rose-600 dark:text-rose-400">
                                        <span>{failedRateVal}% {t('Failed')}</span>
                                    </div>
                                    <div className="mt-1.5 flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span className="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-medium">
                                            {t('Busy')}: {summary?.busy_calls ?? 0}
                                        </span>
                                        <span className="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                            {t('Err')}: {(summary?.failed_calls ?? 0) + (summary?.congestion_calls ?? 0)}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                                    <Percent className="h-4.5 w-4.5" />
                                </div>
                            </div>
                            <div className="mt-3">
                                <Sparkline color="#f43f5e" id="4" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Directional Summary Deck: Inbound vs Outbound Performance */}
                <div className="grid gap-4 grid-cols-1 md:grid-cols-2">
                    {/* Inbound (Incoming) Card */}
                    <Card className="border border-blue-100 dark:border-blue-900/40 shadow-sm bg-gradient-to-br from-blue-50/40 via-white to-white dark:from-blue-950/20 dark:via-slate-900 dark:to-slate-900 transition-all duration-200">
                        <CardHeader className="p-4 pb-3 border-b border-blue-50 dark:border-blue-950/60 flex flex-row items-center justify-between">
                            <div className="flex items-center gap-2.5">
                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400">
                                    <PhoneIncoming className="h-4 w-4" />
                                </div>
                                <div>
                                    <CardTitle className="text-sm font-bold text-slate-900 dark:text-slate-100">
                                        {t('Incoming Calls (Inbound)')}
                                    </CardTitle>
                                    <CardDescription className="text-[11px] text-slate-500 dark:text-slate-400">
                                        {t('Customer inquiries and incoming calls to extensions')}
                                    </CardDescription>
                                </div>
                            </div>
                            <span className="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-950 px-2.5 py-0.5 text-xs font-bold text-blue-700 dark:text-blue-300">
                                {displayInboundCalls} {t('Calls')}
                            </span>
                        </CardHeader>
                        <CardContent className="p-4 pt-3 space-y-4">
                            {/* Outcome breakdown grid */}
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                                <div className="p-2 rounded-lg bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                                    <p className="text-[11px] font-medium text-emerald-700 dark:text-emerald-300">{t('Answered')}</p>
                                    <p className="text-lg font-bold text-emerald-800 dark:text-emerald-200">{displayInboundAnswered}</p>
                                    <p className="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">{displayInboundAnswerRate}%</p>
                                </div>
                                <div className="p-2 rounded-lg bg-amber-50/70 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/40">
                                    <p className="text-[11px] font-medium text-amber-700 dark:text-amber-300">{t('Missed')}</p>
                                    <p className="text-lg font-bold text-amber-800 dark:text-amber-200">{displayInboundMissed}</p>
                                    <p className="text-[10px] font-semibold text-amber-600 dark:text-amber-400">{displayInboundMissRate}%</p>
                                </div>
                                <div className="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                    <p className="text-[11px] font-medium text-slate-600 dark:text-slate-400">{t('Talk Time')}</p>
                                    <p className="text-base font-bold text-slate-800 dark:text-slate-200">{formatDuration(displayInboundTalkTime)}</p>
                                    <p className="text-[10px] text-slate-500 dark:text-slate-400">Avg: {formatDuration(displayInboundAvgTalkTime)}</p>
                                </div>
                                <div className="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                    <p className="text-[11px] font-medium text-slate-600 dark:text-slate-400">{t('Wait Time')}</p>
                                    <p className="text-base font-bold text-slate-800 dark:text-slate-200">{formatDuration(displayInboundRingTime)}</p>
                                    <p className="text-[10px] text-slate-500 dark:text-slate-400">Avg: {formatDuration(displayInboundAvgRingTime)}</p>
                                </div>
                            </div>

                            {/* Answer vs Missed Segmented Progress */}
                            <div className="space-y-1">
                                <div className="flex justify-between text-[11px] font-medium text-slate-600 dark:text-slate-400">
                                    <span>{t('Resolution Ratio')}</span>
                                    <span>{displayInboundAnswered} / {displayInboundCalls} {t('answered')}</span>
                                </div>
                                <div className="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden flex">
                                    <div
                                        className="h-full bg-emerald-500 transition-all duration-500"
                                        style={{ width: `${Math.min(displayInboundAnswerRate, 100)}%` }}
                                        title={`${t('Answered')}: ${displayInboundAnswerRate}%`}
                                    />
                                    <div
                                        className="h-full bg-amber-500 transition-all duration-500"
                                        style={{ width: `${Math.min(displayInboundMissRate, 100)}%` }}
                                        title={`${t('Missed')}: ${displayInboundMissRate}%`}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Outbound (Outgoing) Card */}
                    <Card className="border border-purple-100 dark:border-purple-900/40 shadow-sm bg-gradient-to-br from-purple-50/40 via-white to-white dark:from-purple-950/20 dark:via-slate-900 dark:to-slate-900 transition-all duration-200">
                        <CardHeader className="p-4 pb-3 border-b border-purple-50 dark:border-purple-950/60 flex flex-row items-center justify-between">
                            <div className="flex items-center gap-2.5">
                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400">
                                    <PhoneOutgoing className="h-4 w-4" />
                                </div>
                                <div>
                                    <CardTitle className="text-sm font-bold text-slate-900 dark:text-slate-100">
                                        {t('Outgoing Calls (Outbound)')}
                                    </CardTitle>
                                    <CardDescription className="text-[11px] text-slate-500 dark:text-slate-400">
                                        {t('Outbound agent calls to external clients and leads')}
                                    </CardDescription>
                                </div>
                            </div>
                            <span className="inline-flex items-center rounded-full bg-purple-100 dark:bg-purple-950 px-2.5 py-0.5 text-xs font-bold text-purple-700 dark:text-purple-300">
                                {displayOutboundCalls} {t('Calls')}
                            </span>
                        </CardHeader>
                        <CardContent className="p-4 pt-3 space-y-4">
                            {/* Outcome breakdown grid */}
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                                <div className="p-2 rounded-lg bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                                    <p className="text-[11px] font-medium text-emerald-700 dark:text-emerald-300">{t('Connected')}</p>
                                    <p className="text-lg font-bold text-emerald-800 dark:text-emerald-200">{displayOutboundAnswered}</p>
                                    <p className="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">{displayOutboundAnswerRate}%</p>
                                </div>
                                <div className="p-2 rounded-lg bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40">
                                    <p className="text-[11px] font-medium text-rose-700 dark:text-rose-300">{t('Unanswered')}</p>
                                    <p className="text-lg font-bold text-rose-800 dark:text-rose-200">{displayOutboundUnanswered}</p>
                                    <p className="text-[10px] font-semibold text-rose-600 dark:text-rose-400">{displayOutboundUnansweredRate}%</p>
                                </div>
                                <div className="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                    <p className="text-[11px] font-medium text-slate-600 dark:text-slate-400">{t('Talk Time')}</p>
                                    <p className="text-base font-bold text-slate-800 dark:text-slate-200">{formatDuration(displayOutboundTalkTime)}</p>
                                    <p className="text-[10px] text-slate-500 dark:text-slate-400">Avg: {formatDuration(displayOutboundAvgTalkTime)}</p>
                                </div>
                                <div className="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                    <p className="text-[11px] font-medium text-slate-600 dark:text-slate-400">{t('Dial / Ring')}</p>
                                    <p className="text-base font-bold text-slate-800 dark:text-slate-200">{formatDuration(displayOutboundRingTime)}</p>
                                    <p className="text-[10px] text-slate-500 dark:text-slate-400">Total Ring</p>
                                </div>
                            </div>

                            {/* Connected vs Unanswered Segmented Progress */}
                            <div className="space-y-1">
                                <div className="flex justify-between text-[11px] font-medium text-slate-600 dark:text-slate-400">
                                    <span>{t('Connection Ratio')}</span>
                                    <span>{displayOutboundAnswered} / {displayOutboundCalls} {t('connected')}</span>
                                </div>
                                <div className="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden flex">
                                    <div
                                        className="h-full bg-emerald-500 transition-all duration-500"
                                        style={{ width: `${Math.min(displayOutboundAnswerRate, 100)}%` }}
                                        title={`${t('Connected')}: ${displayOutboundAnswerRate}%`}
                                    />
                                    <div
                                        className="h-full bg-rose-500 transition-all duration-500"
                                        style={{ width: `${Math.min(displayOutboundUnansweredRate, 100)}%` }}
                                        title={`${t('Unanswered')}: ${displayOutboundUnansweredRate}%`}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Classified Call Duration Analytics (3 Duration Cards + Call Length Distribution) */}
                <div className="space-y-4">
                    <div className="grid gap-4 grid-cols-1 md:grid-cols-3">
                        {/* 1. Total Duration */}
                        <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 transition-all duration-200 hover:shadow">
                            <CardContent className="p-4">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <p className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                            {t('Total Duration')}
                                        </p>
                                        <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                            {formatDuration(displayTotalDuration)}
                                        </p>
                                        <div className="mt-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                            <span>{t('Avg')}: {formatDuration(displayAvgDuration)}</span>
                                        </div>
                                        <div className="mt-2 flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                            <span className="inline-flex items-center gap-1 rounded bg-blue-50 dark:bg-blue-950/40 px-1.5 py-0.5 font-medium text-blue-700 dark:text-blue-300">
                                                {t('In')}: {formatDuration(displayInboundDuration)}
                                            </span>
                                            <span className="inline-flex items-center gap-1 rounded bg-purple-50 dark:bg-purple-950/40 px-1.5 py-0.5 font-medium text-purple-700 dark:text-purple-300">
                                                {t('Out')}: {formatDuration(displayOutboundDuration)}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                                        <Clock className="h-4.5 w-4.5" />
                                    </div>
                                </div>
                                <div className="mt-3">
                                    <Sparkline color="#6366f1" id="dur" />
                                </div>
                            </CardContent>
                        </Card>

                        {/* 2. Total Talk Time */}
                        <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 transition-all duration-200 hover:shadow">
                            <CardContent className="p-4">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <p className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                            {t('Total Talk Time')}
                                        </p>
                                        <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                            {formatDuration(displayTotalTalkTime)}
                                        </p>
                                        <div className="mt-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                            <span>{t('Avg')}: {formatDuration(displayAvgTalkTime)}</span>
                                        </div>
                                        <div className="mt-2 flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                            <span className="inline-flex items-center gap-1 rounded bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 font-medium text-emerald-700 dark:text-emerald-300">
                                                {t('In')}: {formatDuration(displayInboundTalkTime)}
                                            </span>
                                            <span className="inline-flex items-center gap-1 rounded bg-indigo-50 dark:bg-indigo-950/40 px-1.5 py-0.5 font-medium text-indigo-700 dark:text-indigo-300">
                                                {t('Out')}: {formatDuration(displayOutboundTalkTime)}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        <PhoneCall className="h-4.5 w-4.5" />
                                    </div>
                                </div>
                                <div className="mt-3">
                                    <Sparkline color="#10b981" id="talk" />
                                </div>
                            </CardContent>
                        </Card>

                        {/* 3. Total Ring Time */}
                        <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 transition-all duration-200 hover:shadow">
                            <CardContent className="p-4">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <p className="text-xs font-medium text-slate-500 dark:text-slate-400">
                                            {t('Total Ring / Wait Time')}
                                        </p>
                                        <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                            {formatDuration(displayTotalRingTime)}
                                        </p>
                                        <div className="mt-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                            <span>{t('Avg')}: {formatDuration(displayAvgRingTime)}</span>
                                        </div>
                                        <div className="mt-2 flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                                            <span className="inline-flex items-center gap-1 rounded bg-purple-50 dark:bg-purple-950/40 px-1.5 py-0.5 font-medium text-purple-700 dark:text-purple-300">
                                                {t('Wait')}: {formatDuration(displayInboundRingTime)}
                                            </span>
                                            <span className="inline-flex items-center gap-1 rounded bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 font-medium text-slate-700 dark:text-slate-300">
                                                {t('Dial')}: {formatDuration(displayOutboundRingTime)}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400">
                                        <Bell className="h-4.5 w-4.5" />
                                    </div>
                                </div>
                                <div className="mt-3">
                                    <Sparkline color="#8b5cf6" id="ring" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Call Length Distribution (5 Classified Brackets) */}
                    <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
                        <CardHeader className="p-4 pb-2 border-b border-slate-100 dark:border-slate-800/80 flex flex-row items-center justify-between">
                            <div className="flex items-center gap-2">
                                <CardTitle className="text-sm font-semibold flex items-center gap-1.5 text-slate-900 dark:text-slate-100">
                                    <BarChart3 className="h-4 w-4 text-slate-500" />
                                    {t('Call Duration Classification')}
                                </CardTitle>
                                <span className="text-[11px] text-slate-400 font-normal">
                                    ({t('Answered Calls by Talk Time Length')})
                                </span>
                            </div>
                            <span className="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                {displayAnswered} {t('Answered Calls')}
                            </span>
                        </CardHeader>
                        <CardContent className="p-4 pt-3">
                            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                                {durationTiers.map((tier) => {
                                    const pct = totalBracketCalls > 0 ? Math.round((tier.count / totalBracketCalls) * 100) : 0;
                                    return (
                                        <div
                                            key={tier.key}
                                            className="p-3 rounded-lg border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col justify-between"
                                        >
                                            <div className="flex items-center justify-between mb-1.5">
                                                <span className="text-xs font-bold text-slate-900 dark:text-slate-100">
                                                    {tier.label}
                                                </span>
                                                <span
                                                    className="inline-block h-2 w-2 rounded-full"
                                                    style={{ backgroundColor: tier.color }}
                                                />
                                            </div>
                                            <div className="flex items-baseline justify-between mb-1">
                                                <span className="text-lg font-bold text-slate-900 dark:text-slate-100">
                                                    {tier.count}
                                                </span>
                                                <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                    {pct}%
                                                </span>
                                            </div>
                                            <div className="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden mb-1.5">
                                                <div
                                                    className="h-full rounded-full transition-all duration-500"
                                                    style={{
                                                        width: `${pct}%`,
                                                        backgroundColor: tier.color,
                                                    }}
                                                />
                                            </div>
                                            <p className="text-[10px] text-slate-400 dark:text-slate-500 truncate">
                                                {tier.tag}
                                            </p>
                                        </div>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Middle Row: Call Volume & Answer Rate + Live Extension Status */}
                <div className="grid gap-6 grid-cols-1 lg:grid-cols-12">
                    {/* Left 8 Cols: Call Volume & Answer Rate */}
                    <Card className="lg:col-span-8 border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800/80">
                            <div>
                                <CardTitle className="text-base font-semibold text-slate-900 dark:text-slate-100">
                                    {t('Call Volume & Answer Rate')}
                                </CardTitle>
                            </div>
                            <div className="w-32">
                                <Select value={timeGrain} onValueChange={setTimeGrain}>
                                    <SelectTrigger className="h-8 text-xs border-slate-200 dark:border-slate-800">
                                        <SelectValue placeholder="Hourly" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="hourly">{t('Hourly')}</SelectItem>
                                        <SelectItem value="daily">{t('Daily')}</SelectItem>
                                        <SelectItem value="weekly">{t('Weekly')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-4">
                            {/* Custom Legend */}
                            <div className="flex items-center gap-6 text-xs text-slate-600 dark:text-slate-400 mb-4">
                                <div className="flex items-center gap-2">
                                    <span className="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                    <span className="font-medium text-slate-700 dark:text-slate-300">Total Calls</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    <span className="font-medium text-slate-700 dark:text-slate-300">Answered Calls</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="h-2 w-4 bg-purple-500 rounded-full"></span>
                                    <span className="font-medium text-slate-700 dark:text-slate-300">Answer Rate (%)</span>
                                </div>
                            </div>

                            <div className="h-[280px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart
                                        data={
                                            timeGrain === 'daily'
                                                ? (charts?.trend && charts.trend.length > 0 ? charts.trend : [])
                                                : timeGrain === 'weekly'
                                                    ? (charts?.dayOfWeekTrend && charts.dayOfWeekTrend.length > 0 ? charts.dayOfWeekTrend : [])
                                                    : hourlyData
                                        }
                                        margin={{ top: 10, right: 10, left: -20, bottom: 0 }}
                                    >
                                        <defs>
                                            <linearGradient id="totalVolumeGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.35} />
                                                <stop offset="95%" stopColor="#3b82f6" stopOpacity={0.0} />
                                            </linearGradient>
                                            <linearGradient id="ansVolumeGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#10b981" stopOpacity={0.35} />
                                                <stop offset="95%" stopColor="#10b981" stopOpacity={0.0} />
                                            </linearGradient>
                                        </defs>
                                        <CartesianGrid strokeDasharray="3 3" vertical={false} opacity={0.2} />
                                        <XAxis
                                            dataKey={
                                                timeGrain === 'daily'
                                                    ? 'date'
                                                    : timeGrain === 'weekly'
                                                        ? 'short'
                                                        : 'time'
                                            }
                                            tickLine={false}
                                            axisLine={false}
                                            tickMargin={8}
                                            fontSize={11}
                                        />
                                        <YAxis yAxisId="left" tickLine={false} axisLine={false} fontSize={12} domain={[0, 'auto']} />
                                        <YAxis yAxisId="right" orientation="right" tickLine={false} axisLine={false} fontSize={12} domain={[0, 100]} unit="%" />
                                        <Tooltip />
                                        <Area yAxisId="left" type="monotone" dataKey="total" name={t('Total Calls')} stroke="#3b82f6" fillOpacity={1} fill="url(#totalVolumeGrad)" strokeWidth={2.5} />
                                        <Area yAxisId="left" type="monotone" dataKey="answered" name={t('Answered Calls')} stroke="#10b981" fillOpacity={1} fill="url(#ansVolumeGrad)" strokeWidth={2.5} />
                                        <Area yAxisId="right" type="monotone" dataKey="answerRate" name={t('Answer Rate (%)')} stroke="#8b5cf6" fill="transparent" strokeWidth={2} strokeDasharray="4 4" />
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Right 4 Cols: Live Extension Status & Utilization */}
                    <Card className="lg:col-span-4 border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 flex flex-col justify-between">
                        <CardHeader className="pb-2 border-b border-slate-100 dark:border-slate-800/80">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-base font-semibold flex items-center gap-1.5 text-slate-900 dark:text-slate-100">
                                    {t('Live Extension Status')}
                                    <Info className="h-4 w-4 text-slate-400" />
                                </CardTitle>
                                {isLiveStatusLoading ? (
                                    <span className="flex items-center gap-1 text-[11px] font-normal text-slate-400">
                                        <RefreshCw className="h-3 w-3 animate-spin" />
                                        {t('Updating...')}
                                    </span>
                                ) : (
                                    <span className="flex items-center gap-1 text-[11px] font-normal text-emerald-600 dark:text-emerald-400">
                                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                        {t('Live')}
                                    </span>
                                )}
                            </div>
                        </CardHeader>

                        <CardContent className="pt-4 space-y-5">
                            {/* 4 Status Grid */}
                            <div className="grid grid-cols-4 gap-2 text-center">
                                <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                    <div className="flex items-center justify-center gap-1 mb-1">
                                        <span className="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        <span className="text-[11px] font-medium text-slate-500 dark:text-slate-400">Online</span>
                                    </div>
                                    <p className="text-lg font-bold text-slate-900 dark:text-slate-100">{liveStatusData.online}</p>
                                    <p className="text-[10px] text-slate-400">{liveStatusData.onlinePercent}%</p>
                                </div>

                                <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                    <div className="flex items-center justify-center gap-1 mb-1">
                                        <span className="h-2 w-2 rounded-full bg-amber-500"></span>
                                        <span className="text-[11px] font-medium text-slate-500 dark:text-slate-400">On Call</span>
                                    </div>
                                    <p className="text-lg font-bold text-slate-900 dark:text-slate-100">{liveStatusData.onCall}</p>
                                    <p className="text-[10px] text-slate-400">{liveStatusData.onCallPercent}%</p>
                                </div>

                                <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                    <div className="flex items-center justify-center gap-1 mb-1">
                                        <span className="h-2 w-2 rounded-full bg-purple-500"></span>
                                        <span className="text-[11px] font-medium text-slate-500 dark:text-slate-400">Ringing</span>
                                    </div>
                                    <p className="text-lg font-bold text-slate-900 dark:text-slate-100">{liveStatusData.ringing}</p>
                                    <p className="text-[10px] text-slate-400">{liveStatusData.ringingPercent}%</p>
                                </div>

                                <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                    <div className="flex items-center justify-center gap-1 mb-1">
                                        <span className="h-2 w-2 rounded-full bg-slate-400"></span>
                                        <span className="text-[11px] font-medium text-slate-500 dark:text-slate-400">Offline</span>
                                    </div>
                                    <p className="text-lg font-bold text-slate-900 dark:text-slate-100">{liveStatusData.offline}</p>
                                    <p className="text-[10px] text-slate-400">{liveStatusData.offlinePercent}%</p>
                                </div>
                            </div>

                            {/* Extension Utilization Radial Section */}
                            <div className="pt-2 border-t border-slate-100 dark:border-slate-800 flex flex-col items-center justify-center">
                                <div className="w-full flex items-center justify-between mb-2">
                                    <p className="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        {t('Extension Utilization')}
                                    </p>
                                </div>

                                <div className="relative flex items-center justify-center my-1">
                                    <svg className="w-36 h-36 transform -rotate-90">
                                        <circle
                                            cx="72"
                                            cy="72"
                                            r="52"
                                            stroke="currentColor"
                                            strokeWidth="12"
                                            className="text-slate-100 dark:text-slate-800"
                                            fill="transparent"
                                        />
                                        <circle
                                            cx="72"
                                            cy="72"
                                            r="52"
                                            stroke="currentColor"
                                            strokeWidth="12"
                                            strokeDasharray={326.72}
                                            strokeDashoffset={326.72 * (1 - (liveStatusData.utilizationPercent || 0) / 100)}
                                            strokeLinecap="round"
                                            className="text-emerald-500 transition-all duration-1000 ease-out"
                                            fill="transparent"
                                        />
                                    </svg>
                                    <div className="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        <span className="text-xl font-bold text-slate-900 dark:text-slate-100">
                                            {liveStatusData.utilizationPercent}%
                                        </span>
                                        <span className="text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                            Active
                                        </span>
                                    </div>
                                </div>

                                <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    {liveStatusData.activeExtensions} of {liveStatusData.totalExtensions} extensions
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Bottom-Middle Row: 3 Columns (Call Disposition, Peak Call Hours, Quick Insights) */}
                <div className="grid gap-6 grid-cols-1 md:grid-cols-3">
                    {/* 1. Call Disposition */}
                    <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
                        <CardHeader className="pb-2 border-b border-slate-100 dark:border-slate-800/80">
                            <CardTitle className="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {t('Call Disposition')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-4 flex flex-col items-center">
                            <div className="relative h-[180px] w-full flex items-center justify-center">
                                <ResponsiveContainer width="100%" height="100%">
                                    <RechartsPieChart>
                                        <Pie
                                            data={dispositionData}
                                            cx="50%"
                                            cy="50%"
                                            innerRadius={55}
                                            outerRadius={80}
                                            paddingAngle={dispositionData.length > 1 ? 3 : 0}
                                            dataKey="value"
                                        >
                                            {dispositionData.map((entry, index) => (
                                                <Cell key={`disp-cell-${index}`} fill={entry.color || '#94a3b8'} />
                                            ))}
                                        </Pie>
                                        <Tooltip />
                                    </RechartsPieChart>
                                </ResponsiveContainer>
                                <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span className="text-xl font-bold text-slate-900 dark:text-slate-100">
                                        {displayTotalCalls}
                                    </span>
                                    <span className="text-[11px] text-slate-400 font-medium">Total</span>
                                </div>
                            </div>

                            {/* Legend below donut */}
                            <div className="w-full space-y-2 mt-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className="h-3 w-3 rounded bg-emerald-500"></span>
                                        <span className="text-slate-600 dark:text-slate-400 font-medium">Answered</span>
                                    </div>
                                    <span className="font-semibold text-slate-800 dark:text-slate-200">
                                        {displayAnswered} ({answerRateVal}%)
                                    </span>
                                </div>

                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className="h-3 w-3 rounded bg-amber-500"></span>
                                        <span className="text-slate-600 dark:text-slate-400 font-medium">No Answer</span>
                                    </div>
                                    <span className="font-semibold text-slate-800 dark:text-slate-200">
                                        {displayNoAnswer} ({noAnswerRateVal}%)
                                    </span>
                                </div>

                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className="h-3 w-3 rounded bg-rose-500"></span>
                                        <span className="text-slate-600 dark:text-slate-400 font-medium">Failed</span>
                                    </div>
                                    <span className="font-semibold text-slate-800 dark:text-slate-200">
                                        {displayFailed} ({failedRateVal}%)
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* 2. Peak Call Hours (24 Hours: 12 AM - 11 PM) */}
                    <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 flex flex-col justify-between">
                        <CardHeader className="pb-2 border-b border-slate-100 dark:border-slate-800/80 flex flex-row items-center justify-between">
                            <CardTitle className="text-base font-semibold flex items-center gap-1.5 text-slate-900 dark:text-slate-100">
                                {t('24-Hour Peak Call Activity')}
                                <Info className="h-4 w-4 text-slate-400" />
                            </CardTitle>
                            {charts?.peaks?.peakHour && (
                                <span className="px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    Peak: {charts.peaks.peakHour}
                                </span>
                            )}
                        </CardHeader>
                        <CardContent className="pt-4 flex-1 flex flex-col justify-between">
                            <div className="h-[210px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <RechartsBarChart
                                        data={hourlyData}
                                        margin={{ top: 10, right: 0, left: -25, bottom: 0 }}
                                    >
                                        <CartesianGrid strokeDasharray="3 3" vertical={false} opacity={0.2} />
                                        <XAxis dataKey="time" tickLine={false} axisLine={false} fontSize={9} interval={1} />
                                        <YAxis tickLine={false} axisLine={false} fontSize={10} domain={[0, 'auto']} />
                                        <Tooltip
                                            formatter={(val: any, name: any, item: any) => [
                                                `${val} calls (${item.payload.answerRate}% answered)`,
                                                'Volume'
                                            ]}
                                        />
                                        <Bar dataKey="total" radius={[3, 3, 0, 0]}>
                                            {hourlyData.map((entry, index) => (
                                                <Cell
                                                    key={`peak-cell-${index}`}
                                                    fill={
                                                        entry.isPeak
                                                            ? '#3b82f6'
                                                            : entry.isPeakAnswer
                                                                ? '#10b981'
                                                                : '#93c5fd'
                                                    }
                                                />
                                            ))}
                                        </Bar>
                                    </RechartsBarChart>
                                </ResponsiveContainer>
                            </div>
                            <div className="flex flex-wrap items-center justify-center gap-4 mt-2 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500">
                                <div className="flex items-center gap-1.5">
                                    <span className="h-2.5 w-2.5 rounded bg-blue-500"></span>
                                    <span>Peak Volume ({charts?.peaks?.peakHour || 'N/A'})</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <span className="h-2.5 w-2.5 rounded bg-emerald-500"></span>
                                    <span>Peak Answer Rate ({charts?.peaks?.peakAnswerHour || 'N/A'})</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* 3. Quick Insights */}
                    <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900 flex flex-col justify-between">
                        <CardHeader className="pb-2 border-b border-slate-100 dark:border-slate-800/80">
                            <CardTitle className="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {t('Quick Insights')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-4 space-y-3">
                            {quickInsights.map((insight, idx) => {
                                const IconComp = insight.IconComponent;
                                return (
                                    <div
                                        key={`quick-insight-${idx}`}
                                        className="flex items-center justify-between p-3 rounded-lg border border-slate-100 dark:border-slate-800 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors cursor-pointer group"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${insight.bgClass}`}>
                                                <IconComp className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <p className="text-xs font-semibold text-slate-900 dark:text-slate-100">
                                                    {insight.title}
                                                </p>
                                                <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                                    {insight.subtitle}
                                                </p>
                                            </div>
                                        </div>
                                        <ChevronRight className="h-4 w-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 transition-colors" />
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                </div>

                {/* Extension Performance Table */}
                <Card className="border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
                    <CardHeader className="flex flex-row items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80">
                        <div className="flex items-center gap-2">
                            <CardTitle className="text-base font-semibold flex items-center gap-1.5 text-slate-900 dark:text-slate-100">
                                {t('Extension Performance')}
                                <Info className="h-4 w-4 text-slate-400" />
                            </CardTitle>
                        </div>
                        <Button
                            variant="link"
                            className="text-xs font-semibold text-blue-600 hover:text-blue-700 p-0 h-auto gap-1"
                            onClick={() => router.get(route('pbx.call-reports.index'))}
                        >
                            {t('View all extensions')}
                            <ChevronRight className="h-3.5 w-3.5" />
                        </Button>
                    </CardHeader>
                    <CardContent className="p-0 overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-50/70 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th className="py-3 px-4 sm:px-6">{t('Extension / User')}</th>
                                    <th className="py-3 px-4 text-center cursor-pointer hover:text-slate-700">
                                        {t('Total Calls')} ⇅
                                    </th>
                                    <th className="py-3 px-4 text-center">{t('Answered Calls')}</th>
                                    <th className="py-3 px-4">{t('Answer Rate')}</th>
                                    <th className="py-3 px-4 text-center">{t('Avg Talk Time')}</th>
                                    <th className="py-3 px-4 text-center">{t('Missed (No Answer)')}</th>
                                    <th className="py-3 px-4 text-center">{t('Failed Calls')}</th>
                                    <th className="py-3 px-4 text-center">{t('Status')}</th>
                                    <th className="py-3 px-4 text-right"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium text-slate-700 dark:text-slate-300">
                                {extensionPerformanceList.map((row, idx) => (
                                    <tr key={idx} className="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                        <td className="py-3.5 px-4 sm:px-6">
                                            <div className="flex items-center gap-3">
                                                <Avatar className="h-9 w-9 border border-slate-200 dark:border-slate-700">
                                                    {row.avatar ? (
                                                        <AvatarImage src={row.avatar} alt={row.user_name} />
                                                    ) : (
                                                        <AvatarFallback className="bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 font-bold text-xs">
                                                            {row.user_name.substring(0, 2).toUpperCase()}
                                                        </AvatarFallback>
                                                    )}
                                                </Avatar>
                                                <div>
                                                    <p className="font-semibold text-slate-900 dark:text-slate-100 text-xs">
                                                        {row.user_name}
                                                    </p>
                                                    <p className="text-[11px] text-slate-400 font-normal">
                                                        {t('Ext')}: {row.extension}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="py-3.5 px-4 text-center font-bold text-slate-900 dark:text-slate-100">
                                            {row.totalCalls}
                                        </td>
                                        <td className="py-3.5 px-4 text-center font-semibold text-slate-800 dark:text-slate-200">
                                            {row.answeredCalls}
                                        </td>
                                        <td className="py-3.5 px-4 min-w-[130px]">
                                            <div className="flex items-center gap-2">
                                                <span className="font-bold text-slate-900 dark:text-slate-100 text-xs min-w-[38px]">
                                                    {row.answerRate}%
                                                </span>
                                                <div className="h-2 w-full max-w-[80px] bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                                    <div
                                                        className="h-full bg-emerald-500 rounded-full"
                                                        style={{ width: `${Math.min(row.answerRate, 100)}%` }}
                                                    />
                                                </div>
                                            </div>
                                        </td>
                                        <td className="py-3.5 px-4 text-center font-medium text-slate-700 dark:text-slate-300">
                                            {row.avgTalkTime}s
                                        </td>
                                        <td className="py-3.5 px-4 text-center font-semibold text-amber-600">
                                            {row.missed} ({row.missedPercent}%)
                                        </td>
                                        <td className="py-3.5 px-4 text-center font-semibold text-rose-600">
                                            {row.failed} ({row.failedPercent}%)
                                        </td>
                                        <td className="py-3.5 px-4 text-center">
                                            {renderExtensionLiveBadge(row)}



                                        </td>
                                        <td className="py-3.5 px-4 text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" className="h-7 w-7 text-slate-400 hover:text-slate-600">
                                                        <MoreVertical className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem onClick={() => router.get(route('pbx.call-reports.index', { extension: row.extension }))}>
                                                        {t('View Call Logs')}
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
