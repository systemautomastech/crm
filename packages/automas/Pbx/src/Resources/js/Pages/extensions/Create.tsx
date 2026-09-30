import type { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { LoaderCircle, Save } from 'lucide-react';

import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

import ExtensionForm, {
    type ExtensionFormData,
    type ExtensionUser,
    type PbxSetting,
} from './components/ExtensionForm';

interface CreateProps {
    users: ExtensionUser[];
    setting: PbxSetting;
    assignedUserIds: number[];
    assignedExtensions: string[];
}

export default function Create({
    users,
    setting,
    assignedUserIds,
    assignedExtensions,
}: CreateProps) {
    const { t } = useTranslation();

    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm<ExtensionFormData>({
        user_id: '',
        extension: '',
        caller_id: '',
        sip_secret: '',
        is_active: true,
    });

    const handleSubmit = (
        event: FormEvent<HTMLFormElement>,
    ): void => {
        event.preventDefault();

        post(route('pbx.extensions.store'), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {
                    label: t('Dashboard'),
                    href: route('dashboard'),
                },
                {
                    label: t('Extensions'),
                    href: route('pbx.extensions.index'),
                },
                {
                    label: t('Create Extension'),
                },
            ]}
            pageTitle={t('Create Extension')}
        >
            <Head title={t('Create Extension')} />

            <form
                onSubmit={handleSubmit}
                noValidate
                className="space-y-6"
            >
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0">
                        <div>
                            <CardTitle>
                                {t('Setup User with Extension')}
                            </CardTitle>

                            <p className="mt-1 text-sm text-muted-foreground">
                                {t(
                                    'Assign a PBX extension and SIP credentials to a user.',
                                )}
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <Label
                                htmlFor="extension-active"
                                className="cursor-pointer"
                            >
                                {t('Active')}
                            </Label>

                            <Switch
                                id="extension-active"
                                checked={data.is_active}
                                onCheckedChange={(checked) =>
                                    setData(
                                        'is_active',
                                        checked,
                                    )
                                }
                            />
                        </div>
                    </CardHeader>

                    <CardContent className="space-y-6">
                        <ExtensionForm
                            data={data}
                            setData={setData}
                            errors={errors}
                            users={users}
                            setting={setting}
                            assignedUserIds={
                                assignedUserIds
                            }
                            assignedExtensions={
                                assignedExtensions
                            }
                        />

                        <div className="flex items-center justify-end gap-3 border-t pt-6">
                            <Button
                                type="button"
                                variant="outline"
                                asChild
                            >
                                <Link
                                    href={route(
                                        'pbx.extensions.index',
                                    )}
                                >
                                    {t('Cancel')}
                                </Link>
                            </Button>

                            <Button
                                type="submit"
                                disabled={processing}
                            >
                                {processing ? (
                                    <LoaderCircle className="mr-2 size-4 animate-spin" />
                                ) : (
                                    <Save className="mr-2 size-4" />
                                )}

                                {processing
                                    ? t('Creating...')
                                    : t('Create Extension')}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </AuthenticatedLayout>
    );
}