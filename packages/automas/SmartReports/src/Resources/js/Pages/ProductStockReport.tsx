import React, { useState, useEffect, useMemo } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Filter, Package, Search, ChevronRight, ChevronDown,
    BarChart3, List, AlertTriangle, CheckCircle, ChevronLeft,
    FilterX, ArrowLeft,
} from 'lucide-react';
import ExportButton from '../Components/ExportButton';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip as RechartsTooltip, Legend, ResponsiveContainer, LabelList,
} from 'recharts';
import { toast } from 'sonner';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { formatCurrency } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Pagination } from '@/components/ui/pagination';
import { PerPageSelector } from '@/components/ui/per-page-selector';

interface FilterOptions {
    categories: { value: number; label: string }[];
    warehouses: { value: number; label: string }[];
}

interface StockRecord {
    id: number;
    product_name: string;
    sku: string;
    category_name: string;
    type: string;
    warehouse_id: number | null;
    warehouse_name: string | null;
    warehouse_breakdown: string | null;
    quantity: number;
    purchase_price: number;
    sale_price: number;
    is_active: boolean;
}

interface Summary {
    total_products: number;
    total_quantity: number;
    in_stock_count: number;
    out_of_stock_count: number;
    total_stock_value: number;
    average_sale_price: number;
}

const PAGE_SIZE = 8;

const ProductStockReport: React.FC = () => {
    const { t } = useTranslation();
    const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
    const [selectedCategory, setSelectedCategory] = useState<string>('all');
    const [selectedWarehouse, setSelectedWarehouse] = useState<string>('all');
    const [selectedType, setSelectedType] = useState<string>('all');
    const [selectedStockStatus, setSelectedStockStatus] = useState<string>('all');
    const [reportData, setReportData] = useState<StockRecord[] | null>(null);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [viewMode, setViewMode] = useState<'chart' | 'table'>('chart');
    const [groupBy, setGroupBy] = useState<'category' | 'warehouse'>('category');
    const [expandedGroups, setExpandedGroups] = useState<Set<string>>(new Set());
    const [outOfStockPage, setOutOfStockPage] = useState(1);
    const [selectedChartGroup, setSelectedChartGroup] = useState<string | null>(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);

    useEffect(() => {
        fetchFilterOptions();
        axios.post(route('smart-reports.product-stock.generate'), { export_type: 'view' })
            .then(res => {
                if (res.data.success) {
                    setReportData(res.data.data.data || []);
                    setSummary(res.data.data.summary || null);
                }
            }).catch(() => { });
    }, []);

    const fetchFilterOptions = async () => {
        try {
            const res = await axios.get(route('smart-reports.product-stock.filter-options'));
            if (res.data.success) setFilterOptions(res.data.data);
        } catch (error: any) {
            toast.error(error.response?.data?.message || t('Failed to load filter options'));
        }
    };

    const handleGenerateReport = async (exportType: 'view') => {
        try {
            const payload: Record<string, any> = {
                export_type: 'view',
                category_ids: selectedCategory !== 'all' ? [parseInt(selectedCategory)] : null,
                warehouse_ids: selectedWarehouse !== 'all' ? [parseInt(selectedWarehouse)] : null,
                type: selectedType !== 'all' ? [selectedType] : null,
                stock_status: selectedStockStatus !== 'all' ? selectedStockStatus : null,
            };
            const res = await axios.post(route('smart-reports.product-stock.generate'), payload);
            if (res.data.success) {
                setReportData(res.data.data.data || []);
                setSummary(res.data.data.summary || null);
                setOutOfStockPage(1);
                toast.success(t('Report generated successfully'));
            }
        } catch (error: any) {
            toast.error(error.response?.data?.message || t('Failed to generate report'));
        }
    };

    const toggleGroup = (key: string) => {
        setExpandedGroups(prev => {
            const s = new Set(prev);
            s.has(key) ? s.delete(key) : s.add(key);
            return s;
        });
    };

    // Build per-product warehouse breakdown string including zero-qty warehouses
    const buildWarehouseBreakdown = (productId: number, allData: StockRecord[]): string => {
        const rows = allData.filter(r => r.id === productId && r.warehouse_name);
        if (!rows.length) return '-';
        return rows.map(r => `${r.warehouse_name} (${parseFloat(r.quantity as any) || 0})`).join(', ');
    };

    const buildGroups = (data: StockRecord[]) => {
        const groups: Record<string, {
            key: string; name: string;
            total_qty: number; total_value: number;
            in_stock: number; out_of_stock: number;
            records: StockRecord[];
        }> = {};

        if (groupBy === 'category') {
            // Deduplicate by product id — sum qty across warehouses per product
            const productMap: Record<number, StockRecord> = {};
            data.forEach(r => {
                if (!productMap[r.id]) {
                    productMap[r.id] = { ...r, quantity: 0 };
                }
                productMap[r.id].quantity = (parseFloat(productMap[r.id].quantity as any) || 0) + (parseFloat(r.quantity as any) || 0);
            });
            const dedupedData = Object.values(productMap);

            dedupedData.forEach(r => {
                const key = r.category_name || t('Uncategorized');
                if (!groups[key]) groups[key] = { key, name: key, total_qty: 0, total_value: 0, in_stock: 0, out_of_stock: 0, records: [] };
                const qty = parseFloat(r.quantity as any) || 0;
                groups[key].total_qty += qty;
                groups[key].total_value += (parseFloat(r.purchase_price as any) || 0) * qty;
                qty > 0 ? groups[key].in_stock++ : groups[key].out_of_stock++;
                groups[key].records.push(r);
            });
        } else {
            // By warehouse — each row already = product × warehouse
            data.forEach(r => {
                const key = r.warehouse_name || t('No Warehouse');
                if (!groups[key]) groups[key] = { key, name: key, total_qty: 0, total_value: 0, in_stock: 0, out_of_stock: 0, records: [] };
                const qty = parseFloat(r.quantity as any) || 0;
                groups[key].total_qty += qty;
                groups[key].total_value += (parseFloat(r.purchase_price as any) || 0) * qty;
                qty > 0 ? groups[key].in_stock++ : groups[key].out_of_stock++;
                groups[key].records.push(r);
            });
        }

        return Object.values(groups).sort((a, b) => b.total_value - a.total_value);
    };

    const groupedData = useMemo(() => {
        if (!reportData?.length) return [];
        return buildGroups(reportData);
    }, [reportData, groupBy]);

    const filteredGroups = useMemo(() => {
        if (!groupedData.length) return [];
        if (!searchQuery.trim()) return groupedData;
        const q = searchQuery.toLowerCase();
        return groupedData
            .map(g => ({
                ...g, records: g.records.filter(r =>
                    r.product_name.toLowerCase().includes(q) ||
                    (r.sku || '').toLowerCase().includes(q) ||
                    (r.category_name || '').toLowerCase().includes(q)
                )
            }))
            .filter(g => g.name.toLowerCase().includes(q) || g.records.length > 0);
    }, [groupedData, searchQuery]);

    // Chart data based on groupBy
    const groupChartData = useMemo(() => {
        if (!reportData) return [];
        const map: Record<string, { name: string; inStock: number; outOfStock: number }> = {};

        if (groupBy === 'category') {
            // deduplicate by product id first
            const productMap: Record<number, StockRecord> = {};
            reportData.forEach(r => {
                if (!productMap[r.id]) productMap[r.id] = { ...r, quantity: 0 };
                productMap[r.id].quantity = (parseFloat(productMap[r.id].quantity as any) || 0) + (parseFloat(r.quantity as any) || 0);
            });
            Object.values(productMap).forEach(r => {
                const key = r.category_name || t('Uncategorized');
                if (!map[key]) map[key] = { name: key, inStock: 0, outOfStock: 0 };
                (parseFloat(r.quantity as any) || 0) > 0 ? map[key].inStock++ : map[key].outOfStock++;
            });
        } else {
            reportData.forEach(r => {
                const key = r.warehouse_name || t('No Warehouse');
                if (!map[key]) map[key] = { name: key, inStock: 0, outOfStock: 0 };
                (parseFloat(r.quantity as any) || 0) > 0 ? map[key].inStock++ : map[key].outOfStock++;
            });
        }

        return Object.values(map)
            .sort((a, b) => (b.inStock + b.outOfStock) - (a.inStock + a.outOfStock))
            .map(d => ({
                name: d.name,
                fullName: d.name,
                [t('In Stock')]: d.inStock,
                [t('Out of Stock')]: d.outOfStock,
            }));
    }, [reportData, groupBy, t]);

    // Out of stock products paginated (deduplicated by product id, total qty across all warehouses must be 0)
    const outOfStockProducts = useMemo(() => {
        if (!reportData) return [];
        const map: Record<number, StockRecord & { outOfStockWarehouses: string }> = {};
        reportData.forEach(r => {
            if ((parseFloat(r.quantity as any) || 0) === 0 && r.warehouse_name) {
                if (!map[r.id]) {
                    map[r.id] = { ...r, outOfStockWarehouses: r.warehouse_name };
                } else {
                    map[r.id].outOfStockWarehouses += ', ' + r.warehouse_name;
                }
            }
        });
        return Object.values(map);
    }, [reportData]);

    const outOfStockTotalPages = Math.max(1, Math.ceil(outOfStockProducts.length / PAGE_SIZE));
    const outOfStockPaged = outOfStockProducts.slice((outOfStockPage - 1) * PAGE_SIZE, outOfStockPage * PAGE_SIZE);

    const clearFilters = () => {
        setSelectedCategory('all'); setSelectedWarehouse('all'); setSelectedType('all'); setSelectedStockStatus('all');
        axios.post(route('smart-reports.product-stock.generate'), { export_type: 'view' }).then(r => { if (r.data.success) { setReportData(r.data.data.data || []); setSummary(r.data.data.summary || null); } }).catch(() => {});
    };

    const actionButtons = (
        <div className="flex items-center gap-2 shrink-0">
            <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" onClick={() => handleGenerateReport('view')}><Search className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('View Report')}</p></TooltipContent></Tooltip></TooltipProvider>
            <TooltipProvider><Tooltip delayDuration={0}><TooltipTrigger asChild><Button size="sm" variant="outline" className="text-muted-foreground" onClick={clearFilters}><FilterX className="h-4 w-4" /></Button></TooltipTrigger><TooltipContent><p>{t('Clear Filter')}</p></TooltipContent></Tooltip></TooltipProvider>
        </div>
    );

    const filtersPanel = (
        <Card>
            <CardContent className="px-4 py-3">
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2">
                    {filterOptions && (<>
                        <Select value={selectedCategory} onValueChange={setSelectedCategory}>
                            <SelectTrigger><SelectValue placeholder={t('Category')} /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('All Categories')}</SelectItem>
                                {filterOptions.categories.map(c => <SelectItem key={c.value} value={c.value.toString()}>{c.label}</SelectItem>)}
                            </SelectContent>
                        </Select>
                        <Select value={selectedWarehouse} onValueChange={setSelectedWarehouse}>
                            <SelectTrigger><SelectValue placeholder={t('Warehouse')} /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('All Warehouses')}</SelectItem>
                                {filterOptions.warehouses.map(w => <SelectItem key={w.value} value={w.value.toString()}>{w.label}</SelectItem>)}
                            </SelectContent>
                        </Select>
                        <Select value={selectedType} onValueChange={setSelectedType}>
                            <SelectTrigger><SelectValue placeholder={t('Type')} /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('All Types')}</SelectItem>
                                <SelectItem value="product">{t('Product')}</SelectItem>
                                <SelectItem value="part">{t('Part')}</SelectItem>
                            </SelectContent>
                        </Select>
                        <div className="flex items-center gap-2">
                            <div className="flex-1">
                                <Select value={selectedStockStatus} onValueChange={setSelectedStockStatus}>
                                    <SelectTrigger><SelectValue placeholder={t('Stock Status')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All')}</SelectItem>
                                        <SelectItem value="in_stock">{t('In Stock')}</SelectItem>
                                        <SelectItem value="out_of_stock">{t('Out of Stock')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="hidden xl:flex">{actionButtons}</div>
                        </div>
                    </>)}
                </div>
                <div className="flex xl:hidden justify-end mt-2">{actionButtons}</div>
            </CardContent>
        </Card>
    );

    const pageActions = (
        <div className="flex items-center gap-2">
            <ExportButton
                data={reportData || []}
                columns={[
                    { key: 'product_name', header: t('Product') },
                    { key: 'category_name', header: t('Category') },
                    { key: 'quantity', header: t('Quantity') },
                ]}
                filename="product-stock-report"
                title={t('Product Stock Report')}
                groupedConfig={{
                    sheetName: 'Product Stock',
                    groupKey: (r) => groupBy === 'category'
                        ? (r.category_name || 'Uncategorized')
                        : (r.warehouse_name || 'No Warehouse'),
                    summaryColumns: [
                        { key: 'group_name',    header: groupBy === 'category' ? t('Category') : t('Warehouse') },
                        { key: 'total_items',   header: t('Total Items'),    type: 'number' },
                        { key: 'in_stock',      header: t('In Stock'),       type: 'number' },
                        { key: 'out_of_stock',  header: t('Out of Stock'),   type: 'number' },
                        { key: 'total_qty',     header: t('Total Qty'),      type: 'number' },
                        { key: 'stock_value',   header: t('Stock Value'),    type: 'currency' },
                    ],
                    detailColumns: [
                        { key: 'product_name',   header: t('Product') },
                        { key: 'sku',            header: t('SKU') },
                        { key: 'type',           header: t('Type') },
                        { key: 'quantity',       header: t('Qty'),            type: 'number' },
                        { key: 'purchase_price', header: t('Purchase Price'), type: 'currency' },
                        { key: 'sale_price',     header: t('Sale Price'),     type: 'currency' },
                        { key: 'stock_status',   header: t('Stock Status'),   type: 'status',
                            render: (_, row) => (parseFloat(row.quantity) || 0) > 0 ? 'In Stock' : 'Out of Stock' },
                    ],
                    summaryBuilder: (rows) => {
                        const groupName = groupBy === 'category'
                            ? (rows[0].category_name || 'Uncategorized')
                            : (rows[0].warehouse_name || 'No Warehouse');
                        const totalQty = rows.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0);
                        const stockValue = rows.reduce((s, r) => s + (parseFloat(r.purchase_price) || 0) * (parseFloat(r.quantity) || 0), 0);
                        const inStock = rows.filter(r => (parseFloat(r.quantity) || 0) > 0).length;
                        return { group_name: groupName, total_items: rows.length, in_stock: inStock, out_of_stock: rows.length - inStock, total_qty: totalQty, stock_value: stockValue };
                    },
                }}
            />
            <Button variant="outline" size="sm" onClick={() => router.visit(route('smart-reports.index'))}>
                <ArrowLeft className="h-4 w-4" />
                {t('Back')}
            </Button>
        </div>
    );

    if (!reportData || reportData.length === 0) {
        return (
            <AuthenticatedLayout
                breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Product Stock Report') }]}
                pageTitle={t('Product Stock Report')}
                pageActions={pageActions}
            >
                <Head title={t('Product Stock Report')} />
                <div className="space-y-6">
                    {filtersPanel}
                    {reportData !== null && reportData.length === 0 && (
                        <Card>
                            <CardContent className="text-center py-12">
                                <Package className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <p className="text-lg font-medium text-muted-foreground">{t('No products found.')}</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Smart Reports'), url: route('smart-reports.index') }, { label: t('Product Stock Report') }]}
            pageTitle={t('Product Stock Report')}
            pageActions={pageActions}
        >
            <Head title={t('Product Stock Report')} />
            <div className="space-y-6">
                {filtersPanel}

                {/* Summary Cards */}
                {summary && (
                    <div className="flex flex-wrap xl:flex-nowrap items-stretch gap-3">
                        <Card className="bg-blue-50/50 border-blue-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Package className="h-4 w-4 text-blue-600 shrink-0" />
                                        <span className="text-sm font-medium text-blue-700 truncate">{t('Total Products')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-blue-900 shrink-0">{summary.total_products}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-green-50/50 border-green-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <CheckCircle className="h-4 w-4 text-green-600 shrink-0" />
                                        <span className="text-sm font-medium text-green-700 truncate">{t('In Stock')}</span>
                                    </div>
                                    <div className="text-right min-w-0">
                                        <div className="text-xl font-bold text-green-700 shrink-0">{summary.in_stock_count}</div>
                                        <div className="text-xs text-green-600 truncate">{t('Qty')}: {summary.total_quantity}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-red-50/50 border-red-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <AlertTriangle className="h-4 w-4 text-red-600 shrink-0" />
                                        <span className="text-sm font-medium text-red-700 truncate">{t('Out of Stock')}</span>
                                    </div>
                                    <div className="text-2xl font-bold text-red-700 shrink-0">{summary.out_of_stock_count}</div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card className="bg-purple-50/50 border-purple-200 w-full lg:w-[calc(50%-6px)] xl:flex-1">
                            <CardContent className="py-3 px-4">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2 min-w-0">
                                        <BarChart3 className="h-4 w-4 text-purple-600 shrink-0" />
                                        <span className="text-sm font-medium text-purple-700 truncate">{t('Stock Value')}</span>
                                    </div>
                                    <div className="text-right min-w-0">
                                        <div className="text-xl font-bold text-purple-700 shrink-0">{formatCurrency(summary.total_stock_value)}</div>
                                        <div className="text-xs text-purple-600 truncate">{t('Avg')}: {formatCurrency(summary.average_sale_price)}</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Controls */}
                <Card>
                    <CardContent className="px-4 py-2">
                        <div className="flex items-center justify-between gap-2 flex-wrap">
                            <div className="flex items-center gap-2">
                                <Button variant={groupBy === 'category' ? 'default' : 'outline'} size="sm" onClick={() => setGroupBy('category')}>{t('By Category')}</Button>
                                <Button variant={groupBy === 'warehouse' ? 'default' : 'outline'} size="sm" onClick={() => setGroupBy('warehouse')}>{t('By Warehouse')}</Button>
                            </div>
                            <div className="flex border rounded-lg">
                                <Button variant={viewMode === 'chart' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('chart')} className="rounded-r-none">
                                    <BarChart3 className="h-4 w-4" />
                                </Button>
                                <Button variant={viewMode === 'table' ? 'default' : 'ghost'} size="sm" onClick={() => setViewMode('table')} className="rounded-l-none">
                                    <List className="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* CHART VIEW */}
                {viewMode === 'chart' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        {/* Horizontal Bar: In Stock vs Out of Stock per groupBy */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base flex items-center gap-2">
                                    <BarChart3 className="h-5 w-5" />
                                    {groupBy === 'category' ? t('Stock Status by Category') : t('Stock Status by Warehouse')}
                                    <TooltipProvider>
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger asChild>
                                                <span className="ml-auto cursor-pointer text-yellow-500 hover:text-yellow-600 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <line x1="12" y1="16" x2="12" y2="12" />
                                                        <line x1="12" y1="8" x2="12.01" y2="8" />
                                                    </svg>
                                                </span>
                                            </TooltipTrigger>
                                            <TooltipContent side="left" className="max-w-xs text-xs bg-yellow-50 text-yellow-700 border border-yellow-200 font-normal">
                                                <p>{t('Click on any bar to view its product details on the right panel.')}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {groupChartData.length === 0 ? (
                                    <div className="flex items-center justify-center h-64 text-muted-foreground">{t('No data')}</div>
                                ) : (
                                    <>
                                        <div className="overflow-x-auto">
                                            <ResponsiveContainer width="100%" height={Math.max(240, groupChartData.length * 52)}>
                                                <BarChart
                                                    data={groupChartData}
                                                    layout="vertical"
                                                    margin={{ top: 4, right: 40, left: 0, bottom: 4 }}
                                                    barSize={14}
                                                    barGap={3}
                                                >
                                                    <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke="#f0f0f0" />
                                                    <XAxis type="number" allowDecimals={false} tick={{ fontSize: 11 }} />
                                                    <YAxis
                                                        type="category"
                                                        dataKey="name"
                                                        width={Math.min(140, Math.max(80, Math.max(...groupChartData.map(d => d.name.length)) * 5.2))}
                                                        tick={(props) => {
                                                            const { x, y, payload } = props;
                                                            const words = payload.value.split(' ');
                                                            const lineH = 14;
                                                            const lines: string[] = [];
                                                            let cur = '';
                                                            words.forEach((w: string) => {
                                                                const test = cur ? `${cur} ${w}` : w;
                                                                if (test.length > 18) { lines.push(cur); cur = w; }
                                                                else cur = test;
                                                            });
                                                            if (cur) lines.push(cur);
                                                            const offset = -((lines.length - 1) * lineH) / 2;
                                                            return (
                                                                <text x={x} y={y} textAnchor="end" fill="#6b7280" fontSize={11}>
                                                                    {lines.map((line, i) => (
                                                                        <tspan key={i} x={x} dy={i === 0 ? offset : lineH}>{line}</tspan>
                                                                    ))}
                                                                </text>
                                                            );
                                                        }}
                                                    />
                                                    <RechartsTooltip
                                                        labelFormatter={(_, p) => p?.[0]?.payload?.fullName || ''}
                                                        contentStyle={{ borderRadius: '8px', fontSize: '13px' }}
                                                    />
                                                    <Bar dataKey={t('In Stock')} fill="#22c55e" radius={[0, 4, 4, 0]} cursor="pointer"
                                                        onClick={(data) => {
                                                            const name = data?.fullName;
                                                            if (name) setSelectedChartGroup(prev => prev === name ? null : name);
                                                        }}
                                                    >
                                                        <LabelList dataKey={t('In Stock')} position="right" style={{ fontSize: 11, fill: '#374151' }} />
                                                    </Bar>
                                                    <Bar dataKey={t('Out of Stock')} fill="#ef4444" radius={[0, 4, 4, 0]} cursor="pointer"
                                                        onClick={(data) => {
                                                            const name = data?.fullName;
                                                            if (name) setSelectedChartGroup(prev => prev === name ? null : name);
                                                        }}
                                                    >
                                                        <LabelList dataKey={t('Out of Stock')} position="right" style={{ fontSize: 11, fill: '#374151' }} />
                                                    </Bar>
                                                </BarChart>
                                            </ResponsiveContainer>
                                        </div>
                                        {/* Static legend outside scroll */}
                                        <div className="flex flex-wrap items-center justify-center gap-4 pt-2 pb-1">
                                            <div className="flex items-center gap-1.5">
                                                <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#22c55e] shrink-0"></span>
                                                <span className="text-sm font-medium text-muted-foreground">{t('In Stock')}</span>
                                            </div>
                                            <div className="flex items-center gap-1.5">
                                                <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#ef4444] shrink-0"></span>
                                                <span className="text-sm font-medium text-muted-foreground">{t('Out of Stock')}</span>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>

                        {/* Right panel: selected group detail OR out-of-stock list */}
                        <Card>
                            <CardHeader>
                                {selectedChartGroup ? (
                                    <CardTitle className="text-base flex items-center justify-between gap-2">
                                        <div className="flex items-center gap-2 min-w-0">
                                            <BarChart3 className="h-5 w-5 text-black-500 shrink-0" />
                                            <span className="truncate">{selectedChartGroup}</span>
                                        </div>
                                        <button onClick={() => setSelectedChartGroup(null)} className="text-xs text-muted-foreground hover:text-foreground shrink-0">
                                            ✕ {t('Clear')}
                                        </button>
                                    </CardTitle>
                                ) : (
                                    <CardTitle className="text-base flex items-center gap-2">
                                        <AlertTriangle className="h-5 w-5 text-black-500" />
                                        {t('Out of Stock Products')}
                                        {outOfStockProducts.length > 0 && (
                                            <span className="ml-1 text-xs font-normal bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                                                {outOfStockProducts.length}
                                            </span>
                                        )}
                                    </CardTitle>
                                )}
                            </CardHeader>
                            <CardContent className="p-0">
                                {selectedChartGroup ? (() => {
                                    const grp = groupedData.find(g => g.name === selectedChartGroup);
                                    if (!grp) return <div className="px-4 py-8 text-center text-muted-foreground text-sm">{t('No data')}</div>;
                                    return (
                                        <div>
                                            {/* Summary strip */}
                                            <div className="grid grid-cols-3 divide-x border-b">
                                                <div className="px-3 py-2 text-center">
                                                    <div className="text-xs text-muted-foreground">{t('Total')}</div>
                                                    <div className="font-bold text-sm">{grp.records.length}</div>
                                                </div>
                                                <div className="px-3 py-2 text-center">
                                                    <div className="text-xs text-green-600">{t('In Stock')}</div>
                                                    <div className="font-bold text-sm text-green-700">{grp.in_stock}</div>
                                                </div>
                                                <div className="px-3 py-2 text-center">
                                                    <div className="text-xs text-red-500">{t('Out of Stock')}</div>
                                                    <div className="font-bold text-sm text-red-600">{grp.out_of_stock}</div>
                                                </div>
                                            </div>
                                            <div className="overflow-x-auto">
                                                <table className="w-full text-sm">
                                                    <thead className="bg-muted/40">
                                                        <tr>
                                                            <th className="px-4 py-2 text-left text-xs font-medium">{t('Product')}</th>
                                                            <th className="px-4 py-2 text-left text-xs font-medium">{t('SKU')}</th>
                                                            {groupBy === 'category'
                                                                ? <th className="px-4 py-2 text-left text-xs font-medium">{t('Warehouses')}</th>
                                                                : <th className="px-4 py-2 text-left text-xs font-medium">{t('Category')}</th>
                                                            }
                                                            <th className="px-4 py-2 text-right text-xs font-medium">{t('Qty')}</th>
                                                            <th className="px-4 py-2 text-right text-xs font-medium">{t('Purchase Price')}</th>
                                                            <th className="px-4 py-2 text-right text-xs font-medium">{t('Sale Price')}</th>
                                                            <th className="px-4 py-2 text-center text-xs font-medium">{t('Status')}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y">
                                                        {grp.records.map((rec, idx) => {
                                                            const qty = parseFloat(rec.quantity as any) || 0;
                                                            const inStock = qty > 0;
                                                            return (
                                                                <tr key={`${rec.id}-${idx}`} className={inStock ? 'hover:bg-green-50/30' : 'hover:bg-red-50/40'}>
                                                                    <td className="px-4 py-2 font-medium text-gray-900">{rec.product_name}</td>
                                                                    <td className="px-4 py-2 text-muted-foreground text-xs">{rec.sku || '-'}</td>
                                                                    {groupBy === 'category'
                                                                        ? <td className="px-4 py-2 text-xs text-muted-foreground">{buildWarehouseBreakdown(rec.id, reportData!)}</td>
                                                                        : <td className="px-4 py-2 text-xs text-muted-foreground">{rec.category_name || '-'}</td>
                                                                    }
                                                                    <td className="px-4 py-2 text-right font-semibold">{qty}</td>
                                                                    <td className="px-4 py-2 text-right text-xs">{formatCurrency(rec.purchase_price)}</td>
                                                                    <td className="px-4 py-2 text-right text-xs">{formatCurrency(rec.sale_price)}</td>
                                                                    <td className="px-4 py-2 text-center">
                                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${inStock ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                                                            {inStock ? t('In Stock') : t('Out of Stock')}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            );
                                                        })}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    );
                                })() : (
                                    outOfStockProducts.length === 0 ? (
                                        <div className="flex flex-col items-center justify-center h-48 text-muted-foreground gap-2">
                                            <CheckCircle className="h-10 w-10 text-green-400" />
                                            <p className="text-sm">{t('All products are in stock!')}</p>
                                        </div>
                                    ) : (
                                        <>
                                            <div className="overflow-x-auto">
                                                <table className="w-full text-sm">
                                                    <thead className="bg-muted/40">
                                                        <tr>
                                                            <th className="px-4 py-2 text-left font-medium text-xs">{t('Product')}</th>
                                                            <th className="px-4 py-2 text-left font-medium text-xs">{t('SKU')}</th>
                                                            <th className="px-4 py-2 text-left font-medium text-xs">{t('Category')}</th>
                                                            <th className="px-4 py-2 text-left font-medium text-xs">{t('Warehouse')}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y">
                                                        {outOfStockPaged.map((rec, idx) => (
                                                            <tr key={`${rec.id}-${idx}`} className="hover:bg-red-50/40">
                                                                <td className="px-4 py-2 font-medium text-gray-900">{rec.product_name}</td>
                                                                <td className="px-4 py-2 text-muted-foreground text-xs">{rec.sku || '-'}</td>
                                                                <td className="px-4 py-2 text-xs text-muted-foreground">{rec.category_name || '-'}</td>
                                                                <td className="px-4 py-2 text-xs text-muted-foreground">{(rec as any).outOfStockWarehouses || '-'}</td>
                                                            </tr>
                                                        ))}
                                                    </tbody>
                                                </table>
                                            </div>
                                            {outOfStockTotalPages > 1 && (
                                                <div className="flex items-center justify-between px-4 py-3 border-t">
                                                    <span className="text-xs text-muted-foreground">
                                                        {t('Page')} {outOfStockPage} / {outOfStockTotalPages} &nbsp;·&nbsp; {outOfStockProducts.length} {t('items')}
                                                    </span>
                                                    <div className="flex gap-1">
                                                        <Button variant="outline" size="sm" disabled={outOfStockPage === 1} onClick={() => setOutOfStockPage(p => p - 1)}>
                                                            <ChevronLeft className="h-4 w-4" />
                                                        </Button>
                                                        <Button variant="outline" size="sm" disabled={outOfStockPage === outOfStockTotalPages} onClick={() => setOutOfStockPage(p => p + 1)}>
                                                            <ChevronRight className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            )}
                                        </>
                                    )
                                )}
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* TABLE VIEW */}
                {viewMode === 'table' && (() => {
                    const totalPages = Math.max(1, Math.ceil(filteredGroups.length / perPage));
                    const paginatedGroups = filteredGroups.slice((currentPage - 1) * perPage, currentPage * perPage);
                    return (
                    <Card>
                        <CardContent className="p-4 border-b bg-gray-50/50">
                            <div className="flex items-center justify-between gap-4">
                                <div className="flex-1 max-w-md relative">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input placeholder={t('Search products...')} value={searchQuery} onChange={e => { setSearchQuery(e.target.value); setCurrentPage(1); }} className="pl-9" />
                                </div>
                                <div className="flex items-center gap-2">
                                    <PerPageSelector
                                        routeName="smart-reports.product-stock.index"
                                        defaultValue={perPage.toString()}
                                        onPageChange={(val) => { setPerPage(Number(val)); setCurrentPage(1); }}
                                    />
                                </div>
                            </div>
                        </CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-muted/50 sticky top-0">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium w-12"></th>
                                        <th className="px-4 py-3 text-left font-medium">
                                            {groupBy === 'category' ? t('Category') : t('Warehouse')}
                                        </th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Total Items')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('In Stock')}</th>
                                        <th className="px-4 py-3 text-center font-medium">{t('Out of Stock')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Total Qty')}</th>
                                        <th className="px-4 py-3 text-right font-medium">{t('Stock Value')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {paginatedGroups.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-4 py-12 text-center text-muted-foreground">
                                                <Package className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                                <p>{t('No records found')}</p>
                                            </td>
                                        </tr>
                                    ) : paginatedGroups.map(group => (
                                        <React.Fragment key={group.key}>
                                            <tr
                                                className="hover:bg-muted/50 transition-colors bg-purple-50/20 cursor-pointer"
                                                onClick={() => toggleGroup(group.key)}
                                            >
                                                <td className="px-4 py-3">
                                                    {expandedGroups.has(group.key)
                                                        ? <ChevronDown className="h-4 w-4" />
                                                        : <ChevronRight className="h-4 w-4" />}
                                                </td>
                                                <td className="px-4 py-3 font-semibold text-gray-900">{group.name}</td>
                                                <td className="px-4 py-3 text-center">
                                                    <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800">{group.records.length}</span>
                                                </td>
                                                <td className="px-4 py-3 text-center">
                                                    <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-green-100 text-green-700">{group.in_stock}</span>
                                                </td>
                                                <td className="px-4 py-3 text-center">
                                                    <span className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700">{group.out_of_stock}</span>
                                                </td>
                                                <td className="px-4 py-3 text-right font-bold text-gray-900">{group.total_qty}</td>
                                                <td className="px-4 py-3 text-right font-bold text-gray-900">{formatCurrency(group.total_value)}</td>
                                            </tr>
                                            {expandedGroups.has(group.key) && (
                                                <tr>
                                                    <td colSpan={7} className="bg-gray-50/50 p-0">
                                                        <div className="px-8 py-4">
                                                            <table className="w-full text-sm">
                                                                <thead className="bg-white">
                                                                    <tr className="border-b">
                                                                        <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Product Name')}</th>
                                                                        <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('SKU')}</th>
                                                                        <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Type')}</th>
                                                                        <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Qty')}</th>
                                                                        {groupBy === 'category' && (
                                                                            <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Warehouses')}</th>
                                                                        )}
                                                                        {groupBy === 'warehouse' && (
                                                                            <th className="px-3 py-2 text-left font-medium text-xs uppercase text-muted-foreground">{t('Category')}</th>
                                                                        )}
                                                                        <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground">{t('Purchase Price')}</th>
                                                                        <th className="px-3 py-2 text-right font-medium text-xs uppercase text-muted-foreground">{t('Sale Price')}</th>
                                                                        <th className="px-3 py-2 text-center font-medium text-xs uppercase text-muted-foreground">{t('Status')}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody className="divide-y">
                                                                    {group.records.map((rec, idx) => {
                                                                        const qty = parseFloat(rec.quantity as any) || 0;
                                                                        const inStock = qty > 0;
                                                                        return (
                                                                            <tr key={`${rec.id}-${idx}`} className="hover:bg-white transition-colors">
                                                                                <td className="px-3 py-2 font-medium">{rec.product_name}</td>
                                                                                <td className="px-3 py-2 text-muted-foreground text-xs">{rec.sku || '-'}</td>
                                                                                <td className="px-3 py-2 text-xs text-muted-foreground capitalize">{rec.type || '-'}</td>
                                                                                <td className="px-3 py-2 text-right font-semibold">{qty}</td>
                                                                                {groupBy === 'category' && (
                                                                                    <td className="px-3 py-2 text-xs text-muted-foreground">{buildWarehouseBreakdown(rec.id, reportData!)}</td>
                                                                                )}
                                                                                {groupBy === 'warehouse' && (
                                                                                    <td className="px-3 py-2 text-xs text-muted-foreground">{rec.category_name || '-'}</td>
                                                                                )}
                                                                                <td className="px-3 py-2 text-right">{formatCurrency(rec.purchase_price)}</td>
                                                                                <td className="px-3 py-2 text-right">{formatCurrency(rec.sale_price)}</td>
                                                                                <td className="px-3 py-2 text-center">
                                                                                    <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${inStock ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                                                                        {inStock ? t('In Stock') : t('Out of Stock')}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                        );
                                                                    })}
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            )}
                                        </React.Fragment>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                            <Pagination
                                data={{ current_page: currentPage, last_page: totalPages, per_page: perPage, total: filteredGroups.length, from: ((currentPage - 1) * perPage) + 1, to: Math.min(currentPage * perPage, filteredGroups.length) }}
                                onPageChange={(page) => setCurrentPage(page)}
                            />
                        </CardContent>
                    </Card>
                    );
                })()}
            </div>
        </AuthenticatedLayout>
    );
};

export default ProductStockReport;
