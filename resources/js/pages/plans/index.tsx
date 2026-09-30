import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { toast } from 'sonner';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Check, Plus, Edit, Trash2, X, Package, MoreVertical, Clock } from 'lucide-react';
import { Switch } from '@/components/ui/switch';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ModuleCard } from '@/components/ui/module-card';
import { SearchInput } from '@/components/ui/search-input';
import SubscriptionLayout from './subscription-layout';
import { formatDate, formatAdminCurrency, formatStorage } from '@/utils/helpers';

interface Plan {
    id: number;
    name: string;
    description: string;
    number_of_users: number;
    custom_plan: boolean;
    status: boolean;
    free_plan: boolean;
    modules: string[];
    package_price_yearly: number;
    package_price_monthly: number;
    price_per_user_monthly: number;
    price_per_user_yearly: number;
    storage_limit: number;
    price_per_storage_monthly: number;
    price_per_storage_yearly: number;
    is_most_popular?: boolean;
    sort_order?: number;

    trial: boolean;
    trial_days: number;
    orders_count?: number;
    creator?: {
        name: string;
    };
}

interface Props {
    plans: Plan[];
    canCreate: boolean;
    activeModules: { module: string; alias: string; image: string; monthly_price: number; yearly_price: number; }[];
    bankTransferEnabled: string;
    bankTransferInstructions: string;
    userTrialInfo?: {
        is_trial_done: number;
    };
    createPackageEnabled: boolean;
    customDesignPackageEnabled: boolean;
}

export default function PlansIndex({ plans, canCreate, activeModules, bankTransferEnabled, bankTransferInstructions, userTrialInfo, createPackageEnabled, customDesignPackageEnabled }: Props) {
    const { t } = useTranslation();
    const [subscriptionType, setSubscriptionType] = useState<'pre-package' | 'usage'>('pre-package');
    const { auth } = usePage().props as any;
    const isCompanyUser = !auth.user?.roles?.includes('superadmin');
    const [localCreatePackage, setLocalCreatePackage] = useState(createPackageEnabled);
    const [localCustomDesignPackage, setLocalCustomDesignPackage] = useState(customDesignPackageEnabled);

    useEffect(() => {
        if (!localCreatePackage && localCustomDesignPackage) {
            setSubscriptionType('usage');
        } else if (localCreatePackage && !localCustomDesignPackage) {
            setSubscriptionType('pre-package');
        }
    }, [localCreatePackage, localCustomDesignPackage]);

    useFlashMessages();
    const [editingModule, setEditingModule] = useState<{ module: string; alias: string; } | null>(null);
    const [modulePrice, setModulePrice] = useState({ monthly: 0, yearly: 0 });
    const [editingPlan, setEditingPlan] = useState<Plan | null>(null);
    const [planPricing, setPlanPricing] = useState({
        package_price_monthly: 0,
        package_price_yearly: 0,
        price_per_user_monthly: 0,
        price_per_user_yearly: 0,
        price_per_storage_monthly: 0,
        price_per_storage_yearly: 0
    });
    const [moduleSearch, setModuleSearch] = useState('');
    const [deletingPlan, setDeletingPlan] = useState<Plan | null>(null);
    const [pricingPeriod, setPricingPeriod] = useState<'monthly' | 'yearly'>('monthly');
    const [selectedModules, setSelectedModules] = useState<string[]>([]);
    const [isSavingModulePrice, setIsSavingModulePrice] = useState(false);
    const [isSavingPlanPrice, setIsSavingPlanPrice] = useState(false);

    const handleDelete = (plan: Plan) => {
        setDeletingPlan(plan);
    };

    const confirmDelete = () => {
        if (deletingPlan) {
            router.delete(route('plans.destroy', deletingPlan.id));
            setDeletingPlan(null);
        }
    };

    // Use active modules from AddOns
    const allModules = activeModules.sort((a, b) => a.alias.localeCompare(b.alias));

    const activePlans = plans.filter(plan => {
        const typeFilter = subscriptionType === 'pre-package'
            ? plan.status && !plan.custom_plan
            : plan.status && plan.custom_plan;

        return typeFilter;
    });

    // Find the plan with the highest order count for "Most Popular" badge
    const selectedMostPopularPlan = activePlans.find(plan => plan.is_most_popular);
    const mostPopularPlanId = selectedMostPopularPlan
        ? selectedMostPopularPlan.id
        : activePlans.length > 0
            ? activePlans.reduce((prev, current) =>
                (current.orders_count || 0) > (prev.orders_count || 0) ? current : prev
              ).id
            : null;

    const sortedActivePlans = [...activePlans].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));

    const hasModule = (plan: Plan, moduleObj: { module: string; alias: string; image: string; }) => {
        return Array.isArray(plan.modules) ? plan.modules.includes(moduleObj.module) : false;
    };

    const handleEditModulePrice = (module: { module: string; alias: string; }) => {
        setEditingModule(module);
        setModulePrice({ monthly: 0, yearly: 0 });
    };

    const handleSaveModulePrice = () => {
        setIsSavingModulePrice(true);
        // Here you would make an API call to save the module price
        setTimeout(() => {
            setEditingModule(null);
            setIsSavingModulePrice(false);
        }, 500);
    };

    const handleModulePriceUpdate = (moduleId: string, data: { monthly: number; yearly: number; name?: string; imageFile?: File | null }) => {
        const formData = new FormData();
        formData.append('module', moduleId);
        formData.append('monthly_price', data.monthly.toString());
        formData.append('yearly_price', data.yearly.toString());

        if (data.name) formData.append('name', data.name);
        if (data.imageFile) formData.append('image', data.imageFile);

        router.post(route('plans.add-on.update-price'), formData, {
            preserveState: true,
            forceFormData: true,
            onSuccess: (page) => {
                // Refresh the page to get updated module prices
                router.reload({ only: ['activeModules'] });
            },
            onError: (errors) => {
                console.error('Failed to update module price:', errors);
            }
        });
    };

    const filteredModules = subscriptionType === 'usage'
        ? allModules.filter(module =>
            module.alias.toLowerCase().includes(moduleSearch.toLowerCase()) ||
            module.module.toLowerCase().includes(moduleSearch.toLowerCase())
          )
        : allModules;

    // Get current plan (find custom plan or create default)
    const currentPlan = activePlans.find(plan => plan.custom_plan) || {
        id: 0,
        name: 'Basic Plan',
        description: 'Basic plan description',
        number_of_users: 0,
        custom_plan: true,
        status: true,
        free_plan: false,
        modules: [],
        package_price_yearly: 0,
        package_price_monthly: 0,
        price_per_user_monthly: 0,
        price_per_user_yearly: 0,
        storage_limit: 0,
        price_per_storage_monthly: 0,
        price_per_storage_yearly: 0,

        trial: false,
        trial_days: 0
    };

    // Convert storage from KB to GB for display
    const currentPlanStorageGB = Math.round(currentPlan.storage_limit / (1024 * 1024));

    const [customPlan, setCustomPlan] = useState({
        maxUsers: currentPlan.number_of_users,
        storageLimit: currentPlanStorageGB,
        currentUsers: currentPlan.number_of_users,
        currentStorage: currentPlanStorageGB,
        couponCode: ''
    });

    const handleModuleSelection = (moduleId: string, selected: boolean) => {
        setSelectedModules(prev =>
            selected
                ? [...prev, moduleId]
                : prev.filter(id => id !== moduleId)
        );
    };

    const handleEditPlanPrice = (plan: Plan) => {
        setEditingPlan(plan);
        setPlanPricing({
            package_price_monthly: plan.package_price_monthly,
            package_price_yearly: plan.package_price_yearly,
            price_per_user_monthly: plan.price_per_user_monthly || 0,
            price_per_user_yearly: plan.price_per_user_yearly || 0,
            price_per_storage_monthly: plan.price_per_storage_monthly || 0,
            price_per_storage_yearly: plan.price_per_storage_yearly || 0
        });
    };

    const handleSavePlanPrice = () => {
        if (editingPlan) {
            setIsSavingPlanPrice(true);
            router.put(route('plans.update', editingPlan.id), planPricing, {
                preserveState: true,
                onSuccess: () => {
                    setEditingPlan(null);
                    setIsSavingPlanPrice(false);
                },
                onError: () => {
                    setIsSavingPlanPrice(false);
                }
            });
        }
    };

    const handleStartTrial = (plan: Plan) => {
        router.post(route('plans.start-trial', plan.id), {}, {
            preserveState: true,
            onSuccess: () => {
                // Reload the page to update sidebar modules and user trial info
                router.reload();
            }
        });
    };

    const handleAssignFreePlan = (plan: Plan) => {
        router.post(route('plans.assign-free', plan.id), {
            duration: pricingPeriod === 'monthly' ? 'Month' : 'Year'
        }, {
            preserveState: true
        });
    };

    const handleCreatePackageToggle = (checked: boolean) => {
        if (!checked && !localCustomDesignPackage) {
            toast.error(t('Both options cannot be disabled at the same time.'));
            return;
        }
        setLocalCreatePackage(checked);
        router.post(route('plans.package-settings.update'), {
            create_package_enabled: checked ? 'on' : 'off',
            custom_design_package_enabled: localCustomDesignPackage ? 'on' : 'off',
        }, { preserveState: true, preserveScroll: true });
        if (checked) {
            setSubscriptionType('pre-package');
        } else if (subscriptionType === 'pre-package') {
            setSubscriptionType('usage');
        }
    };

    const handleCustomDesignPackageToggle = (checked: boolean) => {
        if (!checked && !localCreatePackage) {
            toast.error(t('Both options cannot be disabled at the same time.'));
            return;
        }
        setLocalCustomDesignPackage(checked);
        router.post(route('plans.package-settings.update'), {
            create_package_enabled: localCreatePackage ? 'on' : 'off',
            custom_design_package_enabled: checked ? 'on' : 'off',
        }, { preserveState: true, preserveScroll: true });
        if (checked) {
            setSubscriptionType('usage');
        } else if (subscriptionType === 'usage') {
            setSubscriptionType('pre-package');
        }
    };

    const canStartTrial = (plan: Plan) => {
        return isCompanyUser &&
               plan.trial &&
               plan.trial_days > 0 &&
               auth.user?.is_trial_done === 0;
    };

    const isCurrentlySubscribed = (plan: Plan) => {
        if (!isCompanyUser || !auth.user?.active_plan) return false;
        // Check if it's a paid plan (not trial)
        const isTrialActive = auth.user?.is_trial_done === 2;
        return auth.user.active_plan === plan.id &&
               auth.user.plan_expire_date &&
               new Date(auth.user.plan_expire_date) > new Date() &&
               !isTrialActive; // Exclude trial from "currently subscribed"
    };



    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Subscription Setting') }]}
            pageTitle={t('Subscription Setting')}
            pageActions={
                !isCompanyUser ? (
                    <div className="flex items-center gap-2">
                        <div className="flex items-center gap-2 py-1.5 rounded-lg">
                            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">{t('Create Package')}</span>
                            <Switch
                                checked={localCreatePackage}
                                onCheckedChange={handleCreatePackageToggle}
                            />
                        </div>
                        <div className="flex items-center gap-2 py-1.5 rounded-lg">
                            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">{t('Custom Design Package')}</span>
                            <Switch
                                checked={localCustomDesignPackage}
                                onCheckedChange={handleCustomDesignPackageToggle}
                            />
                        </div>
                    </div>
                ) : null
            }
        >
            <Head title={t('Plans')} />

            <div className="space-y-6">
                {/* Subscription Type Toggle */}
                <div className="flex items-center">
                    <div className="flex-1" />
                    <div className="flex items-center space-x-6">
                        {localCreatePackage && localCustomDesignPackage && (
                            <div className="bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
                                <div className="flex items-center">
                                    <button
                                        onClick={() => setSubscriptionType('pre-package')}
                                        className={`px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 ${
                                            subscriptionType === 'pre-package'
                                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                                        }`}
                                    >
                                        {t('Pre Package Subscription')}
                                    </button>
                                    <button
                                        onClick={() => setSubscriptionType('usage')}
                                        className={`px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 ${
                                            subscriptionType === 'usage'
                                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                                        }`}
                                    >
                                        {t('Usage Subscription')}
                                    </button>
                                </div>
                            </div>
                        )}

                        <div className="bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
                            <div className="flex items-center">
                                <button
                                    onClick={() => setPricingPeriod('monthly')}
                                    className={`px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 ${
                                        pricingPeriod === 'monthly'
                                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                                    }`}
                                >
                                    {t('Monthly')}
                                </button>
                                <button
                                    onClick={() => setPricingPeriod('yearly')}
                                    className={`px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 ${
                                        pricingPeriod === 'yearly'
                                            ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                                    }`}
                                >
                                    {t('Yearly')}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div className="flex-1 flex justify-end">
                        {subscriptionType === 'pre-package' && localCreatePackage && canCreate && (
                            <TooltipProvider>
                                <Tooltip delayDuration={0}>
                                    <TooltipTrigger asChild>
                                        <Link href={route('plans.create')}>
                                            <Button size="sm">
                                                <Plus className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>{t('Create')}</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        )}
                    </div>
                </div>

                {/* Plans Content */}
                {subscriptionType === 'usage' ? (
                    /* Usage Subscription Layout */
                    isCompanyUser ? (
                        /* Company User Usage Layout */
                        <SubscriptionLayout
                            plan={currentPlan}
                            allModules={allModules}
                            pricingPeriod={pricingPeriod}
                            onSubscribe={(planData) => {

                            }}
                            bankTransferEnabled={bankTransferEnabled === 'on'}
                            bankTransferInstructions={bankTransferInstructions}
                            planExpireDate={auth.user?.plan_expire_date}
                            trialExpireDate={auth.user?.is_trial_done === 2 ? auth.user?.plan_expire_date : undefined}
                        />
                    ) : activePlans.length > 0 ? (
                            <div className="space-y-6">
                                {activePlans.map((plan) => (
                                    <div key={plan.id} className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                                        <div className="flex items-center justify-between mb-6">
                                            <div>
                                                <h3 className="text-xl font-bold text-gray-900 dark:text-white">{plan.name}</h3>
                                                <p className="text-gray-600 dark:text-gray-400">{plan.description}</p>
                                            </div>
                                            <Button size="sm" onClick={() => handleEditPlanPrice(plan)}>
                                                <Edit className="w-4 h-4 mr-2" />
                                                {t('Edit Pricing')}
                                            </Button>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                                            <div className="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div className="text-2xl font-bold text-gray-900 dark:text-white">
                                                    {formatAdminCurrency(pricingPeriod === 'monthly' ? plan.package_price_monthly : plan.package_price_yearly)}
                                                </div>
                                                <div className="text-sm text-gray-600 dark:text-gray-400">{t(`${pricingPeriod === 'monthly' ? 'Monthly' : 'Yearly'} Package`)}</div>
                                            </div>
                                            <div className="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div className="text-2xl font-bold text-gray-900 dark:text-white">
                                                    {formatAdminCurrency(pricingPeriod === 'monthly' ? (plan.price_per_user_monthly || 0) : (plan.price_per_user_yearly || 0))}
                                                </div>
                                                <div className="text-sm text-gray-600 dark:text-gray-400">{t(`Per User ${pricingPeriod === 'monthly' ? 'Monthly' : 'Yearly'}`)}</div>
                                            </div>
                                            <div className="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div className="text-2xl font-bold text-gray-900 dark:text-white">
                                                    {formatAdminCurrency(pricingPeriod === 'monthly' ? (plan.price_per_storage_monthly || 0) : (plan.price_per_storage_yearly || 0))}
                                                </div>
                                                <div className="text-sm text-gray-600 dark:text-gray-400">{t(`Per Storage ${pricingPeriod === 'monthly' ? 'Monthly' : 'Yearly'}`)}</div>
                                            </div>
                                        </div>

                                        <div>
                                            <div className="flex items-center justify-between mb-4">
                                                <h4 className="text-lg font-semibold text-gray-900 dark:text-white">{t('Active Add-Ons')}</h4>
                                                <SearchInput
                                                    value={moduleSearch}
                                                    onChange={setModuleSearch}
                                                    onSearch={() => {}}
                                                    placeholder={t('Search add-ons...')}
                                                />
                                            </div>
                                            <div className="max-h-96 overflow-y-auto">
                                                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                                                    {filteredModules.map((module) => (
                                                        <ModuleCard
                                                            key={module.module}
                                                            module={module}
                                                            monthlyPrice={module.monthly_price || 0}
                                                            yearlyPrice={module.yearly_price || 0}
                                                            onPriceUpdate={handleModulePriceUpdate}
                                                            showPricing={true}
                                                            editable={true}
                                                            pricingPeriod={pricingPeriod}
                                                        />
                                                    ))}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                    ) : (
                        <div className="text-center py-12">
                            <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                {t('No custom plans found')}
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400">
                                {t('Custom plans will appear here when created')}
                            </p>
                        </div>
                    )
                ) : (
                    /* Pre-Package Subscription Layout */
                    activePlans.length > 0 ? (
                    <div className="space-y-6 pt-6">
                        <div className="overflow-x-auto pb-2">
                        {/* Plans Header Cards */}
                        <div className="grid gap-6 pt-6" style={{ gridTemplateColumns: `300px repeat(${activePlans.length}, 280px)`, minWidth: `${300 + (activePlans.length * 280) + ((activePlans.length - 1) * 24)}px` }}>
                            {/* Features Header */}
                            <div className="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-6 border border-slate-200 dark:border-gray-700">
                                <div className="flex items-center justify-center space-x-3">
                                    <h3 className="text-xl font-bold text-gray-900 dark:text-white">{t('Features & Add-Ons')}</h3>
                                </div>
                            </div>

                            {/* Plan Header Cards */}
                            {sortedActivePlans.map((plan, index) => (
                                <div key={plan.id} className={`relative rounded-2xl p-6 border-2 ${
                                    plan.id === mostPopularPlanId && activePlans.length > 1
                                        ? 'bg-white dark:bg-gray-800 border-primary ring-2 ring-primary/20'
                                        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700'
                                }`}>
                                    {plan.id === mostPopularPlanId && activePlans.length > 1 && (
                                        <div className="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                            <Badge className="bg-primary dark:bg-black text-white px-4 py-2 text-sm font-bold shadow-lg">
                                                ⭐ {t('Most Popular')}
                                            </Badge>
                                        </div>
                                    )}

                                    {!isCompanyUser && (
                                        <div className="absolute top-4 right-4">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                        <MoreVertical className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <Link href={route('plans.edit', plan.id)} className="flex items-center">
                                                            <Edit className="w-4 h-4 mr-2" />
                                                            {t('Edit')}
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => handleDelete(plan)}
                                                        className="text-red-600 focus:text-red-600"
                                                    >
                                                        <Trash2 className="w-4 h-4 mr-2" />
                                                        {t('Delete')}
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    )}

                                    <div className="text-center space-y-4">
                                        <div>
                                            <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-1">{plan.name}</h3>
                                            <p className="text-xs text-gray-600 dark:text-gray-300">{plan.description}</p>
                                        </div>

                                        <div className="space-y-2">
                                            {plan.free_plan ? (
                                                <div>
                                                    <div className="text-5xl font-black text-primary mb-1">
                                                        {t('Free')}
                                                    </div>
                                                    <div className="text-primary font-semibold">
                                                        {t('Forever')}
                                                    </div>
                                                </div>
                                            ) : (
                                                <div>
                                                    <div className="flex items-baseline justify-center space-x-1 mb-2">
                                                        <span className="text-5xl font-black text-gray-900 dark:text-white">
                                                            {formatAdminCurrency(pricingPeriod === 'monthly' ? plan.package_price_monthly : plan.package_price_yearly).replace('.00', '')}
                                                        </span>
                                                        <span className="text-xl text-gray-500 dark:text-gray-400 font-semibold">
                                                            /{pricingPeriod === 'monthly' ? t('mo') : t('yr')}
                                                        </span>
                                                    </div>
                                                </div>
                                            )}
                                        </div>

                                        <div className="space-y-3 py-4">
                                            <div className="flex items-center space-x-2">
                                                <div className="w-2 h-2 rounded-full bg-primary flex-shrink-0"></div>
                                                <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {plan.number_of_users === -1 ? t('Unlimited users') : `${plan.number_of_users} ${t('users')}`}
                                                </span>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <div className="w-2 h-2 rounded-full bg-primary flex-shrink-0"></div>
                                                <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {formatStorage(plan.storage_limit)} {t('storage')}
                                                </span>
                                            </div>
                                            {plan.trial && (
                                                <div className="flex items-center space-x-2">
                                                    <div className="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></div>
                                                    <span className="text-sm font-medium text-green-600 dark:text-green-400">
                                                        {plan.trial_days}d {t('trial')}
                                                    </span>
                                                </div>
                                            )}
                                        </div>

                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Features Comparison Cards */}
                        <div className="space-y-4 mt-6">
                                <div className="grid gap-6" style={{ gridTemplateColumns: `300px repeat(${activePlans.length}, 280px)`, minWidth: `${300 + (activePlans.length * 280) + ((activePlans.length - 1) * 24)}px` }}>
                                    {/* All Modules Card */}
                                    <div className="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
                                        <div className="space-y-3">
                                            <div className="flex items-center justify-center py-2 h-10 border-b border-gray-200 dark:border-gray-600 mb-3">
                                                <span className="text-gray-900 dark:text-white font-semibold text-sm">
                                                    {t('Add-Ons')}
                                                </span>
                                            </div>
                                            {allModules.map((module) => (
                                                <div key={module.module} className="flex items-center justify-center py-0.5 h-6">
                                                    <span className="text-gray-700 dark:text-gray-300 capitalize text-center leading-none">
                                                        {module.alias}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Plan Feature Cards */}
                                    {sortedActivePlans.map((plan) => {
                                        const enabledAddOns = allModules.filter(module => hasModule(plan, module));
                                        const totalAddOns = allModules.length;

                                        return (
                                        <div key={plan.id} className="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
                                            <div className="space-y-3">
                                                <div className="flex items-center justify-center py-2 h-10 border-b border-gray-200 dark:border-gray-600 mb-3">
                                                    <span className="text-gray-900 dark:text-white font-semibold text-sm">
                                                        {enabledAddOns.length}/{totalAddOns} {t('Enabled')}
                                                    </span>
                                                </div>
                                                {allModules.map((module) => (
                                                    <div key={module.module} className="flex items-center justify-center py-0.5 h-6">
                                                        {hasModule(plan, module) ? (
                                                            <div className="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/40">
                                                                <Check className="w-3 h-3 text-green-600 dark:text-green-400" />
                                                            </div>
                                                        ) : (
                                                            <div className="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-700">
                                                                <X className="w-3 h-3 text-gray-400" />
                                                            </div>
                                                        )}
                                                    </div>
                                                ))}
                                                {isCompanyUser && (
                                                    <div className="pt-4 border-t space-y-2">
                                                        {/* Check trial first */}
                                                        {auth.user?.is_trial_done === 2 && auth.user.active_plan === plan.id && auth.user.plan_expire_date && new Date(auth.user.plan_expire_date) > new Date() ? (
                                                            <>
                                                                <div className="text-center p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                                                    <p className="text-xs text-blue-600 dark:text-blue-300 mt-1">
                                                                        {t('Trial Expire On')} {formatDate(auth.user.plan_expire_date)}
                                                                    </p>
                                                                </div>
                                                                <Button
                                                                    className="w-full"
                                                                    size="sm"
                                                                    onClick={() => router.visit(route('plans.subscribe', plan.id))}
                                                                >
                                                                    {t('Subscribe to Plan')}
                                                                </Button>
                                                            </>
                                                        ) : isCurrentlySubscribed(plan) ? (
                                                            <div className="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                                                <p className="text-xs text-green-600 dark:text-green-300">
                                                                    {t('Expires on')} {formatDate(auth.user.plan_expire_date)}
                                                                </p>
                                                            </div>
                                                        ) : (
                                                            <>
                                                                {plan.free_plan ? (
                                                                    <Button
                                                                        className="w-full"
                                                                        size="sm"
                                                                        onClick={() => handleAssignFreePlan(plan)}
                                                                    >
                                                                        {t('Subscribe to Plan')}
                                                                    </Button>
                                                                ) : (
                                                                    <Button
                                                                        className="w-full"
                                                                        size="sm"
                                                                        onClick={() => router.visit(route('plans.subscribe', plan.id))}
                                                                    >
                                                                        {t('Subscribe to Plan')}
                                                                    </Button>
                                                                )}
                                                                {canStartTrial(plan) && (
                                                                    <Button
                                                                        className="w-full"
                                                                        size="sm"
                                                                        variant="outline"
                                                                        onClick={() => handleStartTrial(plan)}
                                                                    >
                                                                        <Clock className="h-4 w-4 mr-2" />
                                                                        {t('Start Trial')} ({plan.trial_days}d)
                                                                    </Button>
                                                                )}

                                                            </>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                        );
                                    })}
                                </div>
                        </div>
                        </div>
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <Plus className="w-6 h-6 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                {t('No active plans found')}
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400 mb-4">
                                {t('Create your first plan to get started')}
                            </p>
                            {canCreate && (
                                <Link href={route('plans.create')}>
                                    <Button>
                                        <Plus className="w-4 h-4 mr-2" />
                                        {t('Create Plan')}
                                    </Button>
                                </Link>
                            )}
                        </div>
                    )
                )}
            </div>

            {/* Module Price Edit Dialog */}
            <Dialog open={!!editingModule} onOpenChange={() => setEditingModule(null)}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Edit Add-On Price')} - {editingModule?.alias}</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div>
                            <Label htmlFor="monthly_price">{t('Monthly Price')}</Label>
                            <Input
                                id="monthly_price"
                                type="number"
                                step="0.01"
                                min="0"
                                value={modulePrice.monthly}
                                onChange={(e) => setModulePrice(prev => ({ ...prev, monthly: parseFloat(e.target.value) || 0 }))}
                                placeholder="0.00"
                            />
                        </div>
                        <div>
                            <Label htmlFor="yearly_price">{t('Yearly Price')}</Label>
                            <Input
                                id="yearly_price"
                                type="number"
                                step="0.01"
                                min="0"
                                value={modulePrice.yearly}
                                onChange={(e) => setModulePrice(prev => ({ ...prev, yearly: parseFloat(e.target.value) || 0 }))}
                                placeholder="0.00"
                            />
                        </div>
                        <div className="flex items-center justify-end space-x-3 pt-4">
                            <Button variant="outline" onClick={() => setEditingModule(null)}>
                                {t('Cancel')}
                            </Button>
                            <Button onClick={handleSaveModulePrice} disabled={isSavingModulePrice}>
                                {isSavingModulePrice ? t('Saving...') : t('Save Price')}
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            {/* Plan Price Edit Dialog */}
            <Dialog open={!!editingPlan} onOpenChange={() => setEditingPlan(null)}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>{t('Edit Plan Pricing')} - {editingPlan?.name}</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="package_monthly">{t('Package Price (Monthly)')}</Label>
                                <Input
                                    id="package_monthly"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={planPricing.package_price_monthly}
                                    onChange={(e) => setPlanPricing(prev => ({ ...prev, package_price_monthly: parseFloat(e.target.value) || 0 }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="package_yearly">{t('Package Price (Yearly)')}</Label>
                                <Input
                                    id="package_yearly"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={planPricing.package_price_yearly}
                                    onChange={(e) => setPlanPricing(prev => ({ ...prev, package_price_yearly: parseFloat(e.target.value) || 0 }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="user_monthly">{t('Price Per User (Monthly)')}</Label>
                                <Input
                                    id="user_monthly"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={planPricing.price_per_user_monthly}
                                    onChange={(e) => setPlanPricing(prev => ({ ...prev, price_per_user_monthly: parseFloat(e.target.value) || 0 }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="user_yearly">{t('Price Per User (Yearly)')}</Label>
                                <Input
                                    id="user_yearly"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={planPricing.price_per_user_yearly}
                                    onChange={(e) => setPlanPricing(prev => ({ ...prev, price_per_user_yearly: parseFloat(e.target.value) || 0 }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="storage_monthly">{t('Price Per GB (Monthly)')}</Label>
                                <Input
                                    id="storage_monthly"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={planPricing.price_per_storage_monthly}
                                    onChange={(e) => setPlanPricing(prev => ({ ...prev, price_per_storage_monthly: parseFloat(e.target.value) || 0 }))}
                                />
                            </div>
                            <div>
                                <Label htmlFor="storage_yearly">{t('Price Per GB (Yearly)')}</Label>
                                <Input
                                    id="storage_yearly"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={planPricing.price_per_storage_yearly}
                                    onChange={(e) => setPlanPricing(prev => ({ ...prev, price_per_storage_yearly: parseFloat(e.target.value) || 0 }))}
                                />
                            </div>
                        </div>
                        <div className="flex items-center justify-end space-x-3 pt-4">
                            <Button variant="outline" onClick={() => setEditingPlan(null)}>
                                {t('Cancel')}
                            </Button>
                            <Button onClick={handleSavePlanPrice} disabled={isSavingPlanPrice}>
                                {isSavingPlanPrice ? t('Saving...') : t('Save Pricing')}
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog open={!!deletingPlan} onOpenChange={() => setDeletingPlan(null)}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Delete Plan')}</DialogTitle>
                        <DialogDescription>
                            {t('Are you sure you want to delete')} "{deletingPlan?.name}"? {t('This action cannot be undone.')}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeletingPlan(null)}>
                            {t('Cancel')}
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            {t('Delete')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
