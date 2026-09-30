import React, { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import SalesOrderSetupSidebar from "./SalesOrderSetupSidebar";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Save, FileText, Image as ImageIcon, Building, CreditCard, Hash, Truck } from 'lucide-react';
import MediaPicker from '@/components/MediaPicker';
import RichTextEditor from '@/components/ui/rich-text-editor';
import { getImagePath } from '@/utils/helpers';

interface SalesOrderSettingsProps {
    settings?: {
        so_prefix?: string;
        so_starting_number?: string;
        dc_prefix?: string;
        dc_starting_number?: string;
        logo_image?: string;
        show_logo?: string;
        bg_letterhead?: string;
        enable_letterhead?: string;
        default_terms?: string;
        default_notes?: string;
        footer_note?: string;
        template_color?: string;
        [key: string]: any;
    };
    [key: string]: any;
}

export default function Index() {
    const { t } = useTranslation();
    const { settings } = usePage<SalesOrderSettingsProps>().props;
    const [isLoading, setIsLoading] = useState(false);

    const [formData, setFormData] = useState({
        so_prefix:           settings?.so_prefix || 'SO',
        so_starting_number:  settings?.so_starting_number || '1',
        dc_prefix:           settings?.dc_prefix || 'DC',
        dc_starting_number:  settings?.dc_starting_number || '1',
        logo_image:          settings?.logo_image || '',
        show_logo:           settings?.show_logo !== 'off',
        bg_letterhead:       settings?.bg_letterhead || '',
        enable_letterhead:   settings?.enable_letterhead === 'on',
        default_terms:       settings?.default_terms ?? '',
        default_notes:       settings?.default_notes ?? '',
        footer_note:         settings?.footer_note ?? '',
        template_color:      settings?.template_color ?? '#3B82F6',
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
                show_logo:         formData.show_logo ? 'on' : 'off',
                enable_letterhead: formData.enable_letterhead ? 'on' : 'off',
            }
        };

        router.post(route('salesorder.settings.update'), payload, {
            preserveScroll: true,
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Sales Orders'), url: route('salesorder.orders.index') },
                { label: t('System Setup') },
                { label: t('Sales Order Setup') }
            ]}
            pageTitle={t('Sales Order Setup')}
        >
            <Head title={t('Sales Order Setup')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SalesOrderSetupSidebar activeItem="sales-order-settings" />
                </div>

                <div className="flex-1 space-y-6">
                    <form onSubmit={handleSubmit}>
                        <Card className="shadow-xs border-slate-200">
                            <CardHeader className="flex flex-row items-center justify-between pb-4 border-b border-slate-100">
                                <div>
                                    <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900">
                                        <FileText className="h-5 w-5 text-slate-700" />
                                        {t('Sales Order Design & Setup')}
                                    </CardTitle>
                                    <CardDescription className="text-xs text-slate-500 mt-1">
                                        {t('Configure numbering, logo, letterhead, and default terms for Sales Orders and Delivery Challans.')}
                                    </CardDescription>
                                </div>
                                <Button type="submit" disabled={isLoading} size="sm" className="gap-2">
                                    <Save className="h-4 w-4" />
                                    {isLoading ? t('Saving...') : t('Save Settings')}
                                </Button>
                            </CardHeader>

                            <CardContent className="space-y-6 pt-6">

                                {/* SECTION 1: NUMBERING */}
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">

                                    {/* Sales Order Numbering */}
                                    <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50 flex flex-col justify-between">
                                        <div className="flex items-center gap-3 border-b border-slate-200 pb-3">
                                            <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                <Hash className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-900">{t('Sales Order Numbering')}</h3>
                                                <p className="text-xs text-slate-500">
                                                    {t('Set prefix and starting sequence number for Sales Orders.')}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                            <div className="space-y-1.5">
                                                <Label htmlFor="so_prefix" className="text-xs font-medium">{t('SO Prefix')}</Label>
                                                <Input
                                                    id="so_prefix"
                                                    value={formData.so_prefix}
                                                    onChange={(e) => handleChange('so_prefix', e.target.value)}
                                                    placeholder="e.g. SO"
                                                    maxLength={10}
                                                    className="h-9 text-xs bg-white"
                                                />
                                                <p className="text-[11px] text-slate-400">
                                                    {t('Preview')}: <strong>{formData.so_prefix}{String(parseInt(formData.so_starting_number) || 1).padStart(5, '0')}</strong>
                                                </p>
                                            </div>

                                            <div className="space-y-1.5">
                                                <Label htmlFor="so_starting_number" className="text-xs font-medium">{t('Starting Number')}</Label>
                                                <Input
                                                    id="so_starting_number"
                                                    type="number"
                                                    min="1"
                                                    value={formData.so_starting_number}
                                                    onChange={(e) => handleChange('so_starting_number', e.target.value)}
                                                    placeholder="e.g. 1"
                                                    className="h-9 text-xs bg-white"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    {/* Delivery Challan Numbering */}
                                    <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50 flex flex-col justify-between">
                                        <div className="flex items-center gap-3 border-b border-slate-200 pb-3">
                                            <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                <Truck className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-900">{t('Delivery Challan Numbering')}</h3>
                                                <p className="text-xs text-slate-500">
                                                    {t('Set prefix and starting sequence number for Delivery Challans.')}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                            <div className="space-y-1.5">
                                                <Label htmlFor="dc_prefix" className="text-xs font-medium">{t('DC Prefix')}</Label>
                                                <Input
                                                    id="dc_prefix"
                                                    value={formData.dc_prefix}
                                                    onChange={(e) => handleChange('dc_prefix', e.target.value)}
                                                    placeholder="e.g. DC"
                                                    maxLength={10}
                                                    className="h-9 text-xs bg-white"
                                                />
                                                <p className="text-[11px] text-slate-400">
                                                    {t('Preview')}: <strong>{formData.dc_prefix}{String(parseInt(formData.dc_starting_number) || 1).padStart(5, '0')}</strong>
                                                </p>
                                            </div>

                                            <div className="space-y-1.5">
                                                <Label htmlFor="dc_starting_number" className="text-xs font-medium">{t('Starting Number')}</Label>
                                                <Input
                                                    id="dc_starting_number"
                                                    type="number"
                                                    min="1"
                                                    value={formData.dc_starting_number}
                                                    onChange={(e) => handleChange('dc_starting_number', e.target.value)}
                                                    placeholder="e.g. 1"
                                                    className="h-9 text-xs bg-white"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* SECTION 2: LOGO */}
                                <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                                    <div className="flex items-center justify-between border-b border-slate-200 pb-3">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                <Building className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-900">{t('Custom Logo')}</h3>
                                                <p className="text-xs text-slate-500">
                                                    {t('Upload a custom logo or toggle off to use default.')}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Label htmlFor="show-logo" className="cursor-pointer text-xs font-medium text-slate-700">
                                                {formData.show_logo ? t('ON') : t('OFF')}
                                            </Label>
                                            <Switch
                                                id="show-logo"
                                                checked={formData.show_logo}
                                                onCheckedChange={(checked) => handleChange('show_logo', checked)}
                                            />
                                        </div>
                                    </div>

                                    {formData.show_logo && (
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                            <div className="space-y-1.5">
                                                <Label className="text-xs font-medium">{t('Select Logo')}</Label>
                                                <MediaPicker
                                                    value={formData.logo_image}
                                                    onChange={(url) => handleChange('logo_image', Array.isArray(url) ? url[0] : url)}
                                                    placeholder={t('Choose logo image...')}
                                                    showPreview={false}
                                                />
                                            </div>

                                            <div className="space-y-1.5">
                                                <Label className="text-xs font-medium">{t('Preview')}</Label>
                                                <div className="border border-slate-200 rounded-md p-2 h-9 bg-white flex items-center justify-center">
                                                    {formData.logo_image ? (
                                                        <img
                                                            src={getImagePath(formData.logo_image)}
                                                            alt="Logo"
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

                                {/* SECTION 3: BACKGROUND LETTERHEAD */}
                                <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                                    <div className="flex items-center justify-between border-b border-slate-200 pb-3">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                <ImageIcon className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-900">{t('Background Letterhead Image')}</h3>
                                                <p className="text-xs text-slate-500">
                                                    {t('Upload an optional background graphic to be used behind printed A4 documents.')}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Label htmlFor="enable-letterhead" className="cursor-pointer text-xs font-medium text-slate-700">
                                                {formData.enable_letterhead ? t('Letterhead: ON') : t('Letterhead: OFF')}
                                            </Label>
                                            <Switch
                                                id="enable-letterhead"
                                                checked={formData.enable_letterhead}
                                                onCheckedChange={(checked) => handleChange('enable_letterhead', checked)}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-1">
                                        <div className="space-y-2">
                                            <Label className="text-xs">{t('Select / Upload Background Image')}</Label>
                                            <MediaPicker
                                                value={formData.bg_letterhead}
                                                onChange={(url) => handleChange('bg_letterhead', Array.isArray(url) ? url[0] : url)}
                                                placeholder={t('Choose background letterhead...')}
                                                showPreview={false}
                                            />
                                        </div>

                                        <div className="space-y-1">
                                            <Label className="text-xs">{t('Background Preview')}</Label>
                                            <div className="border border-slate-200 rounded-md h-24 bg-white relative overflow-hidden flex items-center justify-center">
                                                {formData.bg_letterhead ? (
                                                    <img
                                                        src={getImagePath(formData.bg_letterhead)}
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

                                {/* SECTION 4: DEFAULT TERMS & CONDITIONS */}
                                <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                                    <div className="flex items-center gap-3 border-b border-slate-200 pb-3">
                                        <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                            <CreditCard className="h-4 w-4" />
                                        </div>
                                        <div>
                                            <h3 className="text-sm font-semibold text-slate-900">{t('Default Terms & Conditions')}</h3>
                                            <p className="text-xs text-slate-500">
                                                {t('Default terms & conditions for Sales Orders.')}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <RichTextEditor
                                            content={formData.default_terms}
                                            onChange={(val) => handleChange('default_terms', val)}
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
