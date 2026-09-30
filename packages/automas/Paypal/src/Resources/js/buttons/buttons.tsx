import { RadioGroupItem } from '@/components/ui/radio-group';
import { Label } from '@/components/ui/label';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { getAdminSetting, getCompanySetting, isPackageActive, getPackageFavicon } from '@/utils/helpers';

export const paymentMethodBtn = (data?: any) => {

    const { t } = useTranslation();
    const { auth } = usePage().props as any;

    const paypalEnabled = getAdminSetting('paypal_enabled');

    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-payment',
            dataUrl: route('paypal.plan.store'),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg w-full">
                    <RadioGroupItem value="paypal" id="paypal" />
                    <Label htmlFor="paypal" className="cursor-pointer flex items-center space-x-2">
                        <div>
                            <div className="font-medium text-gray-900 dark:text-white">{t('PayPal')}</div>
                        </div>
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-10 w-10" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-booking-payment',
            dataUrl: route('paypal.booking.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg w-full">
                    <Label htmlFor="paypal-booking" className="cursor-pointer flex items-center space-x-2">
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-10 w-10" />
                        <div>
                            <div className="font-medium text-gray-900 dark:text-white">{t('PayPal')}</div>
                        </div>
                    </Label>
                    <RadioGroupItem value="paypal" id="paypal-booking" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-beauty-spa-payment',
            dataUrl: route('paypal.beauty-spa.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="paypal-beauty-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#df9896] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-beauty-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-lms-payment',
            dataUrl: route('paypal.lms.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-3 border-2 border-gray-200 rounded-lg w-full hover:border-blue-300 transition-colors cursor-pointer">
                    <RadioGroupItem value="paypal" id="paypal-lms" />
                    <Label htmlFor="paypal-lms" className="cursor-pointer flex items-center space-x-3 flex-1">
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                        <div>
                            <div className="font-medium text-gray-900">{t('PayPal')}</div>
                            <div className="text-sm text-gray-500">{t('Pay securely with PayPal')}</div>
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-parking-payment',
            dataUrl: route('paypal.parking.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-teal-600 transition-colors">
                    <RadioGroupItem value="paypal" id="paypal-parking" />
                    <Label htmlFor="paypal-parking" className="cursor-pointer flex items-center space-x-3 flex-1">
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                        <div>
                            <div className="font-medium text-gray-900">{t('PayPal')}</div>
                        </div>
                    </Label>
                </div>
            )
        }];
    }
    return [];
};

export const laundryPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-laundry-payment',
            dataUrl: route('paypal.laundry.payment.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="paypal-laundry-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-primary cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-laundry-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    const isSelected = data?.selectedMethod === 'paypal';

    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-events-payment',
            dataUrl: route('paypal.events-management.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <label className="cursor-pointer">
                    <input
                        type="radio"
                        name="paymentMethod"
                        value="paypal"
                        className="hidden"
                        checked={isSelected}
                        onChange={() => data?.onMethodChange?.('paypal')}
                        required
                    />
                    <div className={`p-4 border-2 rounded-lg transition-all hover:border-red-200 flex items-center ${isSelected ? 'border-red-500 bg-red-50' : 'border-gray-200'
                        }`}>
                        <div className={`w-4 h-4 rounded-full border-2 mr-3 flex-shrink-0 ${isSelected ? 'border-red-500 bg-red-500' : 'border-gray-300'
                            }`}>
                            {isSelected && <div className="w-2 h-2 bg-white rounded-full m-auto mt-0.5"></div>}
                        </div>
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8  mr-3" />
                        <span className="font-semibold">{t('PayPal')}</span>
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-holidayz-payment',
            dataUrl: route('paypal.holidayz.payment.store', { userSlug: userSlug }),
            component: (
                <div className="flex items-center space-x-3">
                    <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                    <div>
                        <div className="font-medium text-gray-900">{t('PayPal')}</div>
                        <div className="text-sm text-gray-500">{t('Pay securely with PayPal')}</div>
                    </div>
                </div>
            )
        }];
    }
    return [];
};

export const facilitiesPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-facilities-payment',
            dataUrl: route('paypal.facilities.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 p-4 border-2 border-gray-200 rounded-lg hover:border-blue-300 transition-colors cursor-pointer">
                    <RadioGroupItem value="paypal" id="paypal-facilities-payment" />
                    <Label htmlFor="paypal-facilities-payment" className="cursor-pointer flex items-center space-x-3 flex-1">
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                        <div>
                            <div className="font-medium text-gray-900">{t('PayPal')}</div>
                            <div className="text-sm text-gray-500">{t('Pay securely with PayPal')}</div>
                        </div>
                    </Label>
                </div>
            )
        }];
    }
    return [];
};

export const vehicleBookingPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-vehicle-booking-payment',
            dataUrl: route('paypal.vehicle-booking.payment.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="paypal-vehicle-booking-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-yellow-500 cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-vehicle-booking-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-movie-show-payment',
            dataUrl: route('paypal.movie-booking.payment.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="paypal-movie-show-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-primary cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-movie-show-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-ngo-payment',
            dataUrl: route('paypal.ngo.donation.payment.store', { userSlug: userSlug }),
            component: (
                <div className="flex flex-col items-center text-center p-3">
                    <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-6 w-6 mb-1" />
                    <span className="text-xs font-medium">{t('PayPal')}</span>
                </div>
            )
        }];
    }
    return [];
};


export const coworkingSpacePayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const paypalEnabled = getCompanySetting('paypal_enabled');
    const paymentType = data?.type || 'membership';

    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-coworking-payment',
            dataUrl: route('paypal.coworking-space.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            paymentType: paymentType,
            component: (
                <div className="flex items-center space-x-3 p-3 border-2 border-gray-200 rounded-lg w-full hover:border-blue-300 transition-colors cursor-pointer">
                    <RadioGroupItem value="paypal" id="paypal-coworking" />
                    <Label htmlFor="paypal-coworking" className="cursor-pointer flex items-center space-x-3 flex-1">
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                        <div>
                            <div className="font-medium text-white-900">{t('PayPal')}</div>
                            <div className="text-sm text-gray-500">{t('Pay securely with PayPal')}</div>
                        </div>
                    </Label>
                </div>
            )
        }];
    }
    return [];
};

export const sportsClubPayment = (data?: any) => {

    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-sports-club-payment',
            dataUrl: route('paypal.sports-club.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="paypal-sports-club-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#df9896] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-sports-club-payment" />
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
    const { userSlug } = usePage().props as any;

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-sports-club-plan-payment',
            dataUrl: route('paypal.sports-club-plan.payment.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="paypal-sports-club-plan"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#4FAF5F] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-sports-club-plan" />
                    </div>
                </Label>
            )
        }];
    }
    return [];
};


export const influencerMarketingPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;
    const paypalEnabled = getCompanySetting('paypal_enabled');

    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-influencer-marketing',
            dataUrl: route('paypal.influencer-marketing.payment.store', { userSlug: userSlug }),
            component: (
                <>
                    <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                    <span className="text-sm font-medium text-slate-700">{t('PayPal')}</span>
                </>
            )
        }];
    }
    return [];
};

export const waterParkPayment = (data?: any) => {
    const { t } = useTranslation();
    const { userSlug } = usePage().props as any;

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-water-park-payment',
            dataUrl: route('paypal.water-park.payment.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="paypal-water-park-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-sky-500 cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-water-park-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-tvstudio-payment',
            dataUrl: route('paypal.tvstudio.payment.store', { userSlug: userSlug }),
            component: (
                <label className="flex items-center p-4 border border-gray-700 rounded-lg hover:border-red-600 transition-colors cursor-pointer">
                    <input
                        type="radio"
                        name="payment"
                        value="paypal"
                        className="text-red-600"
                    />
                    <span className="mx-3 text-white">{t('PayPal')}</span>
                    <div className="ml-auto w-12 h-8 rounded overflow-hidden bg-white flex items-center justify-center">
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-6 w-6 object-contain" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-art-showcase-payment',
            dataUrl: route('paypal.art-showcase.payment.store', { userSlug: userSlug }),
            component: (
                <div className="flex items-center space-x-3 flex-1">
                    <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                    <div>
                        <div className="font-medium text-gray-900">{t('PayPal')}</div>
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-tattoo-studio-payment',
            dataUrl: route('paypal.tattoo-studio.payment.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="paypal-tattoo-studio-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-blue-500 cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-tattoo-studio-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    const isSelected = data?.selectedMethod === 'paypal';

    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-photo-studio-payment',
            dataUrl: route('paypal.photo-studio.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <label className="cursor-pointer w-full">
                    <input
                        type="radio"
                        name="paymentMethod"
                        value="paypal"
                        className="hidden"
                        checked={isSelected}
                        onChange={() => data?.onMethodChange?.('paypal')}
                    />
                    <div className={`p-4 border-2 rounded-none transition-all flex items-center gap-3 ${isSelected ? 'border-[#674B2F] bg-[#674B2F]/5' : 'border-gray-300'
                        }`}>
                        <div className={`w-4 h-4 rounded-full border-2 flex-shrink-0 ${isSelected ? 'border-[#674B2F] bg-[#674B2F]' : 'border-gray-300'
                            }`}>
                            {isSelected && <div className="w-2 h-2 bg-white rounded-full m-auto mt-0.5"></div>}
                        </div>
                        <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                        <span className="font-medium">{t('PayPal')}</span>
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
    const paypalEnabled = getCompanySetting('paypal_enabled');

    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-ebook-payment',
            dataUrl: route('paypal.ebook.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <>
                    <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                    <span className="text-sm font-medium text-slate-700">{t('PayPal')}</span>
                </>
            )
        }];
    }
    return [];
};

export const yogaClassesPayment = (data?: any) => {
    const { t } = useTranslation();
    const { auth, userSlug } = usePage().props as any;

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-yoga-classes-payment',
            dataUrl: route('paypal.yoga-classes.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center space-x-3 flex-1">
                    <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                    <div>
                        <div className="font-medium text-gray-900">{t('PayPal')}</div>
                        <div className="text-sm text-gray-500">{t('Pay securely with PayPal')}</div>
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-hair-care-studio-payment',
            dataUrl: route('paypal.hair-care-studio.payment.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="paypal-hair-care-studio-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#C58154] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-hair-care-studio-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-pet-care-payment',
            dataUrl: route('paypal.pet-care.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="paypal-pet-care-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[#df9896] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-pet-care-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-boutique-studio-payment',
            dataUrl: route('paypal.boutique-studio.payment.store', { userSlug: userSlug }),
            component: (
                <Label htmlFor="paypal-boutique-studio-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-[var(--primary-color)] cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-boutique-studio-payment" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-investment-system-payment',
            dataUrl: route('paypal.investment-system.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <Label htmlFor="paypal-investment-payment"
                    className="block border border-gray-200 rounded-lg p-4 hover:border-blue-500 cursor-pointer transition-all duration-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-full overflow-hidden bg-white border">
                                <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                            </div>
                            <div>
                                <h5 className="text-base font-medium">{t('PayPal')}</h5>
                            </div>
                        </div>
                        <RadioGroupItem value="paypal" id="paypal-investment-payment" className="border-white text-white hover:text-blue-500" />
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-jewellery-payment',
            dataUrl: route('paypal.jewellery-store.payment.store', { userSlug: userSlug }),
            onFormSubmit: data?.onFormSubmit,
            component: (
                <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full overflow-hidden bg-white border">
                        <img src={getPackageFavicon('Paypal')} alt="PayPal Logo" className="object-contain w-full h-full" />
                    </div>
                    <div>
                        <h5 className="text-base font-medium text-gray-800">{t('PayPal')}</h5>
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

    const paypalEnabled = getCompanySetting('paypal_enabled');
    if (paypalEnabled === 'on') {
        return [{
            id: 'paypal-freelancing-wallet-payment',
            dataUrl: route('paypal.freelancing.wallet.payment.paypal.store', { userSlug: userSlug }),
            component: (
                <div className="flex items-center space-x-3 flex-1">
                    <img src={getPackageFavicon('Paypal')} alt="PayPal" className="h-8 w-8" />
                    <div>
                        <div className="font-medium text-gray-900">{t('PayPal')}</div>
                        <div className="text-sm text-gray-500">{t('Pay securely with PayPal')}</div>
                    </div>
                </div>
            )
        }];
    }
    return [];
};