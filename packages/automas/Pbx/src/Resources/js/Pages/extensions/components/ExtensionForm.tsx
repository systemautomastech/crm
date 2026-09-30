import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export interface ExtensionUser {
    id: number;
    name: string;
    email?: string | null;
}

export interface PbxSetting {
    id?: number;
    extension_start: number;
    extension_end: number;
    max_extensions?: number;
}

export interface ExtensionFormData {
    user_id: number | string;
    extension: string;
    caller_id: string;
    sip_secret: string;
    is_active: boolean;
    canEditUser?: boolean;
}

interface ExtensionFormErrors {
    user_id?: string;
    extension?: string;
    caller_id?: string;
    sip_secret?: string;
    is_active?: string;
}

interface ExtensionFormProps {
    data: ExtensionFormData;
    setData: <K extends keyof ExtensionFormData>(
        key: K,
        value: ExtensionFormData[K],
    ) => void;
    errors: ExtensionFormErrors;
    users: ExtensionUser[];
    setting: PbxSetting;
    assignedUserIds?: number[];
    assignedExtensions?: string[];
    isEditing?: boolean;
}

interface FieldErrorProps {
    message?: string;
}

function FieldError({ message }: FieldErrorProps) {
    if (!message) {
        return null;
    }

    return (
        <p className="mt-1 text-sm text-destructive">
            {message}
        </p>
    );
}

export default function ExtensionForm({
    data,
    setData,
    errors,
    users,
    setting,
    assignedUserIds = [],
    assignedExtensions = [],
    isEditing = false,
    canEditUser = true,
}: ExtensionFormProps) {
    const { t } = useTranslation();

    const extensionOptions = useMemo(() => {
        const options: string[] = [];

        const assigned = new Set(
            assignedExtensions.map((extension) =>
                String(extension),
            ),
        );

        for (
            let extension = Number(
                setting.extension_start,
            );
            extension <=
            Number(setting.extension_end);
            extension++
        ) {
            const value = String(extension);

            if (
                !assigned.has(value) ||
                data.extension === value
            ) {
                options.push(value);
            }
        }

        return options;
    }, [
        setting.extension_start,
        setting.extension_end,
        assignedExtensions,
        data.extension,
    ]);

    const assignedUsers = useMemo(
        () =>
            new Set(
                assignedUserIds.map((id) =>
                    Number(id),
                ),
            ),
        [assignedUserIds],
    );

    return (
        <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-12">
            <div className="space-y-2 xl:col-span-4">
                <Label htmlFor="user_id">
                    {t('User')}
                    <span className="ml-1 text-destructive">
                        *
                    </span>
                </Label>

                <Select
                    value={
                        data.user_id
                            ? String(data.user_id)
                            : ''
                    }
                    onValueChange={(value) =>
                        setData(
                            'user_id',
                            Number(value),
                        )
                    }
                    disabled={
                        isEditing && !canEditUser
                    }
                >
                    <SelectTrigger
                        id="user_id"
                        className={
                            errors.user_id
                                ? 'border-destructive focus-visible:ring-destructive'
                                : ''
                        }
                    >
                        <SelectValue
                            placeholder={t(
                                'Select User',
                            )}
                        />
                    </SelectTrigger>

                    <SelectContent searchable>
                        {users.map((user) => {
                            const disabled =
                                assignedUsers.has(
                                    Number(user.id),
                                );

                            return (
                                <SelectItem
                                    key={user.id}
                                    value={String(
                                        user.id,
                                    )}
                                    disabled={disabled}
                                >
                                    {user.name} -
                                    {user.email && (
                                        <span className="text-xs text-muted-foreground"> {user.email}</span>
                                    )}
                                </SelectItem>
                            );
                        })}
                    </SelectContent>
                </Select>

                <FieldError
                    message={errors.user_id}
                />
            </div>

            <div className="space-y-2 xl:col-span-2">
                <Label htmlFor="extension">
                    {t('Extension')}
                    <span className="ml-1 text-destructive">
                        *
                    </span>
                </Label>

                <Select
                    value={
                        data.extension || undefined
                    }
                    onValueChange={(value) =>
                        setData(
                            'extension',
                            value,
                        )
                    }
                >
                    <SelectTrigger
                        id="extension"
                        className={
                            errors.extension
                                ? 'border-destructive focus-visible:ring-destructive'
                                : ''
                        }
                    >
                        <SelectValue
                            placeholder={t(
                                'Select Extension',
                            )}
                        />
                    </SelectTrigger>

                    <SelectContent searchable>
                        {extensionOptions.map(
                            (extension) => (
                                <SelectItem
                                    key={extension}
                                    value={extension}
                                >
                                    {extension}
                                </SelectItem>
                            ),
                        )}
                    </SelectContent>
                </Select>

                <FieldError
                    message={errors.extension}
                />

                <p className="text-xs text-muted-foreground">
                    {t('Allowed range')}:{' '}
                    {setting.extension_start} -{' '}
                    {setting.extension_end}
                </p>
            </div>

            <div className="space-y-2 xl:col-span-3">
                <Label htmlFor="caller_id">
                    {t('Caller ID')}
                </Label>

                <Input
                    id="caller_id"
                    type="text"
                    value={data.caller_id}
                    onChange={(event) =>
                        setData(
                            'caller_id',
                            event.target.value,
                        )
                    }
                    placeholder={t('Optional')}
                    maxLength={50}
                    className={
                        errors.caller_id
                            ? 'border-destructive focus-visible:ring-destructive'
                            : ''
                    }
                />

                <FieldError
                    message={errors.caller_id}
                />
            </div>

            <div className="space-y-2 xl:col-span-3">
                <Label htmlFor="sip_secret">
                    {t('Password')}
                </Label>

                <Input
                    id="sip_secret"
                    type="text"
                    value={data.sip_secret}
                    onChange={(event) =>
                        setData(
                            'sip_secret',
                            event.target.value,
                        )
                    }
                    placeholder={
                        isEditing
                            ? t(
                                'Leave blank to keep current',
                            )
                            : t(
                                'Auto-generated if empty',
                            )
                    }
                    minLength={6}
                    maxLength={100}
                    autoComplete="new-password"
                    className={
                        errors.sip_secret
                            ? 'border-destructive focus-visible:ring-destructive'
                            : ''
                    }
                />

                <FieldError
                    message={
                        errors.sip_secret
                    }
                />
            </div>
        </div>
    );
}