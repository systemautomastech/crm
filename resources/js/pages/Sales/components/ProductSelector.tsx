import React from 'react';
import { useTranslation } from 'react-i18next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Loader2 } from 'lucide-react';

interface Product {
    id: number;
    name: string;
    sale_price: number;
    description?: string;
    long_description?: string;
    unit?: string;
    unit_name?: string;
    type?: string;
    taxes?: Array<{ id: number; tax_name: string; rate: number }>;
}

interface Props {
    products: Product[];
    value: number;
    onChange: (productId: number, product?: Product) => void;
    placeholder?: string;
    warehouseId?: string | number;
    disabled?: boolean;
    isRefreshing?: boolean;
}

export default function ProductSelector({ products, value, onChange, placeholder, warehouseId, disabled, isRefreshing }: Props) {
    const { t } = useTranslation();

    const isWarehouseMissing = warehouseId !== undefined && (!warehouseId || String(warehouseId).trim() === '' || String(warehouseId) === '0');

    const handleChange = (productId: string) => {
        const id = parseInt(productId);
        const product = products.find(p => p.id === id);
        onChange(id, product);
    };

    let displayPlaceholder: React.ReactNode = placeholder || t('Select Item');
    if (isWarehouseMissing) {
        displayPlaceholder = t('Select Warehouse First');
    } else if (isRefreshing) {
        displayPlaceholder = (
            <div className="flex items-center gap-2 text-muted-foreground font-medium">
                <Loader2 className="h-4 w-4 animate-spin text-primary shrink-0" />
                <span>{t('Loading products...')}</span>
            </div>
        );
    } else if (products.length === 0) {
        displayPlaceholder = t('No items found');
    }

    const isDisabled = disabled || isWarehouseMissing || isRefreshing || products.length === 0;

    return (
        <Select
            key={`${warehouseId ?? 'none'}-${isRefreshing ? 'loading' : products.length}`}
            value={value ? value.toString() : ''}
            onValueChange={handleChange}
            disabled={isDisabled}
        >
            <SelectTrigger className="w-full">
                {isRefreshing ? (
                    displayPlaceholder
                ) : (
                    <SelectValue placeholder={displayPlaceholder as string} />
                )}
            </SelectTrigger>
            {!isDisabled && products.length > 0 && (
                <SelectContent searchable>
                    {products.map((product) => (
                        <SelectItem key={product.id} value={product.id.toString()}>
                            {product.name}
                        </SelectItem>
                    ))}
                </SelectContent>
            )}
        </Select>
    );
}