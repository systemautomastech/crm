import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TrendingUp, Rocket, Calendar, Clock, DollarSign, Target } from 'lucide-react';
import CalendarView from '@/components/calendar-view';
import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer } from 'recharts';
import { formatDate, formatCurrency } from '@/utils/helpers';

interface ClientDashboardProps {
    message: string;
    stats?: {
        total_deals: number;
        active_deals: number;
        won_deals: number;
        total_value: number;
    };
    recentDeals?: any[];
    calendarEvents?: any[];
    dealStatusChart?: any[];
}

export default function ClientDashboard({ message, stats, recentDeals, calendarEvents, dealStatusChart }: ClientDashboardProps) {
    const { t } = useTranslation();
    const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8'];

    return (
        <AuthenticatedLayout
            breadcrumbs={[{label: t('Dashboard')}]}
            pageTitle={t('Client Dashboard')}
        >
            <Head title={t('Client Dashboard')} />

            <div className="space-y-6">
                {/* Summary Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="bg-blue-50/70 dark:bg-blue-950/40 border-blue-200/70 dark:border-blue-900/50 hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-blue-700 dark:text-blue-400">{t('Total Deals')}</CardTitle>
                            <Rocket className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-800 dark:text-white">{stats?.total_deals || 0}</div>
                        </CardContent>
                    </Card>
                    <Card className="bg-emerald-50/70 dark:bg-emerald-950/40 border-emerald-200/70 dark:border-emerald-900/50 hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-emerald-700 dark:text-emerald-400">{t('Active Deals')}</CardTitle>
                            <Target className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-800 dark:text-white">{stats?.active_deals || 0}</div>
                        </CardContent>
                    </Card>
                    <Card className="bg-purple-50/70 dark:bg-purple-950/40 border-purple-200/70 dark:border-purple-900/50 hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-purple-700 dark:text-purple-400">{t('Won Deals')}</CardTitle>
                            <TrendingUp className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-purple-800 dark:text-white">{stats?.won_deals || 0}</div>
                        </CardContent>
                    </Card>
                    <Card className="bg-amber-50/70 dark:bg-amber-950/40 border-amber-200/70 dark:border-amber-900/50 hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="text-sm font-medium text-amber-700 dark:text-amber-400">{t('Total Value')}</CardTitle>
                            <DollarSign className="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-800 dark:text-white">{formatCurrency(stats?.total_value || 0)}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    {/* Calendar */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Calendar className="h-5 w-5" />
                                {t('My Tasks Calendar')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CalendarView
                                events={calendarEvents?.map(event => ({
                                    id: event.id,
                                    title: event.title,
                                    startDate: event.startDate,
                                    endDate: event.endDate,
                                    time: event.time || '00:00',
                                    color: 'hsl(var(--primary))',
                                    description: `${t('Task')}: ${event.title} - ${t('Deal')}: ${event.name || ''} - ${t('Status')}: ${t(event.status?.charAt(0).toUpperCase() + event.status?.slice(1) || 'Unknown')}`,
                                    type: 'Deal Task',
                                })) || []}
                                onEventClick={(event) => { }}
                                onDateClick={(date) => { }}
                            />
                        </CardContent>
                    </Card>

                    {/* Charts and Recent Activity */}
                    <div className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Target className="h-5 w-5 text-primary" />
                                    {t('Deal Status')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {dealStatusChart && dealStatusChart.length > 0 ? (
                                    <ResponsiveContainer width="100%" height={200}>
                                        <PieChart>
                                            <Pie
                                                data={dealStatusChart}
                                                cx="50%"
                                                cy="50%"
                                                innerRadius={40}
                                                outerRadius={80}
                                                dataKey="value"
                                                nameKey="name"
                                            >
                                                {dealStatusChart.map((entry, index) => (
                                                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                                ))}
                                            </Pie>
                                            <Tooltip />
                                        </PieChart>
                                    </ResponsiveContainer>
                                ) : (
                                    <div className="h-[200px] flex items-center justify-center text-gray-500">
                                        <p className="text-sm">{t('No deal data available')}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Clock className="h-5 w-5 text-primary" />
                                    {t('Recent Deals')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {recentDeals && recentDeals.length > 0 ? (
                                    <div className="space-y-3 max-h-80 overflow-y-auto">
                                        {recentDeals.map((deal) => (
                                            <div key={deal.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                                <div className="flex-1">
                                                    <h4 className="font-medium text-sm text-gray-900">{deal.name}</h4>
                                                    <p className="text-xs text-gray-600 mt-1">{deal.stage?.name}</p>
                                                </div>
                                                <div className="text-right">
                                                    <p className="text-xs text-gray-500">{formatDate(deal.created_at)}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12 text-gray-500">
                                        <Clock className="h-12 w-12 mx-auto mb-3 opacity-30" />
                                        <p className="text-sm font-medium">{t('No recent deals')}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>


            </div>
        </AuthenticatedLayout>
    );
}
