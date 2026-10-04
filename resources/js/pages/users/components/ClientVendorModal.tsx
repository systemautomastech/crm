import React from 'react';
import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import InputError from "@/components/ui/input-error";
import { PhoneInputComponent } from "@/components/ui/phone-input";
import { Building2, Store } from 'lucide-react';

interface Address {
    name: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    country: string;
    zip_code: string;
}

interface ClientVendorModalProps {
    type: 'client' | 'vendor';
    mode: 'create' | 'edit';
    data?: any;
    roles?: Record<string, string>;
    onSuccess: () => void;
}

export default function ClientVendorModal({
    type,
    mode,
    data: initialData,
    roles = {},
    onSuccess
}: ClientVendorModalProps) {
    const { t } = useTranslation();
    const isClient = type === 'client';
    const isEdit = mode === 'edit';
    const roleKeys = Object.keys(roles || {});
    const defaultRole = roleKeys.length === 1 ? roleKeys[0] : '';

    const initialRoleId = initialData?.roles && initialData.roles.length > 0
        ? String(initialData.roles[0].id)
        : (Object.entries(roles).find(([id, label]) => label.toLowerCase() === initialData?.type?.toLowerCase() || id === initialData?.type)?.[0] || defaultRole);

    // Initial billing address resolution
    const initialBilling: Address = {
        name: initialData?.billing_address?.name || initialData?.name || initialData?.company_name || '',
        address_line_1: initialData?.billing_address?.address_line_1 || initialData?.billing_address?.address || initialData?.address || '',
        address_line_2: initialData?.billing_address?.address_line_2 || '',
        city: initialData?.billing_address?.city || initialData?.city || '',
        state: initialData?.billing_address?.state || initialData?.state || '',
        country: initialData?.billing_address?.country || initialData?.country || '',
        zip_code: initialData?.billing_address?.zip_code || initialData?.zip_code || '',
    };

    // Initial shipping address resolution
    const initialShipping: Address = {
        name: initialData?.shipping_address?.name || initialData?.name || initialData?.company_name || '',
        address_line_1: initialData?.shipping_address?.address_line_1 || initialData?.shipping_address?.address || initialData?.address || '',
        address_line_2: initialData?.shipping_address?.address_line_2 || '',
        city: initialData?.shipping_address?.city || initialData?.city || '',
        state: initialData?.shipping_address?.state || initialData?.state || '',
        country: initialData?.shipping_address?.country || initialData?.country || '',
        zip_code: initialData?.shipping_address?.zip_code || initialData?.zip_code || '',
    };

    const { data, setData, post, put, processing, errors } = useForm({
        name: initialData?.name || initialData?.company_name || '',
        email: initialData?.email || initialData?.contact_person_email || '',
        mobile_no: initialData?.mobile_no || initialData?.contact_person_mobile || '',
        password: '',
        password_confirmation: '',
        type: initialRoleId || defaultRole,
        is_enable_login: initialData ? Boolean(initialData.is_enable_login) : true,
        // Detailed Address & Business Fields
        tax_number: initialData?.tax_number || '',
        payment_terms: initialData?.payment_terms || '',
        billing_address: initialBilling,
        same_as_billing: initialData ? Boolean(initialData.same_as_billing) : true,
        shipping_address: initialShipping,
        notes: initialData?.notes || '',
    });

    const errs = errors as Record<string, string>;

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (isEdit && initialData?.id) {
            put(route('users.update', initialData.id), {
                onSuccess: () => {
                    onSuccess();
                }
            });
        } else {
            post(route('users.store'), {
                onSuccess: () => {
                    onSuccess();
                }
            });
        }
    };

    const title = isEdit
        ? (isClient ? t('Edit Client') : t('Edit Vendor'))
        : (isClient ? t('Create Client') : t('Create Vendor'));

    return (
        <DialogContent className="max-w-2xl">
            <DialogHeader>
                <DialogTitle className="flex items-center gap-2 text-lg">
                    {isClient ? <Building2 className="h-5 w-5 text-primary" /> : <Store className="h-5 w-5 text-primary" />}
                    <span>{title}</span>
                </DialogTitle>
            </DialogHeader>

            <form onSubmit={submit} className="space-y-4 pt-2">
                {/* Basic Information */}
                <div className="space-y-4">
                    <div>
                        <Label htmlFor="client_vendor_name" required>
                            {isClient ? t('Client Name') : t('Vendor / Company Name')}
                        </Label>
                        <Input
                            id="client_vendor_name"
                            value={data.name}
                            onChange={(e) => {
                                const val = e.target.value;
                                setData(prev => ({
                                    ...prev,
                                    name: val,
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
                            placeholder={isClient ? t('Enter client or company name') : t('Enter vendor or company name')}
                            required
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="client_vendor_email" required>{t('Email Address')}</Label>
                            <Input
                                id="client_vendor_email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder={t('Enter email address')}
                                required
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div>
                            <PhoneInputComponent
                                label={t('Phone Number')}
                                value={data.mobile_no}
                                onChange={(value) => setData('mobile_no', value)}
                                placeholder="+1234567890"
                                error={errors.mobile_no}
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="tax_number">{t('Tax Number')}</Label>
                            <Input
                                id="tax_number"
                                value={data.tax_number}
                                onChange={(e) => setData('tax_number', e.target.value)}
                                placeholder={t('Enter tax number')}
                            />
                            <InputError message={errors.tax_number} />
                        </div>

                        <div>
                            <Label htmlFor="payment_terms">{t('Payment Terms')}</Label>
                            <Input
                                id="payment_terms"
                                value={data.payment_terms}
                                onChange={(e) => setData('payment_terms', e.target.value)}
                                placeholder={t('e.g., Net 30, Due on Receipt')}
                            />
                            <InputError message={errors.payment_terms} />
                        </div>
                    </div>
                </div>

                {/* Password fields only on Create mode */}
                {!isEdit && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t">
                        <div>
                            <Label htmlFor="client_vendor_password" required>{t('Password')}</Label>
                            <Input
                                id="client_vendor_password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder={t('Enter password')}
                                required
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div>
                            <Label htmlFor="client_vendor_password_confirmation" required>{t('Confirm Password')}</Label>
                            <Input
                                id="client_vendor_password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder={t('Confirm password')}
                                required
                            />
                            <InputError message={errors.password_confirmation} />
                        </div>
                    </div>
                )}

                {/* Role & Status */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t">
                    <div>
                        <Label htmlFor="client_vendor_type">{t('Role')}</Label>
                        {roleKeys.length === 1 ? (
                            <Input
                                id="client_vendor_type"
                                value={roles[roleKeys[0]]}
                                disabled
                                className="border-input bg-muted/30 text-foreground opacity-100 disabled:opacity-100 cursor-not-allowed"
                            />
                        ) : (
                            <Select value={data.type} onValueChange={(val) => setData('type', val)}>
                                <SelectTrigger id="client_vendor_type">
                                    <SelectValue placeholder={t('Select Role')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(roles).map(([id, label]) => (
                                        <SelectItem key={id} value={String(id)}>
                                            {label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        )}
                        <InputError message={errors.type} />
                    </div>

                    <div>
                        <Label htmlFor="client_vendor_is_enable_login">{t('Login Status')}</Label>
                        <Select
                            value={data.is_enable_login ? "1" : "0"}
                            onValueChange={(value) => setData('is_enable_login', value === "1")}
                        >
                            <SelectTrigger id="client_vendor_is_enable_login">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="1">{t('Enabled')}</SelectItem>
                                <SelectItem value="0">{t('Disabled')}</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError message={errors.is_enable_login} />
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
                            value={data.billing_address.name}
                            onChange={(e) => {
                                const name = e.target.value;
                                setData(prev => ({
                                    ...prev,
                                    billing_address: { ...prev.billing_address, name },
                                    shipping_address: prev.same_as_billing ? { ...prev.shipping_address, name } : prev.shipping_address
                                }));
                            }}
                            placeholder={t('Enter billing name')}
                        />
                        <InputError message={errs['billing_address.name']} />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="billing_address_1">{t('Address Line 1')}</Label>
                            <Input
                                id="billing_address_1"
                                value={data.billing_address.address_line_1}
                                onChange={(e) => {
                                    const address_line_1 = e.target.value;
                                    setData(prev => ({
                                        ...prev,
                                        billing_address: { ...prev.billing_address, address_line_1 },
                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, address_line_1 } : prev.shipping_address
                                    }));
                                }}
                                placeholder={t('Enter street address')}
                            />
                            <InputError message={errs['billing_address.address_line_1']} />
                        </div>

                        <div>
                            <Label htmlFor="billing_address_2">{t('Address Line 2')}</Label>
                            <Input
                                id="billing_address_2"
                                value={data.billing_address.address_line_2}
                                onChange={(e) => {
                                    const address_line_2 = e.target.value;
                                    setData(prev => ({
                                        ...prev,
                                        billing_address: { ...prev.billing_address, address_line_2 },
                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, address_line_2 } : prev.shipping_address
                                    }));
                                }}
                                placeholder={t('Apartment, suite, unit, etc. (optional)')}
                            />
                            <InputError message={errs['billing_address.address_line_2']} />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="billing_city" required>{t('City')}</Label>
                            <Input
                                id="billing_city"
                                value={data.billing_address.city}
                                onChange={(e) => {
                                    const city = e.target.value;
                                    setData(prev => ({
                                        ...prev,
                                        billing_address: { ...prev.billing_address, city },
                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, city } : prev.shipping_address
                                    }));
                                }}
                                placeholder={t('Enter city')}
                                required
                            />
                            <InputError message={errs['billing_address.city']} />
                        </div>

                        <div>
                            <Label htmlFor="billing_state" required>{t('State / Province')}</Label>
                            <Input
                                id="billing_state"
                                value={data.billing_address.state}
                                onChange={(e) => {
                                    const state = e.target.value;
                                    setData(prev => ({
                                        ...prev,
                                        billing_address: { ...prev.billing_address, state },
                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, state } : prev.shipping_address
                                    }));
                                }}
                                placeholder={t('Enter state / province')}
                                required
                            />
                            <InputError message={errs['billing_address.state']} />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="billing_country" required>{t('Country')}</Label>
                            <Input
                                id="billing_country"
                                value={data.billing_address.country}
                                onChange={(e) => {
                                    const country = e.target.value;
                                    setData(prev => ({
                                        ...prev,
                                        billing_address: { ...prev.billing_address, country },
                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, country } : prev.shipping_address
                                    }));
                                }}
                                placeholder={t('Enter country')}
                                required
                            />
                            <InputError message={errs['billing_address.country']} />
                        </div>

                        <div>
                            <Label htmlFor="billing_zip" required>{t('Zip / Postal Code')}</Label>
                            <Input
                                id="billing_zip"
                                value={data.billing_address.zip_code}
                                onChange={(e) => {
                                    const zip_code = e.target.value;
                                    setData(prev => ({
                                        ...prev,
                                        billing_address: { ...prev.billing_address, zip_code },
                                        shipping_address: prev.same_as_billing ? { ...prev.shipping_address, zip_code } : prev.shipping_address
                                    }));
                                }}
                                placeholder={t('Enter zip / postal code')}
                                required
                            />
                            <InputError message={errs['billing_address.zip_code']} />
                        </div>
                    </div>
                </div>

                {/* Same as Billing Checkbox */}
                <div className="flex items-center space-x-2 pt-2">
                    <Checkbox
                        id="same_as_billing"
                        checked={data.same_as_billing}
                        onCheckedChange={(checked) => {
                            const isChecked = !!checked;
                            setData(prev => ({
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
                {!data.same_as_billing && (
                    <div className="space-y-4 border-t pt-4">
                        <h3 className="text-base font-semibold text-foreground flex items-center gap-2">
                            <span>{t('Shipping Address')}</span>
                        </h3>

                        <div>
                            <Label htmlFor="shipping_name">{t('Shipping Name')}</Label>
                            <Input
                                id="shipping_name"
                                value={data.shipping_address.name}
                                onChange={(e) => setData('shipping_address', { ...data.shipping_address, name: e.target.value })}
                                placeholder={t('Enter shipping recipient name')}
                            />
                            <InputError message={errs['shipping_address.name']} />
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="shipping_address_1">{t('Address Line 1')}</Label>
                                <Input
                                    id="shipping_address_1"
                                    value={data.shipping_address.address_line_1}
                                    onChange={(e) => setData('shipping_address', { ...data.shipping_address, address_line_1: e.target.value })}
                                    placeholder={t('Enter street address')}
                                />
                                <InputError message={errs['shipping_address.address_line_1']} />
                            </div>

                            <div>
                                <Label htmlFor="shipping_address_2">{t('Address Line 2')}</Label>
                                <Input
                                    id="shipping_address_2"
                                    value={data.shipping_address.address_line_2}
                                    onChange={(e) => setData('shipping_address', { ...data.shipping_address, address_line_2: e.target.value })}
                                    placeholder={t('Apartment, suite, unit, etc. (optional)')}
                                />
                                <InputError message={errs['shipping_address.address_line_2']} />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="shipping_city" required>{t('City')}</Label>
                                <Input
                                    id="shipping_city"
                                    value={data.shipping_address.city}
                                    onChange={(e) => setData('shipping_address', { ...data.shipping_address, city: e.target.value })}
                                    placeholder={t('Enter city')}
                                    required={!data.same_as_billing}
                                />
                                <InputError message={errs['shipping_address.city']} />
                            </div>

                            <div>
                                <Label htmlFor="shipping_state" required>{t('State / Province')}</Label>
                                <Input
                                    id="shipping_state"
                                    value={data.shipping_address.state}
                                    onChange={(e) => setData('shipping_address', { ...data.shipping_address, state: e.target.value })}
                                    placeholder={t('Enter state / province')}
                                    required={!data.same_as_billing}
                                />
                                <InputError message={errs['shipping_address.state']} />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="shipping_country" required>{t('Country')}</Label>
                                <Input
                                    id="shipping_country"
                                    value={data.shipping_address.country}
                                    onChange={(e) => setData('shipping_address', { ...data.shipping_address, country: e.target.value })}
                                    placeholder={t('Enter country')}
                                    required={!data.same_as_billing}
                                />
                                <InputError message={errs['shipping_address.country']} />
                            </div>

                            <div>
                                <Label htmlFor="shipping_zip" required>{t('Zip / Postal Code')}</Label>
                                <Input
                                    id="shipping_zip"
                                    value={data.shipping_address.zip_code}
                                    onChange={(e) => setData('shipping_address', { ...data.shipping_address, zip_code: e.target.value })}
                                    placeholder={t('Enter zip / postal code')}
                                    required={!data.same_as_billing}
                                />
                                <InputError message={errs['shipping_address.zip_code']} />
                            </div>
                        </div>
                    </div>
                )}

                {/* Notes */}
                <div className="border-t pt-4">
                    <Label htmlFor="notes">{t('Notes')}</Label>
                    <Textarea
                        id="notes"
                        value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)}
                        placeholder={t('Enter any notes or special instructions...')}
                        rows={3}
                    />
                    <InputError message={errors.notes} />
                </div>

                <div className="flex justify-end gap-2 pt-4 border-t">
                    <Button type="button" variant="outline" onClick={onSuccess}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? (isEdit ? t('Updating...') : t('Creating...')) : (isEdit ? t('Update') : t('Create'))}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}
