import InputError from '@/components/ui/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import AuthLayout from '@/layouts/auth-layout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import { useTranslation } from 'react-i18next';

export default function ConfirmPassword() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.confirm'));
    };

    return (
        <AuthLayout
            title={t('Confirm your password')}
            description={t('This is a secure area of the application. Please confirm your password before continuing.')}
        >
            <Head title={t('Confirm password')} />

            <form onSubmit={submit} className="space-y-6">
                <div className="grid gap-2">
                    <Label htmlFor="password">{t('Password')}</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        placeholder={t('Password')}
                        autoComplete="current-password"
                        autoFocus
                    />
                    <InputError message={errors.password} />
                </div>

                <div className="flex items-center">
                    <Button
                        type="submit"
                        className="w-full"
                        disabled={processing}
                        data-test="confirm-password-button"
                    >
                        {processing ? t('Confirming password...') : t('Confirm password')}
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
