import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Loader2 } from 'lucide-react';
import { toast } from 'sonner';

interface Product {
    id: number;
    name: string;
    sale_price: number;
    unit?: string;
    taxes?: Array<{ id: number; tax_name: string; rate: number }>;
}

interface Props {
    products: Product[];
    value: number;
    warehouseId?: string | number | null;
    onChange: (productId: number, product?: Product) => void;
    disabled?: boolean;
    isRefreshing?: boolean;
}

export default function ProductSelector({ products, value, warehouseId, onChange, disabled, isRefreshing }: Props) {
    const { t } = useTranslation();
    const [isOpen, setIsOpen] = useState(false);

    const hasWarehouse = Boolean(warehouseId && String(warehouseId).trim() !== '' && String(warehouseId) !== '0');

    const handleChange = (productId: string) => {
        const id = parseInt(productId);
        const product = products.find(p => p.id === id);
        onChange(id, product);
    };

    const handleOpenChange = (open: boolean) => {
        if (open) {
            if (!hasWarehouse) {
                toast.warning(t('Please select a warehouse first'), { id: 'warehouse-warning' });
                setIsOpen(false);
                return;
            }
            setIsOpen(true);
        } else {
            setIsOpen(false);
        }
    };

    const isDisabled = disabled || !hasWarehouse || isRefreshing || products.length === 0;

    let displayPlaceholder: React.ReactNode = t('Select Product');
    if (!hasWarehouse) {
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
            value={value ? value.toString() : ''}
            onValueChange={handleChange}
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
