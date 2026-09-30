import AuthenticatedLayout from "@/layouts/authenticated-layout";
import DashboardDateFilter from "@/Components/dashboard-date-filter";
import { Head, usePage, router } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { LineChart, PieChart } from '@/components/charts';
import { FolderKanban, CheckSquare, Bug, Users, UserCheck, Receipt, DollarSign } from 'lucide-react';
import { formatCurrency, formatDate } from '@/utils/helpers';

interface Task {
    id: number;
    title: string;
    priority: string;
    project: string;
    stage: string;
    stage_color?: string;
    assignee: string;
    created_at: string;
    is_completed: boolean;
}

interface ChartData {
    name: string;
    value: number;
    color?: string;
}

interface TeamMember {
    name: string;
    total_tasks: number;
    completed_tasks: number;
    completion_rate: number;
}

interface CompanyDashboardProps {
    stats: {
        total_projects: number;
        total_tasks: number;
        total_bugs: number;
        total_users: number;
        total_clients: number;
        completed_tasks: number;
        completion_rate: number;
        overdue_projects: number;
    };
    recentTasks: Task[];
    projectStatus: ChartData[];
    taskPriority: ChartData[];
    teamPerformance: TeamMember[];
    monthlyProgress: Array<{ month: string; created: number; completed: number }>;
    bugStats: { open: number; resolved: number };
    paymentStats: {
        total_payments: number;
        draft_payments: number;
        posted_payments: number;
        total_amount: number;
        paid_amount: number;
        balance_amount: number;
    };
    recentPayments: Array<{
        id: number;
        payment_number: string;
        project: string;
        customer: string;
        total_amount: number;
        balance_amount: number;
        status: string;
        payment_date: string;
    }>;
}

export default function CompanyDashboard() {
    const { t } = useTranslation();
    const { stats, recentTasks, projectStatus, taskPriority, teamPerformance, monthlyProgress, bugStats, paymentStats, recentPayments } = usePage<CompanyDashboardProps>().props;

    const getPriorityColor = (priority: string) => {
        switch (priority.toLowerCase()) {
            case 'high': return 'bg-red-500 text-white';
            case 'medium': return 'bg-yellow-500 text-white';
            case 'low': return 'bg-green-500 text-white';
            default: return 'bg-gray-500 text-white';
        }
    };

    const getStageColor = (stage: string) => {
        switch (stage.toLowerCase()) {
            case 'done': return 'bg-green-100 text-green-800';
            case 'in progress': return 'bg-blue-100 text-blue-800';
            case 'review': return 'bg-purple-100 text-purple-800';
            case 'todo': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const StatCard = ({ title, value, subtitle, color = "blue", icon: Icon, extraStats }: any) => {
        const colorClasses = {
            blue: "bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200",
            green: "bg-gradient-to-r from-green-50 to-green-100 border-green-200",
            red: "bg-gradient-to-r from-red-50 to-red-100 border-red-200",
            purple: "bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200",
            orange: "bg-gradient-to-r from-orange-50 to-orange-100 border-orange-200"
        };
        const textColors = {
            blue: "text-blue-700",
            green: "text-green-700",
            red: "text-red-700",
            purple: "text-purple-700",
            orange: "text-orange-700"
        };
        return (
            <Card className={`relative overflow-hidden ${colorClasses[color as keyof typeof colorClasses]}`}>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className={`text-sm font-medium ${textColors[color as keyof typeof textColors]}`}>{title}</CardTitle>
                    {Icon && <Icon className={`h-8 w-8 ${textColors[color as keyof typeof textColors]} opacity-80`} />}
                </CardHeader>
                <CardContent>
                    <div className={`text-2xl font-bold ${textColors[color as keyof typeof textColors]}`}>{value}</div>
                    {subtitle && (
                        <p className={`text-xs ${textColors[color as keyof typeof textColors]} opacity-80 mt-1`}>{subtitle}</p>
                    )}
                    {extraStats && (
                        <div className="mt-3 pt-3 border-t border-current/20 grid grid-cols-2 gap-2">
                            {extraStats.map((stat: any, idx: number) => (
                                <div key={idx}>
                                    <p className={`text-xs ${textColors[color as keyof typeof textColors]} opacity-70`}>{stat.label}</p>
                                    <p className={`text-sm font-semibold ${textColors[color as keyof typeof textColors]}`}>{stat.value}</p>
                                </div>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>
        );
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Project Dashboard') }]}
        >
            <Head title={t('Project Dashboard')} />

            <div className="space-y-6">
                {/* Header Title Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-slate-900">{t('Project & Task Management Overview')}</h1>
                    </div>
                    <div className="flex items-center gap-2 self-start sm:self-auto">
                        <DashboardDateFilter />
                    </div>
                </div>
                {/* Company Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div onClick={() => router.get(route('project.index'))} className="cursor-pointer">
                        <StatCard
                            title={t('Projects & Tasks')}
                            value={stats.total_projects}
                            subtitle={stats.overdue_projects > 0 ? `${stats.overdue_projects} overdue projects` : 'All projects on track'}
                            color="blue"
                            icon={FolderKanban}
                            extraStats={[
                                { label: t('Completed'), value: `${stats.completion_rate}%` },
                                { label: t('Total Tasks'), value: stats.total_tasks }
                            ]}
                        />
                    </div>
                    <div onClick={() => router.get(route('project.bugs.index'))} className="cursor-pointer">
                        <StatCard
                            title={t('Bugs & Issues')}
                            value={bugStats.open}
                            subtitle={t('Active bugs')}
                            color="red"
                            icon={Bug}
                            extraStats={[
                                { label: t('Resolved'), value: bugStats.resolved },
                                { label: t('Total'), value: bugStats.open + bugStats.resolved }
                            ]}
                        />
                    </div>
                    <div onClick={() => router.get(route('users.index'))} className="cursor-pointer">
                        <StatCard
                            title={t('Team & Clients')}
                            value={stats.total_users}
                            subtitle={t('Staff members')}
                            color="purple"
                            icon={Users}
                            extraStats={[
                                { label: t('Clients'), value: stats.total_clients },
                                { label: t('Total Users'), value: stats.total_users + stats.total_clients }
                            ]}
                        />
                    </div>
                    <div onClick={() => router.get(route('project-payments.index'))} className="cursor-pointer">
                        <StatCard
                            title={t('Payments')}
                            value={paymentStats ? paymentStats.total_payments : 0}
                            subtitle={paymentStats ? `${paymentStats.posted_payments} posted, ${paymentStats.draft_payments} draft` : 'No payments'}
                            color="green"
                            icon={Receipt}
                            extraStats={paymentStats ? [
                                { label: t('Balance Due'), value: formatCurrency(paymentStats.balance_amount / 1000) + 'K' },
                                { label: t('Total Amount'), value: formatCurrency(paymentStats.total_amount / 1000) + 'K' }
                            ] : []}
                        />
                    </div>
                </div>

                {/* Company Progress Overview */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">{t('Company Monthly Progress')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <LineChart
                            data={monthlyProgress}
                            height={300}
                            showTooltip={true}
                            showGrid={true}
                            lines={[
                                { dataKey: 'created', color: '#3b82f6', name: 'Tasks Created' },
                                { dataKey: 'completed', color: '#10b981', name: 'Tasks Completed' }
                            ]}
                            xAxisKey="month"
                            showLegend={true}
                        />
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Project Status Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">{t('Project Status')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {projectStatus.length === 0 || !projectStatus.some(item => item.value > 0) ? (
                                    <PieChart
                                        data={[{ name: t('No Data'), value: 1, color: '#e5e7eb' }]}
                                        dataKey="value"
                                        nameKey="name"
                                        height={200}
                                        donut={true}
                                        showTooltip={false}
                                    />
                                ) : (
                                    <>
                                        <PieChart
                                            data={projectStatus.filter(item => item.value > 0)}
                                            dataKey="value"
                                            nameKey="name"
                                            height={200}
                                            donut={true}
                                            showTooltip={true}
                                        />
                                        <div className="space-y-2">
                                            {projectStatus.filter(item => item.value > 0).map((item, index) => (
                                                <div key={index} className="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                    <div className="flex items-center gap-2">
                                                        <div
                                                            className="w-3 h-3 rounded-full"
                                                            style={{ backgroundColor: item.color }}
                                                        ></div>
                                                        <span className="text-sm font-medium">{item.name}</span>
                                                    </div>
                                                    <span className="text-base font-bold">{item.value}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Task Priority Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">{t('Task Priority')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {taskPriority.length === 0 || !taskPriority.some(item => item.value > 0) ? (
                                    <PieChart
                                        data={[{ name: t('No Data'), value: 1, color: '#e5e7eb' }]}
                                        dataKey="value"
                                        nameKey="name"
                                        height={200}
                                        donut={true}
                                        showTooltip={false}
                                    />
                                ) : (
                                    <>
                                        <PieChart
                                            data={taskPriority.filter(item => item.value > 0)}
                                            dataKey="value"
                                            nameKey="name"
                                            height={200}
                                            donut={true}
                                            showTooltip={true}
                                        />
                                        <div className="space-y-2">
                                            {taskPriority.filter(item => item.value > 0).map((item, index) => (
                                                <div key={index} className="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                    <div className="flex items-center gap-2">
                                                        <div
                                                            className="w-3 h-3 rounded-full"
                                                            style={{ backgroundColor: item.color }}
                                                        ></div>
                                                        <span className="text-sm font-medium">{item.name}</span>
                                                    </div>
                                                    <span className="text-base font-bold">{item.value}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Team Performance */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">{t('Team Performance')}</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {teamPerformance.slice(0, 5).map((member, index) => (
                                <div key={index} className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="font-medium">{member.name}</span>
                                        <span className="text-muted-foreground">
                                            {member.completed_tasks}/{member.total_tasks}
                                        </span>
                                    </div>
                                    <div className="w-full bg-gray-200 rounded-full h-2">
                                        <div className="bg-blue-600 h-2 rounded-full" style={{ width: `${member.completion_rate}%` }}></div>
                                    </div>
                                    <div className="text-xs text-muted-foreground text-right">
                                        {member.completion_rate}% {t('completed')}
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Company Tasks */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">{t('Recent Company Tasks')}</CardTitle>
                        <p className="text-sm text-muted-foreground">
                            {stats.completed_tasks} {t('of')} {stats.total_tasks} {t('tasks completed across all projects')}
                        </p>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            {recentTasks.map((task) => (
                                <div key={task.id} className="border rounded-lg p-4 space-y-3">
                                    <div className="flex items-start justify-between">
                                        <h4 className="font-medium text-sm truncate">{task.title}</h4>
                                        {task.is_completed && (
                                            <span className="text-green-500 text-xs">✓</span>
                                        )}
                                    </div>
                                    <div className="space-y-2">
                                        <div className="flex justify-between text-xs">
                                            <span className="text-muted-foreground">{t('Priority')}:</span>
                                            <Badge size="sm" className={`${getPriorityColor(task.priority)} hover:!bg-current hover:!text-current pointer-events-none`}>
                                                {task.priority}
                                            </Badge>
                                        </div>
                                        <div className="flex justify-between text-xs">
                                            <span className="text-muted-foreground">{t('Stage')}:</span>
                                            <Badge
                                                size="sm"
                                                variant="secondary"
                                                className={!task.stage_color ? getStageColor(task.stage) : ''}
                                                style={task.stage_color ? { backgroundColor: task.stage_color, color: '#fff' } : {}}
                                            >
                                                {task.stage}
                                            </Badge>
                                        </div>
                                        <div className="flex justify-between text-xs">
                                            <span className="text-muted-foreground">{t('Assignee')}:</span>
                                            <span className="font-medium">{task.assignee}</span>
                                        </div>
                                        <div className="flex justify-between text-xs">
                                            <span className="text-muted-foreground">{t('Project')}:</span>
                                            <span className="font-medium truncate">{task.project}</span>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Recent Payments */}
                {recentPayments && recentPayments.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">{t('Recent Payments')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">{t('Payment')}</th>
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">{t('Project')}</th>
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">{t('Customer')}</th>
                                            <th className="text-right py-3 px-4 text-sm font-medium text-muted-foreground">{t('Total')}</th>
                                            <th className="text-right py-3 px-4 text-sm font-medium text-muted-foreground">{t('Balance')}</th>
                                            <th className="text-center py-3 px-4 text-sm font-medium text-muted-foreground">{t('Status')}</th>
                                            <th className="text-left py-3 px-4 text-sm font-medium text-muted-foreground">{t('Date')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {recentPayments.map((payment) => (
                                            <tr key={payment.id} className="border-b hover:bg-gray-50 cursor-pointer" onClick={() => router.get(route('project-payments.show', payment.id))}>
                                                <td className="py-3 px-4 text-sm font-medium text-blue-600">{payment.payment_number}</td>
                                                <td className="py-3 px-4 text-sm">{payment.project}</td>
                                                <td className="py-3 px-4 text-sm">{payment.customer}</td>
                                                <td className="py-3 px-4 text-sm text-right font-medium">{formatCurrency(payment.total_amount)}</td>
                                                <td className="py-3 px-4 text-sm text-right font-medium text-orange-600">{formatCurrency(payment.balance_amount)}</td>
                                                <td className="py-3 px-4">
                                                    <div className="flex justify-center">
                                                        <Badge
                                                            size="sm"
                                                            className={payment.status === 'posted'
                                                                ? '!bg-green-500 !text-white hover:!bg-green-500'
                                                                : '!bg-gray-500 !text-white hover:!bg-gray-500'
                                                            }
                                                        >
                                                            {t(payment.status.charAt(0).toUpperCase() + payment.status.slice(1))}
                                                        </Badge>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">{formatDate(payment.payment_date)}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
