import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { formatCurrency } from '@/utils/helpers';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { BarChart } from '@/components/charts/BarChart';
import { PieChart } from '@/components/charts/PieChart';
import { Package, DollarSign, AlertTriangle, Boxes, TrendingUp, Warehouse, PieChart as PieChartIcon } from 'lucide-react';

export default function StockValuationReport() {
    const { t } = useTranslation();
    const { stockValuation, valuationByWarehouse, valuationByMethod, lowStockItems, warehouses } = usePage<any>().props;
    const [warehouseId, setWarehouseId] = useState(new URLSearchParams(window.location.search).get('warehouse_id') || 'all');

    const handleFilterApply = () => {
        const params = warehouseId !== 'all' ? { warehouse_id: warehouseId } : {};
        router.get(route('inventory.reports.stock-valuation'), params, { preserveState: true, preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('Inventory')},
                {label: t('Reports')},
                {label: t('Stock Valuation Report')}
            ]}
            pageTitle={t('Manage Stock Valuation Report')}
        >
            <Head title={t('Stock Valuation Report')} />

            <div className="space-y-6">
                <Card>
                    <CardContent className="p-4">
                        <div className="flex flex-wrap items-end gap-3">
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
                            <Button onClick={() => { setWarehouseId('all'); router.get(route('inventory.reports.stock-valuation')); }} variant="outline">{t('Clear')}</Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-blue-700">{t('Total Value')}</CardTitle>
                            <DollarSign className="h-8 w-8 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-blue-700">{formatCurrency(stockValuation.totalValue)}</div>
                        </CardContent>
                    </Card>
                    <Card className="bg-gradient-to-r from-green-50 to-green-100 border-green-200">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-green-700">{t('Total Items')}</CardTitle>
                            <Package className="h-8 w-8 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-green-700">{stockValuation.totalItems}</div>
                        </CardContent>
                    </Card>
                    <Card className="bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-purple-700">{t('Total Quantity')}</CardTitle>
                            <Boxes className="h-8 w-8 text-purple-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-bold text-purple-700">{stockValuation.totalQuantity}</div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader className="bg-gray-50">
                            <CardTitle className="flex items-center gap-2">
                                <PieChartIcon className="h-5 w-5" />
                                {t('Valuation by Method')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="pt-6">
                            <PieChart 
                                data={valuationByMethod} 
                                dataKey="value" 
                                nameKey="name" 
                                height={350}
                                donut
                                showLabels
                                showLegend
                                showTooltip
                            />
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="bg-gray-50">
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-2">
                                    <AlertTriangle className="h-5 w-5 text-amber-600" />
                                    {t('Low Stock Items')}
                                </CardTitle>
                                {lowStockItems?.length > 0 && (
                                    <Badge variant="destructive" className="text-sm">{lowStockItems.length}</Badge>
                                )}
                            </div>
                        </CardHeader>
                        <CardContent className="pt-6">
                            {lowStockItems?.length > 0 ? (
                                <div className="space-y-3 max-h-[350px] overflow-y-auto pr-2">
                                    {lowStockItems.map((item: any, idx: number) => (
                                        <div key={idx} className="p-4 rounded-lg border bg-card hover:shadow-md transition-shadow">
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="flex-1">
                                                    <p className="font-semibold text-sm mb-2">{item.name}</p>
                                                    <div className="flex items-center gap-4 text-xs text-muted-foreground">
                                                        <span className="flex items-center gap-1">
                                                            <Package className="h-3 w-3" />
                                                            {t('Stock')}: <span className="font-medium text-foreground">{item.current_stock}</span>
                                                        </span>
                                                        <span className="flex items-center gap-1">
                                                            <AlertTriangle className="h-3 w-3" />
                                                            {t('Reorder')}: <span className="font-medium text-foreground">{item.reorder_level}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <Badge 
                                                    variant={item.status === 'Out of Stock' ? 'destructive' : 'secondary'}
                                                    className={item.status === 'Out of Stock' ? '' : 'bg-yellow-500 text-white hover:bg-yellow-500'}
                                                >
                                                    {item.status}
                                                </Badge>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <div className="inline-flex p-4 rounded-full bg-green-50 dark:bg-green-950 mb-4">
                                        <Package className="h-12 w-12 text-green-600" />
                                    </div>
                                    <p className="font-medium text-foreground">{t('No low stock items')}</p>
                                    <p className="text-sm text-muted-foreground mt-2">{t('All items are above reorder level')}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="bg-gray-50">
                        <CardTitle className="flex items-center gap-2">
                            <Warehouse className="h-5 w-5" />
                            {t('Valuation by Warehouse')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-6">
                        <BarChart 
                            data={valuationByWarehouse} 
                            dataKey="value" 
                            xAxisKey="name" 
                            color="#3B82F6"
                            height={350}
                            showGrid
                            showTooltip
                        />
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
