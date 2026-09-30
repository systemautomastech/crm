import InputError from '@/components/ui/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AuthLayout from '@/layouts/auth-layout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { usePageButtons } from '@/hooks/usePageButtons';
import { useFormFields } from '@/hooks/useFormFields';

export default function Register() {
    const { t } = useTranslation();
    const { adminAllSetting } = usePage().props as any;
    const [agreedToTerms, setAgreedToTerms] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        recaptcha_token: null,
    });

    const formFields = useFormFields('getReCaptchFields', data, setData, errors, 'create', t);
    const registerButtons = usePageButtons('getLoginButtons', { t, isLoading: processing });

    useEffect(() => {
        return () => {
            reset('password', 'password_confirmation');
        };
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'));
    };

    return (
        <AuthLayout
            title={t('Create an Account')}
            description={t('Enter your details below to create your account')}
        >
            <Head title={t('Register')} />
            <form onSubmit={submit} className="flex flex-col gap-6">
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="name">{t('Name')}</Label>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="name"
                            placeholder={t('Full name')}
                        />
                        <InputError
                            message={errors.name}
                            className="mt-2"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('Email Address')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            tabIndex={2}
                            autoComplete="email"
                            placeholder="email@example.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">{t('Password')}</Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                            tabIndex={3}
                            autoComplete="new-password"
                            placeholder={t('Password')}
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            {t('Confirm Password')}
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            required
                            tabIndex={4}
                            autoComplete="new-password"
                            placeholder={t('Confirm password')}
                        />
                        <InputError
                            message={errors.password_confirmation}
                        />
                    </div>

                    {formFields.map((field) => (
                        <div key={field.id}>
                            {field.component}
                        </div>
                    ))}

                    {adminAllSetting?.termsConditionsUrl && (
                        <div className="flex items-start space-x-2">
                            <Checkbox
                                id="terms"
                                checked={agreedToTerms}
                                onCheckedChange={(checked) => setAgreedToTerms(checked as boolean)}
                                tabIndex={5}
                            />
                            <label
                                htmlFor="terms"
                                className="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                {t('I agree to the')}{' '}
                                <a
                                    href={adminAllSetting.termsConditionsUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-primary hover:underline"
                                >
                                    {t('Terms and Conditions')}
                                </a>
                            </label>
                        </div>
                    )}

                    <Button
                        type="submit"
                        className="mt-2 w-full"
                        tabIndex={6}
                        disabled={processing || (adminAllSetting?.termsConditionsUrl && !agreedToTerms)}
                        data-test="register-user-button"
                    >
                        {processing ? t('Creating account...') : t('Create an account')}
                    </Button>

                    {registerButtons.length > 0 && (
                        <div className="space-y-2">
                            <div className="relative">
                                <div className="absolute inset-0 flex items-center">
                                    <span className="w-full border-t" />
                                </div>
                                <div className="relative flex justify-center text-xs uppercase">
                                    <span className="bg-background px-2 text-muted-foreground">{t('Or continue with')}</span>
                                </div>
                            </div>
                            {registerButtons.map((button) => (
                                <div key={button.id}>
                                    {button.component}
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="text-center text-sm text-muted-foreground">
                    {t('Already have an account?')}{' '}
                    <Link href={route('login')} tabIndex={7} className="text-primary hover:underline">
                        {t('Log in')}
                    </Link>
                </div>
            </form>
        </AuthLayout>
    );
}
