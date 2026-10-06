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
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { InputError } from '@/components/ui/input-error';
import { PhoneInputComponent } from '@/components/ui/phone-input';
import { MultiSelectEnhanced } from '@/components/ui/multi-select-enhanced';

interface Address {
    name: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    country: string;
    zip_code: string;
}

interface SalesProposal {
    id: number;
    proposal_number: string;
    subject?: string | null;
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

interface SalesOrderModalProps {
    proposal: SalesProposal;
    customers?: Array<{ id: number; name: string; email?: string }>;
    users?: Array<{ id: number; name: string }>;
    userGroups?: Array<{ id: number; name: string }>;
    buttonClassName?: string;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export default function SalesOrderModal({
    proposal,
    customers = [],
    users = [],
    userGroups = [],
    buttonClassName,
    trigger,
    open: externalOpen,
    onOpenChange: externalOnOpenChange
}: SalesOrderModalProps) {
    const { t } = useTranslation();
    const [internalOpen, setInternalOpen] = useState(false);
    const isOpen = externalOpen !== undefined ? externalOpen : internalOpen;
    const setIsOpen = externalOnOpenChange || setInternalOpen;

    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);

    const existingCustomerId = proposal?.customer_id || proposal?.customer?.id;

    const availableCustomers = useMemo(() => {
        const list = [...(customers || [])];
        if (existingCustomerId) {
            const found = list.some(c => String(c.id) === String(existingCustomerId));
            if (!found) {
                list.unshift({
                    id: existingCustomerId,
                    name: proposal.customer?.name || proposal.customer_name || `${t('Customer')} #${existingCustomerId}`,
                    email: proposal.customer?.email || proposal.customer_email || ''
                });
            }
        }
        return list;
    }, [customers, proposal, existingCustomerId, t]);

    const initialCustName = proposal?.customer?.name || proposal?.customer_name || '';
    const initialCustAddress = proposal?.customer?.address || proposal?.customer_address || '';

    const [formData, setFormData] = useState({
        order_name: proposal?.proposal_number ? `${t('Order for')} ${proposal.proposal_number}` : '',
        customer_check: existingCustomerId ? 'exist' : (proposal?.customer_name ? 'new' : 'exist'),
        customer_id: existingCustomerId ? String(existingCustomerId) : '',
        customer_name: initialCustName,
        customer_email: proposal?.customer?.email || proposal?.customer_email || '',
        customer_phone: proposal?.customer?.phone || proposal?.customer_phone || '',
        tax_number: '',
        payment_terms: '',
        billing_address: {
            name: initialCustName,
            address_line_1: initialCustAddress,
            address_line_2: '',
            city: '',
            state: '',
            country: '',
            zip_code: '',
        } as Address,
        same_as_billing: true,
        shipping_address: {
            name: initialCustName,
            address_line_1: initialCustAddress,
            address_line_2: '',
            city: '',
            state: '',
            country: '',
            zip_code: '',
        } as Address,
        notes: '',
        assignment_check: 'none',
        assigned_user_id: '',
        assigned_user_ids: [] as string[],
        assigned_group_id: '',
    });

    useEffect(() => {
        if (isOpen && proposal) {
            const extId = proposal.customer_id || proposal.customer?.id;
            const custName = proposal.customer?.name || proposal.customer_name || '';
            const custAddr = proposal.customer?.address || proposal.customer_address || '';

            setFormData({
                order_name: proposal.proposal_number ? `${t('Order for')} ${proposal.proposal_number}` : '',
                customer_check: extId ? 'exist' : (proposal.customer_name ? 'new' : 'exist'),
                customer_id: extId ? String(extId) : '',
                customer_name: custName,
                customer_email: proposal.customer?.email || proposal.customer_email || '',
                customer_phone: proposal.customer?.phone || proposal.customer_phone || '',
                tax_number: '',
                payment_terms: '',
                billing_address: {
                    name: custName,
                    address_line_1: custAddr,
                    address_line_2: '',
                    city: '',
                    state: '',
                    country: '',
                    zip_code: '',
                },
                same_as_billing: true,
                shipping_address: {
                    name: custName,
                    address_line_1: custAddr,
                    address_line_2: '',
                    city: '',
                    state: '',
                    country: '',
                    zip_code: '',
                },
                notes: '',
                assignment_check: 'none',
                assigned_user_id: '',
                assigned_user_ids: [],
                assigned_group_id: '',
            });
            setErrors({});
        }
    }, [isOpen, proposal, t]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);

        const payload: Record<string, any> = {
            order_name: formData.order_name,
            customer_type: formData.customer_check === 'exist' ? 'existing' : (formData.customer_check === 'new' ? 'new' : 'existing'),
        };

        if (formData.customer_check === 'exist') {
            payload.customer_id = formData.customer_id;
        } else if (formData.customer_check === 'new') {
            payload.customer_name = formData.customer_name;
            payload.customer_email = formData.customer_email;
            payload.customer_phone = formData.customer_phone;
            payload.tax_number = formData.tax_number;
            payload.payment_terms = formData.payment_terms;

            // Billing fields
            payload.billing_name = formData.billing_address.name;
            payload.billing_address_line_1 = formData.billing_address.address_line_1;
            payload.billing_address_line_2 = formData.billing_address.address_line_2;
            payload.billing_city = formData.billing_address.city;
            payload.billing_state = formData.billing_address.state;
            payload.billing_country = formData.billing_address.country;
            payload.billing_zip_code = formData.billing_address.zip_code;

            // Shipping fields
            payload.same_as_billing = formData.same_as_billing;
            if (formData.same_as_billing) {
                payload.shipping_name = formData.billing_address.name;
                payload.shipping_address_line_1 = formData.billing_address.address_line_1;
                payload.shipping_address_line_2 = formData.billing_address.address_line_2;
                payload.shipping_city = formData.billing_address.city;
                payload.shipping_state = formData.billing_address.state;
                payload.shipping_country = formData.billing_address.country;
                payload.shipping_zip_code = formData.billing_address.zip_code;
            } else {
                payload.shipping_name = formData.shipping_address.name;
                payload.shipping_address_line_1 = formData.shipping_address.address_line_1;
                payload.shipping_address_line_2 = formData.shipping_address.address_line_2;
                payload.shipping_city = formData.shipping_address.city;
                payload.shipping_state = formData.shipping_address.state;
                payload.shipping_country = formData.shipping_address.country;
                payload.shipping_zip_code = formData.shipping_address.zip_code;
            }

            payload.notes = formData.notes;
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

        router.post(route('sales-proposals.convert-to-sales-order', proposal.id), payload, {
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

    if (proposal?.sales_order_id && !externalOpen) {
        return (
            <TooltipProvider>
                <Tooltip delayDuration={0}>
                    <TooltipTrigger asChild>
                        <Link href={route('salesorder.orders.show', proposal.sales_order_id)}>
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
            {trigger && (
                <DialogTrigger asChild>
                    {trigger}
                </DialogTrigger>
            )}

            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <ShoppingCart className="h-5 w-5 text-primary" />
                        {t('Convert Proposal to Sales Order')}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Order Name */}
                    <div>
                        <Label htmlFor="order_name">{t('Sales Order Name')}</Label>
                        <Input
                            id="order_name"
                            value={formData.order_name}
                            onChange={(e) => setFormData(prev => ({ ...prev, order_name: e.target.value }))}
                            placeholder={t('Enter order name')}
                        />
                        <InputError message={errors.order_name} />
                    </div>

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

                    <div className="space-y-4">
                        {formData.customer_check === 'exist' ? (
                            <div>
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
                            <div className="space-y-4">
                                <div>
                                    <Label htmlFor="customer_name" required>{t('Customer / Company Name')}</Label>
                                    <Input
                                        id="customer_name"
                                        value={formData.customer_name}
                                        onChange={(e) => {
                                            const val = e.target.value;
                                            setFormData(prev => ({
                                                ...prev,
                                                customer_name: val,
                                                billing_address: {
                                                    ...prev.billing_address,
                                                    name: prev.billing_address.name || val
                                                },
                                                shipping_address: {
                                                    ...prev.shipping_address,
                                                    name: prev.same_as_billing ? (prev.billing_address.name || val) : prev.shipping_address.name
                                                }
                                            }));
                                        }}
                                        placeholder={t('Enter Name')}
                                        required
                                    />
                                    <InputError message={errors.customer_name} />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor="customer_email" required>{t('Email Address')}</Label>
                                        <Input
                                            id="customer_email"
                                            type="email"
                                            value={formData.customer_email}
                                            onChange={(e) => setFormData(prev => ({ ...prev, customer_email: e.target.value }))}
                                            placeholder={t('Enter Email')}
                                            required
                                        />
                                        <InputError message={errors.customer_email} />
                                    </div>

                                    <div>
                                        <PhoneInputComponent
                                            label={t('Phone Number')}
                                            value={formData.customer_phone}
                                            onChange={(value) => setFormData(prev => ({ ...prev, customer_phone: value }))}
                                            placeholder="+1234567890"
                                            error={errors.customer_phone}
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor="customer_tax_number">{t('Tax Number')}</Label>
                                        <Input
                                            id="customer_tax_number"
                                            value={formData.tax_number}
                                            onChange={(e) => setFormData(prev => ({ ...prev, tax_number: e.target.value }))}
                                            placeholder={t('Enter tax number')}
                                        />
                                        <InputError message={errors.tax_number} />
                                    </div>

                                    <div>
                                        <Label htmlFor="customer_payment_terms">{t('Payment Terms')}</Label>
                                        <Input
                                            id="customer_payment_terms"
                                            value={formData.payment_terms}
                                            onChange={(e) => setFormData(prev => ({ ...prev, payment_terms: e.target.value }))}
                                            placeholder={t('e.g., Net 30, Due on Receipt')}
                                        />
                                        <InputError message={errors.payment_terms} />
                                    </div>
                                </div>

                                {/* Billing Address Section */}
                                <div className="space-y-4 border-t pt-4">
                                    <h3 className="text-base font-semibold text-foreground flex items-center gap-2">
                                        <span>{t('Billing Address')}</span>
                                    </h3>

                                    <div>
                                        <Label htmlFor="billing_name">{t('Billing Name')}</Label>
                                        <Input
                                            id="billing_name"
                                            value={formData.billing_address.name}
                                            onChange={(e) => {
                                                const name = e.target.value;
                                                setFormData(prev => ({
                                                    ...prev,
                                                    billing_address: { ...prev.billing_address, name },
                                                    shipping_address: prev.same_as_billing ? { ...prev.shipping_address, name } : prev.shipping_address
                                                }));
                                            }}
                                            placeholder={t('Enter billing name')}
                                        />
                                        <InputError message={errors['billing_name'] || errors['billing_address.name']} />
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="billing_address_1">{t('Address Line 1')}</Label>
                                            <Input
                                                id="billing_address_1"
                                                value={formData.billing_address.address_line_1}
                                                onChange={(e) => {
                                                    const address_line_1 = e.target.value;
                                                    setFormData(prev => ({
                                                        ...prev,
                                                        billing_address: { ...prev.billing_address, address_line_1 },
                                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, address_line_1 } : prev.shipping_address
                                                    }));
                                                }}
                                                placeholder={t('Enter street address')}
                                            />
                                            <InputError message={errors['billing_address_line_1'] || errors['billing_address.address_line_1']} />
                                        </div>

                                        <div>
                                            <Label htmlFor="billing_address_2">{t('Address Line 2')}</Label>
                                            <Input
                                                id="billing_address_2"
                                                value={formData.billing_address.address_line_2}
                                                onChange={(e) => {
                                                    const address_line_2 = e.target.value;
                                                    setFormData(prev => ({
                                                        ...prev,
                                                        billing_address: { ...prev.billing_address, address_line_2 },
                                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, address_line_2 } : prev.shipping_address
                                                    }));
                                                }}
                                                placeholder={t('Apartment, suite, unit, etc. (optional)')}
                                            />
                                            <InputError message={errors['billing_address_line_2'] || errors['billing_address.address_line_2']} />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="billing_city" required>{t('City')}</Label>
                                            <Input
                                                id="billing_city"
                                                value={formData.billing_address.city}
                                                onChange={(e) => {
                                                    const city = e.target.value;
                                                    setFormData(prev => ({
                                                        ...prev,
                                                        billing_address: { ...prev.billing_address, city },
                                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, city } : prev.shipping_address
                                                    }));
                                                }}
                                                placeholder={t('Enter city')}
                                                required={formData.customer_check === 'new'}
                                            />
                                            <InputError message={errors['billing_city'] || errors['billing_address.city']} />
                                        </div>

                                        <div>
                                            <Label htmlFor="billing_state" required>{t('State / Province')}</Label>
                                            <Input
                                                id="billing_state"
                                                value={formData.billing_address.state}
                                                onChange={(e) => {
                                                    const state = e.target.value;
                                                    setFormData(prev => ({
                                                        ...prev,
                                                        billing_address: { ...prev.billing_address, state },
                                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, state } : prev.shipping_address
                                                    }));
                                                }}
                                                placeholder={t('Enter state / province')}
                                                required={formData.customer_check === 'new'}
                                            />
                                            <InputError message={errors['billing_state'] || errors['billing_address.state']} />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="billing_country" required>{t('Country')}</Label>
                                            <Input
                                                id="billing_country"
                                                value={formData.billing_address.country}
                                                onChange={(e) => {
                                                    const country = e.target.value;
                                                    setFormData(prev => ({
                                                        ...prev,
                                                        billing_address: { ...prev.billing_address, country },
                                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, country } : prev.shipping_address
                                                    }));
                                                }}
                                                placeholder={t('Enter country')}
                                                required={formData.customer_check === 'new'}
                                            />
                                            <InputError message={errors['billing_country'] || errors['billing_address.country']} />
                                        </div>

                                        <div>
                                            <Label htmlFor="billing_zip" required>{t('Zip / Postal Code')}</Label>
                                            <Input
                                                id="billing_zip"
                                                value={formData.billing_address.zip_code}
                                                onChange={(e) => {
                                                    const zip_code = e.target.value;
                                                    setFormData(prev => ({
                                                        ...prev,
                                                        billing_address: { ...prev.billing_address, zip_code },
                                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, zip_code } : prev.shipping_address
                                                    }));
                                                }}
                                                placeholder={t('Enter zip / postal code')}
                                                required={formData.customer_check === 'new'}
                                            />
                                            <InputError message={errors['billing_zip_code'] || errors['billing_address.zip_code']} />
                                        </div>
                                    </div>
                                </div>

                                {/* Same as Billing Checkbox */}
                                <div className="flex items-center space-x-2 pt-2">
                                    <Checkbox
                                        id="same_as_billing"
                                        checked={formData.same_as_billing}
                                        onCheckedChange={(checked) => {
                                            const isChecked = !!checked;
                                            setFormData(prev => ({
                                                ...prev,
                                                same_as_billing: isChecked,
                                                shipping_address: isChecked ? { ...prev.billing_address } : prev.shipping_address
                                            }));
                                        }}
                                    />
                                    <Label htmlFor="same_as_billing" className="cursor-pointer font-normal">
                                        {t('Shipping address same as billing')}
                                    </Label>
                                </div>

                                {/* Shipping Address Section (if not same as billing) */}
                                {!formData.same_as_billing && (
                                    <div className="space-y-4 border-t pt-4">
                                        <h3 className="text-base font-semibold text-foreground flex items-center gap-2">
                                            <span>{t('Shipping Address')}</span>
                                        </h3>

                                        <div>
                                            <Label htmlFor="shipping_name">{t('Shipping Name')}</Label>
                                            <Input
                                                id="shipping_name"
                                                value={formData.shipping_address.name}
                                                onChange={(e) => setFormData(prev => ({ ...prev, shipping_address: { ...prev.shipping_address, name: e.target.value } }))}
                                                placeholder={t('Enter shipping recipient name')}
                                            />
                                            <InputError message={errors['shipping_name'] || errors['shipping_address.name']} />
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <Label htmlFor="shipping_address_1">{t('Address Line 1')}</Label>
                                                <Input
                                                    id="shipping_address_1"
                                                    value={formData.shipping_address.address_line_1}
                                                    onChange={(e) => setFormData(prev => ({ ...prev, shipping_address: { ...prev.shipping_address, address_line_1: e.target.value } }))}
                                                    placeholder={t('Enter street address')}
                                                />
                                                <InputError message={errors['shipping_address_line_1'] || errors['shipping_address.address_line_1']} />
                                            </div>

                                            <div>
                                                <Label htmlFor="shipping_address_2">{t('Address Line 2')}</Label>
                                                <Input
                                                    id="shipping_address_2"
                                                    value={formData.shipping_address.address_line_2}
                                                    onChange={(e) => setFormData(prev => ({ ...prev, shipping_address: { ...prev.shipping_address, address_line_2: e.target.value } }))}
                                                    placeholder={t('Apartment, suite, unit, etc. (optional)')}
                                                />
                                                <InputError message={errors['shipping_address_line_2'] || errors['shipping_address.address_line_2']} />
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <Label htmlFor="shipping_city" required>{t('City')}</Label>
                                                <Input
                                                    id="shipping_city"
                                                    value={formData.shipping_address.city}
                                                    onChange={(e) => setFormData(prev => ({ ...prev, shipping_address: { ...prev.shipping_address, city: e.target.value } }))}
                                                    placeholder={t('Enter city')}
                                                    required={!formData.same_as_billing}
                                                />
                                                <InputError message={errors['shipping_city'] || errors['shipping_address.city']} />
                                            </div>

                                            <div>
                                                <Label htmlFor="shipping_state" required>{t('State / Province')}</Label>
                                                <Input
                                                    id="shipping_state"
                                                    value={formData.shipping_address.state}
                                                    onChange={(e) => setFormData(prev => ({ ...prev, shipping_address: { ...prev.shipping_address, state: e.target.value } }))}
                                                    placeholder={t('Enter state / province')}
                                                    required={!formData.same_as_billing}
                                                />
                                                <InputError message={errors['shipping_state'] || errors['shipping_address.state']} />
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <Label htmlFor="shipping_country" required>{t('Country')}</Label>
                                                <Input
                                                    id="shipping_country"
                                                    value={formData.shipping_address.country}
                                                    onChange={(e) => setFormData(prev => ({ ...prev, shipping_address: { ...prev.shipping_address, country: e.target.value } }))}
                                                    placeholder={t('Enter country')}
                                                    required={!formData.same_as_billing}
                                                />
                                                <InputError message={errors['shipping_country'] || errors['shipping_address.country']} />
                                            </div>

                                            <div>
                                                <Label htmlFor="shipping_zip" required>{t('Zip / Postal Code')}</Label>
                                                <Input
                                                    id="shipping_zip"
                                                    value={formData.shipping_address.zip_code}
                                                    onChange={(e) => setFormData(prev => ({ ...prev, shipping_address: { ...prev.shipping_address, zip_code: e.target.value } }))}
                                                    placeholder={t('Enter zip / postal code')}
                                                    required={!formData.same_as_billing}
                                                />
                                                <InputError message={errors['shipping_zip_code'] || errors['shipping_address.zip_code']} />
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {/* Notes */}
                                <div className="border-t pt-4">
                                    <Label htmlFor="customer_notes">{t('Notes')}</Label>
                                    <Textarea
                                        id="customer_notes"
                                        value={formData.notes}
                                        onChange={(e) => setFormData(prev => ({ ...prev, notes: e.target.value }))}
                                        placeholder={t('Enter any notes or special instructions...')}
                                        rows={3}
                                    />
                                    <InputError message={errors.notes} />
                                </div>
                            </div>
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
