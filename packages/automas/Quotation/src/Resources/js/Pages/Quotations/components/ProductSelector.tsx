import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Loader2 } from 'lucide-react';
import { toast } from 'sonner';

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
    value?: number;
    selectedProductId?: number;
    warehouseId?: string | number | null;
    onChange?: (productId: number, product?: Product) => void;
    onSelect?: (productId: number, product?: Product) => void;
    placeholder?: string;
    disabled?: boolean;
    isRefreshing?: boolean;
}

export default function ProductSelector({
    products,
    value,
    selectedProductId,
    warehouseId,
    onChange,
    onSelect,
    placeholder,
    disabled,
    isRefreshing
}: Props) {
    const { t } = useTranslation();
    const [isOpen, setIsOpen] = useState(false);

    const actualValue = selectedProductId !== undefined ? selectedProductId : (value || 0);

    const hasWarehouse = Boolean(
        warehouseId !== undefined &&
        warehouseId !== null &&
        String(warehouseId).trim() !== '' &&
        String(warehouseId) !== '0'
    );

    const handleProductChange = (productIdStr: string) => {
        const id = parseInt(productIdStr, 10);
        const product = products.find(p => p.id === id);
        if (onSelect) {
            onSelect(id, product);
        }
        if (onChange) {
            onChange(id, product);
        }
    };

    const handleOpenChange = (open: boolean) => {
        if (open) {
            if (warehouseId !== undefined && !hasWarehouse) {
                toast.warning(t('Please select a warehouse first'), { id: 'warehouse-warning' });
                setIsOpen(false);
                return;
            }
            setIsOpen(true);
        } else {
            setIsOpen(false);
        }
    };

    const isDisabled = disabled || (warehouseId !== undefined && !hasWarehouse) || isRefreshing;

    let displayPlaceholder: React.ReactNode = placeholder || t('Select Product');
    if (warehouseId !== undefined && !hasWarehouse) {
        displayPlaceholder = t('Select Warehouse First');
    } else if (isRefreshing) {
        displayPlaceholder = (
            <div className="flex items-center gap-2 text-muted-foreground font-medium">
                <Loader2 className="h-4 w-4 animate-spin text-primary shrink-0" />
                <span>{t('Loading products...')}</span>
            </div>
        );
    } else if (products.length === 0) {
        displayPlaceholder = t('No products found');
    }

    return (
        <Select
            key={`${warehouseId ?? 'none'}-${isRefreshing ? 'loading' : products.length}`}
            open={isOpen}
            value={actualValue ? actualValue.toString() : ''}
            onValueChange={handleProductChange}
            onOpenChange={handleOpenChange}
            disabled={isDisabled}
        >
            <SelectTrigger className="w-full">
                {isRefreshing ? (
                    displayPlaceholder
                ) : (
                    <SelectValue placeholder={displayPlaceholder as string} />
                )}
            </SelectTrigger>
            {!isDisabled && (
                <SelectContent searchable>
                    {products.length === 0 ? (
                        <div className="py-3 px-2 text-xs text-center text-muted-foreground">
                            {t('No products found')}
                        </div>
                    ) : (
                        products.map((product) => (
                            <SelectItem key={product.id} value={product.id.toString()}>
                                {product.name}
                            </SelectItem>
                        ))
                    )}
                </SelectContent>
            )}
        </Select>
    );
}
