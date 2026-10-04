import React from 'react';
import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { Building2, Store, User, Mail, Phone, MapPin, FileText, CreditCard } from 'lucide-react';
import { Badge } from "@/components/ui/badge";

interface ViewClientVendorModalProps {
    type: 'client' | 'vendor';
    data: any;
}

export default function ViewClientVendorModal({
    type,
    data
}: ViewClientVendorModalProps) {
    const { t } = useTranslation();
    const isClient = type === 'client';

    if (!data) return null;

    const billing = data.billing_address || {};
    const shipping = data.shipping_address || {};
    const hasBilling = billing.address_line_1 || billing.city || billing.country || billing.address;
    const hasShipping = shipping.address_line_1 || shipping.city || shipping.country || shipping.address;

    return (
        <DialogContent className="max-w-2xl">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2.5 bg-primary/10 rounded-xl text-primary">
                        {isClient ? <Building2 className="h-6 w-6" /> : <Store className="h-6 w-6" />}
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">
                            {isClient ? t('Client Details') : t('Vendor Details')}
                        </DialogTitle>
                        <p className="text-sm text-muted-foreground">
                            {data.company_name || data.name}
                        </p>
                    </div>
                </div>
            </DialogHeader>

            <div className="space-y-6 pt-2">
                {/* General Info Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {data.customer_code && (
                        <div className="space-y-1">
                            <span className="text-xs font-medium text-muted-foreground">{t('Customer Code')}</span>
                            <p className="text-sm font-semibold text-primary bg-muted/30 p-2 rounded-md">{data.customer_code}</p>
                        </div>
                    )}
                    {data.vendor_code && (
                        <div className="space-y-1">
                            <span className="text-xs font-medium text-muted-foreground">{t('Vendor Code')}</span>
                            <p className="text-sm font-semibold text-primary bg-muted/30 p-2 rounded-md">{data.vendor_code}</p>
                        </div>
                    )}

                    <div className="space-y-1">
                        <span className="text-xs font-medium text-muted-foreground">{t('Company / Name')}</span>
                        <p className="text-sm font-medium text-foreground bg-muted/30 p-2 rounded-md">
                            {data.company_name || data.name}
                        </p>
                    </div>

                    <div className="space-y-1">
                        <span className="text-xs font-medium text-muted-foreground">{t('Contact Person')}</span>
                        <p className="text-sm text-foreground bg-muted/30 p-2 rounded-md">
                            {data.contact_person_name || data.name || '-'}
                        </p>
                    </div>

                    <div className="space-y-1">
                        <span className="text-xs font-medium text-muted-foreground">{t('Email Address')}</span>
                        <p className="text-sm text-foreground bg-muted/30 p-2 rounded-md">
                            {data.contact_person_email || data.email || '-'}
                        </p>
                    </div>

                    <div className="space-y-1">
                        <span className="text-xs font-medium text-muted-foreground">{t('Phone / Mobile')}</span>
                        <p className="text-sm text-foreground bg-muted/30 p-2 rounded-md">
                            {data.contact_person_mobile || data.mobile_no || '-'}
                        </p>
                    </div>

                    <div className="space-y-1">
                        <span className="text-xs font-medium text-muted-foreground">{t('Tax Number')}</span>
                        <p className="text-sm text-foreground bg-muted/30 p-2 rounded-md">
                            {data.tax_number || '-'}
                        </p>
                    </div>

                    <div className="space-y-1">
                        <span className="text-xs font-medium text-muted-foreground">{t('Payment Terms')}</span>
                        <p className="text-sm text-foreground bg-muted/30 p-2 rounded-md">
                            {data.payment_terms || '-'}
                        </p>
                    </div>
                </div>

                {/* Billing Address */}
                {hasBilling && (
                    <div className="space-y-2 border-t pt-4">
                        <div className="flex items-center gap-2 text-sm font-semibold text-foreground">
                            <MapPin className="h-4 w-4 text-primary" />
                            <span>{t('Billing Address')}</span>
                        </div>
                        <div className="text-sm text-foreground bg-muted/30 p-3 rounded-md space-y-1">
                            {billing.name && <p className="font-medium">{billing.name}</p>}
                            {(billing.address_line_1 || billing.address) && (
                                <p>{billing.address_line_1 || billing.address}</p>
                            )}
                            {billing.address_line_2 && <p>{billing.address_line_2}</p>}
                            <p>
                                {[billing.city, billing.state, billing.zip_code].filter(Boolean).join(', ')}
                            </p>
                            {billing.country && <p>{billing.country}</p>}
                        </div>
                    </div>
                )}

                {/* Shipping Address */}
                {(hasShipping && !data.same_as_billing) && (
                    <div className="space-y-2 border-t pt-4">
                        <div className="flex items-center gap-2 text-sm font-semibold text-foreground">
                            <MapPin className="h-4 w-4 text-primary" />
                            <span>{t('Shipping Address')}</span>
                        </div>
                        <div className="text-sm text-foreground bg-muted/30 p-3 rounded-md space-y-1">
                            {shipping.name && <p className="font-medium">{shipping.name}</p>}
                            {(shipping.address_line_1 || shipping.address) && (
                                <p>{shipping.address_line_1 || shipping.address}</p>
                            )}
                            {shipping.address_line_2 && <p>{shipping.address_line_2}</p>}
                            <p>
                                {[shipping.city, shipping.state, shipping.zip_code].filter(Boolean).join(', ')}
                            </p>
                            {shipping.country && <p>{shipping.country}</p>}
                        </div>
                    </div>
                )}

                {/* Notes */}
                {data.notes && (
                    <div className="space-y-2 border-t pt-4">
                        <span className="text-xs font-medium text-muted-foreground">{t('Notes')}</span>
                        <div className="text-sm text-foreground bg-muted/30 p-3 rounded-md whitespace-pre-wrap">
                            {data.notes}
                        </div>
                    </div>
                )}
            </div>
        </DialogContent>
    );
}
