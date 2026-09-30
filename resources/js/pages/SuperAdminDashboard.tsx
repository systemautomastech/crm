import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Head, Link } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { LineChart } from '@/components/charts';
import { Building2, ShoppingCart, CreditCard, Crown, ExternalLink } from "lucide-react";
import { formatCurrency } from '@/utils/helpers';

interface SuperAdminDashboardProps {
    stats: {
        order_payments: number;
        total_orders: number;
        total_plans: number;
        total_companies: number;
    };
    chartData: Array<{
        month: string;
        orders: number;
        payments: number;
    }>;
    ticketChartData: Array<{
        month: string;
        created: number;
        resolved: number;
    }>;
    recentTickets: Array<{
        id: number;
        ticket_id: string;
        title: string;
        status: string;
        priority: string;
        category: string;
        category_color: string;
        creator: string;
        created_at: string;
    }>;
    weeklyPendingTickets: Array<{
        id: number;
        ticket_id: string;
        title: string;
        status: string;
        priority: string;
        category: string;
        category_color: string;
        creator: string;
        created_at: string;
        last_reply_at: string;
        days_pending: number;
    }>;
}

export default function SuperAdminDashboard({ stats, chartData, ticketChartData, recentTickets, weeklyPendingTickets }: SuperAdminDashboardProps) {
    const { t } = useTranslation();

    const getStatusBadgeColor = (status: string) => {
        switch(status) {
            case 'open': return 'bg-blue-100 text-blue-800';
            case 'in_progress': return 'bg-yellow-100 text-yellow-800';
            case 'resolved': return 'bg-green-100 text-green-800';
            case 'closed': return 'bg-gray-100 text-gray-800';
            default: return 'bg-purple-100 text-purple-800';
        }
    };

    const getPriorityBadgeColor = (priority: string) => {
        switch(priority) {
            case 'low': return 'bg-green-100 text-green-800';
            case 'medium': return 'bg-yellow-100 text-yellow-800';
            case 'high': return 'bg-orange-100 text-orange-800';
            case 'urgent': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Dashboard') }]}
            pageTitle={t('Dashboard')}
        >
            <Head title={t('Dashboard')} />

            {/* Stats Cards */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card className="relative overflow-hidden bg-gradient-to-r from-green-50 to-green-100 border-green-200">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-green-700">{t('Total Orders')}</CardTitle>
                        <ShoppingCart className="h-8 w-8 text-green-700 opacity-80" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-green-700">{stats.total_orders}</div>
                        <p className="text-xs text-green-700 opacity-80 mt-1">{t('All orders')}</p>
                    </CardContent>
                </Card>

                <Card className="relative overflow-hidden bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-blue-700">{t('Order Payments')}</CardTitle>
                        <CreditCard className="h-8 w-8 text-blue-700 opacity-80" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-blue-700">{formatCurrency(stats.order_payments)}</div>
                        <p className="text-xs text-blue-700 opacity-80 mt-1">{t('Total payments')}</p>
                    </CardContent>
                </Card>

                <Card className="relative overflow-hidden bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-purple-700">{t('Total Plans')}</CardTitle>
                        <Crown className="h-8 w-8 text-purple-700 opacity-80" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-purple-700">{stats.total_plans}</div>
                        <p className="text-xs text-purple-700 opacity-80 mt-1">{t('Available plans')}</p>
                    </CardContent>
                </Card>

                <Card className="relative overflow-hidden bg-gradient-to-r from-orange-50 to-orange-100 border-orange-200">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-orange-700">{t('Total Companies')}</CardTitle>
                        <Building2 className="h-8 w-8 text-orange-700 opacity-80" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-orange-700">{stats.total_companies}</div>
                        <p className="text-xs text-orange-700 opacity-80 mt-1">{t('Registered companies')}</p>
                    </CardContent>
                </Card>
            </div>

            {/* Recent Orders Chart */}
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle className="text-lg">{t('Recent Orders (Monthly)')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <LineChart
                        data={chartData}
                        dataKey="orders"
                        height={300}
                        showTooltip={true}
                        showGrid={true}
                        lines={[
                            { dataKey: 'orders', color: '#3b82f6', name: 'Orders' }
                        ]}
                        xAxisKey="month"
                        showLegend={true}
                    />
                </CardContent>
            </Card>

            {/* Helpdesk Section */}
            <div className="grid gap-6 md:grid-cols-2 mt-6">
                {/* Recent Helpdesk Activity */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-lg">{t('Recent Helpdesk Activity')}</CardTitle>
                        {recentTickets && recentTickets.length > 0 && (
                            <span className="text-sm text-muted-foreground">
                                {recentTickets.length} {recentTickets.length === 1 ? 'ticket' : 'tickets'}
                            </span>
                        )}
                    </CardHeader>
                    <CardContent>
                        {recentTickets && recentTickets.length > 0 ? (
                            <div className="space-y-2">
                                {recentTickets.map((ticket) => (
                                    <Link
                                        key={ticket.id}
                                        href={route('helpdesk-tickets.show', ticket.id)}
                                        className="block p-3 rounded-lg border hover:bg-accent/50 transition-all relative overflow-hidden"
                                    >
                                        {ticket.category_color && (
                                            <div 
                                                className="absolute left-0 top-0 bottom-0 w-1" 
                                                style={{ backgroundColor: ticket.category_color }}
                                            />
                                        )}
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center gap-2 mb-2">
                                                    <span className="text-xs font-mono text-blue-600 font-semibold">#{ticket.ticket_id}</span>
                                                    <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${getPriorityBadgeColor(ticket.priority)}`}>
                                                        {t(ticket.priority.charAt(0).toUpperCase() + ticket.priority.slice(1))}
                                                    </span>
                                                    <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeColor(ticket.status)}`}>
                                                        {t(ticket.status.replace('_', ' ').charAt(0).toUpperCase() + ticket.status.replace('_', ' ').slice(1))}
                                                    </span>
                                                </div>
                                                <p className="text-sm font-medium text-gray-900 mb-2 line-clamp-2">
                                                    {ticket.title}
                                                </p>
                                                <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                                    {ticket.category && (
                                                        <>
                                                            <div className="flex items-center gap-1.5">
                                                                {ticket.category_color && (
                                                                    <span 
                                                                        className="w-2 h-2 rounded-full flex-shrink-0" 
                                                                        style={{ backgroundColor: ticket.category_color }}
                                                                    />
                                                                )}
                                                                <span className="font-medium">Category:</span>
                                                                <span>{ticket.category}</span>
                                                            </div>
                                                            <span className="text-gray-300">•</span>
                                                        </>
                                                    )}
                                                    <div className="flex items-center gap-1">
                                                        <span className="font-medium">From:</span>
                                                        <span>{ticket.creator}</span>
                                                    </div>
                                                    <span className="text-gray-300">•</span>
                                                    <div className="flex items-center gap-1">
                                                        <span>{ticket.created_at}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <ExternalLink className="h-4 w-4 text-muted-foreground flex-shrink-0 mt-1" />
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12 text-muted-foreground">
                                <div className="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                    <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <p className="font-medium text-gray-900 mb-1">{t('No recent activity')}</p>
                                <p className="text-sm">{t('No tickets have been created yet')}</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Tickets Awaiting Your Response */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-lg">{t('Tickets Awaiting Your Response')}</CardTitle>
                        {weeklyPendingTickets && weeklyPendingTickets.length > 0 && (
                            <span className="text-sm text-muted-foreground">
                                {weeklyPendingTickets.length} {weeklyPendingTickets.length === 1 ? 'ticket' : 'tickets'}
                            </span>
                        )}
                    </CardHeader>
                    <CardContent>
                        {weeklyPendingTickets && weeklyPendingTickets.length > 0 ? (
                            <div className="space-y-2">
                                {weeklyPendingTickets.map((ticket) => {
                                    const daysAgo = ticket.days_pending;
                                    let timeDisplay = '';
                                    
                                    if (daysAgo < 1) {
                                        timeDisplay = 'Today';
                                    } else if (daysAgo < 2) {
                                        timeDisplay = '1 day ago';
                                    } else if (daysAgo < 30) {
                                        timeDisplay = `${Math.floor(daysAgo)} days ago`;
                                    } else if (daysAgo < 60) {
                                        timeDisplay = '1 month ago';
                                    } else if (daysAgo < 365) {
                                        timeDisplay = `${Math.floor(daysAgo / 30)} months ago`;
                                    } else {
                                        timeDisplay = `${Math.floor(daysAgo / 365)} year${Math.floor(daysAgo / 365) > 1 ? 's' : ''} ago`;
                                    }

                                    return (
                                        <Link
                                            key={ticket.id}
                                            href={route('helpdesk-tickets.show', ticket.id)}
                                            className="block p-3 rounded-lg border hover:bg-accent/50 transition-all group relative overflow-hidden"
                                        >
                                            {ticket.category_color && (
                                                <div 
                                                    className="absolute left-0 top-0 bottom-0 w-1" 
                                                    style={{ backgroundColor: ticket.category_color }}
                                                />
                                            )}
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center gap-2 mb-2">
                                                        <span className="text-xs font-mono text-blue-600 font-semibold">#{ticket.ticket_id}</span>
                                                        <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${getPriorityBadgeColor(ticket.priority)}`}>
                                                            {t(ticket.priority.charAt(0).toUpperCase() + ticket.priority.slice(1))}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm font-medium text-gray-900 mb-2 line-clamp-2">
                                                        {ticket.title}
                                                    </p>
                                                    <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                                        {ticket.category && (
                                                            <>
                                                                <div className="flex items-center gap-1.5">
                                                                    {ticket.category_color && (
                                                                        <span 
                                                                            className="w-2 h-2 rounded-full flex-shrink-0" 
                                                                            style={{ backgroundColor: ticket.category_color }}
                                                                        />
                                                                    )}
                                                                    <span className="font-medium">Category:</span>
                                                                    <span>{ticket.category}</span>
                                                                </div>
                                                                <span className="text-gray-300">•</span>
                                                            </>
                                                        )}
                                                        <div className="flex items-center gap-1">
                                                            <span className="font-medium">From:</span>
                                                            <span>{ticket.creator}</span>
                                                        </div>
                                                        <span className="text-gray-300">•</span>
                                                        <div className="flex items-center gap-1">
                                                            <span className="font-medium text-orange-600">Waiting:</span>
                                                            <span className="text-orange-600 font-medium">{timeDisplay}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <ExternalLink className="h-4 w-4 text-muted-foreground flex-shrink-0 mt-1" />
                                            </div>
                                        </Link>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="text-center py-12 text-muted-foreground">
                                <div className="mx-auto w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mb-3">
                                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p className="font-medium text-gray-900 mb-1">{t('All caught up!')}</p>
                                <p className="text-sm">{t('No tickets awaiting response')}</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Monthly Ticket Trends */}
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle className="text-lg">{t('Monthly Ticket Trends')}</CardTitle>
                </CardHeader>
                <CardContent>
                    {ticketChartData && ticketChartData.length > 0 ? (
                        <LineChart
                            data={ticketChartData}
                            dataKey="created"
                            height={300}
                            showTooltip={true}
                            showGrid={true}
                            lines={[
                                { dataKey: 'created', color: '#3b82f6', name: 'Created' },
                                { dataKey: 'resolved', color: '#10b981', name: 'Resolved' }
                            ]}
                            xAxisKey="month"
                            showLegend={true}
                        />
                    ) : (
                        <div className="flex items-center justify-center h-[300px] text-muted-foreground">
                            {t('No ticket trend data available')}
                        </div>
                    )}
                </CardContent>
            </Card>

        </AuthenticatedLayout>
    );
}
