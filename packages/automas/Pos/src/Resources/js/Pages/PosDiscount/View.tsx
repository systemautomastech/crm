import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Tag } from 'lucide-react';
import { PosDiscount } from './types';
import { formatDate } from '@/utils/helpers';

interface ViewDiscountProps {
    discount: PosDiscount;
}

export default function View({ discount }: ViewDiscountProps) {
    const { t } = useTranslation();

    const getApplyOnLabel = () => {
        if (discount.category_id && discount.category) {
            return `${t('Category')}: ${discount.category.name}`;
        }
        if (discount.products && discount.products.length > 0) {
            return discount.products.length === 1 
                ? `${t('Product')}: ${discount.products[0].name}` 
                : `${discount.products.length} ${t('Products')}`;
        }
        return t('All Products');
    };

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <Tag className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Discount Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>

            <div className="overflow-y-auto flex-1 p-4 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Discount Name')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{discount.name || '-'}</p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Discount Type')}</label>
                        <p className="text-sm text-gray-900 p-1 rounded">
                            <span className={`px-2 py-1 rounded-full text-sm ${
                                discount.discount_type === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
                            }`}>
                                {discount.discount_type === 'percentage' ? t('Percentage') : t('Fixed')}
                            </span>
                        </p>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Discount Value')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded font-semibold text-blue-600">
                            {discount.discount_type === 'percentage' ? `${discount.discount_value}%` : `₹${discount.discount_value}`}
                        </p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Minimum Quantity')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{discount.min_quantity}</p>
                    </div>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Apply On')}</label>
                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{getApplyOnLabel()}</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Start Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                            {discount.start_date ? formatDate(discount.start_date) : '-'}
                        </p>
                    </div>
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('End Date')}</label>
                        <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">
                            {discount.end_date ? formatDate(discount.end_date) : '-'}
                        </p>
                    </div>
                </div>

                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">{t('Status')}</label>
                    <p className="text-sm text-gray-900 p-1 rounded">
                        <span className={`px-2 py-1 rounded-full text-sm ${
                            discount.is_active === true ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }`}>
                            {discount.is_active === true ? t('Active') : t('Inactive')}
                        </span>
                    </p>
                </div>

                {discount.products && discount.products.length > 0 && (
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Applicable Products')}</label>
                        <div className="bg-gray-50 p-2 rounded max-h-40 overflow-y-auto">
                            <div className="grid grid-cols-2 gap-2">
                                {discount.products.map((product, index) => (
                                    <div key={product.id} className="text-sm text-gray-900">
                                        {index + 1}. {product.name} {product.sku && `(${product.sku})`}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}
