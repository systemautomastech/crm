import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { formatCurrency, formatDate } from '@/utils/helpers';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { DateRangePicker } from "@/components/ui/date-range-picker";
import { BarChart } from '@/components/charts/BarChart';
import { DollarSign, Package, TrendingUp, TrendingDown, AreaChart as AreaChartIcon, Target, Clock } from 'lucide-react';

export default function CogsReport() {
    const { t } = useTranslation();
    const { cogsSummary, cogsMonthly, cogsTopItems, cogsDetails, warehouses } = usePage<any>().props;
    const [dateRange, setDateRange] = useState(() => {
        const fromDate = new URLSearchParams(window.location.search).get('from_date');
        const toDate = new URLSearchParams(window.location.search).get('to_date');
        return fromDate && toDate ? `${fromDate} - ${toDate}` : '';
    });
    const [warehouseId, setWarehouseId] = useState(new URLSearchParams(window.location.search).get('warehouse_id') || 'all');

    const handleFilterApply = () => {
        const params: any = {};
        if (dateRange) {
            const [fromDate, toDate] = dateRange.split(' - ');
            if (fromDate) params.from_date = fromDate;
            if (toDate) params.to_date = toDate;
        }
        if (warehouseId !== 'all') params.warehouse_id = warehouseId;
        router.get(route('inventory.reports.cogs'), params, { preserveState: true, preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('Inventory')},
                {label: t('Reports')},
                {label: t('COGS Report')}
            ]}
            pageTitle={t('Manage COGS Report')}
        >
            <Head title={t('COGS Report')} />

            <div className="space-y-6">
                <Card>
                    <CardContent className="p-4">
                        <div className="flex flex-wrap items-end gap-3">
                            <div className="flex-1 min-w-[200px]">
                                <Label className="text-sm font-medium">{t('Date Range')}</Label>
                                <DateRangePicker value={dateRange} onChange={setDateRange} placeholder={t('Select date range')} className="mt-1.5" />
                            </div>
                            <div className="flex-1 min-w-[200px]">
                                <Label className="text-sm font-medium">{t('Warehouse')}</Label>
                                <Select value={warehouseId} onValueChange={setWarehouseId}>
                                    <SelectTrigger className="mt-1.5">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Warehouses')}</SelectItem>
                                        {warehouses?.map((w: any) => (
                                            <SelectItem key={w.id} value={w.id.toString()}>{w.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button onClick={handleFilterApply}>{t('Apply')}</Button>
                            <Button onClick={() => { setDateRange(''); setWarehouseId('all'); router.get(route('inventory.reports.cogs')); }} variant="outline">{t('Clear')}</Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="bg-gradient-to-r from-red-50 to-red-100 border-red-200">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-red-700">{t('Total COGS')}</CardTitle>
                            <DollarSign className="h-8 w-8 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-red-700">{formatCurrency(cogsSummary.totalCogs)}</div>
                        </CardContent>
                    </Card>
                    <Card className="bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-blue-700">{t('Total Quantity Sold')}</CardTitle>
                            <Package className="h-8 w-8 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-blue-700">{cogsSummary.totalQuantity}</div>
                        </CardContent>
                    </Card>
                    <Card className="bg-gradient-to-r from-green-50 to-green-100 border-green-200">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-green-700">{t('Avg Unit Cost')}</CardTitle>
                            <TrendingUp className="h-8 w-8 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-green-700">{formatCurrency(cogsSummary.avgUnitCost)}</div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="bg-gray-50">
                        <CardTitle className="flex items-center gap-2">
                            <AreaChartIcon className="h-5 w-5" />
                            {t('Monthly COGS Trend')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-6">
                        <BarChart 
                            data={cogsMonthly} 
                            dataKey="cogs" 
                            xAxisKey="name" 
                            color="#EF4444"
                            height={350}
                            showGrid
                            showTooltip
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="bg-gray-50">
                        <CardTitle className="flex items-center gap-2">
                            <Target className="h-5 w-5" />
                            {t('Top 10 Items by COGS')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-6">
                        {cogsTopItems?.length > 0 ? (
                            <BarChart 
                                data={cogsTopItems} 
                                dataKey="cogs" 
                                xAxisKey="name" 
                                color="#8B5CF6"
                                height={350}
                                showGrid
                                showTooltip
                            />
                        ) : (
                            <p className="text-center text-muted-foreground py-8">{t('No data found')}</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="bg-gray-50">
                        <CardTitle className="flex items-center gap-2">
                            <Clock className="h-5 w-5" />
                            {t('Recent COGS Transactions')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-6">
                        {cogsDetails?.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-left p-2">{t('Date')}</th>
                                            <th className="text-left p-2">{t('Product')}</th>
                                            <th className="text-right p-2">{t('Quantity')}</th>
                                            <th className="text-right p-2">{t('Unit Cost')}</th>
                                            <th className="text-right p-2">{t('Total COGS')}</th>
                                            <th className="text-center p-2">{t('Type')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {cogsDetails.map((item: any, idx: number) => (
                                            <tr key={idx} className="border-b hover:bg-gray-50">
                                                <td className="p-3">{formatDate(item.date)}</td>
                                                <td className="p-3">{item.product}</td>
                                                <td className="text-right p-3">{item.quantity}</td>
                                                <td className="text-right p-3">{formatCurrency(item.unit_cost)}</td>
                                                <td className="text-right p-3">{formatCurrency(item.total_cogs)}</td>
                                                <td className="text-center p-3">
                                                    <span className="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{item.type}</span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <p className="text-center text-muted-foreground py-4">{t('No COGS transactions')}</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
