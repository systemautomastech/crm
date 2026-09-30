import { useState } from 'react';
import { Head, usePage, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { Copy, Eye, EyeOff, Save, Fingerprint } from 'lucide-react';
import { Switch } from '@/components/ui/switch';
import { toast } from 'sonner';

interface BiometricSetting {
    id?: number;
    zkteco_api_url?: string;
    username?: string;
    password?: string;
    auth_token?: string;
    is_zkteco_sync?: boolean;
}

interface Props {
    setting?: BiometricSetting;
}

export default function Settings({ setting }: Props) {
    const { t } = useTranslation();
    const [showPassword, setShowPassword] = useState(false);
    const { auth } = usePage().props;
    const canEdit = auth?.user?.permissions?.includes('edit-biometric-settings');
    useFlashMessages();
    
    const { data, setData, post, processing, errors } = useForm({
        zkteco_api_url: setting?.zkteco_api_url || '',
        username: setting?.username || '',
        password: setting?.password || '',
        auth_token: setting?.auth_token || '',
        is_zkteco_sync: setting?.is_zkteco_sync || false,
    });

    const handleSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        post(route('biometric-attendance.settings.save'), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('Biometric Attendance'), url: route('biometric-attendance.index')},
                {label: t('Settings')}
            ]}
            pageTitle={
                <div className="flex items-center justify-between">
                    <span>{t('Biometric Settings')}</span>
                </div>
            }
        >
            <Head title={t('Biometric Settings')} />
            
            <Card>
                <CardHeader className="flex flex-row items-center justify-between">
                    <div className="order-1 rtl:order-2">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <Fingerprint className="h-5 w-5" />
                            {t('Biometric Settings')}
                        </CardTitle>
                        <p className="text-sm text-muted-foreground mt-1">
                            {t('Configure ZKTeco biometric device integration')}
                        </p>
                    </div>
                    <div className="order-2 rtl:order-1 flex items-center gap-3">
                        <Label htmlFor="is_zkteco_sync_header" className="text-sm font-normal">
                            {t('Enable ZKTeco Sync')}
                        </Label>
                        <Switch
                            id="is_zkteco_sync_header"
                            checked={data.is_zkteco_sync}
                            onCheckedChange={(checked) => setData('is_zkteco_sync', checked)}
                            disabled={!canEdit}
                        />
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="space-y-6">
                        {data.is_zkteco_sync && (
                            <>
                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    {/* Left Side - Form Fields */}
                                    <div className="lg:col-span-2 space-y-6">
                                        {/* ZKTeco API URL */}
                                        <div>
                                            <Label htmlFor="zkteco_api_url">{t('ZKTeco API URL')}</Label>
                                            <Input
                                                id="zkteco_api_url"
                                                type="url"
                                                value={data.zkteco_api_url}
                                                onChange={(e) => setData('zkteco_api_url', e.target.value)}
                                                placeholder="http://192.168.0.107:70"
                                                disabled={!canEdit}
                                            />
                                            <InputError message={errors.zkteco_api_url} />
                                        </div>

                                        {/* Username */}
                                        <div>
                                            <Label htmlFor="username">{t('Username')}</Label>
                                            <Input
                                                id="username"
                                                type="text"
                                                value={data.username}
                                                onChange={(e) => setData('username', e.target.value)}
                                                placeholder={t('Enter Username')}
                                                disabled={!canEdit}
                                            />
                                            <InputError message={errors.username} />
                                        </div>

                                        {/* Password */}
                                        <div>
                                            <Label htmlFor="password">{t('Password')}</Label>
                                            <div className="relative">
                                                <Input
                                                    id="password"
                                                    type={showPassword ? 'text' : 'password'}
                                                    value={data.password}
                                                    onChange={(e) => setData('password', e.target.value)}
                                                    placeholder={t('Enter Password')}
                                                    disabled={!canEdit}
                                                    className="pr-10"
                                                />
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                                    onClick={() => setShowPassword(!showPassword)}
                                                >
                                                    {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                </Button>
                                            </div>
                                            <InputError message={errors.password} />
                                        </div>

                                        {/* Auth Token - Show only after save */}
                                        {data.auth_token && (
                                            <div>
                                                <Label htmlFor="auth_token">{t('Auth Token')}</Label>
                                                <div className="relative">
                                                    <Input
                                                        id="auth_token"
                                                        type="text"
                                                        value={data.auth_token}
                                                        disabled
                                                        className="pr-10 bg-muted"
                                                    />
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                                                        onClick={() => {
                                                            navigator.clipboard.writeText(data.auth_token);
                                                            toast.success(t('Auth token copied to clipboard'));
                                                        }}
                                                    >
                                                        <Copy className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                                <p className="text-xs text-green-600 mt-1">
                                                    {t('Token generated successfully! Click copy icon to copy.')}
                                                </p>
                                            </div>
                                        )}

                                        {/* Save Button */}
                                        {canEdit && (
                                            <div className="flex justify-end pt-4">
                                                <Button onClick={() => handleSubmit()} disabled={processing}>
                                                    <Save className="h-4 w-4 mr-2" />
                                                    {processing ? t('Saving...') : t('Save Changes')}
                                                </Button>
                                            </div>
                                        )}
                                    </div>

                                    {/* Right Side - Guide */}
                                    <div className="lg:col-span-1 space-y-4">
                                        <div className="border rounded-lg p-4 bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800">
                                            <h4 className="font-semibold mb-2 text-yellow-900 dark:text-yellow-100 flex items-center gap-2">
                                                <span className="text-lg">⚠️</span>
                                                {t('Important Note')}
                                            </h4>
                                            <p className="text-xs text-yellow-800 dark:text-yellow-200 leading-relaxed mb-3">
                                                {t('Please note that you can use the biometric attendance system only if you are using the ZKTeco machine.')}
                                            </p>
                                            <p className="text-xs text-yellow-800 dark:text-yellow-200 leading-relaxed mb-2">
                                                {t('You can only use this module if the API supports the ZKTeco Machine.')}
                                            </p>
                                            <div className="mt-3 pt-3 border-t border-yellow-200 dark:border-yellow-800">
                                                <p className="text-xs font-medium text-yellow-900 dark:text-yellow-100 mb-1">{t('We are using ZKBioTime API. For documentation, please refer to:')}</p>
                                                <a 
                                                    href="https://www.zkteco.com/en/ZKBioTime_API/ZKBioTime_API" 
                                                    target="_blank" 
                                                    rel="noopener noreferrer" 
                                                    className="text-xs underline hover:no-underline font-medium text-yellow-900 dark:text-yellow-100 break-all"
                                                >
                                                    https://www.zkteco.com/en/ZKBioTime_API/ZKBioTime_API
                                                </a>
                                            </div>
                                        </div>

                                        <div className="border rounded-lg p-4 bg-blue-50 dark:bg-blue-950/20">
                                            <h4 className="font-semibold mb-3 text-blue-900 dark:text-blue-100">
                                                {t('Configuration Guide')}
                                            </h4>
                                            <div className="space-y-1 text-sm text-blue-800 dark:text-blue-200">
                                                <div className="flex items-start gap-2">
                                                    <span className="font-semibold text-blue-900 dark:text-blue-100">1.</span>
                                                    <p className="text-xs leading-relaxed">{t('Enter the API endpoint URL provided by your biometric device or service.')}</p>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <span className="font-semibold text-blue-900 dark:text-blue-100">2.</span>
                                                    <p className="text-xs leading-relaxed">{t('Enter the username required to authenticate with the biometric API.')}</p>
                                                </div>
                                                <div className="flex items-start gap-2">
                                                    <span className="font-semibold text-blue-900 dark:text-blue-100">3.</span>
                                                    <p className="text-xs leading-relaxed">{t('Enter the password corresponding to the username for API authentication.')}</p>
                                                </div>
                                                <div className="pt-3 border-t border-blue-200 dark:border-blue-800">
                                                    <p className="text-xs leading-relaxed mb-2 font-medium text-blue-900 dark:text-blue-100">
                                                        {t('After entering these details, click Save Changes button:')}
                                                    </p>
                                                    <ul className="space-y-1.5 ml-1">
                                                        <li className="text-xs flex items-start gap-2">
                                                            <span className="text-blue-600 dark:text-blue-400 mt-0.5">✓</span>
                                                            <span>{t('Save the entered information securely')}</span>
                                                        </li>
                                                        <li className="text-xs flex items-start gap-2">
                                                            <span className="text-blue-600 dark:text-blue-400 mt-0.5">✓</span>
                                                            <span>{t('Generate an authentication token')}</span>
                                                        </li>
                                                        <li className="text-xs flex items-start gap-2">
                                                            <span className="text-blue-600 dark:text-blue-400 mt-0.5">✓</span>
                                                            <span>{t('Enable communication with the biometric device')}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                </CardContent>
            </Card>
        </AuthenticatedLayout>
    );
}