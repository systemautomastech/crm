import React, { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import SalesSetupSidebar from "./SalesSetupSidebar";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Save, FileText, Image as ImageIcon, Building, CreditCard, Hash } from 'lucide-react';
import MediaPicker from '@/components/MediaPicker';
import RichTextEditor from '@/components/ui/rich-text-editor';
import { getImagePath } from '@/utils/helpers';

interface SalesInvoiceSettingsProps {
    settings?: {
        sales_invoice_logo?: string;
        sales_invoice_show_logo?: string;
        sales_invoice_bg_letterhead?: string;
        sales_invoice_enable_letterhead?: string;
        sales_invoice_default_payment_terms?: string;
        [key: string]: any;
    };
    [key: string]: any;
}

export default function Index() {
    const { t } = useTranslation();
    const { settings } = usePage<SalesInvoiceSettingsProps>().props;
    const [isLoading, setIsLoading] = useState(false);

    const [formData, setFormData] = useState({
        sales_invoice_prefix: settings?.sales_invoice_prefix || 'SI',
        sales_invoice_starting_number: settings?.sales_invoice_starting_number || '1',
        sales_invoice_logo: settings?.sales_invoice_logo || '',
        sales_invoice_show_logo: settings?.sales_invoice_show_logo !== 'off',
        sales_invoice_bg_letterhead: settings?.sales_invoice_bg_letterhead || '',
        sales_invoice_enable_letterhead: settings?.sales_invoice_enable_letterhead === 'on',
        sales_invoice_default_payment_terms: settings?.sales_invoice_default_payment_terms ?? '<p>Payment due within 30 days of invoice date.</p>',
    });

    useFlashMessages();

    const handleChange = (field: string, value: any) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);

        const payload = {
            settings: {
                ...formData,
                sales_invoice_show_logo: formData.sales_invoice_show_logo ? 'on' : 'off',
                sales_invoice_enable_letterhead: formData.sales_invoice_enable_letterhead ? 'on' : 'off',
            }
        };

        router.post(route('sales-invoice-setup.update'), payload, {
            preserveScroll: true,
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Sales'), url: route('sales-invoices.index') },
                { label: t('System Setup') },
                { label: t('Invoice Setup') }
            ]}
            pageTitle={t('Invoice Setup')}
        >
            <Head title={t('Invoice Setup')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SalesSetupSidebar activeItem="sales-invoice-settings" />
                </div>

                <div className="flex-1 space-y-6">
                    <form onSubmit={handleSubmit}>
                        <Card className="shadow-xs border-slate-200">
                            <CardHeader className="flex flex-row items-center justify-between pb-4 border-b border-slate-100">
                                <div>
                                    <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900">
                                        <FileText className="h-5 w-5 text-slate-700" />
                                        {t('Invoice Design & Setup')}
                                    </CardTitle>
                                    <CardDescription className="text-xs text-slate-500 mt-1">
                                        {t('Configure custom logo, background letterhead image, and default payment terms for invoices.')}
                                    </CardDescription>
                                </div>
                                <Button type="submit" disabled={isLoading} size="sm" className="gap-2">
                                    <Save className="h-4 w-4" />
                                    {isLoading ? t('Saving...') : t('Save Settings')}
                                </Button>
                            </CardHeader>

                            <CardContent className="space-y-6 pt-6">
                                {/* SECTION 0 & 1: NUMBERING & LOGO (IN SAME ROW) */}
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    {/* INVOICE NUMBERING SETUP */}
                                    <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50 flex flex-col justify-between">
                                        <div className="flex items-center gap-3 border-b border-slate-200 pb-3">
                                            <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                <Hash className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-900">{t('Invoice Numbering')}</h3>
                                                <p className="text-xs text-slate-500">
                                                    {t('Set default prefix and starting sequence number.')}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                            <div className="space-y-1.5">
                                                <Label htmlFor="sales_invoice_prefix" className="text-xs font-medium">{t('Invoice Prefix')}</Label>
                                                <Input
                                                    id="sales_invoice_prefix"
                                                    value={formData.sales_invoice_prefix}
                                                    onChange={(e) => handleChange('sales_invoice_prefix', e.target.value)}
                                                    placeholder="e.g. SI"
                                                    className="h-9 text-xs bg-white"
                                                />
                                            </div>

                                            <div className="space-y-1.5">
                                                <Label htmlFor="sales_invoice_starting_number" className="text-xs font-medium">{t('Starting Number')}</Label>
                                                <Input
                                                    id="sales_invoice_starting_number"
                                                    type="number"
                                                    min="1"
                                                    value={formData.sales_invoice_starting_number}
                                                    onChange={(e) => handleChange('sales_invoice_starting_number', e.target.value)}
                                                    placeholder="e.g. 1"
                                                    className="h-9 text-xs bg-white"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    {/* SALES INVOICE LOGO OPTION WITH TOGGLE */}
                                    <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                                        <div className="flex items-center justify-between border-b border-slate-200 pb-3">
                                            <div className="flex items-center gap-3">
                                                <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                    <Building className="h-4 w-4" />
                                                </div>
                                                <div>
                                                    <h3 className="text-sm font-semibold text-slate-900">{t('Invoice Custom Logo')}</h3>
                                                    <p className="text-xs text-slate-500">
                                                        {t('Upload custom logo or toggle off.')}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label htmlFor="show-logo" className="cursor-pointer text-xs font-medium text-slate-700">
                                                    {formData.sales_invoice_show_logo ? t('ON') : t('OFF')}
                                                </Label>
                                                <Switch
                                                    id="show-logo"
                                                    checked={formData.sales_invoice_show_logo}
                                                    onCheckedChange={(checked) => handleChange('sales_invoice_show_logo', checked)}
                                                />
                                            </div>
                                        </div>

                                        {formData.sales_invoice_show_logo && (
                                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                                <div className="space-y-1.5">
                                                    <Label className="text-xs font-medium">{t('Select Logo')}</Label>
                                                    <MediaPicker
                                                        value={formData.sales_invoice_logo}
                                                        onChange={(url) => handleChange('sales_invoice_logo', Array.isArray(url) ? url[0] : url)}
                                                        placeholder={t('Choose logo image...')}
                                                        showPreview={false}
                                                    />
                                                </div>

                                                <div className="space-y-1.5">
                                                    <Label className="text-xs font-medium">{t('Preview')}</Label>
                                                    <div className="border border-slate-200 rounded-md p-2 h-9 bg-white flex items-center justify-center">
                                                        {formData.sales_invoice_logo ? (
                                                            <img
                                                                src={getImagePath(formData.sales_invoice_logo)}
                                                                alt="Invoice Logo"
                                                                className="max-h-7 max-w-full object-contain"
                                                            />
                                                        ) : (
                                                            <span className="text-[11px] text-slate-400 italic">
                                                                {t('No logo selected')}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* SECTION 2: BACKGROUND LETTERHEAD */}
                                <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                                    <div className="flex items-center justify-between border-b border-slate-200 pb-3">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                <ImageIcon className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-900">{t('Background Letterhead Image')}</h3>
                                                <p className="text-xs text-slate-500">
                                                    {t('Upload an optional background graphic to be used behind printed A4 invoices')}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Label htmlFor="enable-letterhead" className="cursor-pointer text-xs font-medium text-slate-700">
                                                {formData.sales_invoice_enable_letterhead ? t('Letterhead: ON') : t('Letterhead: OFF')}
                                            </Label>
                                            <Switch
                                                id="enable-letterhead"
                                                checked={formData.sales_invoice_enable_letterhead}
                                                onCheckedChange={(checked) => handleChange('sales_invoice_enable_letterhead', checked)}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-1">
                                        <div className="space-y-2">
                                            <Label className="text-xs">{t('Select / Upload Background Image')}</Label>
                                            <MediaPicker
                                                value={formData.sales_invoice_bg_letterhead}
                                                onChange={(url) => handleChange('sales_invoice_bg_letterhead', Array.isArray(url) ? url[0] : url)}
                                                placeholder={t('Choose background letterhead...')}
                                                showPreview={false}
                                            />
                                        </div>

                                        <div className="space-y-1">
                                            <Label className="text-xs">{t('Background Preview')}</Label>
                                            <div className="border border-slate-200 rounded-md h-24 bg-white relative overflow-hidden flex items-center justify-center">
                                                {formData.sales_invoice_bg_letterhead ? (
                                                    <img
                                                        src={getImagePath(formData.sales_invoice_bg_letterhead)}
                                                        alt="Letterhead Preview"
                                                        className="absolute inset-0 w-full h-full object-cover opacity-70"
                                                    />
                                                ) : (
                                                    <span className="text-xs text-slate-400 italic">{t('No background image selected')}</span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* SECTION 3: DEFAULT TERMS & CONDITIONS */}
                                <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                                    <div className="flex items-center gap-3 border-b border-slate-200 pb-3">
                                        <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                            <CreditCard className="h-4 w-4" />
                                        </div>
                                        <div>
                                            <h3 className="text-sm font-semibold text-slate-900">{t('Default Terms & Conditions')}</h3>
                                            <p className="text-xs text-slate-500">
                                                {t('Default terms & conditions for invoices.')}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <RichTextEditor
                                            content={formData.sales_invoice_default_payment_terms}
                                            onChange={(val) => handleChange('sales_invoice_default_payment_terms', val)}
                                            placeholder={t('Enter default terms & conditions...')}
                                            minimal={true}
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
