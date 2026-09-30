import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { DatePicker } from '@/components/ui/date-picker';
import { Repeater, RepeaterField } from '@/components/ui/repeater';
import InputError from '@/components/ui/input-error';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useEffect, useState } from 'react';
import axios from 'axios';
import { ClipboardList, Warehouse } from 'lucide-react';

interface FormData {
    adjustment_date: string;
    warehouse_id: string;
    adjustment_type: string;
    reason: string;
    items: any[];
}

export default function CreatePage() {
    const { t } = useTranslation();
    const { warehouses: initialWarehouses } = usePage<any>().props;
    const [warehouses] = useState<any[]>(initialWarehouses || []);
    const [inventoryItems, setInventoryItems] = useState<any[]>([]);
    const [repeaterFields, setRepeaterFields] = useState<RepeaterField[]>([]);

    const { data, setData, post, processing, errors } = useForm<FormData>({
        adjustment_date: new Date().toISOString().split('T')[0],
        warehouse_id: '',
        adjustment_type: 'increase',
        reason: '',
        items: []
    });

    const handleWarehouseChange = async (warehouseId: string) => {
        setData('warehouse_id', warehouseId);
        setData('items', []);

        if (warehouseId) {
            try {
                const response = await axios.get(route('inventory.adjustments.warehouse.items'), {
                    params: { warehouse_id: warehouseId }
                });
                setInventoryItems(response.data.inventoryItems || []);
            } catch (error) {
                setInventoryItems([]);
            }
        } else {
            setInventoryItems([]);
        }
    };

    useEffect(() => {
        setRepeaterFields([
            {
                name: 'item_id',
                label: t('Inventory Item'),
                type: 'select',
                required: true,
                placeholder: t('Select Inventory Item'),
                options: inventoryItems.map((item: any) => ({
                    value: item.id.toString(),
                    label: item.product?.name || ''
                })),
                disabled: !data.warehouse_id || inventoryItems.length === 0
            },
            {
                name: 'current_quantity',
                label: t('Current Quantity'),
                type: 'number',
                placeholder: '0',
                disabled: true
            },
            {
                name: 'quantity',
                label: t('Quantity'),
                type: 'number',
                required: true,
                placeholder: '0',
                step: 0.01
            },
            {
                name: 'adjusted_quantity',
                label: t('Adjusted Quantity'),
                type: 'number',
                placeholder: '0',
                disabled: true
            },
            {
                name: 'notes',
                label: t('Notes'),
                type: 'text',
                placeholder: t('Item notes')
            }
        ]);
    }, [inventoryItems, t, data.warehouse_id]);

    useEffect(() => {
        if (data.items.length > 0) {
            const updatedItems = data.items.map(item => {
                if (item.current_quantity !== undefined && item.quantity) {
                    const currentQty = parseFloat(item.current_quantity) || 0;
                    const qty = parseFloat(item.quantity) || 0;

                    if (data.adjustment_type === 'increase') {
                        item.adjusted_quantity = currentQty + qty;
                    } else if (data.adjustment_type === 'decrease') {
                        item.adjusted_quantity = currentQty - qty;
                    } else {
                        item.adjusted_quantity = qty;
                    }
                }
                return item;
            });
            setData('items', updatedItems);
        }
    }, [data.adjustment_type]);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('inventory.adjustments.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('Inventory'), url: route('inventory.adjustments.index')},
                {label: t('Adjustments'), url: route('inventory.adjustments.index')},
                {label: t('Create')}
            ]}
            pageTitle={t('Create Inventory Adjustment')}
        >
            <Head title={t('Create Inventory Adjustment')} />

            <form onSubmit={submit} className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <ClipboardList className="h-5 w-5" />
                            {t('Adjustment Details')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <Label htmlFor="adjustment_date" required>
                                    {t('Adjustment Date')}
                                </Label>
                                <DatePicker
                                    id="adjustment_date"
                                    value={data.adjustment_date}
                                    onChange={(value) => setData('adjustment_date', value)}
                                    required
                                />
                                <InputError message={errors.adjustment_date} />
                            </div>
                            <div>
                                <Label htmlFor="warehouse_id" required>
                                    {t('Warehouse')}
                                </Label>
                                <Select value={data.warehouse_id} onValueChange={handleWarehouseChange}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Select Warehouse')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map((warehouse) => (
                                            <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                {warehouse.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.warehouse_id} />
                            </div>
                            <div>
                                <Label htmlFor="adjustment_type">
                                    {t('Adjustment Type')}
                                </Label>
                                <Select value={data.adjustment_type} onValueChange={(value) => setData('adjustment_type', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="increase">{t('Increase')}</SelectItem>
                                        <SelectItem value="decrease">{t('Decrease')}</SelectItem>
                                        <SelectItem value="recount">{t('Recount')}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.adjustment_type} />
                            </div>
                        </div>

                        <div className="mt-4">
                            <Label htmlFor="reason">
                                {t('Reason')}
                            </Label>
                            <Textarea
                                id="reason"
                                value={data.reason}
                                onChange={(e) => setData('reason', e.target.value)}
                                placeholder={t('Additional notes...')}
                                rows={2}
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <Warehouse className="h-5 w-5" />
                            {t('Adjustment Items')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Repeater
                            fields={repeaterFields}
                            value={data.items}
                            onChange={async (items) => {
                                const updatedItems = await Promise.all(items.map(async (item, index) => {
                                    const prevItem = data.items[index];
                                    if (item.item_id && data.warehouse_id && item.item_id !== prevItem?.item_id) {
                                        const inventoryItem = inventoryItems.find((i: any) => i.id.toString() === item.item_id);
                                        if (inventoryItem?.product_id) {
                                            try {
                                                const response = await axios.get(route('inventory.adjustments.get-quantity'), {
                                                    params: {
                                                        product_id: inventoryItem.product_id,
                                                        warehouse_id: data.warehouse_id
                                                    }
                                                });
                                                item.current_quantity = response.data.quantity || 0;
                                            } catch (error) {
                                                item.current_quantity = 0;
                                            }
                                        }
                                    }
                                    // Calculate adjusted_quantity based on adjustment_type
                                    if (item.current_quantity !== undefined && item.quantity) {
                                        const currentQty = parseFloat(item.current_quantity) || 0;
                                        const qty = parseFloat(item.quantity) || 0;
                                        if (data.adjustment_type === 'increase') {
                                            item.adjusted_quantity = currentQty + qty;
                                        } else if (data.adjustment_type === 'decrease') {
                                            item.adjusted_quantity = currentQty - qty;
                                        } else {
                                            item.adjusted_quantity = qty;
                                        }
                                    }
                                    return item;
                                }));
                                setData('items', updatedItems);
                            }}
                            addButtonText={t('Add Item')}
                            deleteTooltipText={t('Delete')}
                            minItems={0}
                            showDefault={true}
                        />
                        <InputError message={errors.items} />
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={() => window.history.back()}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Creating...') : t('Create')}
                    </Button>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
