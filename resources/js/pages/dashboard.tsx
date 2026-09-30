import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Head, usePage } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { getImagePath } from "@/utils/helpers";
import { User } from "lucide-react";

export default function Dashboard() {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;

    return (
        <AuthenticatedLayout
            header={t('Dashboard')}
        >
            <Head title={t('Dashboard')} />

            <div className="flex items-center justify-center h-full">
                <div className="text-center">
                    <div className="mb-6 flex justify-center">
                        {auth.user?.avatar ? (
                            <img src={getImagePath(auth.user.avatar)} alt={auth.user.name} className="h-24 w-24 rounded-full" />
                        ) : (
                            <div className="h-24 w-24 rounded-full bg-muted flex items-center justify-center">
                                <User className="h-12 w-12 text-muted-foreground" />
                            </div>
                        )}
                    </div>
                    <h1 className="text-4xl font-bold mb-2">{t('Welcome')}, {auth.user?.name}!</h1>
                    <p className="text-muted-foreground text-lg">{auth.user?.email}</p>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
