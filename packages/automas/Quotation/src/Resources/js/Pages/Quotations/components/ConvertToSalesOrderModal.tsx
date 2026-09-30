import React, { useState, useEffect, useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { ShoppingCart, CheckCircle, User, UserPlus, Users, UserCheck } from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { InputError } from '@/components/ui/input-error';
import { MultiSelectEnhanced } from '@/components/ui/multi-select-enhanced';

interface SalesQuotation {
    id: number;
    quotation_number: string;
    quotation_date: string;
    due_date: string;
    customer_id?: number | null;
    customer_name?: string | null;
    customer_email?: string | null;
    customer_phone?: string | null;
    customer_address?: string | null;
    customer?: { id: number; name: string; email?: string; phone?: string; address?: string } | null;
    total_amount: number;
    sales_order_id?: number | null;
    status: string;
}

interface ConvertToSalesOrderModalProps {
    quotation: SalesQuotation;
    customers?: Array<{ id: number; name: string; email?: string }>;
    users?: Array<{ id: number; name: string }>;
    userGroups?: Array<{ id: number; name: string }>;
    buttonClassName?: string;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export default function ConvertToSalesOrderModal({
    quotation,
    customers = [],
    users = [],
    userGroups = [],
    buttonClassName,
    trigger,
    open: externalOpen,
    onOpenChange: externalOnOpenChange
}: ConvertToSalesOrderModalProps) {
    const { t } = useTranslation();
    const [internalOpen, setInternalOpen] = useState(false);
    const isOpen = externalOpen !== undefined ? externalOpen : internalOpen;
    const setIsOpen = externalOnOpenChange || setInternalOpen;

    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);

    const existingCustomerId = quotation?.customer_id || quotation?.customer?.id;

    const availableCustomers = useMemo(() => {
        const list = [...(customers || [])];
        if (existingCustomerId) {
            const found = list.some(c => String(c.id) === String(existingCustomerId));
            if (!found) {
                list.unshift({
                    id: existingCustomerId,
                    name: quotation.customer?.name || quotation.customer_name || `${t('Customer')} #${existingCustomerId}`,
                    email: quotation.customer?.email || quotation.customer_email || ''
                });
            }
        }
        return list;
    }, [customers, quotation, existingCustomerId, t]);

    const [formData, setFormData] = useState({
        order_name: quotation.quotation_number ? `${t('Order for')} ${quotation.quotation_number}` : '',
        customer_check: existingCustomerId ? 'exist' : (quotation.customer_name ? 'new' : 'exist'),
        customer_id: existingCustomerId ? String(existingCustomerId) : '',
        customer_name: quotation.customer?.name || quotation.customer_name || '',
        customer_email: quotation.customer?.email || quotation.customer_email || '',
        customer_phone: quotation.customer?.phone || quotation.customer_phone || '',
        customer_address: quotation.customer?.address || quotation.customer_address || '',
        customer_city: '',
        customer_state: '',
        customer_country: '',
        customer_zip_code: '',
        assignment_check: 'none',
        assigned_user_id: '',
        assigned_user_ids: [] as string[],
        assigned_group_id: '',
    });

    useEffect(() => {
        if (isOpen && quotation) {
            const extId = quotation.customer_id || quotation.customer?.id;
            setFormData({
                order_name: quotation.quotation_number ? `${t('Order for')} ${quotation.quotation_number}` : '',
                customer_check: extId ? 'exist' : (quotation.customer_name ? 'new' : 'exist'),
                customer_id: extId ? String(extId) : '',
                customer_name: quotation.customer?.name || quotation.customer_name || '',
                customer_email: quotation.customer?.email || quotation.customer_email || '',
                customer_phone: quotation.customer?.phone || quotation.customer_phone || '',
                customer_address: quotation.customer?.address || quotation.customer_address || '',
                customer_city: '',
                customer_state: '',
                customer_country: '',
                customer_zip_code: '',
                assignment_check: 'none',
                assigned_user_id: '',
                assigned_user_ids: [],
                assigned_group_id: '',
            });
            setErrors({});
        }
    }, [isOpen, quotation, t]);

    const toggleUserSelection = (userId: number) => {
        setFormData(prev => {
            const idStr = String(userId);
            const current = prev.assigned_user_ids || [];
            const exists = current.includes(idStr);
            const updated = exists ? current.filter(id => id !== idStr) : [...current, idStr];
            return {
                ...prev,
                assigned_user_ids: updated,
                assigned_user_id: updated[0] || ''
            };
        });
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);

        const payload: Record<string, any> = {
            customer_type: formData.customer_check === 'exist' ? 'existing' : (formData.customer_check === 'new' ? 'new' : 'existing'),
        };

        if (formData.customer_check === 'exist') {
            payload.customer_id = formData.customer_id;
        } else if (formData.customer_check === 'new') {
            payload.customer_name = formData.customer_name;
            payload.customer_email = formData.customer_email;
            payload.customer_phone = formData.customer_phone;
            payload.customer_address = formData.customer_address;
            payload.billing_address = formData.customer_address;
            payload.billing_city = formData.customer_city;
            payload.customer_city = formData.customer_city;
            payload.billing_state = formData.customer_state;
            payload.customer_state = formData.customer_state;
            payload.billing_country = formData.customer_country;
            payload.customer_country = formData.customer_country;
            payload.billing_postal_code = formData.customer_zip_code;
            payload.customer_zip_code = formData.customer_zip_code;
        }

        if (formData.assignment_check === 'user') {
            if (formData.assigned_user_ids && formData.assigned_user_ids.length > 0) {
                payload.assigned_user_ids = formData.assigned_user_ids;
            } else if (formData.assigned_user_id) {
                payload.assigned_user_id = formData.assigned_user_id;
            }
        } else if (formData.assignment_check === 'group' && formData.assigned_group_id) {
            payload.assigned_group_id = formData.assigned_group_id;
        }

        router.post(route('salesorder.orders.convert-from-quotation'), {
            quotation_id: quotation.id,
            ...payload
        }, {
            onSuccess: () => {
                setIsOpen(false);
                setErrors({});
                setProcessing(false);
            },
            onError: (errs) => {
                setErrors(errs);
                setProcessing(false);
            }
        });
    };

    if (quotation.sales_order_id && !externalOpen) {
        return (
            <TooltipProvider>
                <Tooltip delayDuration={0}>
                    <TooltipTrigger asChild>
                        <Link href={route('salesorder.orders.show', quotation.sales_order_id)}>
                            <Button size="sm" variant={buttonClassName ? 'ghost' : 'default'} className={buttonClassName || "text-blue-600 border-blue-200 hover:bg-blue-50"}>
                                <CheckCircle className="h-4 w-4" />
                            </Button>
                        </Link>
                    </TooltipTrigger>
                    <TooltipContent>
                        <span className="font-normal">{t('Already Converted To Sales Order')}</span>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        );
    }

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            <TooltipProvider>
                <Tooltip delayDuration={0}>
                    <TooltipTrigger asChild>
                        <DialogTrigger asChild>
                            {trigger ? (
                                trigger
                            ) : (
                                <Button size="sm" variant={buttonClassName ? 'ghost' : 'default'} className={buttonClassName || "bg-primary text-primary-foreground hover:bg-primary/90"}>
                                    <ShoppingCart className="h-4 w-4 mr-1" />
                                    {t('Convert to Sales Order')}
                                </Button>
                            )}
                        </DialogTrigger>
                    </TooltipTrigger>
                    <TooltipContent>
                        <span className="font-normal">{t('Convert Quotation to Sales Order')}</span>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>

            <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <ShoppingCart className="h-5 w-5 text-primary" />
                        {t('Convert Quotation to Sales Order')}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Customer Selection Section */}
                    <div className="space-y-2">
                        <Label className="font-semibold text-sm">{t('Customer Options')}</Label>
                        <div className="inline-flex p-1 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg w-full">
                            <button
                                type="button"
                                className={`flex-1 py-1.5 px-3 text-xs font-medium rounded-md transition-all flex items-center justify-center gap-1.5 ${formData.customer_check === 'exist'
                                    ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm font-semibold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                                    }`}
                                onClick={() => setFormData(prev => ({ ...prev, customer_check: 'exist' }))}
                            >
                                <User className="w-3.5 h-3.5" />
                                {t('Existing Customer')}
                            </button>
                            <button
                                type="button"
                                className={`flex-1 py-1.5 px-3 text-xs font-medium rounded-md transition-all flex items-center justify-center gap-1.5 ${formData.customer_check === 'new'
                                    ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm font-semibold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                                    }`}
                                onClick={() => setFormData(prev => ({ ...prev, customer_check: 'new' }))}
                            >
                                <UserPlus className="w-3.5 h-3.5" />
                                {t('New Customer')}
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {formData.customer_check === 'exist' ? (
                            <div className="col-span-2">
                                <Label htmlFor="customer_id">{t('Select Customer')}</Label>
                                <Select
                                    value={formData.customer_id}
                                    onValueChange={(val) => setFormData(prev => ({ ...prev, customer_id: val }))}
                                >
                                    <SelectTrigger id="customer_id">
                                        <SelectValue placeholder={t('Select Customer')} />
                                    </SelectTrigger>
                                    <SelectContent searchable>
                                        {availableCustomers?.map((client) => (
                                            <SelectItem key={client.id} value={String(client.id)}>
                                                {client.name} {client.email ? `(${client.email})` : ''}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.customer_id} />
                            </div>
                        ) : (
                            <>
                                <div>
                                    <Label htmlFor="customer_name">{t('Customer Name')}</Label>
                                    <Input
                                        id="customer_name"
                                        value={formData.customer_name}
                                        onChange={(e) => setFormData(prev => ({ ...prev, customer_name: e.target.value }))}
                                        placeholder={t('Enter Customer Name')}
                                        required
                                    />
                                    <InputError message={errors.customer_name} />
                                </div>
                                <div>
                                    <Label htmlFor="customer_email">{t('Customer Email')}</Label>
                                    <Input
                                        id="customer_email"
                                        type="email"
                                        value={formData.customer_email}
                                        onChange={(e) => setFormData(prev => ({ ...prev, customer_email: e.target.value }))}
                                        placeholder={t('Enter Customer Email')}
                                    />
                                    <InputError message={errors.customer_email} />
                                </div>
                                <div>
                                    <Label htmlFor="customer_phone">{t('Customer Phone')}</Label>
                                    <Input
                                        id="customer_phone"
                                        value={formData.customer_phone}
                                        onChange={(e) => setFormData(prev => ({ ...prev, customer_phone: e.target.value }))}
                                        placeholder={t('Enter Customer Phone')}
                                    />
                                    <InputError message={errors.customer_phone} />
                                </div>
                                <div>
                                    <Label htmlFor="customer_address">{t('Billing Address')}</Label>
                                    <Input
                                        id="customer_address"
                                        value={formData.customer_address}
                                        onChange={(e) => setFormData(prev => ({ ...prev, customer_address: e.target.value }))}
                                        placeholder={t('Enter Address')}
                                    />
                                    <InputError message={errors.customer_address} />
                                </div>
                                <div>
                                    <Label htmlFor="customer_city">{t('City')}</Label>
                                    <Input
                                        id="customer_city"
                                        value={formData.customer_city}
                                        onChange={(e) => setFormData(prev => ({ ...prev, customer_city: e.target.value }))}
                                        placeholder={t('Enter City')}
                                    />
                                    <InputError message={errors.customer_city} />
                                </div>
                                <div>
                                    <Label htmlFor="customer_state">{t('State')}</Label>
                                    <Input
                                        id="customer_state"
                                        value={formData.customer_state}
                                        onChange={(e) => setFormData(prev => ({ ...prev, customer_state: e.target.value }))}
                                        placeholder={t('Enter State')}
                                    />
                                    <InputError message={errors.customer_state} />
                                </div>
                                <div>
                                    <Label htmlFor="customer_country">{t('Country')}</Label>
                                    <Input
                                        id="customer_country"
                                        value={formData.customer_country}
                                        onChange={(e) => setFormData(prev => ({ ...prev, customer_country: e.target.value }))}
                                        placeholder={t('Enter Country')}
                                    />
                                    <InputError message={errors.customer_country} />
                                </div>
                                <div>
                                    <Label htmlFor="customer_zip_code">{t('Zip / Postal Code')}</Label>
                                    <Input
                                        id="customer_zip_code"
                                        value={formData.customer_zip_code}
                                        onChange={(e) => setFormData(prev => ({ ...prev, customer_zip_code: e.target.value }))}
                                        placeholder={t('Enter Zip Code')}
                                    />
                                    <InputError message={errors.customer_zip_code} />
                                </div>
                            </>
                        )}
                    </div>

                    {/* Assignment Options Section */}
                    <div className="space-y-2 pt-2 border-t">
                        <Label className="font-semibold text-sm">{t('Order Assignment')}</Label>
                        <div className="inline-flex p-1 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg w-full">
                            <button
                                type="button"
                                className={`flex-1 py-1.5 px-3 text-xs font-medium rounded-md transition-all flex items-center justify-center gap-1.5 ${formData.assignment_check === 'none'
                                    ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm font-semibold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                                    }`}
                                onClick={() => setFormData(prev => ({ ...prev, assignment_check: 'none', assigned_group_id: '', assigned_user_ids: [], assigned_user_id: '' }))}
                            >
                                <UserCheck className="w-3.5 h-3.5" />
                                {t('Unassigned')}
                            </button>
                            <button
                                type="button"
                                className={`flex-1 py-1.5 px-3 text-xs font-medium rounded-md transition-all flex items-center justify-center gap-1.5 ${formData.assignment_check === 'group'
                                    ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm font-semibold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                                    }`}
                                onClick={() => setFormData(prev => ({ ...prev, assignment_check: 'group', assigned_user_ids: [], assigned_user_id: '' }))}
                            >
                                <Users className="w-3.5 h-3.5" />
                                {t('User Group')}
                            </button>
                            <button
                                type="button"
                                className={`flex-1 py-1.5 px-3 text-xs font-medium rounded-md transition-all flex items-center justify-center gap-1.5 ${formData.assignment_check === 'user'
                                    ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-sm font-semibold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                                    }`}
                                onClick={() => setFormData(prev => ({ ...prev, assignment_check: 'user', assigned_group_id: '' }))}
                            >
                                <User className="w-3.5 h-3.5" />
                                {t('User')}
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        {formData.assignment_check === 'group' && (
                            <div className="col-span-2">
                                <Label htmlFor="assigned_group_id">{t('User Group')}</Label>
                                <Select
                                    value={formData.assigned_group_id}
                                    onValueChange={(val) => setFormData(prev => ({ ...prev, assigned_group_id: val }))}
                                >
                                    <SelectTrigger id="assigned_group_id">
                                        <SelectValue placeholder={t('Select User Group')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {userGroups?.map((group) => (
                                            <SelectItem key={group.id} value={String(group.id)}>
                                                {group.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        {formData.assignment_check === 'user' && (
                            <div className="col-span-2 space-y-2">
                                <Label>{t('Assigned User(s)')}</Label>
                                <MultiSelectEnhanced
                                    options={users?.map((u) => ({ value: String(u.id), label: u.name })) || []}
                                    value={formData.assigned_user_ids}
                                    onValueChange={(selectedValues) => {
                                        setFormData(prev => ({
                                            ...prev,
                                            assigned_user_ids: selectedValues,
                                            assigned_user_id: selectedValues[0] || '',
                                        }));
                                    }}
                                    placeholder={t('Select user(s)...')}
                                    searchable={true}
                                />
                            </div>
                        )}
                    </div>

                    <div className="flex justify-end gap-2 pt-4 border-t">
                        <Button type="button" variant="outline" onClick={() => setIsOpen(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? t('Converting...') : t('Convert')}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
