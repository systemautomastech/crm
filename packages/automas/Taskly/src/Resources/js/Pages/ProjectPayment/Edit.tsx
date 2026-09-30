import { useState, useEffect, useMemo } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { DatePicker } from '@/components/ui/date-picker';
import { InputError } from '@/components/ui/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useFormFields } from '@/hooks/useFormFields';
import { formatCurrency } from '@/utils/helpers';
import { CalendarDays, Package } from 'lucide-react';
import type { ProjectPayment, ProjectPaymentItem } from './types';
import PaymentItemRow from './Components/PaymentItemRow'; 

interface Milestone {
    id: number;
    title: string;
    cost: number;
    status: string;
    progress: number;
}

interface Project {
    id: number;
    name: string;
    clients: Array<{ id: number; name: string }>;
}

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface EditProps {
    payment: ProjectPayment;
    projects: Project[];
    customers: Customer[];
}

interface PaymentItemForm {
    milestone_id: string;
    price: string;
    discount_percentage: string;
    discount_amount: number;
    total_amount: number;
}

export default function Edit() {
    const { t } = useTranslation();
    const { payment, projects, customers } = usePage<EditProps>().props;
    useFlashMessages();

    const { data, setData, put, processing, errors } = useForm({
        ...payment,
        project_id: payment.project_id?.toString() || '',
        customer_id: payment.customer_id?.toString() || '',
        items: payment.items?.map(item => ({
            ...item,
            milestone_id: item.milestone_id?.toString() || '',
            price: item.price?.toString() || '',
            discount_percentage: item.discount_percentage?.toString() || '0',
        })) || [],
    });

    const [milestones, setMilestones] = useState<Milestone[]>([]);
    const [loadingMilestones, setLoadingMilestones] = useState(false);

    const bankAccountField = useFormFields('bankAccountField', data, setData, errors, 'edit');

    const filteredCustomers = useMemo(() => {
        if (!data.project_id) return customers;
        const project = projects.find((p) => p.id.toString() === data.project_id);
        return project?.clients || [];
    }, [data.project_id, projects, customers]);

    const totals = useMemo(() => {
        let subtotal = 0;
        let totalDiscount = 0;
        data.items.forEach((item) => {
            const price = parseFloat(item.price) || 0;
            const discountPercentage = parseFloat(item.discount_percentage) || 0;
            const discountAmount = (price * discountPercentage) / 100;
            const totalAmount = price - discountAmount;
            subtotal += price;
            totalDiscount += discountAmount;
        });
        return {
            subtotal,
            discountAmount: totalDiscount,
            total: subtotal - totalDiscount,
        };
    }, [data.items]);

    useEffect(() => {
        if (data.project_id) {
            setLoadingMilestones(true);
            fetch(route('project-payments.get-milestones') + `?project_id=${data.project_id}`)
                .then((res) => res.json())
                .then((data) => {
                    setMilestones(data);
                    setLoadingMilestones(false);
                })
                .catch(() => setLoadingMilestones(false));
        } else {
            setMilestones([]);
        }
    }, [data.project_id]);

    const updateItem = (index: number, field: keyof PaymentItemForm, value: string | number) => {
        const newItems = [...data.items];
        newItems[index] = { ...newItems[index], [field]: value };

        if (field === 'milestone_id') {
            const milestone = milestones.find((m) => m.id.toString() === value);
            if (milestone) {
                newItems[index].price = milestone.cost.toString();
            }
        }

        const price = parseFloat(newItems[index].price) || 0;
        const discountPercentage = parseFloat(newItems[index].discount_percentage) || 0;
        newItems[index].discount_amount = (price * discountPercentage) / 100;
        newItems[index].total_amount = price - newItems[index].discount_amount;

        setData('items', newItems);
    };

    const addItem = () => {
        setData('items', [
            ...data.items,
            { milestone_id: '', price: '', discount_percentage: '0', discount_amount: 0, total_amount: 0 },
        ]);
    };

    const removeItem = (index: number) => {
        if (data.items.length <= 1) return;
        const newItems = data.items.filter((_, i) => i !== index);
        setData('items', newItems);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('project-payments.update', payment.id));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Project'), url: route('project.dashboard.index') },
                { label: t('Project Payments'), url: route('project-payments.index') },
                { label: payment.payment_number },
            ]}
            pageTitle={t('Edit Project Payment')}
        >
            <Head title={t('Edit Project Payment')} />

            <form onSubmit={handleSubmit} className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <CalendarDays className="h-5 w-5" />
                            {t('Payment Details')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <Label htmlFor="payment_date" required>
                                    {t('Payment Date')}
                                </Label>
                                <DatePicker
                                    id="payment_date"
                                    value={data.payment_date}
                                    onChange={(date) => setData('payment_date', date || '')}
                                    required
                                />
                                <InputError message={errors.payment_date} />
                            </div>
                            <div>
                                <Label htmlFor="due_date" required>
                                    {t('Due Date')}
                                </Label>
                                <DatePicker
                                    id="due_date"
                                    value={data.due_date}
                                    onChange={(date) => setData('due_date', date || '')}
                                    required
                                />
                                <InputError message={errors.due_date} />
                            </div>
                            <div>
                                <Label htmlFor="project_id" required>
                                    {t('Project')}
                                </Label>
                                <Select value={data.project_id} onValueChange={(value) => setData('project_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Select Project')} />
                                    </SelectTrigger>
                                    <SelectContent searchable>
                                        {projects.map((project) => (
                                            <SelectItem key={project.id} value={project.id.toString()}>
                                                {project.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.project_id} />
                            </div>
                            <div>
                                <Label htmlFor="customer_id" required>
                                    {t('Customer')}
                                </Label>
                                <Select value={data.customer_id} onValueChange={(value) => setData('customer_id', value)} disabled={!data.project_id}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={data.project_id ? t('Select Customer') : t('Select Project First')} />
                                    </SelectTrigger>
                                    <SelectContent searchable>
                                        {filteredCustomers.map((customer) => (
                                            <SelectItem key={customer.id} value={customer.id.toString()}>
                                                {customer.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.customer_id} />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                            {bankAccountField.map((field) => (
                                <div key={field.id}>{field.component}</div>
                            ))}
                            <div>
                                <Label htmlFor="payment_terms">
                                    {t('Payment Terms')}
                                </Label>
                                <Input
                                    id="payment_terms"
                                    value={data.payment_terms}
                                    onChange={(e) => setData('payment_terms', e.target.value)}
                                    placeholder={t('e.g., Net 30')}
                                />
                                <InputError message={errors.payment_terms} />
                            </div>
                            <div className="lg:col-span-2">
                                <Label htmlFor="notes">
                                    {t('Notes')}
                                </Label>
                                <Textarea
                                    id="notes"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    rows={2}
                                    placeholder={t('Additional notes...')}
                                />
                                <InputError message={errors.notes} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Package className="h-5 w-5" />
                                {t('Payment Items')}
                            </CardTitle>
                            <Button
                                type="button"
                                onClick={addItem}
                                variant="default"
                                size="sm"
                            >
                                + {t('Add Item')}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {errors.items && typeof errors.items === 'string' && (
                            <p className="text-sm text-red-500 mb-4">{errors.items}</p>
                        )}

                        <div className="overflow-x-auto">
                            <table className="min-w-full">
                                <thead>
                                    <tr className="border-b border-border">
                                        <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                            {t('Milestone')} <span className="text-red-500">*</span>
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                            {t('Price')} <span className="text-red-500">*</span>
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                            {t('Discount')} %
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-semibold text-foreground">
                                            {t('Total')}
                                        </th>
                                        <th className="px-4 py-3 text-center text-sm font-semibold text-foreground">
                                            {t('Action')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    {data.items.map((item, index) => (
                                        <PaymentItemRow
                                            key={index}
                                            index={index}
                                            item={item}
                                            milestones={milestones}
                                            loadingMilestones={loadingMilestones}
                                            projectSelected={!!data.project_id}
                                            errors={errors}
                                            onChange={updateItem}
                                            onRemove={removeItem}
                                            canRemove={data.items.length > 1}
                                        />
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="mt-6 flex justify-end">
                            <div className="w-80 bg-muted/30 rounded-lg p-4">
                                <h3 className="font-semibold mb-3">{t('Payment Summary')}</h3>
                                <div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">{t('Subtotal')}</span>
                                        <span className="font-medium">{formatCurrency(totals.subtotal)}</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">{t('Discount')}</span>
                                        <span className="font-medium text-red-600">-{formatCurrency(totals.discountAmount)}</span>
                                    </div>
                                    <Separator className="my-2" />
                                    <div className="flex justify-between">
                                        <span className="font-semibold">{t('Total')}</span>
                                        <span className="font-bold text-lg">{formatCurrency(totals.total)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>



                <div className="flex justify-between items-center">
                    <div className="text-sm text-muted-foreground">
                        {data.items.length} {t('items added')}
                    </div>
                    <div className="flex gap-3">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => window.history.back()}
                        >
                            {t('Cancel')}
                        </Button>
                        <Button
                            type="submit"
                            disabled={processing || data.items.length === 0}
                        >
                            {processing ? t('Updating...') : t('Update')}
                        </Button>
                    </div>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
