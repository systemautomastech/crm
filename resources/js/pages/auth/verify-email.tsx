import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { useTranslation } from 'react-i18next';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useTranslation();
    const { post, processing, errors } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <AuthLayout
            title={t('Verify email')}
            description={t('Please verify your email address by clicking on the link we just emailed to you.')}
        >
            <Head title={t('Email verification')} />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {t('A new verification link has been sent to the email address you provided during registration.')}
                </div>
            )}

            {(errors as any).email && (
                <div className="mb-4 text-center text-sm font-medium text-red-600">
                    {(errors as any).email}
                </div>
            )}

            <form onSubmit={submit} className="space-y-6 text-center">
                <Button
                    type="submit"
                    disabled={processing}
                    variant="secondary"
                >
                {processing ? t('Resending verification email...') : t('Resend verification email')}
                </Button>

            </form>
                <Link
                    href={route('logout')}
                    method="post"
                    as={"button"}
                    className="mx-auto block text-sm text-primary hover:underline"
                >
                    {t('Log out')}
                </Link>
        </AuthLayout>
    );
}
