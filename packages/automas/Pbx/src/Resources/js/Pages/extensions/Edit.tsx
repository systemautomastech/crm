import type { FormEvent } from 'react';
import {
    Head,
    Link,
    useForm,
    usePage,
} from '@inertiajs/react';
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

interface PbxExtension {
    id: number;
    user_id: number;
    extension: string;
    caller_id?: string | null;
    is_active: boolean | number;
}

interface AuthUser {
    permissions?: string[];
}

interface EditProps {
    extension: PbxExtension;
    users: ExtensionUser[];
    setting: PbxSetting;
    assignedUserIds: number[];
    assignedExtensions: string[];
    auth?: {
        user?: AuthUser;
    };
    [key: string]: any;
}

export default function Edit({
    extension,
    users,
    setting,
    assignedUserIds,
    assignedExtensions,
}: EditProps) {
    const { t } = useTranslation();

    const { auth } = usePage<EditProps>().props;

    const permissions = auth?.user?.permissions ?? [];

    const canViewAllExtensions =
        permissions.includes('view all extensions') ||
        permissions.length === 0;

    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm<ExtensionFormData>({
        user_id: extension.user_id,
        extension: String(extension.extension),
        caller_id: extension.caller_id ?? '',
        sip_secret: '',
        is_active: Boolean(extension.is_active),
    });

    const handleSubmit = (
        event: FormEvent<HTMLFormElement>,
    ): void => {
        event.preventDefault();

        put(
            route(
                'pbx.extensions.update',
                extension.id,
            ),
            {
                preserveScroll: true,
            },
        );
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
                    href: route(
                        'pbx.extensions.index',
                    ),
                },
                {
                    label: t('Edit Extension'),
                },
            ]}
            pageTitle={t('Edit Extension')}
        >
            <Head title={t('Edit Extension')} />

            <form
                onSubmit={handleSubmit}
                className="space-y-6"
            >
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>
                                {t(
                                    'Setup User with Extension',
                                )}
                            </CardTitle>

                            <p className="mt-1 text-sm text-muted-foreground">
                                {t(
                                    'Update the PBX extension configuration.',
                                )}
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <Label htmlFor="extension-active">
                                {t('Active')}
                            </Label>

                            <Switch
                                id="extension-active"
                                checked={
                                    data.is_active
                                }
                                onCheckedChange={(
                                    checked,
                                ) =>
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
                            isEditing
                            canEditUser={
                                canViewAllExtensions
                            }
                        />

                        <div className="flex justify-end gap-3 border-t pt-6">
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
                                    <>
                                        <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                                        {t(
                                            'Updating...',
                                        )}
                                    </>
                                ) : (
                                    <>
                                        <Save className="mr-2 h-4 w-4" />
                                        {t('Update')}
                                    </>
                                )}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </AuthenticatedLayout>
    );
}