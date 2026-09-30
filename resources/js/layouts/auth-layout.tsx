import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';
import { BrandProvider } from '@/contexts/brand-context';

export default function AuthLayout({
    children,
    title,
    description,
    ...props
}: {
    children: React.ReactNode;
    title: string;
    description: string;
}) {
    return (
        <BrandProvider>
            <AuthLayoutTemplate title={title} description={description} {...props}>
                {children}
            </AuthLayoutTemplate>
        </BrandProvider>
    );
}