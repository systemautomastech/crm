import React from 'react';
import { useTranslation } from 'react-i18next';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/ui/input-error';

const InventoryFormComponent = ({ data, setData, errors }: any) => {
    const { t } = useTranslation();

    // Set default values if not already set
    React.useEffect(() => {
        if (!data.valuation_method && data.valuation_method !== '') {
            setData('valuation_method', 'weighted_average');
        }
        if (data.is_tracked === undefined || data.is_tracked === null) {
            setData('is_tracked', true);
        }
    }, [data.valuation_method, data.is_tracked]);

    return (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <Label htmlFor="reorder_level">{t('Reorder Level')}</Label>
                <Input
                    type="number"
                    step="0.01"
                    value={data.reorder_level || ''}
                    onChange={(e) => setData('reorder_level', e.target.value)}
                    placeholder={t('Enter Reorder Level')}
                />
                <InputError message={errors.reorder_level} />
            </div>
            <div>
                <Label htmlFor="maximum_level">{t('Maximum Level')}</Label>
                <Input
                    type="number"
                    step="0.01"
                    value={data.maximum_level || ''}
                    onChange={(e) => setData('maximum_level', e.target.value)}
                    placeholder={t('Enter Maximum Level')}
                />
                <InputError message={errors.maximum_level} />
            </div>
            <div>
                <Label htmlFor="valuation_method">{t('Valuation Method')}</Label>
                <Select
                    value={data.valuation_method || 'weighted_average'}
                    onValueChange={(value) => setData('valuation_method', value)}
                >
                    <SelectTrigger>
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="fifo">{t('FIFO (First In First Out)')}</SelectItem>
                        <SelectItem value="lifo">{t('LIFO (Last In First Out)')}</SelectItem>
                        <SelectItem value="weighted_average">{t('Weighted Average')}</SelectItem>
                    </SelectContent>
                </Select>
                <InputError message={errors.valuation_method} />
            </div>
            <div className="flex items-center space-x-2 mt-6">
                <Checkbox
                    id="is_tracked"
                    checked={data.is_tracked ?? true}
                    onCheckedChange={(checked) => setData('is_tracked', checked as boolean)}
                />
                <Label htmlFor="is_tracked" className="cursor-pointer">{t('Track Inventory')}</Label>
            </div>
        </div>
    );
};

export const inventoryFields = (data: any, setData: any, errors: any, mode: string = 'create') => {
    return [{
        id: 'inventory_fields',
        order: 200,
        component: (
            <div key="inventory_fields">
                <InventoryFormComponent
                    data={data}
                    setData={setData}
                    errors={errors}
                />
            </div>
        )
    }];
};

export const inventoryEditFields = (data: any, setData: any, errors: any, mode: string, item: any) => {
    const [inventoryData, setInventoryData] = React.useState(null);

    React.useEffect(() => {
        if (item?.id) {
            fetch(route('inventory.items.by-product', item.id))
                .then(res => res.json())
                .then(inventoryItem => {
                    if (inventoryItem) {
                        setData('reorder_level', inventoryItem.reorder_level || '');
                        setData('maximum_level', inventoryItem.maximum_level || '');
                        setData('valuation_method', inventoryItem.valuation_method || 'weighted_average');
                        setData('is_tracked', inventoryItem.is_tracked ?? true);
                        setInventoryData(inventoryItem);
                    }
                })
                .catch(err => 'Failed to fetch inventory data:');
        }
    }, [item?.id]);

    return [{
        id: 'inventory_fields',
        order: 200,
        component: (
            <div key="inventory_fields">
                <InventoryFormComponent
                    data={data}
                    setData={setData}
                    errors={errors}
                />
            </div>
        )
    }];
};
