import { FormEventHandler, useEffect } from "react";
import AuthLayout from "@/layouts/auth-layout";
import { Head, Link, useForm } from "@inertiajs/react";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import InputError from "@/components/ui/input-error";
import { Checkbox } from "@/components/ui/checkbox";

import { useTranslation } from 'react-i18next';
import { useFormFields } from '@/hooks/useFormFields';
import { usePageButtons } from '@/hooks/usePageButtons';

export default function Login({
    status,
    canResetPassword,
    enableRegistration,
}: {
    status?: string;
    canResetPassword: boolean;
    enableRegistration?: boolean;
}) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
        recaptcha_token: null,
    });

    const formFields = useFormFields('getReCaptchFields', data, setData, errors, 'create', t);
    const loginButtons = usePageButtons('getLoginButtons', { t, isLoading: processing });

    useEffect(() => {
        return () => {
            reset("password");
        };
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route("login"));
    };

    return (
        <AuthLayout
            title={t('Log in to your account')}
            description={t('Enter your email and password below to log in')}
        >
            <Head title={t('Log in')} />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="flex flex-col gap-6">
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('Email Address')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="email"
                            placeholder="email@example.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center">
                            <Label htmlFor="password">{t('Password')}</Label>
                            {canResetPassword && (
                                <Link
                                    href={route('password.request')}
                                    className="ml-auto text-sm text-primary hover:underline"
                                    tabIndex={5}
                                >
                                    {t('Forgot password?')}
                                </Link>
                            )}
                        </div>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            placeholder={t('Password')}
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="flex items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            checked={data.remember}
                            onCheckedChange={(checked) => setData('remember', !!checked)}
                            tabIndex={3}
                        />
                        <Label htmlFor="remember">{t('Remember me')}</Label>
                    </div>

                    {formFields.map((field) => (
                        <div key={field.id}>
                            {field.component}
                        </div>
                    ))}

                    <Button
                        type="submit"
                        className="mt-4 w-full"
                        tabIndex={4}
                        disabled={processing}
                        data-test="login-button"
                    >
                        {processing ? t('Logging in...') : t('Log in')}
                    </Button>

                    {loginButtons.length > 0 && (
                        <div className="space-y-2">
                            <div className="relative">
                                <div className="absolute inset-0 flex items-center">
                                    <span className="w-full border-t" />
                                </div>
                                <div className="relative flex justify-center text-xs uppercase">
                                    <span className="bg-background px-2 text-muted-foreground">{t('Or continue with')}</span>
                                </div>
                            </div>
                            {loginButtons.map((button) => (
                                <div key={button.id}>
                                    {button.component}
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {enableRegistration && (
                    <div className="text-center text-sm text-muted-foreground">
                        {t("Don't have an account?")}{' '}
                        <Link href={route('register')} tabIndex={6} className="text-primary hover:underline">
                            {t('Sign up')}
                        </Link>
                    </div>
                )}
            </form>
        </AuthLayout>
    );
}
