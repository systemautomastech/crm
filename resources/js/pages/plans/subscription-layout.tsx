import { useState, useEffect, useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { router, usePage } from '@inertiajs/react';
import { usePageButtons } from '@/hooks/usePageButtons';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

import { SearchInput } from '@/components/ui/search-input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { formatAdminCurrency, formatStorage, formatDate, getPackageFavicon, getPackageAlias, getSubscriptionDetails } from '@/utils/helpers';

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
    trial: boolean;
    trial_days: number;
}

interface Module {
    module: string;
    alias: string;
    image: string;
    monthly_price: number;
    yearly_price: number;
}

interface Props {
    plan: Plan;
    allModules: Module[];
    userActiveModules?: string[];
    pricingPeriod: 'monthly' | 'yearly';
    onSubscribe: (planData: any) => void;
    bankTransferEnabled?: boolean;
    bankTransferInstructions?: string;

    planExpireDate?: string;
    trialExpireDate?: string;
}

function SubscriptionLayout({ plan, allModules, pricingPeriod, onSubscribe, bankTransferEnabled = false, bankTransferInstructions = '', planExpireDate, trialExpireDate }: Props) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const [moduleSearch, setModuleSearch] = useState('');
    const [selectedModules, setSelectedModules] = useState<string[]>([]);

    // Get subscription details using the helper function
    const subscriptionDetail = getSubscriptionDetails(auth?.user?.id);

    // Check if plan is expired
    const isPlanExpired = () => {
        if (!auth?.user?.plan_expire_date) return false;
        return new Date(auth.user.plan_expire_date) <= new Date();
    };

    // Get current values based on plan expiry and custom plan status
    const getCurrentValues = () => {
        const isExpired = isPlanExpired();

        if (plan.custom_plan && isExpired) {
            // If custom plan is expired, show 0 for storage and users
            return {
                currentUsers: 0,
                currentStorage: 0
            };
        } else if (subscriptionDetail.status) {
            // If subscription is active, use subscription details
            return {
                currentUsers: subscriptionDetail.total_user || 0,
                currentStorage: Math.round((plan.storage_limit || 0) / (1024 * 1024))
            };
        } else {
            // Default values
            return {
                currentUsers: plan.number_of_users || 0,
                currentStorage: Math.round((plan.storage_limit || 0) / (1024 * 1024))
            };
        }
    };

    const currentValues = getCurrentValues();

    const [customPlan, setCustomPlan] = useState(() => ({
        storageLimit: 0,
        currentUsers: 0,
        currentStorage: currentValues.currentStorage,
        couponCode: ''
    }));
    const [selectedPaymentMethod, setSelectedPaymentMethod] = useState<string | null>(bankTransferEnabled ? 'bank_transfer' : null);
    const [receiptFile, setReceiptFile] = useState<File | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [couponDiscount, setCouponDiscount] = useState<{amount: number, finalAmount: number} | null>(null);
    const [couponError, setCouponError] = useState<string>('');
    const [isApplyingCoupon, setIsApplyingCoupon] = useState(false);
    const [userActivatedModules, setUserActivatedModules] = useState<Module[]>([]);
    const [removingModule, setRemovingModule] = useState<Module | null>(null);
    const [fileError, setFileError] = useState<string>('');

    // Add payment method buttons hook
    const paymentButtons = usePageButtons('paymentMethodBtn', {
        selectedMethod: selectedPaymentMethod,
        onMethodChange: setSelectedPaymentMethod
    },true);

    const handleRemoveModule = (module: Module) => {
        setRemovingModule(module);
    };

    const confirmRemoveModule = async () => {
        if (!removingModule) return;

        try {
            const response = await fetch(route('user.active-modules.remove', removingModule.module), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            const data = await response.json();
            if (data.success) {
                setUserActivatedModules(prev => prev.filter(module => module.module !== removingModule.module));
            }
        } catch (error) {
            console.error('Error removing module:', error);
        } finally {
            setRemovingModule(null);
        }
    };

    useEffect(() => {
        // Fetch user's activated modules
        fetch(route('user.active-modules'))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const uniqueModules = new Map();
                    data.modules.forEach((userModule: any) => {
                        if (!uniqueModules.has(userModule.module)) {
                            const moduleData = allModules.find(m => m.module === userModule.module);
                            if (moduleData) {
                                uniqueModules.set(userModule.module, {
                                    ...moduleData,
                                    monthly_price: userModule.monthly_price,
                                    yearly_price: userModule.yearly_price
                                });
                            }
                        }
                    });
                    setUserActivatedModules(Array.from(uniqueModules.values()));
                }
            })
            .catch();
    }, [allModules]);

    const filteredModules = allModules.filter(module => {
        const matchesSearch = module.alias.toLowerCase().includes(moduleSearch.toLowerCase()) ||
            module.module.toLowerCase().includes(moduleSearch.toLowerCase());

        const isNotActivated = !userActivatedModules.some(activatedModule => activatedModule.module === module.module);

        return plan.custom_plan ? (matchesSearch && isNotActivated) : (matchesSearch && plan.modules?.includes(module.module));
    });

    const handleModuleSelection = (moduleId: string, selected: boolean) => {
        setSelectedModules(prev =>
            selected
                ? [...prev, moduleId]
                : prev.filter(id => id !== moduleId)
        );
    };

    const applyCouponWithAmount = async (amount: number) => {
        if (!customPlan.couponCode.trim()) return;

        setIsApplyingCoupon(true);
        setCouponError('');

        try {
            const response = await fetch(route('plans.apply-coupon'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    coupon_code: customPlan.couponCode,
                    total_amount: amount
                })
            });

            const data = await response.json();

            if (data.success) {
                setCouponDiscount({
                    amount: data.discount_amount,
                    finalAmount: amount - data.discount_amount
                });
            } else {
                setCouponError(data.message);
                setCouponDiscount(null);
            }
        } catch (error) {
            setCouponError('Failed to apply coupon');
            setCouponDiscount(null);
        } finally {
            setIsApplyingCoupon(false);
        }
    };

    const handleApplyCoupon = async () => {
        await applyCouponWithAmount(subtotal);
    };

    // Calculate subtotal without discount
    const subtotal = useMemo(() => {
        if (plan.free_plan) return 0;

        const basePrice = pricingPeriod === 'monthly'
            ? Number(plan?.package_price_monthly || 0)
            : Number(plan?.package_price_yearly || 0);

        const userUnitPrice = pricingPeriod === 'monthly'
            ? Number(plan?.price_per_user_monthly || 0)
            : Number(plan?.price_per_user_yearly || 0);
        const storageUnitPrice = pricingPeriod === 'monthly'
            ? Number(plan?.price_per_storage_monthly || 0)
            : Number(plan?.price_per_storage_yearly || 0);

        const userTotalPrice = userUnitPrice * Number(customPlan.currentUsers || 0);
        const storageTotalPrice = storageUnitPrice * Number(customPlan.storageLimit || 0);
        const modulePrice = selectedModules.reduce((total, moduleId) => {
            const module = allModules.find(m => m.module === moduleId);
            const price = module ? (pricingPeriod === 'monthly' ? Number(module.monthly_price || 0) : Number(module.yearly_price || 0)) : 0;
            return total + price;
        }, 0);

        return basePrice + userTotalPrice + storageTotalPrice + modulePrice;
    }, [plan.free_plan, customPlan.currentUsers, customPlan.storageLimit, selectedModules, pricingPeriod, plan?.package_price_monthly, plan?.package_price_yearly, plan?.price_per_user_monthly, plan?.price_per_user_yearly, plan?.price_per_storage_monthly, plan?.price_per_storage_yearly, allModules]);

    // Final total after discount
    const dynamicTotal = useMemo(() => {
        const discountAmount = couponDiscount ? Number(couponDiscount.amount || 0) : 0;
        return Math.max(0, subtotal - discountAmount);
    }, [subtotal, couponDiscount]);



    const handleSubscribe = () => {
        if (selectedPaymentMethod === 'bank_transfer' && !receiptFile) {
            setFileError(t('Please upload payment receipt'));
            return;
        }

        setFileError('');

        if (selectedPaymentMethod === 'bank_transfer' && receiptFile) {
            setIsSubmitting(true);

            const formData = new FormData();
            formData.append('plan_id', plan.id.toString());
            formData.append('user_counter_input', (customPlan.currentUsers || 0).toString());
            formData.append('storage_counter_input', (customPlan.storageLimit || 0).toString());

            formData.append('userprice_input', ((pricingPeriod === 'monthly' ? (plan?.price_per_user_monthly ?? 0) : (plan?.price_per_user_yearly ?? 0)) * (customPlan.currentUsers || 0)).toString());
            formData.append('storage_price_input', ((pricingPeriod === 'monthly' ? (plan?.price_per_storage_monthly ?? 0) : (plan?.price_per_storage_yearly ?? 0)) * (customPlan.storageLimit || 0)).toString());
            formData.append('user_module_price_input', selectedModules.reduce((total, moduleId) => {
                const module = allModules.find(m => m.module === moduleId);
                const price = module ? (pricingPeriod === 'monthly' ? Number(module.monthly_price) || 0 : Number(module.yearly_price) || 0) : 0;
                return total + price;
            }, 0).toString());
            formData.append('time_period', pricingPeriod === 'monthly' ? 'Month' : 'Year');
            formData.append('payment_receipt', receiptFile);

            if (plan.custom_plan) {
                formData.append('user_module_input', selectedModules.join(','));
            } else {
                formData.append('user_module_input', (plan.modules || []).join(','));
            }

            if (customPlan.couponCode) {
                formData.append('coupon_code', customPlan.couponCode);
            }

            router.post(route('payment.bank-transfer.store'), formData, {
                forceFormData: true,
                onFinish: () => setIsSubmitting(false)
            });
        } else if (selectedPaymentMethod && paymentButtons.some(button => button.id.includes(selectedPaymentMethod))) {
            setIsSubmitting(true);

            const selectedButton = paymentButtons.find(button => button.id.includes(selectedPaymentMethod));
            const dataUrl = (selectedButton as any)?.dataUrl;

            if (dataUrl) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = dataUrl;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                }

                const formData = {
                    plan_id: plan.id,
                    user_id: auth?.user?.id,
                    user_counter_input: customPlan.currentUsers || 0,
                    storage_limit_input: customPlan.storageLimit || 0,
                    time_period: pricingPeriod === 'monthly' ? 'Month' : 'Year',
                    user_module_input: plan.custom_plan ? selectedModules.join(',') : (plan.modules || []).join(','),
                    coupon_code: customPlan.couponCode || ''
                };

                Object.entries(formData).forEach(([key, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
            setIsSubmitting(false);
        } else {
            const subscriptionData = plan.custom_plan ? {
                planId: plan.id,
                customPlan: {
                    ...customPlan,
                    storageCounter: customPlan.storageLimit
                },
                selectedModules,
                totalPrice: dynamicTotal
            } : {
                planId: plan.id,
                totalPrice: dynamicTotal
            };

            onSubscribe(subscriptionData);
        }
    };

    return (
        <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
            {/* Left Side - Plan Info */}
            <div className="lg:col-span-3 space-y-6">
                {/* Current Plan */}
                <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">{t('Plan Details')}</h3>
                    <div className="space-y-3 text-sm">
                        <div className="flex justify-between">
                            <span className="text-gray-600 dark:text-gray-400">{t('Users')}</span>
                            <span className="font-medium text-gray-900 dark:text-white">
                                {!plan.custom_plan
                                    ? (plan.number_of_users === -1 ? t('Unlimited users') : `${plan.number_of_users} ${t('users')}`)
                                    : (isPlanExpired() ? '0 users' : (subscriptionDetail.status ? `${subscriptionDetail.total_user} ${t('users')}` : (plan.number_of_users === -1 ? t('Unlimited users') : `${plan.number_of_users} ${t('users')}`))
                                )}
                            </span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-gray-600 dark:text-gray-400">{t('Storage')}</span>
                            <span className="font-medium text-gray-900 dark:text-white">
                                {!plan.custom_plan
                                    ? formatStorage(plan.storage_limit)
                                    : (isPlanExpired() ? '0 GB' : (subscriptionDetail.status ? subscriptionDetail.total_storage : (customPlan.storageLimit > 0 ? (customPlan.storageLimit < 1 ? (customPlan.storageLimit * 1000).toFixed(0) + ' MB' : customPlan.storageLimit + ' GB') : '0 GB'))
                                )}
                            </span>
                        </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600 dark:text-gray-400">
                                    {auth?.user?.is_trial_done === 2 ? t('Trial Expire On') : t('Plan Expire Date')}
                                </span>
                                <span className={(() => {
                                    const expireDate = subscriptionDetail.status ? subscriptionDetail.plan_expire_date : planExpireDate;
                                    const isExpired = expireDate && new Date(expireDate) <= new Date();
                                    return isExpired ? 'font-medium text-red-600 dark:text-red-400' : 'font-medium text-gray-900 dark:text-white';
                                })()}>
                                     {subscriptionDetail.status
                                            ? (subscriptionDetail.plan_expire_date ? formatDate(subscriptionDetail.plan_expire_date) : '-')
                                            : (planExpireDate ? formatDate(planExpireDate) : '-')
                                     }
                                </span>
                            </div>
                    </div>
                </div>

                {plan.custom_plan && (
                    <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">{t('Pricing Breakdown')}</h3>
                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-gray-600 dark:text-gray-400">{t(`Base Package (${pricingPeriod === 'monthly' ? 'Monthly' : 'Yearly'})`)}</span>
                                <span className="font-medium text-gray-900 dark:text-white">{formatAdminCurrency(pricingPeriod === 'monthly' ? (plan?.package_price_monthly ?? 0) : (plan?.package_price_yearly ?? 0))}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600 dark:text-gray-400">{t(`Per User (${pricingPeriod === 'monthly' ? 'Monthly' : 'Yearly'})`)}</span>
                                <span className="font-medium text-gray-900 dark:text-white">{formatAdminCurrency(pricingPeriod === 'monthly' ? (plan?.price_per_user_monthly ?? 0) : (plan?.price_per_user_yearly ?? 0))}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600 dark:text-gray-400">{t(`Per GB Storage (${pricingPeriod === 'monthly' ? 'Monthly' : 'Yearly'})`)}</span>
                                <span className="font-medium text-gray-900 dark:text-white">{formatAdminCurrency(pricingPeriod === 'monthly' ? (plan?.price_per_storage_monthly ?? 0) : (plan?.price_per_storage_yearly ?? 0))}</span>
                            </div>
                        </div>
                    </div>
                )}

                {/* Available Add-Ons */}
                <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('Available Add-Ons')}</h3>
                        <SearchInput
                            value={moduleSearch}
                            onChange={setModuleSearch}
                            onSearch={() => {}}
                            placeholder={t('Search add-ons...')}
                            className="w-48"
                        />
                    </div>
                    <div className="max-h-64 overflow-y-auto">
                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            {filteredModules.map((module) => (
                                <div
                                    key={module.module}
                                    className="flex items-start gap-3 p-4 border rounded hover:bg-muted/50"
                                >
                                    <img src={getPackageFavicon(module.module)} alt="" className="w-8 h-8 border rounded flex-shrink-0" />
                                    <div className="flex-1 min-w-0">
                                        <span className="text-sm break-words block">{getPackageAlias(module.alias)}</span>
                                    {plan.custom_plan ? (
                                        <span className="text-xs text-gray-500 dark:text-gray-400 block mt-1">
                                            {formatAdminCurrency(pricingPeriod === 'monthly' ? (module.monthly_price || 0) : (module.yearly_price || 0))}/{pricingPeriod === 'monthly' ? 'monthly' : 'yearly'}
                                        </span>
                                    ) : ''}
                                    </div>
                                    {plan.custom_plan ? (
                                        <Checkbox
                                            checked={selectedModules.includes(module.module)}
                                            onCheckedChange={(checked) => handleModuleSelection(module.module, !!checked)}
                                            className="flex-shrink-0 mt-1"
                                        />
                                    ) : ''}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
                {/* Activated Add-Ons - Only show for custom plans */}
                {plan.custom_plan && (
                    <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{t('Activated Add-Ons')}</h3>
                        </div>
                        <div className="max-h-64 overflow-y-auto">
                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                {userActivatedModules.map((module, index) => (
                                    <div key={`${module.module}-${index}`} className="flex items-center gap-3 p-4 border rounded hover:bg-muted/50 relative">
                                        <img src={getPackageFavicon(module.module)} alt="" className="w-8 h-8 border rounded" />
                                        <div className="flex-1 min-w-0">
                                            <div className="text-sm break-words">{getPackageAlias(module.alias)}</div>
                                            <div className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {formatAdminCurrency(pricingPeriod === 'monthly' ? (module.monthly_price || 0) : (module.yearly_price || 0))}/{pricingPeriod === 'monthly' ? 'monthly' : 'yearly'}
                                            </div>
                                        </div>
                                        <button
                                            onClick={() => handleRemoveModule(module)}
                                            className="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold transition-colors duration-200 flex-shrink-0"
                                            title={t('Remove add-on')}
                                        >
                                            ×
                                        </button>
                                    </div>
                                ))}
                            </div>
                            {userActivatedModules.length === 0 && (
                                <div className="text-center py-8 text-gray-500">
                                    <p>{t('No activated add-ons')}</p>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* Right Side - Plan Builder or Subscription */}
            <div className="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                    {plan.custom_plan ? t('Build Your Custom Plan') : t('Subscribe to Plan')}
                </h3>

                <div className="space-y-6">
                    {plan.custom_plan ? (
                        <>
                            {/* Custom Plan Builder */}
                            <div>
                                <Label htmlFor="storage_limit">{t('Storage Limit (GB)')}</Label>
                                <Input
                                    id="storage_limit"
                                    type="number"
                                    min="0"
                                    step="0.1"
                                    placeholder={t('Enter storage limit in GB')}
                                    value={customPlan.storageLimit || '0'}
                                    onChange={(e) => setCustomPlan(prev => ({ ...prev, storageLimit: parseFloat(e.target.value) || 0 }))}
                                />
                            </div>

                            <div>
                                <Label htmlFor="custom_users">{t('User')}</Label>
                                <Input
                                    id="custom_users"
                                    type="number"
                                    min="0"
                                    placeholder={t('Enter number of users')}
                                    value={customPlan.currentUsers || '0'}
                                    onChange={(e) => setCustomPlan(prev => ({ ...prev, currentUsers: parseInt(e.target.value) || 0 }))}
                                />
                            </div>

                            <div>
                                <Label htmlFor="coupon_code">{t('Coupon Code')}</Label>
                                <div className="flex space-x-2">
                                    <Input
                                        id="coupon_code"
                                        placeholder={t('Enter coupon code')}
                                        value={customPlan.couponCode}
                                        onChange={(e) => setCustomPlan(prev => ({ ...prev, couponCode: e.target.value }))}
                                    />
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={handleApplyCoupon}
                                        disabled={isApplyingCoupon || !customPlan.couponCode.trim()}
                                    >
                                        {isApplyingCoupon ? t('Applying...') : t('Apply')}
                                    </Button>
                                </div>
                                {couponError && (
                                    <p className="text-sm text-red-500">{couponError}</p>
                                )}
                                {couponDiscount && (
                                    <p className="text-sm text-green-600">{t('Coupon applied successfully!')}</p>
                                )}
                            </div>

                            {/* Price Calculation */}
                            <div className="border-t pt-4">
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-gray-600 dark:text-gray-400">{t('Base Package')}</span>
                                        <span>{formatAdminCurrency(pricingPeriod === 'monthly' ? (plan?.package_price_monthly ?? 0) : (plan?.package_price_yearly ?? 0))}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600 dark:text-gray-400">{t('Users')} ({customPlan.currentUsers || 0} × {formatAdminCurrency(pricingPeriod === 'monthly' ? (plan?.price_per_user_monthly ?? 0) : (plan?.price_per_user_yearly ?? 0))})</span>
                                        <span>{formatAdminCurrency((pricingPeriod === 'monthly' ? (plan?.price_per_user_monthly ?? 0) : (plan?.price_per_user_yearly ?? 0)) * (customPlan.currentUsers || 0))}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600 dark:text-gray-400">{t('Storage')} ({customPlan.storageLimit || 0}GB × {formatAdminCurrency(pricingPeriod === 'monthly' ? (plan?.price_per_storage_monthly ?? 0) : (plan?.price_per_storage_yearly ?? 0))})</span>
                                        <span>{formatAdminCurrency((pricingPeriod === 'monthly' ? (plan?.price_per_storage_monthly ?? 0) : (plan?.price_per_storage_yearly ?? 0)) * (customPlan.storageLimit || 0))}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600 dark:text-gray-400">{t('Add-Ons')} ({selectedModules.length})</span>
                                        <span>{formatAdminCurrency(selectedModules.reduce((total, moduleId) => {
                                            const module = allModules.find(m => m.module === moduleId);
                                            const price = module ? (pricingPeriod === 'monthly' ? Number(module.monthly_price) || 0 : Number(module.yearly_price) || 0) : 0;
                                            return total + price;
                                        }, 0))}</span>
                                    </div>

                                    {couponDiscount && (
                                        <div className="flex justify-between text-green-600">
                                            <span>{t('Coupon Discount')}</span>
                                            <span>-{formatAdminCurrency(couponDiscount.amount)}</span>
                                        </div>
                                    )}
                                    <div className="border-t pt-2 flex justify-between font-semibold text-lg">
                                        <span>{t(`Total ${pricingPeriod === 'monthly' ? 'Monthly' : 'Yearly'}`)}</span>
                                        <span className="text-primary">{formatAdminCurrency(dynamicTotal)}</span>
                                    </div>
                                </div>
                            </div>
                        </>
                    ) : (
                        /* Regular Plan Subscription */
                        <>
                            <div>
                                <Label htmlFor="coupon_code_regular">{t('Coupon Code')}</Label>
                                <div className="flex space-x-2">
                                    <Input
                                        id="coupon_code_regular"
                                        placeholder={t('Enter coupon code')}
                                        value={customPlan.couponCode}
                                        onChange={(e) => setCustomPlan(prev => ({ ...prev, couponCode: e.target.value }))}
                                    />
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={handleApplyCoupon}
                                        disabled={isApplyingCoupon || !customPlan.couponCode.trim()}
                                    >
                                        {isApplyingCoupon ? t('Applying...') : t('Apply')}
                                    </Button>
                                </div>
                                {couponError && (
                                    <p className="text-sm text-red-500">{couponError}</p>
                                )}
                                {couponDiscount && (
                                    <p className="text-sm text-green-600">{t('Coupon applied successfully!')}</p>
                                )}
                            </div>

                            {/* Price Summary */}

                            <div className="grid grid-cols-1 gap-3">
                                <div className="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div className="flex items-center space-x-2">
                                        <svg className="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                        </svg>
                                        <span className="text-sm font-medium">{t('Users')}</span>
                                    </div>
                                    <span className="text-sm text-gray-600 dark:text-gray-400">{plan.number_of_users === -1 ? t('Unlimited Users') : plan.number_of_users}</span>
                                </div>
                                <div className="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div className="flex items-center space-x-2">
                                        <svg className="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                        </svg>
                                        <span className="text-sm font-medium">{t('Storage')}</span>
                                    </div>
                                    <span className="text-sm text-gray-600 dark:text-gray-400">{formatStorage(plan.storage_limit)}</span>
                                </div>
                                {plan.trial && (
                                    <div className="flex items-center justify-between p-3 border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        <div className="flex items-center space-x-2">
                                            <svg className="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            <span className="text-sm font-medium text-green-700 dark:text-green-300">{t('Free Trial')}</span>
                                        </div>
                                        <span className="text-sm text-green-600 dark:text-green-400">{plan.trial_days} {t('days')}</span>
                                    </div>
                                )}
                            </div>
                        </>
                    )}

                    {/* Payment Methods */}
                    {(bankTransferEnabled || paymentButtons.length > 0) && (
                        <div className="space-y-4">
                            <h4 className="font-medium text-gray-900 dark:text-white">{t('Payment Method')}</h4>
                            <RadioGroup value={selectedPaymentMethod || ''} onValueChange={setSelectedPaymentMethod}>
                                {bankTransferEnabled && (
                                    <div className="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg w-full">
                                        <RadioGroupItem value="bank_transfer" id="bank_transfer" />
                                        <Label htmlFor="bank_transfer" className="cursor-pointer">
                                            <div className="font-medium text-gray-900 dark:text-white">{t('Bank Transfer')}</div>
                                            <div className="text-sm text-gray-600 dark:text-gray-400">{t('Pay via bank transfer')}</div>
                                        </Label>
                                    </div>
                                )}

                                {paymentButtons.map((button) => (
                                    <div key={button.id}>{button.component}</div>
                                ))}
                            </RadioGroup>

                            {selectedPaymentMethod === 'bank_transfer' && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="text-sm">{t('Bank Transfer Instructions')}</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        {bankTransferInstructions && (
                                            <div className="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                <div className="text-sm text-blue-800 dark:text-blue-200" dangerouslySetInnerHTML={{ __html: bankTransferInstructions.replace(/\n/g, '<br/>') }} />
                                            </div>
                                        )}

                                        <div>
                                            <Label htmlFor="receipt">{t('Upload Payment Receipt')}</Label>
                                            <Input
                                                id="receipt"
                                                type="file"
                                                accept=".png,.jpg,.jpeg,.pdf"
                                                onChange={(e) => setReceiptFile(e.target.files?.[0] || null)}
                                            />
                                            {receiptFile && (
                                                <p className="mt-2 text-sm text-green-600 dark:text-green-400">
                                                    {t('Selected')}: {receiptFile.name}
                                                </p>
                                            )}
                                        </div>
                                        {fileError && (
                                            <p className="text-sm text-red-500">{fileError}</p>
                                        )}
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    )}

                    {/* Subscribe Button */}
                    <Button
                        className="w-full"
                        size="lg"
                        onClick={handleSubscribe}
                        disabled={!selectedPaymentMethod || isSubmitting}
                    >
                        {isSubmitting ? t('Submitting...') : (plan.custom_plan ? `${t('Subscribe to Custom Plan')} - ${formatAdminCurrency(dynamicTotal)}` : `${t('Subscribe to Plan')} - ${formatAdminCurrency(dynamicTotal)}`)}
                    </Button>

                    {(paymentButtons.length === 0) && (
                        <p className="text-sm text-gray-500 dark:text-gray-400 text-center">
                            {t('Payment methods will be available once the Super Admin configures the payment gateway credentials.')}
                        </p>
                    )}

                    {(paymentButtons.length > 0 || bankTransferEnabled) && !selectedPaymentMethod && (
                        <p className="text-sm text-gray-500 dark:text-gray-400 text-center">
                            {t('Please select a payment method')}
                        </p>
                    )}

                </div>
            </div>

            {/* Remove Add-On Confirmation Dialog */}
            <Dialog open={!!removingModule} onOpenChange={() => setRemovingModule(null)}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Remove Add-On')}</DialogTitle>
                        <DialogDescription>
                            {t('Are you sure you want to remove')} "{removingModule?.alias}"? {t('This action cannot be undone.')}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setRemovingModule(null)}>
                            {t('Cancel')}
                        </Button>
                        <Button variant="destructive" onClick={confirmRemoveModule}>
                            {t('Remove')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}

export default SubscriptionLayout;
