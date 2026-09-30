import { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';
import { Tag, Package, Search } from 'lucide-react';
import { formatCurrency } from '@/utils/helpers';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import NoRecordsFound from '@/components/no-records-found';
import InputError from '@/components/ui/input-error';
import { PosDiscount } from './types';
import { ScrollArea } from '@/components/ui/scroll-area';

interface Product {
    id: number;
    name: string;
    sku: string;
    price: number;
    category_id?: number;
}

interface Category {
    id: number;
    name: string;
}

interface EditProps {
    discount: PosDiscount;
    products: Product[];
    categories: Category[];
}

export default function Edit() {
    const { t } = useTranslation();
    const { discount, products = [], categories = [] } = usePage<EditProps>().props;

    const [searchTerm, setSearchTerm] = useState('');
    const [selectedCategoryFilter, setSelectedCategoryFilter] = useState('all');

    // Determine apply_on based on existing data
    const initialApplyOn = discount?.category_id ? 'category' : 'product';
    const initialProductIds = discount?.products?.map(p => p.id) || [];

    const { data, setData, put, processing, errors } = useForm({
        name: discount?.name || '',
        discount_type: discount?.discount_type || 'percentage',
        discount_value: discount?.discount_value?.toString() || '',
        apply_on: initialApplyOn,
        product_ids: initialProductIds,
        category_id: discount?.category_id || null,
        min_quantity: discount?.min_quantity?.toString() || '1',
        start_date: discount?.start_date || '',
        end_date: discount?.end_date || '',
        is_active: discount?.is_active ?? true,
    });

    const filteredProducts = products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            product.sku.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesCategory = selectedCategoryFilter === 'all' || product.category_id?.toString() === selectedCategoryFilter;
        return matchesSearch && matchesCategory;
    });

    const handleProductSelect = (productId: number, checked: boolean) => {
        if (checked) {
            setData('product_ids', [...data.product_ids, productId]);
        } else {
            setData('product_ids', data.product_ids.filter(id => id !== productId));
        }
    };

    const handleSelectAll = (checked: boolean) => {
        const allProductIds = filteredProducts.map(p => p.id);
        if (checked) {
            setData('product_ids', allProductIds);
        } else {
            setData('product_ids', []);
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        
        const formData: any = {
            name: data.name,
            discount_type: data.discount_type,
            discount_value: data.discount_value,
            min_quantity: data.min_quantity,
            start_date: data.start_date,
            end_date: data.end_date,
            is_active: data.is_active,
        };
        
        if (data.apply_on === 'product') {
            formData.product_ids = data.product_ids;
            formData.category_id = null;
        } else {
            formData.category_id = data.category_id;
            formData.product_ids = [];
        }
        
        put(route('pos.discounts.update', discount.id), {
            data: formData,
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('POS'), url: route('pos.index') },
                { label: t('Pos Discounts'), url: route('pos.discounts.index') },
                { label: t('Edit') }
            ]}
            pageTitle={t('Edit Discount')}
            pageActions={null}
        >
            <Head title={t('Edit Discount')} />

            <form onSubmit={submit} className="h-full flex flex-col">
                <ScrollArea className="flex-1">
                    <div className="space-y-6 pr-4">
                        {/* Discount Details Card */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Tag className="h-5 w-5" />
                                {t('Discount Details')}
                            </CardTitle>
                            <div className="flex items-center space-x-2">
                                <Switch
                                    id="is_active"
                                    checked={data.is_active === true}
                                    onCheckedChange={(checked) => setData('is_active', checked)}
                                />
                                <Label htmlFor="is_active" className="text-sm font-normal cursor-pointer">
                                    {data.is_active === true ? t('Active') : t('Inactive')}
                                </Label>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <Label htmlFor="name" required>{t('Name')}</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder={t('Enter discount name')}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div>
                                <Label htmlFor="apply_on" required>{t('Apply On')}</Label>
                                <Select
                                    value={data.apply_on}
                                    onValueChange={(value) => setData('apply_on', value as 'product' | 'category')}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="product">{t('Product')}</SelectItem>
                                        <SelectItem value="category">{t('Category')}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.apply_on} />
                            </div>

                             {data.apply_on === 'category' && (
                                <div>
                                    <Label htmlFor="category_id" required>{t('Category')}</Label>
                                    <Select
                                        value={data.category_id ? String(data.category_id) : ''}
                                        onValueChange={(value) => setData('category_id', value ? parseInt(value) : null)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select a category')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem key={category.id} value={String(category.id)}>
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.category_id} />
                                </div>
                            )}
                            
                            <div>
                                <Label htmlFor="discount_type" required>{t('Discount Type')}</Label>
                                <Select
                                    value={data.discount_type}
                                    onValueChange={(value) => setData('discount_type', value as 'percentage' | 'fixed')}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="percentage">{t('Percentage')}</SelectItem>
                                        <SelectItem value="fixed">{t('Fixed')}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.discount_type} />
                            </div>

                            <div>
                                <Label htmlFor="discount_value" required>
                                    {t('Discount')} {data.discount_type === 'percentage' ? '(%)' : '(₹)'}
                                </Label>
                                <Input
                                    id="discount_value"
                                    type="number"
                                    step="0.01"
                                    value={data.discount_value}
                                    onChange={(e) => setData('discount_value', e.target.value)}
                                    placeholder="0"
                                    required
                                />
                                <InputError message={errors.discount_value} />
                            </div>

                            <div>
                                <Label htmlFor="min_quantity" required>{t('Minimum Quantity')}</Label>
                                <Input
                                    id="min_quantity"
                                    type="number"
                                    min="1"
                                    value={data.min_quantity}
                                    onChange={(e) => setData('min_quantity', e.target.value)}
                                    placeholder="1"
                                    required
                                />
                                <InputError message={errors.min_quantity} />
                            </div>

                            <div>
                                <Label required>{t('Start Date')}</Label>
                                <DatePicker
                                    value={data.start_date}
                                    onChange={(value) => setData('start_date', value)}
                                    placeholder={t('Select start date')}
                                />
                                <InputError message={errors.start_date} />
                            </div>

                            <div>
                                <Label required>{t('End Date')}</Label>
                                <DatePicker
                                    value={data.end_date}
                                    onChange={(value) => setData('end_date', value)}
                                    placeholder={t('Select end date')}
                                />
                                <InputError message={errors.end_date} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Products Selection Card */}
                {data.apply_on === 'product' && (
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Package className="h-5 w-5" />
                                    {t('Select Products')}
                                    <Badge variant="secondary" className="ml-2">{filteredProducts.length}</Badge>
                                </CardTitle>
                                <div className="flex items-center gap-3">
                                    {data.product_ids.length > 0 && (
                                        <Badge variant="default" className="bg-blue-600">
                                            {data.product_ids.length} {t('Selected')}
                                        </Badge>
                                    )}
                                    <div className="flex items-center gap-2">
                                        <Checkbox
                                            id="select-all"
                                            checked={data.product_ids.length === filteredProducts.length && filteredProducts.length > 0}
                                            onCheckedChange={(checked) => handleSelectAll(checked as boolean)}
                                        />
                                        <Label htmlFor="select-all" className="text-sm font-normal cursor-pointer">
                                            {t('Select All')}
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <Card className="mb-4">
                                <CardContent className="pt-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <Label className="text-sm font-medium mb-2 block">{t('Filter by Category')}</Label>
                                            <Select value={selectedCategoryFilter} onValueChange={setSelectedCategoryFilter}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('All Categories')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">{t('All Categories')}</SelectItem>
                                                    {categories.map(category => (
                                                        <SelectItem key={category.id} value={category.id.toString()}>
                                                            {category.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Label className="text-sm font-medium mb-2 block">{t('Search Products')}</Label>
                                            <div className="relative">
                                                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                                <Input
                                                    placeholder={t('Search by name or SKU...')}
                                                    value={searchTerm}
                                                    onChange={(e) => setSearchTerm(e.target.value)}
                                                    className="pl-10"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                            <div className="max-h-[50vh] overflow-y-auto">
                                {filteredProducts.length > 0 ? (
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {filteredProducts.map((product) => {
                                            const isSelected = data.product_ids.includes(product.id);
                                            return (
                                            <div
                                                key={product.id}
                                                className={`border rounded-lg p-3 transition-all ${
                                                    isSelected
                                                        ? 'border-gray-400 bg-gray-50'
                                                        : 'border-gray-200 hover:border-gray-300'
                                                }`}
                                            >
                                                <div className="flex items-center gap-3">
                                                    <Checkbox
                                                        id={`product-${product.id}`}
                                                        checked={isSelected}
                                                        onCheckedChange={(checked) => handleProductSelect(product.id, checked as boolean)}
                                                    />
                                                    <Label 
                                                        htmlFor={`product-${product.id}`}
                                                        className="flex-1 min-w-0 flex items-center justify-between gap-2 cursor-pointer"
                                                    >
                                                        <span className="font-medium text-sm text-gray-900 truncate">
                                                            {product.name}
                                                        </span>
                                                        <span className="text-xs text-gray-500 whitespace-nowrap">
                                                            {product.sku || '-'}
                                                        </span>
                                                        <span className="text-sm font-semibold text-green-600 whitespace-nowrap">
                                                            {formatCurrency(product.price || 0)}
                                                        </span>
                                                    </Label>
                                                </div>
                                            </div>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <NoRecordsFound
                                        icon={Package}
                                        title={searchTerm || selectedCategoryFilter !== 'all' ? t('No products found') : t('No products available')}
                                        description={searchTerm || selectedCategoryFilter !== 'all' ? t('Try adjusting your search terms or category filter') : t('Add products to create discounts')}
                                        hasFilters={!!searchTerm || selectedCategoryFilter !== 'all'}
                                        onClearFilters={() => {
                                            setSearchTerm('');
                                            setSelectedCategoryFilter('all');
                                        }}
                                        className="h-auto py-8"
                                    />
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}
                    {/* Action Buttons */}
                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="outline" onClick={() => window.history.back()}>
                            {t('Cancel')}
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? t('Updating...') : t('Update')}
                        </Button>
                    </div>
                </div>
                </ScrollArea>

            </form>
        </AuthenticatedLayout>
    );
}
