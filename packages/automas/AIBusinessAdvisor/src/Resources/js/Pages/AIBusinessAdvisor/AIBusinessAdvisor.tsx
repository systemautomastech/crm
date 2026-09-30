"use client";

import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useEffect, useState } from 'react';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { formatDate } from '@/utils/helpers';
import { DatePicker } from '@/components/ui/date-picker';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { LineChart } from '@/components/charts/LineChart';
import {
    TrendingUp,
    TrendingDown,
    Minus,
    CheckCircle,
    XCircle,
    AlertTriangle,
    AlertOctagon,
    RefreshCw,
    Eye,
    Lightbulb,
    Target,
    Zap,
    Bell,
    Wallet,
    Users,
    ShoppingCart,
    Briefcase,
    Settings,
    BarChart3,
    ChevronRight,
    Activity,
    Filter,
} from 'lucide-react';
import type { DashboardProps } from './types';

const CustomTooltip = ({ active, payload }: any) => {
  if (active && payload && payload.length) {
    return (
      <div className="bg-white p-2 border border-gray-200 rounded shadow-lg">
        <p className="text-sm font-medium text-gray-900">{payload[0].value}</p>
      </div>
    );
  }
  return null;
};

export default function Dashboard() {
    const { t } = useTranslation();
    const { props } = usePage<{ auth: { user: { permissions: string[] } } }>();
    const { health_score, insights, recommendations, alerts, score_history, analysis_date, has_today_data, aiAdvisorEnabled, selected_date } =
        usePage<DashboardProps>().props;

    useFlashMessages();

    useEffect(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, []);

    const canManageAI = props.auth.user.permissions?.includes('manage-ai-business-advisor');
    const canManageStatus = props.auth.user.permissions?.includes('manage-ai-business-adviser-status');

    const todayStr = new Date().toISOString().split('T')[0];
    const [filterDate, setFilterDate] = useState<string>(selected_date || todayStr);
    const [alertModuleFilter, setAlertModuleFilter] = useState<string>('all');
    const [insightModuleFilter, setInsightModuleFilter] = useState<string>('all');
    const [recommendationModuleFilter, setRecommendationModuleFilter] = useState<string>('all');
    const [showAllRecommendations, setShowAllRecommendations] = useState(false);
    const [showAllInsights, setShowAllInsights] = useState(false);
    const [showAllAlerts, setShowAllAlerts] = useState(false);

    const handleDateChange = (date: string) => {
        setFilterDate(date);
        router.visit(route('ai-advisor.dashboard'), {
            data: { date },
            preserveScroll: true,
        });
    };

    const handleRefresh = () => {
        router.post(route('ai-advisor.generate'), {}, { preserveScroll: true });
    };

    const handleDismissInsight = (id: number) => {
        router.post(route('ai-advisor.insights.dismiss', id), {}, { preserveScroll: true });
    };

    const handleMarkDone = (id: number) => {
        router.post(route('ai-advisor.recommendations.done', id), {}, { preserveScroll: true });
    };

    const handleDismissRecommendation = (id: number) => {
        router.post(route('ai-advisor.recommendations.dismiss', id), {}, { preserveScroll: true });
    };

    const handleResolveAlert = (id: number) => {
        router.post(route('ai-advisor.alerts.resolve', id), {}, { preserveScroll: true });
    };

    const shouldShowRefreshButton = () => {
        const today = new Date().toISOString().split('T')[0];
        const isToday = filterDate === today;
        return isToday && !health_score;
    };

    const getTrendBadge = (trend: string) => {
        const trendLower = trend?.toLowerCase() || 'stable';
        switch (trendLower) {
            case 'improving':
                return (
                    <span className="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                        <TrendingUp className="h-3 w-3" />
                        {t('Improving')}
                    </span>
                );
            case 'declining':
                return (
                    <span className="inline-flex items-center gap-1 text-xs font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full">
                        <TrendingDown className="h-3 w-3" />
                        {t('Declining')}
                    </span>
                );
            default:
                return (
                    <span className="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">
                        <Minus className="h-3 w-3" />
                        {t('Stable')}
                    </span>
                );
        }
    };

    const getSeverityIcon = (severity: string) => {
        switch (severity) {
            case 'positive':
                return <CheckCircle className="h-4 w-4 text-emerald-500 shrink-0" />;
            case 'warning':
                return <AlertTriangle className="h-4 w-4 text-amber-500 shrink-0" />;
            case 'critical':
                return <AlertOctagon className="h-4 w-4 text-red-500 shrink-0" />;
            default:
                return <Lightbulb className="h-4 w-4 text-blue-500 shrink-0" />;
        }
    };

    const getPriorityBadge = (priority: string) => {
        switch (priority) {
            case 'high':
                return <span className="text-[10px] font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded">{t('High')}</span>;
            case 'medium':
                return <span className="text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">{t('Medium')}</span>;
            default:
                return <span className="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">{t('Low')}</span>;
        }
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'done':
                return <span className="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">{t('Done')}</span>;
            case 'dismissed':
                return <span className="text-[10px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{t('Dismissed')}</span>;
            default:
                return null;
        }
    };

    const getScoreColor = (score: number) => {
        if (score >= 80) return 'text-emerald-600';
        if (score >= 60) return 'text-amber-600';
        if (score >= 40) return 'text-orange-600';
        return 'text-red-600';
    };

    const getScoreBarColor = (score: number) => {
        if (score >= 80) return 'bg-emerald-500';
        if (score >= 60) return 'bg-amber-500';
        if (score >= 40) return 'bg-orange-500';
        return 'bg-red-500';
    };

    const getScoreIconBg = (score: number) => {
        if (score >= 80) return 'bg-emerald-50 text-emerald-600';
        if (score >= 60) return 'bg-amber-50 text-amber-600';
        if (score >= 40) return 'bg-orange-50 text-orange-600';
        return 'bg-red-50 text-red-600';
    };

    const subScores = health_score
        ? [
            { label: t('Financial'), value: health_score.financial_score, icon: Wallet },
            { label: t('Team'), value: health_score.team_score, icon: Users },
            { label: t('Sales'), value: health_score.sales_score, icon: ShoppingCart },
            { label: t('Projects'), value: health_score.project_score, icon: Briefcase },
            { label: t('Operations'), value: health_score.operations_score, icon: Settings },
        ]
        : [];

    const chartData = health_score
        ? [{ name: t('Score'), value: health_score.score, fill: '#3b82f6' }]
        : [];

    const lineChartData = [...score_history]
        .sort((a, b) => new Date(a.analysis_date).getTime() - new Date(b.analysis_date).getTime())
        .map((item) => ({
            date: formatDate(item.analysis_date),
            score: item.score,
        }));

    const analysisTime = analysis_date ? formatDate(analysis_date) : '';

    const pageActions = (
        <div className="flex items-center gap-2">
            <DatePicker
                value={filterDate}
                onChange={handleDateChange}
                placeholder={t('Filter by date')}
                className="w-44"
                maxDate={new Date()}
            />
            {canManageAI && shouldShowRefreshButton() && (
                <Button onClick={handleRefresh} variant="outline" size="sm" className="h-10 gap-1.5 text-xs px-3">
                    <RefreshCw className="h-3.5 w-3.5" />
                    <span className="hidden sm:inline">{t('Refresh')}</span>
                </Button>
            )}
        </div>
    );

    const filteredAlerts = (alertModuleFilter === 'all' ? alerts : alerts.filter(a => a.module === alertModuleFilter))
        .filter(a => showAllAlerts || !a.is_resolved);

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('AI Business Advisor') }
            ]}
            pageTitle={t('AI Business Advisor')}
            pageActions={pageActions}
        >
            <Head title={t('AI Business Advisor')} />

            <div className="space-y-5">
                {/* Not Enabled Warning */}
                {!aiAdvisorEnabled && (
                    <div className="flex items-start gap-3 p-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-800">
                        <AlertTriangle className="h-5 w-5 shrink-0 mt-0.5" />
                        <div>
                            <p className="font-medium text-sm">{t('AI Advisor Not Enabled')}</p>
                            <p className="text-xs opacity-90 mt-0.5">{t('Please contact your superadmin to enable it in AI Advisor Settings.')}</p>
                        </div>
                    </div>
                )}

                {/* Empty State */}
                {!health_score && (
                    <Card className="border-dashed">
                        <CardContent className="flex flex-col items-center text-center py-12">
                            <BarChart3 className="h-12 w-12 text-muted-foreground/40 mb-3" />
                            <p className="font-medium text-base">{t('No analysis available yet')}</p>
                            <p className="text-muted-foreground mt-1 text-sm max-w-sm">
                                {t('Check back after the next scheduled run, or trigger it manually.')}
                            </p>
                            {canManageAI && shouldShowRefreshButton() && (
                                <Button onClick={handleRefresh} size="sm" className="mt-4 gap-1.5 h-9 text-xs">
                                    <Zap className="h-3.5 w-3.5" />
                                    {t('Run Analysis')}
                                </Button>
                            )}
                        </CardContent>
                    </Card>
                )}

                {health_score && (
                    <>
                        {/* Main Score + Category Breakdown */}
                        <div className="grid gap-4 lg:grid-cols-3">
                            {/* Main Health Score Card */}
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium text-muted-foreground flex items-center justify-between">
                                        <span className="flex items-center gap-2">
                                            <Activity className="h-4 w-4" />
                                            {t('Overall Health Score')}
                                        </span>
                                        {getTrendBadge(health_score.trend)}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-1">
                                    <div className="flex flex-col items-center justify-center gap-4">
                                        <div className="relative w-40 h-40 flex items-center justify-center">
                                            <svg className="w-full h-full" viewBox="0 0 160 160" style={{ transform: 'rotate(-90deg)' }}>
                                                <circle cx="80" cy="80" r="70" fill="none" stroke="#e5e7eb" strokeWidth="8" />
                                                <circle
                                                    cx="80"
                                                    cy="80"
                                                    r="70"
                                                    fill="none"
                                                    stroke={health_score.score >= 80 ? '#10b981' : health_score.score >= 60 ? '#f59e0b' : health_score.score >= 40 ? '#f97316' : '#ef4444'}
                                                    strokeWidth="8"
                                                    strokeDasharray={`${(health_score.score / 100) * 439.82} 439.82`}
                                                    strokeLinecap="round"
                                                    style={{ transition: 'stroke-dasharray 0.5s ease' }}
                                                />
                                            </svg>
                                            <div className="absolute inset-0 flex flex-col items-center justify-center">
                                                <div className={`text-4xl font-bold ${getScoreColor(health_score.score)}`}>
                                                    {health_score.score}
                                                </div>
                                                <div className="text-xs text-muted-foreground mt-0.5">/100</div>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Category Breakdown */}
                            <Card className="lg:col-span-2">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                                        <BarChart3 className="h-4 w-4" />
                                        {t('Category Breakdown')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                                        {subScores.map((sub) => (
                                            <div key={sub.label} className="space-y-1.5">
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-2">
                                                        <div className={`p-1.5 rounded-md ${getScoreIconBg(sub.value)}`}>
                                                            <sub.icon className="h-3.5 w-3.5" />
                                                        </div>
                                                        <span className="text-sm font-medium text-foreground">{sub.label}</span>
                                                    </div>
                                                    <span className={`text-sm font-bold ${getScoreColor(sub.value)}`}>{Math.round(sub.value)}%</span>
                                                </div>
                                                <div className="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                                    <div
                                                        className={`h-full rounded-full transition-all duration-500 ${getScoreBarColor(sub.value)}`}
                                                        style={{ width: `${sub.value}%` }}
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Trend Chart */}
                        {lineChartData.length > 0 && (
                            <Card>
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                                        <TrendingUp className="h-4 w-4 text-blue-500" />
                                        {t('7-day Trend')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <LineChart
                                        data={lineChartData}
                                        dataKey="score"
                                        xAxisKey="date"
                                        height={260}
                                        showGrid={true}
                                        showDots={true}
                                        color="#3b82f6"
                                        strokeWidth={2}
                                    />
                                </CardContent>
                            </Card>
                        )}

                        {/* Alerts, Insights, Recommendations Grid */}
                        <div className="grid gap-4 lg:grid-cols-3 items-stretch">
                            {/* Alerts */}
                            <Card className="h-full flex flex-col">
                                <CardHeader className="pb-3 border-b">
                                    <div className="flex items-center justify-between gap-2 flex-wrap">
                                        <div className="flex items-center gap-2">
                                            <CardTitle className="text-sm font-medium flex items-center gap-2">
                                                <Bell className="h-4 w-4 text-red-500" />
                                                {t('Alerts')}
                                            </CardTitle>
                                            {alerts.length > 0 && (
                                                <Badge variant="secondary" className="text-[10px] h-5">
                                                    {filteredAlerts.length}
                                                </Badge>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {alerts.length > 0 && (
                                                <div className="relative">
                                                    <Filter className="absolute left-2 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground pointer-events-none" />
                                                    <select
                                                        value={alertModuleFilter}
                                                        onChange={(e) => setAlertModuleFilter(e.target.value)}
                                                        className="h-7 pl-6 pr-6 text-[11px] font-medium rounded-md bg-background text-foreground appearance-none cursor-pointer focus:outline-none focus:ring-1 focus:ring-ring hover:bg-accent transition-colors"
                                                    >
                                                        <option value="all">{t('All Modules')}</option>
                                                        {[...new Set(alerts.map(a => a.module))].sort().map((mod) => (
                                                            <option key={mod} value={mod}>
                                                                {t(mod.charAt(0).toUpperCase() + mod.slice(1))}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    <ChevronRight className="absolute right-2 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground pointer-events-none rotate-90" />
                                                </div>
                                            )}
                                            <label className="flex items-center gap-1.5 cursor-pointer select-none">
                                                <Checkbox
                                                    checked={showAllAlerts}
                                                    onCheckedChange={(checked) => setShowAllAlerts(!!checked)}
                                                    className="h-3.5 w-3.5"
                                                />
                                                <span className="text-[11px] font-medium text-muted-foreground">{t('Show all')}</span>
                                            </label>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-3 pt-3 h-[400px] overflow-y-auto">
                                    {filteredAlerts.length === 0 ? (
                                        <div className="flex flex-col items-center justify-center text-center py-10">
                                            <CheckCircle className="h-10 w-10 text-emerald-400 mb-2" />
                                            <p className="text-sm text-muted-foreground">{t('No active alerts')}</p>
                                        </div>
                                    ) : (
                                        filteredAlerts.map((alert) => (
                                            <div
                                                key={alert.id}
                                                className={`p-3 rounded-lg border transition-opacity ${alert.is_resolved ? 'opacity-60 bg-gray-50/50 border-gray-200' : alert.severity === 'critical' ? 'bg-red-50/50 border-red-200' : 'bg-amber-50/50 border-amber-200'}`}
                                            >
                                                <div className="flex items-start gap-2 justify-between">
                                                    <div className="flex items-start gap-2 flex-1">
                                                        <AlertTriangle className={`h-4 w-4 shrink-0 mt-0.5 ${alert.is_resolved ? 'text-gray-400' : alert.severity === 'critical' ? 'text-red-500' : 'text-amber-500'}`} />
                                                        <div className="flex-1 min-w-0">
                                                            <div className="flex items-center gap-1.5 mb-0.5 flex-wrap">
                                                                <span className="font-medium text-xs">{alert.title}</span>
                                                                <Badge variant="outline" className="text-[9px] h-4 px-1">{alert.module.charAt(0).toUpperCase() + alert.module.slice(1)}</Badge>
                                                            </div>
                                                            <p className="text-xs text-muted-foreground leading-relaxed">{alert.message}</p>
                                                        </div>
                                                    </div>
                                                    {!alert.is_resolved && canManageStatus ? (
                                                        <Button
                                                            size="sm"
                                                            variant="ghost"
                                                            onClick={() => handleResolveAlert(alert.id)}
                                                            className={`h-7 text-xs px-2 gap-1 shrink-0 ml-2 ${alert.severity === 'critical' ? 'hover:bg-red-50' : 'hover:bg-amber-50'}`}
                                                        >
                                                            <CheckCircle className="h-3 w-3" />
                                                            {t('Mark as read')}
                                                        </Button>
                                                    ) : alert.is_resolved ? (
                                                        <span className="text-[10px] text-muted-foreground italic shrink-0 self-center ml-2">
                                                            {t('Resolved')}
                                                        </span>
                                                    ) : null}
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </CardContent>
                            </Card>

                            {/* Insights */}
                            <Card className="h-full flex flex-col">
                                <CardHeader className="pb-3 border-b">
                                    <div className="flex items-center justify-between gap-2 flex-wrap">
                                        <div className="flex items-center gap-2">
                                            <CardTitle className="text-sm font-medium flex items-center gap-2">
                                                <Lightbulb className="h-4 w-4 text-blue-500" />
                                                {t('Insights')}
                                            </CardTitle>
                                            {insights.length > 0 && (
                                                <Badge variant="secondary" className="text-[10px] h-5">
                                                    {insightModuleFilter === 'all'
                                                        ? insights.filter(i => showAllInsights || !i.is_dismissed).length
                                                        : insights.filter(i => i.module === insightModuleFilter && (showAllInsights || !i.is_dismissed)).length}
                                                </Badge>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {insights.length > 0 && (
                                                <div className="relative">
                                                    <Filter className="absolute left-2 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground pointer-events-none" />
                                                    <select
                                                        value={insightModuleFilter}
                                                        onChange={(e) => setInsightModuleFilter(e.target.value)}
                                                        className="h-7 pl-6 pr-6 text-[11px] font-medium rounded-md bg-background text-foreground appearance-none cursor-pointer focus:outline-none focus:ring-1 focus:ring-ring hover:bg-accent transition-colors"
                                                    >
                                                        <option value="all">{t('All Modules')}</option>
                                                        {[...new Set(insights.map(i => i.module))].sort().map((mod) => (
                                                            <option key={mod} value={mod}>
                                                                {t(mod.charAt(0).toUpperCase() + mod.slice(1))}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    <ChevronRight className="absolute right-2 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground pointer-events-none rotate-90" />
                                                </div>
                                            )}
                                            <label className="flex items-center gap-1.5 cursor-pointer select-none">
                                                <Checkbox
                                                    checked={showAllInsights}
                                                    onCheckedChange={(checked) => setShowAllInsights(!!checked)}
                                                    className="h-3.5 w-3.5"
                                                />
                                                <span className="text-[11px] font-medium text-muted-foreground">{t('Show all')}</span>
                                            </label>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-3 pt-3 h-[400px] overflow-y-auto">
                                    {(() => {
                                        const baseList = insightModuleFilter === 'all'
                                            ? insights
                                            : insights.filter(i => i.module === insightModuleFilter);
                                        const visibleList = baseList.filter(i => showAllInsights || !i.is_dismissed);

                                        return visibleList.length === 0 ? (
                                            <div className="flex flex-col items-center justify-center text-center py-10">
                                                <CheckCircle className="h-10 w-10 text-emerald-400 mb-2" />
                                                <p className="text-sm text-muted-foreground">{t('No insights match the selected filter.')}</p>
                                            </div>
                                        ) : (
                                            visibleList.map((insight) => (
                                                <div
                                                    key={insight.id}
                                                    className={`flex items-start gap-2.5 p-3 rounded-lg border transition-opacity ${insight.is_dismissed ? 'opacity-60 bg-gray-50/50 border-gray-200' : insight.severity === 'positive' ? 'bg-emerald-50/30 border-emerald-200'
                                                        : insight.severity === 'warning' ? 'bg-amber-50/30 border-amber-200'
                                                            : insight.severity === 'critical' ? 'bg-red-50/30 border-red-200'
                                                                : 'bg-blue-50/30 border-blue-200'
                                                    }`}
                                                >
                                                    <div className="mt-0.5 shrink-0">{getSeverityIcon(insight.severity)}</div>
                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-1.5 mb-0.5 flex-wrap">
                                                            <span className="font-medium text-xs">{insight.title}</span>
                                                            <Badge variant="outline" className="text-[9px] h-4 px-1">{insight.module.charAt(0).toUpperCase() + insight.module.slice(1)}</Badge>
                                                             
                                                        </div>
                                                        <p className="text-xs text-muted-foreground leading-relaxed">{insight.description}</p>
                                                    </div>
                                                    {!insight.is_dismissed && canManageStatus ? (
                                                        <button
                                                            onClick={() => handleDismissInsight(insight.id)}
                                                            className="shrink-0 p-1 rounded text-muted-foreground hover:text-red-500 hover:bg-red-50 transition-colors"
                                                            title={t('Dismiss')}
                                                        >
                                                            <XCircle className="h-3.5 w-3.5" />
                                                        </button>
                                                    ) : insight.is_dismissed ? (
                                                        <span className="text-[10px] text-muted-foreground italic shrink-0 self-center">
                                                            {t('Dismissed')}
                                                        </span>
                                                    ) : null}
                                                </div>
                                            ))
                                        );
                                    })()}
                                </CardContent>
                            </Card>

                            {/* Recommendations */}
                            <Card className="h-full flex flex-col">
                                <CardHeader className="pb-3 border-b">
                                    <div className="flex items-center justify-between gap-2 flex-wrap">
                                        <div className="flex items-center gap-2">
                                            <CardTitle className="text-sm font-medium flex items-center gap-2">
                                                <Target className="h-4 w-4 text-purple-500" />
                                                {t('Recommendations')}
                                            </CardTitle>
                                            {recommendations.length > 0 && (
                                                <Badge variant="secondary" className="text-[10px] h-5">
                                                    {recommendationModuleFilter === 'all'
                                                        ? recommendations.filter(r => showAllRecommendations || r.status === 'pending').length
                                                        : recommendations.filter(r => r.related_module === recommendationModuleFilter && (showAllRecommendations || r.status === 'pending')).length}
                                                </Badge>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {recommendations.length > 0 && (
                                                <div className="relative">
                                                    <Filter className="absolute left-2 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground pointer-events-none" />
                                                    <select
                                                        value={recommendationModuleFilter}
                                                        onChange={(e) => setRecommendationModuleFilter(e.target.value)}
                                                        className="h-7 pl-6 pr-6 text-[11px] font-medium rounded-md bg-background text-foreground appearance-none cursor-pointer focus:outline-none focus:ring-1 focus:ring-ring hover:bg-accent transition-colors"
                                                    >
                                                        <option value="all">{t('All Modules')}</option>
                                                        {[...new Set(recommendations.map(r => r.related_module))].sort().map((mod) => (
                                                            <option key={mod} value={mod}>
                                                                {t(mod.charAt(0).toUpperCase() + mod.slice(1))}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    <ChevronRight className="absolute right-2 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground pointer-events-none rotate-90" />
                                                </div>
                                            )}
                                            <label className="flex items-center gap-1.5 cursor-pointer select-none">
                                                <Checkbox
                                                    checked={showAllRecommendations}
                                                    onCheckedChange={(checked) => setShowAllRecommendations(!!checked)}
                                                    className="h-3.5 w-3.5"
                                                />
                                                <span className="text-[11px] font-medium text-muted-foreground">{t('Show all')}</span>
                                            </label>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-3 pt-3 h-[400px] overflow-y-auto">
                                    {(() => {
                                        const baseList = recommendationModuleFilter === 'all'
                                            ? recommendations
                                            : recommendations.filter(r => r.related_module === recommendationModuleFilter);
                                        const visibleList = baseList.filter(r => showAllRecommendations || r.status === 'pending');

                                        return visibleList.length === 0 ? (
                                            <div className="flex flex-col items-center justify-center text-center py-10">
                                                <CheckCircle className="h-10 w-10 text-emerald-400 mb-2" />
                                                <p className="text-sm text-muted-foreground">{t('No recommendations match the selected filter.')}</p>
                                            </div>
                                        ) : (
                                            visibleList.map((rec) => (
                                                <div
                                                    key={rec.id}
                                                    className={`rounded-lg border p-3 space-y-2 transition-opacity ${rec.status !== 'pending' ? 'opacity-60 bg-gray-50/50' : ''}`}
                                                >
                                                    <div className="flex items-center gap-1.5 flex-wrap">
                                                        {getPriorityBadge(rec.priority)}
                                                        {rec.related_module && (
                                                            <Badge variant="outline" className="text-[9px] h-4 px-1">{rec.related_module.charAt(0).toUpperCase() + rec.related_module.slice(1)}</Badge>
                                                        )}
                                                    </div>

                                                    <p className="text-xs font-medium leading-relaxed">{rec.recommendation}</p>
                                                    {rec.reason && (
                                                        <p className="text-[11px] text-muted-foreground leading-relaxed">{rec.reason}</p>
                                                    )}

                                                    <div className="flex items-center justify-between pt-1">
                                                        {rec.status === 'pending' && canManageStatus ? (
                                                            <div className="flex items-center gap-1">
                                                                <Button
                                                                    size="sm"
                                                                    variant="ghost"
                                                                    onClick={() => handleMarkDone(rec.id)}
                                                                    className="h-7 text-xs px-2 gap-1 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                                                                >
                                                                    <CheckCircle className="h-3 w-3" />
                                                                    {t('Done')}
                                                                </Button>
                                                                <Button
                                                                    size="sm"
                                                                    variant="ghost"
                                                                    onClick={() => handleDismissRecommendation(rec.id)}
                                                                    className="h-7 text-xs px-2 gap-1 text-muted-foreground hover:text-red-600 hover:bg-red-50"
                                                                >
                                                                    <XCircle className="h-3 w-3" />
                                                                    {t('Dismiss')}
                                                                </Button>
                                                            </div>
                                                        ) : rec.status !== 'pending' ? (
                                                            <div className="flex items-center gap-1">
                                                                <span className="text-[10px] text-muted-foreground italic">
                                                                    {rec.status === 'done' ? t('Marked as done') : t('Dismissed')}
                                                                </span>
                                                            </div>
                                                        ) : null}
                                                    </div>
                                                </div>
                                            ))
                                        );
                                    })()}
                                </CardContent>
                            </Card>
                        </div>
                    </>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
