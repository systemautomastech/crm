import React, { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import SystemSetupSidebar from "../SystemSetupSidebar";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Save, FileText, Image as ImageIcon, FileCheck, Building } from 'lucide-react';
import MediaPicker from '@/components/MediaPicker';
import { getImagePath } from '@/utils/helpers';
import { toast } from 'sonner';

interface PayslipSettingsProps {
    settings: {
        payslip_logo?: string;
        payslip_show_logo?: string;
        payslip_bg_letterhead?: string;
        payslip_enable_letterhead?: string;
        payslip_hr_signature?: string;
        payslip_hr_name?: string;
        payslip_hr_title?: string;
        payslip_show_signatures?: string;
        payslip_note?: string;
    };
}

export default function Index() {
    const { t } = useTranslation();
    const { settings } = usePage<PayslipSettingsProps>().props;
    const [isLoading, setIsLoading] = useState(false);

    const [formData, setFormData] = useState({
        payslip_logo: settings?.payslip_logo || '',
        payslip_show_logo: settings?.payslip_show_logo !== 'off',
        payslip_bg_letterhead: settings?.payslip_bg_letterhead || '',
        payslip_enable_letterhead: settings?.payslip_enable_letterhead === 'on',
        payslip_hr_signature: settings?.payslip_hr_signature || '',
        payslip_hr_name: settings?.payslip_hr_name || '',
        payslip_hr_title: settings?.payslip_hr_title || 'HR Manager / Authorized Signatory',
        payslip_show_signatures: settings?.payslip_show_signatures !== 'off',
        payslip_note: settings?.payslip_note || '',
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
                payslip_show_logo: formData.payslip_show_logo ? 'on' : 'off',
                payslip_enable_letterhead: formData.payslip_enable_letterhead ? 'on' : 'off',
                payslip_show_signatures: formData.payslip_show_signatures ? 'on' : 'off',
            }
        };

        router.post(route('hrm.payslip-settings.update'), payload, {
            preserveScroll: true,
            onSuccess: () => {
                setIsLoading(false);
                toast.success(t('Payroll & Payslip settings saved successfully'));
            },
            onError: () => {
                setIsLoading(false);
                toast.error(t('Failed to save settings'));
            }
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Hrm'), url: route('hrm.index') },
                { label: t('System Setup') },
                { label: t('Payslip Setup') }
            ]}
            pageTitle={t('Payslip Setup')}
        >
            <Head title={t('Payslip Setup')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="payslip-settings" />
                </div>

                <div className="flex-1 space-y-6">
                    <form onSubmit={handleSubmit}>
                        <Card className="shadow-xs border-slate-200">
                            <CardHeader className="flex flex-row items-center justify-between pb-4 border-b border-slate-100">
                                <div>
                                    <CardTitle className="flex items-center gap-2 text-lg font-semibold text-slate-900">
                                        <FileText className="h-5 w-5 text-slate-700" />
                                        {t('Payslip Design & Setup')}
                                    </CardTitle>
                                    <CardDescription className="text-xs text-slate-500 mt-1">
                                        {t('Configure logo visibility, letterhead background, digital signatures, and minimal document layout rules.')}
                                    </CardDescription>
                                </div>
                                <Button type="submit" disabled={isLoading} size="sm" className="gap-2">
                                    <Save className="h-4 w-4" />
                                    {isLoading ? t('Saving...') : t('Save Settings')}
                                </Button>
                            </CardHeader>

                            <CardContent className="space-y-6 pt-6">
                                {/* SECTION 1: PAYSLIP LOGO OPTION WITH TOGGLE */}
                                <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                                    <div className="flex items-center justify-between border-b border-slate-200 pb-3">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                <Building className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-900">{t('Payslip Custom Logo')}</h3>
                                                <p className="text-xs text-slate-500">
                                                    {t('Upload a custom logo or toggle off if your letterhead paper already contains a printed logo.')}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Label htmlFor="show-logo" className="cursor-pointer text-xs font-medium text-slate-700">
                                                {formData.payslip_show_logo ? t('Logo: ON') : t('Logo: OFF')}
                                            </Label>
                                            <Switch
                                                id="show-logo"
                                                checked={formData.payslip_show_logo}
                                                onCheckedChange={(checked) => handleChange('payslip_show_logo', checked)}
                                            />
                                        </div>
                                    </div>

                                    {formData.payslip_show_logo && (
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-1">
                                            <div className="space-y-2">
                                                <Label className="text-xs">{t('Select / Upload Payslip Logo')}</Label>
                                                <MediaPicker
                                                    value={formData.payslip_logo}
                                                    onChange={(url) => handleChange('payslip_logo', Array.isArray(url) ? url[0] : url)}
                                                    placeholder={t('Choose payslip logo image...')}
                                                    showPreview={false}
                                                />
                                            </div>

                                            <div className="space-y-1">
                                                <Label className="text-xs">{t('Logo Preview')}</Label>
                                                <div className="border border-slate-200 rounded-md p-3 h-16 bg-white flex items-center justify-center">
                                                    {formData.payslip_logo ? (
                                                        <img
                                                            src={getImagePath(formData.payslip_logo)}
                                                            alt="Payslip Logo"
                                                            className="max-h-12 max-w-full object-contain"
                                                        />
                                                    ) : (
                                                        <span className="text-xs text-slate-400 italic">
                                                            {t('Using default company logo')}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    )}
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
                                                    {t('Upload an optional background graphic to be used behind printed A4 payslips')}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Label htmlFor="enable-letterhead" className="cursor-pointer text-xs font-medium text-slate-700">
                                                {formData.payslip_enable_letterhead ? t('Letterhead: ON') : t('Letterhead: OFF')}
                                            </Label>
                                            <Switch
                                                id="enable-letterhead"
                                                checked={formData.payslip_enable_letterhead}
                                                onCheckedChange={(checked) => handleChange('payslip_enable_letterhead', checked)}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-1">
                                        <div className="space-y-2">
                                            <Label className="text-xs">{t('Select / Upload Background Image')}</Label>
                                            <MediaPicker
                                                value={formData.payslip_bg_letterhead}
                                                onChange={(url) => handleChange('payslip_bg_letterhead', Array.isArray(url) ? url[0] : url)}
                                                placeholder={t('Choose background letterhead...')}
                                                showPreview={false}
                                            />
                                        </div>

                                        <div className="space-y-1">
                                            <Label className="text-xs">{t('Background Preview')}</Label>
                                            <div className="border border-slate-200 rounded-md h-24 bg-white relative overflow-hidden flex items-center justify-center">
                                                {formData.payslip_bg_letterhead ? (
                                                    <img
                                                        src={getImagePath(formData.payslip_bg_letterhead)}
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

                                {/* SECTION 3: SIGNATURES & SIGNATORY */}
                                <div className="space-y-4 border border-slate-200 rounded-lg p-5 bg-slate-50/50">
                                    <div className="flex items-center justify-between border-b border-slate-200 pb-3">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 bg-slate-200 text-slate-700 rounded-md">
                                                <FileCheck className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <h3 className="text-sm font-semibold text-slate-900">{t('Digital Signatures & Signatory')}</h3>
                                                <p className="text-xs text-slate-500">
                                                    {t('Configure HR signatory details and signature block visibility')}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Label htmlFor="show-signatures" className="cursor-pointer text-xs font-medium text-slate-700">
                                                {formData.payslip_show_signatures ? t('Signatures: Visible') : t('Signatures: Hidden')}
                                            </Label>
                                            <Switch
                                                id="show-signatures"
                                                checked={formData.payslip_show_signatures}
                                                onCheckedChange={(checked) => handleChange('payslip_show_signatures', checked)}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-1">
                                        <div className="space-y-3">
                                            <div className="space-y-1">
                                                <Label htmlFor="hr-name" className="text-xs">{t('HR Manager / Signatory Name')}</Label>
                                                <Input
                                                    id="hr-name"
                                                    value={formData.payslip_hr_name}
                                                    onChange={(e) => handleChange('payslip_hr_name', e.target.value)}
                                                    placeholder={t('e.g. Sarah Jenkins')}
                                                    className="text-xs h-9"
                                                />
                                            </div>

                                            <div className="space-y-1">
                                                <Label htmlFor="hr-title" className="text-xs">{t('Designation / Title')}</Label>
                                                <Input
                                                    id="hr-title"
                                                    value={formData.payslip_hr_title}
                                                    onChange={(e) => handleChange('payslip_hr_title', e.target.value)}
                                                    placeholder={t('e.g. Head of Human Resources')}
                                                    className="text-xs h-9"
                                                />
                                            </div>

                                            <div className="space-y-1">
                                                <Label className="text-xs">{t('HR Signature Image / Stamp')}</Label>
                                                <MediaPicker
                                                    value={formData.payslip_hr_signature}
                                                    onChange={(url) => handleChange('payslip_hr_signature', Array.isArray(url) ? url[0] : url)}
                                                    placeholder={t('Upload signature image...')}
                                                    showPreview={false}
                                                />
                                            </div>
                                        </div>

                                        <div className="space-y-1">
                                            <Label className="text-xs">{t('Signature Block Preview')}</Label>
                                            <div className="border border-slate-200 rounded-md p-4 bg-white space-y-4">
                                                <div className="grid grid-cols-3 gap-2 text-center">
                                                    <div className="border-t border-slate-400 pt-1.5">
                                                        <span className="text-[10px] font-semibold text-slate-600 block">{t('Prepared By')}</span>
                                                    </div>
                                                    <div className="border-t border-slate-400 pt-1.5">
                                                        <span className="text-[10px] font-semibold text-slate-600 block">{t('Employee Sign')}</span>
                                                    </div>
                                                    <div className="border-t border-slate-400 pt-1.5">
                                                        {formData.payslip_hr_signature && (
                                                            <img
                                                                src={getImagePath(formData.payslip_hr_signature)}
                                                                alt="HR Sign"
                                                                className="max-h-7 object-contain mx-auto mb-1"
                                                            />
                                                        )}
                                                        <span className="text-[10px] font-semibold text-slate-800 block">
                                                            {formData.payslip_hr_name || t('Authorized Sign')}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* SECTION 4: FOOTER NOTE */}
                                <div className="space-y-1">
                                    <Label htmlFor="payslip-note" className="text-xs">{t('Payslip Disclaimer / Footer Note')}</Label>
                                    <Textarea
                                        id="payslip-note"
                                        rows={2}
                                        value={formData.payslip_note}
                                        onChange={(e) => handleChange('payslip_note', e.target.value)}
                                        placeholder={t('e.g. This payslip is computer generated and valid without signature.')}
                                        className="text-xs"
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
