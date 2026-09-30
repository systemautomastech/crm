import { FormEvent, useRef, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { LoaderCircle, Save, Volume2, Square } from 'lucide-react';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFlashMessages } from '@/hooks/useFlashMessages';

interface RingtoneOption {
    value: string;
    label: string;
}

interface PbxSetting {
    id?: number;
    pbx_name?: string | null;
    pbx_host?: string | null;
    ami_host?: string | null;
    ami_port?: number | null;
    ami_username?: string | null;
    sip_domain?: string | null;
    websocket_url?: string | null;
    stun_server?: string | null;
    sip_trunk_name?: string | null;
    extension_start?: number | null;
    extension_end?: number | null;
    max_extensions?: number | null;
    call_report_api_url?: string | null;
    call_report_api_key?: string | null;
    is_enabled?: boolean | number;
    ringtone?: string | null;
}

interface PbxSettingsPageProps {
    setting: PbxSetting | null;
    availableRingtones?: RingtoneOption[];
}

interface PbxSettingsForm {
    pbx_name: string;
    pbx_host: string;
    ami_host: string;
    ami_port: number | string;
    ami_username: string;
    ami_password: string;
    sip_domain: string;
    websocket_url: string;
    stun_server: string;
    sip_trunk_name: string;
    extension_start: number | string;
    extension_end: number | string;
    max_extensions: number | string;
    is_enabled: boolean;
    call_report_api_url: string;
    call_report_api_key: string;
    ringtone: string;
}

export default function Index({ setting, availableRingtones = [] }: PbxSettingsPageProps) {
    const { t } = useTranslation();
    const [isPlaying, setIsPlaying] = useState(false);
    const audioRef = useRef<HTMLAudioElement | null>(null);

    useFlashMessages();

    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm<PbxSettingsForm>({
        pbx_name: setting?.pbx_name ?? '',
        pbx_host: setting?.pbx_host ?? '',
        ami_host: setting?.ami_host ?? '',
        ami_port: setting?.ami_port ?? 5038,
        ami_username: setting?.ami_username ?? '',
        ami_password: '',
        sip_domain: setting?.sip_domain ?? '',
        websocket_url: setting?.websocket_url ?? '',
        stun_server:
            setting?.stun_server ??
            'stun:stun.l.google.com:19302',
        sip_trunk_name: setting?.sip_trunk_name ?? '',
        extension_start: setting?.extension_start ?? 100,
        extension_end: setting?.extension_end ?? 199,
        max_extensions: setting?.max_extensions ?? 50,
        call_report_api_url: setting?.call_report_api_url ?? '',
        call_report_api_key: setting?.call_report_api_key ?? '',
        is_enabled: Boolean(setting?.is_enabled),
        ringtone: setting?.ringtone ?? 'ringtone.mp3',
    });

    const toggleRingtonePreview = (): void => {
        if (isPlaying) {
            if (audioRef.current) {
                audioRef.current.pause();
                audioRef.current.currentTime = 0;
                audioRef.current = null;
            }
            setIsPlaying(false);
            return;
        }

        if (audioRef.current) {
            audioRef.current.pause();
            audioRef.current = null;
        }

        const selected = data.ringtone || 'ringtone.mp3';
        const ringtoneUrl = `${route('pbx.ringtone')}?file=${encodeURIComponent(selected)}&_t=${Date.now()}`;

        const audio = new Audio(ringtoneUrl);
        audioRef.current = audio;

        audio.onended = () => {
            setIsPlaying(false);
            audioRef.current = null;
        };

        audio.onerror = (e) => {
            console.error('Failed to play ringtone preview:', e);
            setIsPlaying(false);
            audioRef.current = null;
        };

        audio
            .play()
            .then(() => {
                setIsPlaying(true);
            })
            .catch((err) => {
                console.error('Ringtone play error:', err);
                setIsPlaying(false);
                audioRef.current = null;
            });
    };

    const handleSubmit = (event: FormEvent<HTMLFormElement>): void => {
        event.preventDefault();

        post(route('pbx.settings.store'), {
            preserveScroll: true,
        });
    };

    const getError = (
        field: keyof PbxSettingsForm,
    ): string | undefined => {
        return errors[field];
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Dashboard') }]}
            pageTitle={t('Setup PBX(Call-Center)')}
        >
            <Head title={t('PBX Settings')} />

            <div className="space-y-6">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0">
                        <div>
                            <CardTitle>{t('PBX Settings')}</CardTitle>

                            <p className="mt-1 text-sm text-muted-foreground">
                                {t(
                                    'Configure your PBX, AMI, WebRTC and extension settings.',
                                )}
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <Label
                                htmlFor="pbx-enabled"
                                className="cursor-pointer"
                            >
                                {t('Enable PBX')}
                            </Label>

                            <Switch
                                id="pbx-enabled"
                                checked={data.is_enabled}
                                onCheckedChange={(checked) =>
                                    setData('is_enabled', checked)
                                }
                            />
                        </div>
                    </CardHeader>

                    <CardContent>
                        <form
                            onSubmit={handleSubmit}
                            className="space-y-6"
                        >
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                                <FormField
                                    id="pbx_name"
                                    label={t('PBX Name')}
                                    error={getError('pbx_name')}
                                    required
                                >
                                    <Input
                                        id="pbx_name"
                                        value={data.pbx_name}
                                        onChange={(event) =>
                                            setData(
                                                'pbx_name',
                                                event.target.value,
                                            )
                                        }
                                        placeholder={t(
                                            'Example: Main Office PBX',
                                        )}
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="pbx_host"
                                    label={t('PBX Host')}
                                    error={getError('pbx_host')}
                                >
                                    <Input
                                        id="pbx_host"
                                        value={data.pbx_host}
                                        onChange={(event) =>
                                            setData(
                                                'pbx_host',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="pbx.example.com"
                                    />
                                </FormField>

                                <FormField
                                    id="ami_host"
                                    label={t('AMI Host')}
                                    error={getError('ami_host')}
                                    required
                                >
                                    <Input
                                        id="ami_host"
                                        value={data.ami_host}
                                        onChange={(event) =>
                                            setData(
                                                'ami_host',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="127.0.0.1"
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="ami_port"
                                    label={t('AMI Port')}
                                    error={getError('ami_port')}
                                    required
                                >
                                    <Input
                                        id="ami_port"
                                        type="number"
                                        min={1}
                                        max={65535}
                                        value={data.ami_port}
                                        onChange={(event) =>
                                            setData(
                                                'ami_port',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="ami_username"
                                    label={t('AMI Username')}
                                    error={getError('ami_username')}
                                    required
                                >
                                    <Input
                                        id="ami_username"
                                        value={data.ami_username}
                                        onChange={(event) =>
                                            setData(
                                                'ami_username',
                                                event.target.value,
                                            )
                                        }
                                        autoComplete="username"
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="ami_password"
                                    label={t('AMI Password')}
                                    error={getError('ami_password')}
                                >
                                    <Input
                                        id="ami_password"
                                        type="password"
                                        value={data.ami_password}
                                        onChange={(event) =>
                                            setData(
                                                'ami_password',
                                                event.target.value,
                                            )
                                        }
                                        placeholder={
                                            setting
                                                ? t(
                                                    'Leave blank to keep current password',
                                                )
                                                : t('Enter AMI password')
                                        }
                                        autoComplete="new-password"
                                    />
                                </FormField>

                                <FormField
                                    id="sip_domain"
                                    label={t('SIP Domain')}
                                    error={getError('sip_domain')}
                                    required
                                >
                                    <Input
                                        id="sip_domain"
                                        value={data.sip_domain}
                                        onChange={(event) =>
                                            setData(
                                                'sip_domain',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="pbx.example.com"
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="websocket_url"
                                    label={t('WebSocket URL')}
                                    error={getError('websocket_url')}
                                    required
                                >
                                    <Input
                                        id="websocket_url"
                                        value={data.websocket_url}
                                        onChange={(event) =>
                                            setData(
                                                'websocket_url',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="wss://pbx.example.com:8089/ws"
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="stun_server"
                                    label={t('STUN Server')}
                                    error={getError('stun_server')}
                                >
                                    <Input
                                        id="stun_server"
                                        value={data.stun_server}
                                        onChange={(event) =>
                                            setData(
                                                'stun_server',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="stun:stun.l.google.com:19302"
                                    />
                                </FormField>

                                <FormField
                                    id="sip_trunk_name"
                                    label={t('SIP Trunk Name')}
                                    error={getError('sip_trunk_name')}
                                >
                                    <Input
                                        id="sip_trunk_name"
                                        value={data.sip_trunk_name}
                                        onChange={(event) =>
                                            setData(
                                                'sip_trunk_name',
                                                event.target.value,
                                            )
                                        }
                                        placeholder={t(
                                            'Example: primary-trunk',
                                        )}
                                    />
                                </FormField>

                                <FormField
                                    id="extension_start"
                                    label={t('Extension Start')}
                                    error={getError('extension_start')}
                                    required
                                >
                                    <Input
                                        id="extension_start"
                                        type="number"
                                        min={1}
                                        value={data.extension_start}
                                        onChange={(event) =>
                                            setData(
                                                'extension_start',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="extension_end"
                                    label={t('Extension End')}
                                    error={getError('extension_end')}
                                    required
                                >
                                    <Input
                                        id="extension_end"
                                        type="number"
                                        min={1}
                                        value={data.extension_end}
                                        onChange={(event) =>
                                            setData(
                                                'extension_end',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="max_extensions"
                                    label={t('Max Extensions')}
                                    error={getError('max_extensions')}
                                    required
                                >
                                    <Input
                                        id="max_extensions"
                                        type="number"
                                        min={1}
                                        value={data.max_extensions}
                                        onChange={(event) =>
                                            setData(
                                                'max_extensions',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                </FormField>

                                <FormField
                                    id="ringtone"
                                    label={t('Incoming Call Ringtone')}
                                    error={getError('ringtone')}
                                >
                                    <div className="flex items-center gap-2">
                                        <div className="flex-1">
                                            <Select
                                                value={data.ringtone}
                                                onValueChange={(value) =>
                                                    setData('ringtone', value)
                                                }
                                            >
                                                <SelectTrigger id="ringtone">
                                                    <SelectValue placeholder={t('Select Ringtone')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {availableRingtones.map((item) => (
                                                        <SelectItem key={item.value} value={item.value}>
                                                            {item.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            onClick={toggleRingtonePreview}
                                            title={isPlaying ? t('Stop Ringtone') : t('Test Ringtone')}
                                        >
                                            {isPlaying ? (
                                                <Square className="h-4 w-4 text-destructive fill-current" />
                                            ) : (
                                                <Volume2 className="h-4 w-4" />
                                            )}
                                        </Button>
                                    </div>
                                </FormField>

                                <FormField
                                    id="call_report_api_url"
                                    label={t('Call Report API URL')}
                                    error={getError('call_report_api_url')}
                                >
                                    <Input
                                        id="call_report_api_url"
                                        type="text"
                                        value={data.call_report_api_url}
                                        onChange={(event) =>
                                            setData(
                                                'call_report_api_url',
                                                event.target.value,
                                            )
                                        }
                                    />
                                </FormField>

                                <FormField
                                    id="call_report_api_key"
                                    label={t('Call Report API Key')}
                                    error={getError('call_report_api_key')}
                                >
                                    <Input
                                        id="call_report_api_key"
                                        type="text"
                                        value={data.call_report_api_key}
                                        onChange={(event) =>
                                            setData(
                                                'call_report_api_key',
                                                event.target.value,
                                            )
                                        }
                                    />
                                </FormField>
                            </div>

                            <div className="flex items-center justify-end gap-3 border-t pt-5">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                >
                                    {processing ? (
                                        <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                                    ) : (
                                        <Save className="mr-2 h-4 w-4" />
                                    )}

                                    {processing
                                        ? t('Saving...')
                                        : t('Save Settings')}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}

interface FormFieldProps {
    id: string;
    label: string;
    error?: string;
    required?: boolean;
    children: React.ReactNode;
}

function FormField({
    id,
    label,
    error,
    children,
}: FormFieldProps) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>
                {label}
            </Label>

            {children}

            {error && (
                <p className="text-sm text-destructive">
                    {error}
                </p>
            )}
        </div>
    );
}