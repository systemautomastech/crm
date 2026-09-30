import { RadioGroupItem } from '@/components/ui/radio-group';
import { Label } from '@/components/ui/label';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { getAdminSetting, getCompanySetting, isPackageActive, getPackageFavicon } from '@/utils/helpers';

export const paymentMethodBtn = (data?: any) => {

    const { t } = useTranslation();
    const { auth } = usePage().props as any;

    const stripeEnabled = getAdminSetting('stripe_enabled');

    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-payment',
            dataUrl: route('payment.stripe.store'),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg w-full">
                    <RadioGroupItem value="stripe" id="stripe" />
                    <Label htmlFor="stripe" className="cursor-pointer flex items-center space-x-2">
                        <div>
                            <div className="font-medium text-gray-900 dark:text-white">{t('Stripe')}</div>
                        </div>
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-10 w-10" />
                    </Label>
                </div>
            )
        }];
    }
    else {
        return [];
    }
};

export const bookingPayment = (data?: any) => {

    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-booking-payment',
            dataUrl: route('booking.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg w-full">
                    <Label htmlFor="stripe-booking" className="cursor-pointer flex items-center space-x-2">
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-10 w-10" />
                        <div>
                            <div className="font-medium text-gray-900 dark:text-white">{t('Stripe')}</div>
                        </div>
                    </Label>
                    <RadioGroupItem value="stripe" id="stripe-booking" />
                </div>
            )
        }];
    }
    else {
        return [];
    }
};



export const beautySpaPayment = (data?: any) => {

    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-beauty-spa-payment',
            dataUrl: route('beauty-spa.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="stripe-beauty-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#df9896] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-beauty-payment" />

                    </div>
                </Label>
            )
        }];
    }
    else {
        return [];
    }
};

export const lmsPayment = (data?: any) => {
    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-lms-payment',
            dataUrl: route('lms.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-3 border-2 border-gray-200 rounded-lg w-full hover:border-blue-300 transition-colors cursor-pointer">
                    <RadioGroupItem value="stripe" id="stripe-lms" />
                    <Label htmlFor="stripe-lms" className="cursor-pointer flex items-center space-x-3 flex-1">
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                        <div>
                            <div className="font-medium text-gray-900">{t('Stripe')}</div>
                            <div className="text-sm text-gray-500">{t('Pay securely with Stripe')}</div>
                        </div>
                    </Label>
                </div>
            )
        }];
    }
    else {
        return [];
    }
};

export const parkingPayment = (data?: any) => {
    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-parking-payment',
            dataUrl: route('parking.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-teal-600 transition-colors">
                    <RadioGroupItem value="stripe" id="stripe-parking" />
                    <Label htmlFor="stripe-parking" className="cursor-pointer flex items-center space-x-3 flex-1">
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                        <div>
                            <div className="font-medium text-gray-900">{t('Stripe')}</div>
                        </div>
                    </Label>
                </div>
            )
        }];
    }
    else {
        return [];
    }
};

export const laundryPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-laundry-payment',
            dataUrl: route('laundry.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="stripe-laundry-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-primary cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-laundry-payment" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};

export const eventsPayment = (data?: any) => {
    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    const isSelected = data?.selectedMethod === 'stripe';

    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-events-payment',
            dataUrl: route('events-management.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <label className="cursor-pointer">
                    <input
                        type="radio"
                        name="paymentMethod"
                        value="stripe"
                        className="hidden"
                        checked={isSelected}
                        onChange={() => data?.onMethodChange?.('stripe')}
                        required
                    />
                    <div className={`p-4 border-2 rounded-lg transition-all hover:border-red-200 flex items-center ${isSelected ? 'border-red-500 bg-red-50' : 'border-gray-200'
                        }`}>
                        <div className={`w-4 h-4 rounded-full border-2 mr-3 flex-shrink-0 ${isSelected ? 'border-red-500 bg-red-500' : 'border-gray-300'
                            }`}>
                            {isSelected && <div className="w-2 h-2 bg-white rounded-full m-auto mt-0.5"></div>}
                        </div>
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8  mr-3" />
                        <span className="font-semibold">{t('Stripe')}</span>
                    </div>
                </label>
            )
        }];
    }
    else {
        return [];
    }
};

export const holidayzPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-holidayz-payment',
            dataUrl: route('holidayz.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <div className="flex items-center space-x-3">
                    <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                    <div>
                        <div className="font-medium text-gray-900">{t('Stripe')}</div>
                        <div className="text-sm text-gray-500">{t('Pay securely with Stripe')}</div>
                    </div>
                </div>
            )
        }];
    }
    return [];
};

export const facilitiesPayment = (data?: any) => {
    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-facilities-payment',
            dataUrl: route('facilities.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-4 border-2 border-gray-200 rounded-lg hover:border-blue-300 transition-colors cursor-pointer">
                    <RadioGroupItem value="stripe" id="stripe-facilities" />
                    <Label htmlFor="stripe-facilities" className="cursor-pointer flex items-center space-x-3 flex-1">
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                        <div>
                            <div className="font-medium text-gray-900">{t('Stripe')}</div>
                            <div className="text-sm text-gray-500">{t('Pay securely with Stripe')}</div>
                        </div>
                    </Label>
                </div>
            )
        }];
    }
    else {
        return [];
    }
};

export const vehicleBookingPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-vehicle-booking-payment',
            dataUrl: route('vehicle-booking.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="stripe-vehicle-booking-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-yellow-500 cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-vehicle-booking-payment" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};

export const movieShowPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-movie-show-payment',
            dataUrl: route('movie-booking.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="stripe-movie-show-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-primary cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-movie-show-payment" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};

export const ngoPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-ngo-payment',
            dataUrl: route('ngo.donation.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <div className="flex flex-col items-center text-center p-3">
                    <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-6 w-6 mb-1" />
                    <span className="text-xs font-medium">{t('Stripe')}</span>
                </div>
            )
        }];
    }
    return [];
};

export const coworkingSpacePayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    const paymentType = data?.type || 'membership';

    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-coworking-payment',
            dataUrl: route('coworking-space.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            paymentType: paymentType,
            component: (
                <div className="flex items-center space-x-3 p-3 border-2 border-gray-200 rounded-lg w-full hover:border-blue-300 transition-colors cursor-pointer">
                    <RadioGroupItem value="stripe" id="stripe-coworking" />
                    <Label htmlFor="stripe-coworking" className="cursor-pointer flex items-center space-x-3 flex-1">
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                        <div>
                            <div className="font-medium text-white-900">{t('Stripe')}</div>
                            <div className="text-sm text-gray-500">{t('Pay securely with Stripe')}</div>
                        </div>
                    </Label>
                </div>
            )
        }];
    }
    else {
        return [];
    }
};

export const sportsClubPayment = (data?: any) => {

    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-sports-club-payment',
            dataUrl: route('sports-club.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="stripe-sports-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#4FAF5F] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-sports-payment" />

                    </div>
                </Label>
            )
        }];
    }
    else {
        return [];
    }
};

export const sportsClubPlanPayment = (data?: any) => {

    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-sports-club-plan-payment',
            dataUrl: route('sports-club-plan.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="stripe-sports-plan-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#4FAF5F] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-sports-plan-payment" />

                    </div>
                </Label>
            )
        }];
    }
    else {
        return [];
    }
};


export const influencerMarketingPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;
    const stripeEnabled = getCompanySetting('stripe_enabled');

    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-influencer-marketing',
            dataUrl: route('influencer-marketing.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <>
                    <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                    <span className="text-sm font-medium text-slate-700">{t('Stripe')}</span>
                </>
            )
        }];
    }
    return [];
};

export const waterParkPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-water-park-payment',
            dataUrl: route('water-park.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="stripe-water-park-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-sky-500 cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-water-park-payment" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};

export const tvStudioPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-tvstudio-payment',
            dataUrl: route('tvstudio.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <label className="flex items-center p-4 border border-gray-700 rounded-lg hover:border-red-600 transition-colors cursor-pointer">
                    <input
                        type="radio"
                        name="payment"
                        value="stripe"
                        className="text-red-600"
                    />
                    <span className="mx-3 text-white">{t('Stripe')}</span>
                    <div className="ml-auto w-12 h-8 rounded overflow-hidden bg-white flex items-center justify-center">
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-6 w-6 object-contain" />
                    </div>
                </label>
            )
        }];
    }
    return [];
};

export const artShowcasePayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-art-showcase-payment',
            dataUrl: route('art-showcase.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <div className="flex items-center space-x-3 flex-1">
                    <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                    <div>
                        <div className="font-medium text-gray-900">{t('Stripe')}</div>
                    </div>
                </div>
            )
        }];
    }
    return [];
};

export const tattooStudioPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-tattoo-studio-payment',
            dataUrl: route('tattoo-studio.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="stripe-tattoo-studio-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-blue-500 cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-tattoo-studio-payment" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};

export const photoStudioPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    const isSelected = data?.selectedMethod === 'stripe';

    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-photo-studio-payment',
            dataUrl: route('photo-studio.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <label className="cursor-pointer w-full">
                    <input
                        type="radio"
                        name="paymentMethod"
                        value="stripe"
                        className="hidden"
                        checked={isSelected}
                        onChange={() => data?.onMethodChange?.('stripe')}
                    />
                    <div className={`p-4 border-2 rounded-none transition-all flex items-center gap-3 ${isSelected ? 'border-[#674B2F] bg-[#674B2F]/5' : 'border-gray-300'
                        }`}>
                        <div className={`w-4 h-4 rounded-full border-2 flex-shrink-0 ${isSelected ? 'border-[#674B2F] bg-[#674B2F]' : 'border-gray-300'
                            }`}>
                            {isSelected && <div className="w-2 h-2 bg-white rounded-full m-auto mt-0.5"></div>}
                        </div>
                        <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                        <span className="font-medium">{t('Stripe')}</span>
                    </div>
                </label>
            )
        }];
    }
    return [];
};


export const ebookPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;
    const stripeEnabled = getCompanySetting('stripe_enabled');

    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-ebook',
            dataUrl: route('ebook.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <>
                    <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                    <span className="text-sm font-medium text-slate-700">{t('Stripe')}</span>
                </>
            )
        }];
    }
    return [];
};

export const yogaClassesPayment = (data?: any) => {
    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-yoga-classes-payment',
            dataUrl: route('yoga-classes.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 flex-1">
                    <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                    <div>
                        <div className="font-medium text-gray-900">{t('Stripe')}</div>
                        <div className="text-sm text-gray-500">{t('Pay securely with Stripe')}</div>
                    </div>
                </div>
            )
        }];
    }
    else {
        return [];
    }
};

export const hairCareStudioPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-hair-care-studio-payment',
            dataUrl: route('hair-care-studio.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="stripe-hair-care-studio-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#C58154] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-hair-care-studio-payment" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};

export const petCarePayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-pet-care-payment',
            dataUrl: route('pet-care.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="stripe-pet-care-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#df9896] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-pet-care-payment" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};

export const boutiqueStudioPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-boutique-studio-payment',
            dataUrl: route('boutique-studio.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="stripe-boutique-studio-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[var(--primary-color)] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-boutique-studio-payment" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};


export const investmentSystemPayment = (data?: any) => {

    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-investment-system-payment',
            dataUrl: route('investment-system.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="stripe-investment-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-blue-500 cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium">{t('Stripe')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="stripe" id="stripe-investment-payment" className="border-white text-white hover:text-blue-500" />

                    </div>
                </Label>
            )
        }];
    }
    else {
        return [];
    }
};

export const jewelleryPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const stripeEnabled = getCompanySetting('stripe_enabled');
    if (stripeEnabled === 'on') {
        return [{
            id: 'stripe-jewellery-payment',
            dataUrl: route('jewellery-store.payment.stripe.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full overflow-hidden bg-white border">
                        <img src={getPackageFavicon('Stripe')} alt="Stripe Logo" className="object-contain w-full h-full" />
                    </div>
                    <div>
                        <h5 className="text-base font-medium text-gray-800">{t('Stripe')}</h5>
                    </div>
                </div>
            )
        }];
    }
    return [];
};

export const freelancingWalletPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;
    const stripeEnabled = getCompanySetting('stripe_enabled');
    
    if (stripeEnabled === 'on') {        
        return [{
            id: 'stripe-freelancing-wallet-payment',
            dataUrl: route('freelancing.wallet.payment.stripe.store', { userSlug: userSlug }),
            component: (
                <div className="flex items-center space-x-3 flex-1">
                    <img src={getPackageFavicon('Stripe')} alt="Stripe" className="h-8 w-8" />
                    <div>
                        <div className="font-medium text-gray-900">{t('Stripe')}</div>
                    </div>
                </div>
            )
        }];
    }
    return [];
};