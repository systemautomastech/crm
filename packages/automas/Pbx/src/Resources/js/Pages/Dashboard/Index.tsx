import { useState, useEffect, useMemo, useCallback } from 'react';
import { Head, router, Link, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import axios from 'axios';
import { getImagePath } from '@/utils/helpers';
import {
    Users,
    CheckCircle2,
    Settings,
    Activity,
    Radio,
    AlertCircle,
    UserCheck,
    Phone,
    Plus,
    Eye,
    RefreshCw,
    ShieldCheck,
    PhoneCall,
    PhoneIncoming,
    PhoneOff,
    Edit,
} from 'lucide-react';
import {
    PieChart,
    Pie,
    Cell,
    ResponsiveContainer,
    Tooltip as RechartsTooltip,
} from 'recharts';

import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';

interface Extension {
    id: number;
    extension: string;
    user_id: number | null;
    user_name: string | null;
    user_avatar?: string | null;
    user_email?: string | null;
    user_type?: string | null;
    caller_id?: string | null;
    is_active: boolean;
    created_at?: string | null;
}

interface LiveStatusInfo {
    extension_id?: number;
    extension?: string;
    status: 'available' | 'ringing' | 'on_call' | 'offline' | 'unknown';
    registered?: boolean;
    in_call?: boolean;
    is_active?: boolean;
}

interface PbxSetting {
    max_extensions: number;
    extension_start: number;
    extension_end: number;
    pbx_name?: string;
    ami_host?: string;
    ami_port?: string | number;
    is_enabled: boolean;
}

interface Stats {
    total_extensions: number;
    active_extensions: number;
    inactive_extensions: number;
    assigned_users: number;
    unassigned_extensions: number;
}

interface AuthUser {
    id?: number;
    permissions?: string[];
}

interface DashboardProps {
    isConfigured: boolean;
    setting: PbxSetting | null;
    extensions: Extension[];
    liveStatuses: Record<number, LiveStatusInfo>;
    stats: Stats;
    canCreateExtension: boolean;
    auth?: {
        user?: AuthUser;
    };
    [key: string]: any;
}

export default function PbxDashboard() {
    const {
        isConfigured = true,
        setting,
        extensions = [],
        liveStatuses: initialLiveStatuses = {},
        stats: initialStats,
        canCreateExtension = false,
        auth,
    } = usePage<DashboardProps>().props;

    const { t } = useTranslation();

    const permissions = auth?.user?.permissions ?? [];
    const userId = auth?.user?.id;

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

    const canEditExt = (ext: Extension) => {
        if (!canEdit) return false;
        if (canViewAll) return true;
        return userId !== undefined && ext.user_id === userId;
    };

    const [liveStatuses, setLiveStatuses] = useState<Record<number, LiveStatusInfo>>(initialLiveStatuses);
    const [isRefreshing, setIsRefreshing] = useState<boolean>(false);
    const [lastUpdatedTime, setLastUpdatedTime] = useState<string | null>(
        new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
    );

    const fetchLiveStatuses = useCallback(async () => {
        setIsRefreshing(true);
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
            setIsRefreshing(false);
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
    }, [fetchLiveStatuses]);

    // Live Metrics Calculations
    const liveMetrics = useMemo(() => {
        const liveValues = Object.values(liveStatuses);
        let availableCount = 0;
        let ringingCount = 0;
        let onCallCount = 0;
        let offlineCount = 0;
        let unknownCount = 0;

        if (liveValues.length > 0) {
            liveValues.forEach((info) => {
                const status = info.status || 'unknown';
                if (status === 'available') availableCount++;
                else if (status === 'ringing') ringingCount++;
                else if (status === 'on_call') onCallCount++;
                else if (status === 'offline') offlineCount++;
                else unknownCount++;
            });
        } else {
            unknownCount = extensions.length;
        }

        const registeredCount = availableCount + ringingCount + onCallCount;
        const totalChecked = liveValues.length || extensions.length || 1;
        const registeredRate = Math.round((registeredCount / totalChecked) * 100);

        return {
            available: availableCount,
            ringing: ringingCount,
            onCall: onCallCount,
            offline: offlineCount,
            unknown: unknownCount,
            registered: registeredCount,
            registeredRate,
        };
    }, [liveStatuses, extensions.length]);

    // Recharts Donut Chart Data
    const chartData = useMemo(() => {
        const data = [
            { name: t('Available'), value: liveMetrics.available, color: '#10b981' },
            { name: t('Ringing'), value: liveMetrics.ringing, color: '#f59e0b' },
            { name: t('On Call'), value: liveMetrics.onCall, color: '#3b82f6' },
            { name: t('Offline'), value: liveMetrics.offline, color: '#64748b' },
            { name: t('Unknown'), value: liveMetrics.unknown, color: '#94a3b8' },
        ];
        const totalVal = data.reduce((acc, curr) => acc + curr.value, 0);
        if (totalVal === 0) {
            return [{ name: t('Checking...'), value: 1, color: '#cbd5e1' }];
        }
        return data.filter((item) => item.value > 0);
    }, [liveMetrics, t]);

    const renderLiveStatusBadge = (extensionId: number) => {
        const info = liveStatuses[extensionId];
        const status = info?.status || 'unknown';

        switch (status) {
            case 'available':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        {t('Available')}
                    </span>
                );
            case 'ringing':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-400">
                        <span className="h-1.5 w-1.5 animate-ping rounded-full bg-amber-500" />
                        {t('Ringing')}
                    </span>
                );
            case 'on_call':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-950/40 dark:text-blue-400">
                        <span className="h-1.5 w-1.5 rounded-full bg-blue-500" />
                        {t('On Call')}
                    </span>
                );
            case 'offline':
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-500/20 dark:bg-slate-800 dark:text-slate-400">
                        <span className="h-1.5 w-1.5 rounded-full bg-slate-500" />
                        {t('Offline')}
                    </span>
                );
            case 'unknown':
            default:
                return (
                    <span className="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500 ring-1 ring-inset ring-gray-400/20 dark:bg-gray-800 dark:text-gray-400">
                        <span className="h-1.5 w-1.5 rounded-full bg-gray-400" />
                        {t('Unknown')}
                    </span>
                );
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Call Center') }]}
        >
            <Head title={t('Call Center Dashboard')} />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {t('PBX Extension Dashboard')}
                        </h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            {t('Real-time extension directory, live statuses, and workspace capacity')}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => fetchLiveStatuses()}
                            disabled={isRefreshing}
                            className="bg-white dark:bg-slate-800 border-slate-200"
                        >
                            <RefreshCw className={`mr-1.5 h-3.5 w-3.5 ${isRefreshing ? 'animate-spin text-blue-600' : ''}`} />
                            {t('Refresh')}
                        </Button>
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800">
                            <span className="h-1.5 w-1.5 animate-ping rounded-full bg-emerald-500" />
                            {t('Auto 5s')}
                        </span>
                        <Button variant="outline" size="sm" className="bg-white dark:bg-slate-800 border-slate-200" asChild>
                            <Link href={route('pbx.extensions.index')}>
                                <Eye className="mr-2 h-4 w-4 text-blue-600" />
                                {t('Manage Extensions')}
                            </Link>
                        </Button>
                        {canCreateExtension && (canCreate) && (
                            <Button size="sm" className="bg-blue-600 hover:bg-blue-700 text-white font-bold" asChild>
                                <Link href={route('pbx.extensions.create')}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    {t('New Extension')}
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>
                {/* ── Unconfigured PBX Banner ──────────────────────────────────── */}
                {!isConfigured && (
                    <Card className="border-amber-300/60 bg-gradient-to-r from-amber-50/80 to-orange-50/50 dark:from-amber-950/30 dark:to-orange-950/20 dark:border-amber-700/50 rounded-2xl shadow-sm">
                        <CardContent className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                    <AlertCircle className="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-sm text-slate-900 dark:text-white">
                                        {t('PBX Settings Not Configured')}
                                    </h3>
                                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {t('Please configure PBX AMI and extension range before creating extensions.')}
                                    </p>
                                </div>
                            </div>
                            <Button asChild size="sm" className="shrink-0 bg-amber-500 hover:bg-amber-600 text-white font-bold">
                                <Link href={route('pbx.settings.index')}>
                                    <Settings className="mr-2 h-4 w-4" />
                                    {t('Configure PBX')}
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {/* ── 1. KPI Extension Stat Cards ─────────────────────────────── */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Total Extensions */}
                    <div className="bg-gradient-to-br from-violet-50/80 to-purple-50/60 border border-violet-100/90 dark:from-slate-900 dark:to-slate-900 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-600 flex items-center justify-center shrink-0">
                                <Users className="w-5 h-5" />
                            </div>
                            <div>
                                <p className="text-[11px] font-semibold text-violet-600/70 dark:text-violet-400 uppercase tracking-wide">{t('Total Extensions')}</p>
                                <p className="text-xl font-bold text-slate-900 dark:text-white leading-none mt-0.5">{initialStats?.total_extensions || extensions.length || 0}</p>
                                <p className="text-[11px] text-slate-500 mt-0.5">
                                    {setting ? `${t('Limit')}: ${setting.max_extensions}` : t('PBX Directory')}
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Config Active Extensions */}
                    <div className="bg-gradient-to-br from-emerald-50/80 to-green-50/60 border border-emerald-100/90 dark:from-slate-900 dark:to-slate-900 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                <UserCheck className="w-5 h-5" />
                            </div>
                            <div>
                                <p className="text-[11px] font-semibold text-emerald-600/70 dark:text-emerald-400 uppercase tracking-wide">{t('Active Extensions')}</p>
                                <p className="text-xl font-bold text-slate-900 dark:text-white leading-none mt-0.5">{initialStats?.active_extensions || 0}</p>
                                <p className="text-[11px] text-slate-500 mt-0.5">
                                    <span className="text-slate-400 font-medium">{initialStats?.inactive_extensions || 0} {t('inactive')}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Live Reachable Extensions */}
                    <div className="bg-gradient-to-br from-blue-50/80 to-indigo-50/60 border border-blue-100/90 dark:from-slate-900 dark:to-slate-900 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                                <Phone className="w-5 h-5" />
                            </div>
                            <div>
                                <p className="text-[11px] font-semibold text-blue-600/70 dark:text-blue-400 uppercase tracking-wide">{t('Live Registered')}</p>
                                <p className="text-xl font-bold text-slate-900 dark:text-white leading-none mt-0.5">{liveMetrics.registered}</p>
                                <p className="text-[11px] text-emerald-600 font-medium mt-0.5 flex items-center gap-1">
                                    <Radio className="h-3 w-3 animate-pulse" />
                                    {liveMetrics.registeredRate}% {t('online')}
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Assigned Users */}
                    <div className="bg-gradient-to-br from-amber-50/80 to-orange-50/60 border border-amber-100/90 dark:from-slate-900 dark:to-slate-900 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                <CheckCircle2 className="w-5 h-5" />
                            </div>
                            <div>
                                <p className="text-[11px] font-semibold text-amber-600/70 dark:text-amber-400 uppercase tracking-wide">{t('Assigned Users')}</p>
                                <p className="text-xl font-bold text-slate-900 dark:text-white leading-none mt-0.5">{initialStats?.assigned_users || 0}</p>
                                <p className="text-[11px] text-slate-500 mt-0.5">
                                    {initialStats?.unassigned_extensions || 0} {t('unassigned')}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ── 2. Live Activity Breakdown & Capacity ─────────────────────── */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    {/* Live Activity Chart */}
                    <Card className="lg:col-span-7 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
                        <CardHeader className="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-row items-center justify-between">
                            <div>
                                <CardTitle className="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    <Activity className="h-5 w-5 text-blue-600" />
                                    {t('Live Extension Activity')}
                                </CardTitle>
                                <CardDescription className="text-xs text-slate-500 mt-0.5">
                                    {t('Real-time registered channel state distribution')}
                                    {lastUpdatedTime && ` • ${t('Updated')}: ${lastUpdatedTime}`}
                                </CardDescription>
                            </div>
                            <Button variant="ghost" size="sm" onClick={fetchLiveStatuses} disabled={isRefreshing} className="h-7 px-2 text-xs">
                                <RefreshCw className={`h-3.5 w-3.5 mr-1 ${isRefreshing ? 'animate-spin text-blue-600' : ''}`} />
                                {t('Sync')}
                            </Button>
                        </CardHeader>
                        <CardContent className="p-5">
                            <div className="flex flex-col items-center justify-between gap-6 sm:flex-row">
                                <div className="relative h-44 w-44 shrink-0">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <PieChart>
                                            <Pie
                                                data={chartData}
                                                cx="50%"
                                                cy="50%"
                                                innerRadius={50}
                                                outerRadius={72}
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
                                    <div className="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                                        <span className="text-xl font-bold text-slate-900 dark:text-slate-100">
                                            {extensions.length}
                                        </span>
                                        <span className="text-[10px] uppercase tracking-wider font-semibold text-slate-500">
                                            {t('Total')}
                                        </span>
                                    </div>
                                </div>

                                <div className="w-full space-y-2 sm:max-w-xs">
                                    <div className="flex items-center justify-between rounded-lg border border-slate-100 p-2 text-xs dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                                        <div className="flex items-center gap-2">
                                            <span className="h-2.5 w-2.5 rounded-full bg-emerald-500" />
                                            <span className="font-medium text-slate-700 dark:text-slate-300">{t('Available / Idle')}</span>
                                        </div>
                                        <span className="font-bold text-slate-900 dark:text-white">{liveMetrics.available}</span>
                                    </div>
                                    <div className="flex items-center justify-between rounded-lg border border-slate-100 p-2 text-xs dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                                        <div className="flex items-center gap-2">
                                            <span className="h-2.5 w-2.5 rounded-full bg-blue-500" />
                                            <span className="font-medium text-slate-700 dark:text-slate-300">{t('Ongoing Call')}</span>
                                        </div>
                                        <span className="font-bold text-slate-900 dark:text-white">{liveMetrics.onCall}</span>
                                    </div>
                                    <div className="flex items-center justify-between rounded-lg border border-slate-100 p-2 text-xs dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                                        <div className="flex items-center gap-2">
                                            <span className="h-2.5 w-2.5 rounded-full bg-amber-500" />
                                            <span className="font-medium text-slate-700 dark:text-slate-300">{t('Ringing')}</span>
                                        </div>
                                        <span className="font-bold text-slate-900 dark:text-white">{liveMetrics.ringing}</span>
                                    </div>
                                    <div className="flex items-center justify-between rounded-lg border border-slate-100 p-2 text-xs dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                                        <div className="flex items-center gap-2">
                                            <span className="h-2.5 w-2.5 rounded-full bg-slate-500" />
                                            <span className="font-medium text-slate-700 dark:text-slate-300">{t('Offline')}</span>
                                        </div>
                                        <span className="font-bold text-slate-900 dark:text-white">{liveMetrics.offline}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Workspace Capacity & PBX Health */}
                    <Card className="lg:col-span-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
                        <CardHeader className="p-5 border-b border-slate-100 dark:border-slate-800">
                            <CardTitle className="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <ShieldCheck className="h-5 w-5 text-emerald-600" />
                                {t('Workspace Capacity & PBX')}
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500 mt-0.5">
                                {t('Extension range limits and server connection state')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="p-5 space-y-4">
                            {setting && (
                                <div className="space-y-1.5">
                                    <div className="flex justify-between text-xs font-medium">
                                        <span className="text-slate-600 dark:text-slate-400">{t('License Capacity')}</span>
                                        <span className="font-bold text-slate-900 dark:text-slate-100">
                                            {extensions.length} / {setting.max_extensions} ({Math.round((extensions.length / setting.max_extensions) * 100)}%)
                                        </span>
                                    </div>
                                    <Progress
                                        value={Math.round((extensions.length / setting.max_extensions) * 100)}
                                        className="h-2 bg-slate-100 dark:bg-slate-800"
                                    />
                                </div>
                            )}

                            <div className="space-y-1.5">
                                <div className="flex justify-between text-xs font-medium">
                                    <span className="text-slate-600 dark:text-slate-400">{t('Live Reachable Rate')}</span>
                                    <span className="font-bold text-emerald-600 dark:text-emerald-400">
                                        {liveMetrics.registeredRate}% {t('Registered')}
                                    </span>
                                </div>
                                <Progress
                                    value={liveMetrics.registeredRate}
                                    className="h-2 bg-slate-100 dark:bg-slate-800 [&>div]:bg-emerald-500"
                                />
                            </div>

                            <div className="mt-4 flex items-center justify-between rounded-xl border border-slate-200/70 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                                <div className="flex items-center gap-2.5">
                                    <Radio className="h-4 w-4 text-emerald-500 animate-pulse" />
                                    <div>
                                        <div className="text-xs font-bold text-slate-900 dark:text-slate-100">
                                            {setting?.pbx_name || 'Asterisk / amarSIP PBX'}
                                        </div>
                                        <div className="text-[11px] text-slate-500">
                                            {setting?.extension_start ? `${t('Range')}: ${setting.extension_start}–${setting.extension_end}` : t('No PBX Range')}
                                        </div>
                                    </div>
                                </div>
                                <span className="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    {setting ? t('Connected') : t('Unconfigured')}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* ── 3. Extension Directory Grid ──────────────────────────────── */}
                <Card className="rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
                    <CardHeader className="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-row items-center justify-between">
                        <div>
                            <CardTitle className="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <Users className="h-5 w-5 text-violet-600" />
                                {t('Extension Directory')}
                            </CardTitle>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {t('Overview of all created workspace extensions and assigned team members')}
                            </p>
                        </div>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={route('pbx.extensions.index')}>
                                <Eye className="mr-1.5 h-3.5 w-3.5" />
                                {t('View Table View')}
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent className="p-5">
                        {extensions.length > 0 ? (
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                {extensions.map((ext) => (
                                    <div
                                        key={ext.id}
                                        className={`p-4 rounded-2xl border flex flex-col justify-between gap-3 transition-all hover:shadow-md ${ext.is_active
                                            ? 'border-emerald-200/80 bg-emerald-50/20 dark:border-emerald-800/40 dark:bg-emerald-950/10'
                                            : 'border-slate-200/80 bg-slate-50/40 dark:border-slate-700/50 dark:bg-slate-800/20'
                                            }`}
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="flex items-center gap-3 min-w-0">
                                                {/* Extension Number Badge */}
                                                <div className={`w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm border shadow-xs shrink-0 ${ext.is_active
                                                    ? 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/50 dark:text-emerald-300 dark:border-emerald-700'
                                                    : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-600'
                                                    }`}>
                                                    {ext.extension}
                                                </div>

                                                {/* User Avatar */}
                                                <div className="h-10 w-10 rounded-full border-2 border-white dark:border-slate-700 overflow-hidden ring-1 ring-border/50 shrink-0">
                                                    {ext.user_avatar ? (
                                                        <img
                                                            src={getImagePath(ext.user_avatar)}
                                                            alt={ext.user_name || ''}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="h-full w-full bg-indigo-600 text-white font-extrabold flex items-center justify-center text-xs">
                                                            {ext.user_name ? ext.user_name.substring(0, 2).toUpperCase() : 'EX'}
                                                        </div>
                                                    )}
                                                </div>

                                                {/* User Details */}
                                                <div className="min-w-0">
                                                    <h4 className="font-bold text-sm text-slate-900 dark:text-slate-100 truncate">
                                                        {ext.user_name || t('Unassigned')}
                                                    </h4>
                                                    <p className="text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">
                                                        {ext.user_email || ext.caller_id || `Ext ${ext.extension}`}
                                                    </p>
                                                </div>
                                            </div>

                                            {canEditExt(ext) && (
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 w-8 p-0 text-slate-400 hover:text-slate-700 dark:hover:text-white"
                                                    onClick={() => router.visit(route('pbx.extensions.edit', ext.id))}
                                                >
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>

                                        <div className="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 pt-3 mt-1">
                                            {/* Config Active Badge */}
                                            <Badge className={`text-[10px] font-bold ${ext.is_active
                                                ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/50 dark:text-emerald-300'
                                                : 'bg-slate-100 text-slate-500 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-400'
                                                }`}>
                                                {ext.is_active ? t('Active Config') : t('Inactive')}
                                            </Badge>

                                            {/* Live Activity Status */}
                                            {renderLiveStatusBadge(ext.id)}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Users className="h-10 w-10 text-slate-300 mb-3" />
                                <p className="text-sm font-medium text-slate-600 dark:text-slate-400">{t('No extensions created yet.')}</p>
                                <p className="text-xs text-slate-400 mt-1 max-w-sm">{t('Create your first PBX extension to connect users to the phone system.')}</p>
                                {canCreateExtension && (canCreate) && (
                                    <Button size="sm" className="mt-4 bg-violet-600 hover:bg-violet-700 text-white font-bold" asChild>
                                        <Link href={route('pbx.extensions.create')}>
                                            <Plus className="mr-1.5 h-4 w-4" />
                                            {t('Create Extension')}
                                        </Link>
                                    </Button>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
