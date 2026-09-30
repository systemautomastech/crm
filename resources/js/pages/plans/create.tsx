import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import PlanForm from './form';

interface Module {
    module: string;
    alias: string;
    image: string;
}

interface UserSubscriptionInfo {
    is_superadmin: boolean;
    active_plan_id?: number;
    available_modules_count: number;
}

interface Props {
    activeModules: Module[];
    userSubscriptionInfo?: UserSubscriptionInfo;
}

export default function CreatePlan({ activeModules, userSubscriptionInfo }: Props) {
    const { t } = useTranslation();

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Subscription Setting'), url: route('plans.index') },
                { label: t('Create Plan') }
            ]}
            pageTitle={t('Create Plan')}
            pageActions={
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => router.visit(route('plans.index'))}
                >
                    <ArrowLeft className="h-4 w-4" />
                    {t('Back')}
                </Button>
            }
        >
            <Head title={t('Create Plan')} />

            <Card>
                <CardContent className="pt-6">
                    <PlanForm activeModules={activeModules} userSubscriptionInfo={userSubscriptionInfo} />
                </CardContent>
            </Card>
        </AuthenticatedLayout>
    );
}
