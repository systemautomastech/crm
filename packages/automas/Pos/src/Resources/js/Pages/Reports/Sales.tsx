import { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { formatDate, formatCurrency } from '@/utils/helpers';
import { BarChart3, TrendingUp, DollarSign, Tag, RotateCcw, TrendingDown } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, LineChart, Line } from 'recharts';
import { DataTable } from "@/components/ui/data-table";
import NoRecordsFound from '@/components/no-records-found';

interface SalesReportProps {
    salesData: {
        data: Array<{
            id: number;
            sale_number: string;
            total: number;
            created_at: string;
            customer?: { name: string };
            warehouse?: { name: string };
        }>;
    };
    dailySales?: Array<{ date: string; sales: number; count: number }>;
    monthlySales?: Array<{ month: string; sales: number; count: number }>;
    warehouseSales?: Array<{ name: string; sales: number; count: number }>;
    discountData?: Array<{
        product_name: string;
        sku: string;
        total_discount_given: number;
        total_revenue: number;
    }>;
    monthlyDiscounts?: Array<{ month: string; discount_amount: number }>;
    returnData?: Array<{
        product_name: string;
        sku: string;
        total_returns: number;
        total_return_amount: number;
    }>;
    monthlyReturns?: Array<{ month: string; return_amount: number; return_count: number }>;
    returnStats?: {
        total_returns: number;
        total_return_amount: number;
    };
}

export default function SalesReport({ salesData, dailySales, monthlySales, warehouseSales, discountData, monthlyDiscounts, returnData, monthlyReturns, returnStats }: SalesReportProps) {
    const { t } = useTranslation();
    const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8'];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('POS'), url: route('pos.index') },
                { label: t('Sales Report') }
            ]}
            pageTitle={t('Sales Report')}
        >
            <Head title={t('Sales Report')} />

            <Tabs defaultValue="daily" className="w-full">
                    <TabsList className="grid w-full grid-cols-5">
                        <TabsTrigger value="daily">{t('Daily Sales')}</TabsTrigger>
                        <TabsTrigger value="monthly">{t('Monthly Sales')}</TabsTrigger>
                        <TabsTrigger value="warehouse">{t('Warehouse Sales')}</TabsTrigger>
                        <TabsTrigger value="discounts">{t('Discounts')}</TabsTrigger>
                        <TabsTrigger value="returns">{t('Returns')}</TabsTrigger>
                    </TabsList>

                <TabsContent value="daily" className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{t('Daily Sales Performance')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={400}>
                                <BarChart data={dailySales || []}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="date" />
                                    <YAxis />
                                    <Tooltip formatter={(value) => [formatCurrency(value), t('Sales')]} />
                                    <Bar dataKey="sales" fill="#8884d8" />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="monthly" className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{t('Monthly Sales Performance')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={400}>
                                <BarChart data={monthlySales || []}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="month" />
                                    <YAxis />
                                    <Tooltip formatter={(value) => [formatCurrency(value), t('Sales')]} />
                                    <Bar dataKey="sales" fill="#00C49F" />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="warehouse" className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{t('Warehouse Sales Comparison')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={400}>
                                <BarChart data={warehouseSales || []}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip formatter={(value) => [formatCurrency(value), t('Sales')]} />
                                    <Bar dataKey="sales" fill="#ff7300" />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="discounts" className="space-y-6">
                    {/* Discount Summary Cards */}
                    <div className="grid gap-4 md:grid-cols-3">
                        <Card className="relative overflow-hidden bg-gradient-to-r from-red-50 to-red-100 border-red-200">
                            <div className="absolute top-2 right-2">
                                <Tag className="h-5 w-5 text-red-700 opacity-80" />
                            </div>
                            <CardHeader className="text-center space-y-0 pb-1 pt-3">
                                <div className="text-2xl font-bold text-red-700">
                                    {formatCurrency(discountData?.reduce((sum, d) => sum + (Number(d.total_discount_given) || 0), 0) || 0)}
                                </div>
                            </CardHeader>
                            <CardContent className="text-center pt-1 pb-3">
                                <CardTitle className="text-sm font-medium text-red-700">{t('Total Discount Given')}</CardTitle>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden bg-gradient-to-r from-green-50 to-green-100 border-green-200">
                            <div className="absolute top-2 right-2">
                                <DollarSign className="h-5 w-5 text-green-700 opacity-80" />
                            </div>
                            <CardHeader className="text-center space-y-0 pb-1 pt-3">
                                <div className="text-2xl font-bold text-green-700">
                                    {formatCurrency(discountData?.reduce((sum, d) => sum + (Number(d.total_revenue) || 0), 0) || 0)}
                                </div>
                            </CardHeader>
                            <CardContent className="text-center pt-1 pb-3">
                                <CardTitle className="text-sm font-medium text-green-700">{t('Revenue with Discounts')}</CardTitle>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
                            <div className="absolute top-2 right-2">
                                <BarChart3 className="h-5 w-5 text-blue-700 opacity-80" />
                            </div>
                            <CardHeader className="text-center space-y-0 pb-1 pt-3">
                                <div className="text-2xl font-bold text-blue-700">{discountData?.length || 0}</div>
                            </CardHeader>
                            <CardContent className="text-center pt-1 pb-3">
                                <CardTitle className="text-sm font-medium text-blue-700">{t('Products with Discounts')}</CardTitle>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Monthly Discount Trends */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base flex items-center gap-2">
                                <TrendingUp className="h-4 w-4" />
                                {t('Monthly Discount Trends')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <LineChart data={monthlyDiscounts || []}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="month" />
                                    <YAxis />
                                    <Tooltip formatter={(value) => [formatCurrency(value), t('Discount Amount')]} />
                                    <Line type="monotone" dataKey="discount_amount" stroke="#f97316" strokeWidth={2} />
                                </LineChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* Top Products with Discounts */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base flex items-center gap-2">
                                <Tag className="h-4 w-4" />
                                {t('Top Products by Discount Given')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-96">
                                <DataTable
                                    data={discountData || []}
                                    columns={[
                                        {
                                            key: 'product_name',
                                            header: t('Product Name'),
                                            render: (value: string) => (
                                                <Badge variant="secondary" className="text-xs">
                                                    {value}
                                                </Badge>
                                            )
                                        },
                                        {
                                            key: 'sku',
                                            header: t('SKU')
                                        },
                                        {
                                            key: 'total_discount_given',
                                            header: t('Discount Given'),
                                            render: (value: number) => (
                                                <span className="text-red-600 font-medium">
                                                    {formatCurrency(value)}
                                                </span>
                                            )
                                        },
                                        {
                                            key: 'total_revenue',
                                            header: t('Revenue Generated'),
                                            render: (value: number) => (
                                                <span className="text-green-600 font-medium">
                                                    {formatCurrency(value)}
                                                </span>
                                            )
                                        }
                                    ]}
                                    emptyState={
                                        <NoRecordsFound
                                            icon={Tag}
                                            title={t('No discount data')}
                                            description={t('No products with discounts found.')}
                                            hasFilters={false}
                                            onClearFilters={() => {}}
                                        />
                                    }
                                />
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="returns" className="space-y-6">
                    {/* Return Summary Cards */}
                    <div className="grid gap-4 md:grid-cols-3">
                        <Card className="relative overflow-hidden bg-gradient-to-r from-red-50 to-red-100 border-red-200">
                            <div className="absolute top-2 right-2">
                                <RotateCcw className="h-5 w-5 text-red-700 opacity-80" />
                            </div>
                            <CardHeader className="text-center space-y-0 pb-1 pt-3">
                                <div className="text-2xl font-bold text-red-700">{returnStats?.total_returns || 0}</div>
                            </CardHeader>
                            <CardContent className="text-center pt-1 pb-3">
                                <CardTitle className="text-sm font-medium text-red-700">{t('Total Returns')}</CardTitle>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden bg-gradient-to-r from-orange-50 to-orange-100 border-orange-200">
                            <div className="absolute top-2 right-2">
                                <DollarSign className="h-5 w-5 text-orange-700 opacity-80" />
                            </div>
                            <CardHeader className="text-center space-y-0 pb-1 pt-3">
                                <div className="text-2xl font-bold text-orange-700">
                                    {formatCurrency(returnStats?.total_return_amount || 0)}
                                </div>
                            </CardHeader>
                            <CardContent className="text-center pt-1 pb-3">
                                <CardTitle className="text-sm font-medium text-orange-700">{t('Return Amount')}</CardTitle>
                            </CardContent>
                        </Card>
                        <Card className="relative overflow-hidden bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
                            <div className="absolute top-2 right-2">
                                <BarChart3 className="h-5 w-5 text-blue-700 opacity-80" />
                            </div>
                            <CardHeader className="text-center space-y-0 pb-1 pt-3">
                                <div className="text-2xl font-bold text-blue-700">{returnData?.length || 0}</div>
                            </CardHeader>
                            <CardContent className="text-center pt-1 pb-3">
                                <CardTitle className="text-sm font-medium text-blue-700">{t('Products Returned')}</CardTitle>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Monthly Return Trends */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base flex items-center gap-2">
                                <TrendingDown className="h-4 w-4" />
                                {t('Monthly Return Trends')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <LineChart data={monthlyReturns || []}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="month" />
                                    <YAxis />
                                    <Tooltip formatter={(value) => [formatCurrency(value), t('Return Amount')]} />
                                    <Line type="monotone" dataKey="return_amount" stroke="#ef4444" strokeWidth={2} />
                                </LineChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* Top Returned Products */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base flex items-center gap-2">
                                <RotateCcw className="h-4 w-4" />
                                {t('Top Returned Products')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-96">
                                <DataTable
                                    data={returnData || []}
                                    columns={[
                                        {
                                            key: 'product_name',
                                            header: t('Product Name'),
                                            render: (value: string) => (
                                                <Badge variant="secondary" className="text-xs">
                                                    {value}
                                                </Badge>
                                            )
                                        },
                                        {
                                            key: 'sku',
                                            header: t('SKU')
                                        },
                                        {
                                            key: 'total_returns',
                                            header: t('Return Count'),
                                            render: (value: number) => (
                                                <span className="text-red-600 font-medium">
                                                    {value}
                                                </span>
                                            )
                                        },
                                        {
                                            key: 'total_return_amount',
                                            header: t('Return Amount'),
                                            render: (value: number) => (
                                                <span className="text-red-600 font-medium">
                                                    {formatCurrency(value)}
                                                </span>
                                            )
                                        }
                                    ]}
                                    emptyState={
                                        <NoRecordsFound
                                            icon={RotateCcw}
                                            title={t('No return data')}
                                            description={t('No product returns found.')}
                                            hasFilters={false}
                                            onClearFilters={() => {}}
                                        />
                                    }
                                />
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </AuthenticatedLayout>
    );
}