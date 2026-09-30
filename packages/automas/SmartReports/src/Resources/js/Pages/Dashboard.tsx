import React from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, router } from '@inertiajs/react';
import {
    Calendar,
    FileText,
    DollarSign,
    TrendingUp,
    Package,
    ShoppingCart,
    Users,
    BarChart3,
    Layers,
    Eye,
} from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { getImagePath } from '@/utils/helpers';

interface ReportStat {
    label: string;
    value: number | string;
    color: 'blue' | 'green' | 'yellow' | 'red';
}

interface Report {
    type: string;
    name: string;
    description: string;
    module: string;
    icon: string;
    stats: ReportStat[];
}

interface Props {
    reports: Report[];
    message?: string;
}

const reportGifs: Record<string, string> = {
    attendance_summary:      'packages/automas/SmartReports/src/Resources/assets/images/attendance.png',
    payroll_sheet:           'packages/automas/SmartReports/src/Resources/assets/images/payroll.png',
    leave_report:            'packages/automas/SmartReports/src/Resources/assets/images/leave.png',
    sales_invoice_report:    'packages/automas/SmartReports/src/Resources/assets/images/sales.png',
    purchase_invoice_report: 'packages/automas/SmartReports/src/Resources/assets/images/purchase.png',
    deal_pipeline_report:    'packages/automas/SmartReports/src/Resources/assets/images/deal.png',
    lead_report:             'packages/automas/SmartReports/src/Resources/assets/images/lead.png',
    product_stock_report:    'packages/automas/SmartReports/src/Resources/assets/images/stock.png',
    project_progress_report: 'packages/automas/SmartReports/src/Resources/assets/images/project.png',
};

const SmartReportsIndex: React.FC<Props> = ({ reports }) => {
    const { t } = useTranslation();

    const handleGenerateReport = (reportType: string) => {
        const routes: Record<string, string> = {
            attendance_summary:      'smart-reports.attendance.report',
            payroll_sheet:           'smart-reports.payroll.report',
            leave_report:            'smart-reports.leave.report',
            sales_invoice_report:    'smart-reports.sales-invoice.report',
            purchase_invoice_report: 'smart-reports.purchase-invoice.report',
            deal_pipeline_report:    'smart-reports.deal-pipeline.report',
            lead_report:             'smart-reports.lead.report',
            product_stock_report:    'smart-reports.product-stock.report',
            project_progress_report: 'smart-reports.project-progress.report',
        };
        if (routes[reportType]) {
            router.visit(route(routes[reportType]));
        }
    };

    const getIcon = (iconName: string) => {
        const icons: Record<string, React.ReactNode> = {
            'calendar-check':  <Calendar className="h-3.5 w-3.5" />,
            'currency-dollar': <DollarSign className="h-3.5 w-3.5" />,
            'calendar-days':   <Calendar className="h-3.5 w-3.5" />,
            'trending-up':     <TrendingUp className="h-3.5 w-3.5" />,
            'shopping-cart':   <ShoppingCart className="h-3.5 w-3.5" />,
            'users':           <Users className="h-3.5 w-3.5" />,
            'package':         <Package className="h-3.5 w-3.5" />,
            'chart-bar':       <BarChart3 className="h-3.5 w-3.5" />,
            'pipeline':        <Layers className="h-3.5 w-3.5" />,
        };
        return icons[iconName] || <FileText className="h-3.5 w-3.5" />;
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Smart Reports') }]}
            pageTitle={t('Smart Reports')}
        >
            <Head title={t('Smart Reports')} />

            <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                {reports.map((report) => {
                    const gifSrc = reportGifs[report.type];
                    return (
                        <div
                            key={report.type}
                            className="group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            {/* Top section with GIF */}
                            <div className="relative h-60 w-full overflow-hidden bg-muted/30 cursor-pointer" onClick={() => handleGenerateReport(report.type)}>
                                <img
                                    src={getImagePath(gifSrc)}
                                    alt=""
                                    className="absolute inset-0 h-full w-full object-contain"
                                    onError={(e) => { (e.target as HTMLImageElement).style.display = 'none'; }}
                                />
                            </div>

                            {/* Content */}
                            <div className="flex flex-1 flex-col p-5 pt-4">
                                {/* Badge - show here only when no gif */}
                                {!gifSrc && (
                                    <span className="mb-3 inline-flex w-fit items-center gap-1.5 rounded-full border border-border bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                                        {getIcon(report.icon)}
                                        {t(report.module)}
                                    </span>
                                )}
                                <div className="flex items-center justify-between gap-2">
                                    <h3 className="text-base font-bold text-foreground leading-snug">
                                        {t(report.name)}
                                    </h3>
                                    <span className="shrink-0 rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground">
                                        {report.type === 'payroll_sheet' ? t('Last month') : t('This month')}
                                    </span>
                                </div>
                                <p className="mt-1.5 text-xs text-muted-foreground leading-relaxed line-clamp-2">
                                    {t(report.description)}
                                </p>

                                {/* Stats */}
                                {report.stats && report.stats.length > 0 && (
                                    <div className="mt-3 border-t border-border pt-3">
                                        <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground">
                                            {report.stats.map((stat, i) => (
                                                <React.Fragment key={stat.label}>
                                                    <span className="flex items-center gap-1">
                                                        <span className="font-bold text-foreground">{stat.value}</span>
                                                        <span>{t(stat.label)}</span>
                                                    </span>
                                                    {i < report.stats.length - 1 && (
                                                        <span className="text-muted-foreground/40">•</span>
                                                    )}
                                                </React.Fragment>
                                            ))}
                                        </div>

                                    </div>
                                )}

                                {/* Button */}
                                <div className="mt-4 flex items-center justify-between gap-2 rounded-xl border border-border px-3 py-2">
                                    <button
                                        onClick={() => handleGenerateReport(report.type)}
                                        className="flex flex-1 items-center justify-center gap-2 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        <Eye className="h-3.5 w-3.5" />
                                        {t('View Report')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </AuthenticatedLayout>
    );
};

export default SmartReportsIndex;
