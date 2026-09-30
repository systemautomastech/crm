<?php

use Illuminate\Support\Facades\Route;
use Automas\Stripe\Http\Controllers\StripeSettingsController;
use Automas\Stripe\Http\Controllers\StripeController;


Route::middleware('web')->prefix('stripe')->group(function () {

    Route::post('settings', [StripeSettingsController::class, 'update'])->name('stripe.settings.update')->middleware(['auth', 'verified', 'PlanModuleCheck:Stripe']);

    // Plan payment routes
    Route::post('/plan/company/payment', [StripeController::class, 'planPayWithStripe'])->name('payment.stripe.store')->middleware('auth');
    Route::get('/plan/company/status', [StripeController::class, 'planGetStripeStatus'])->name('payment.stripe.status')->middleware('auth');

    // Booking payment routes
    Route::post('{userSlug?}/booking/payment', [StripeController::class, 'bookingPayWithStripe'])->name('booking.payment.stripe.store');
    Route::get('{userSlug?}/booking/status', [StripeController::class, 'bookingGetStripeStatus'])->name('booking.payment.stripe.status');

    // BeautySpa payment routes
    Route::post('{userSlug?}/beauty-spa/payment', [StripeController::class, 'beautySpaPayWithStripe'])->name('beauty-spa.payment.stripe.store');
    Route::get('{userSlug?}/beauty-spa/status', [StripeController::class, 'beautySpaGetStripeStatus'])->name('beauty-spa.payment.stripe.status');

    // LMS payment routes
    Route::post('{userSlug?}/lms/payment', [StripeController::class, 'lmsPayWithStripe'])->name('lms.payment.stripe.store');
    Route::get('{userSlug?}/lms/status', [StripeController::class, 'lmsGetStripeStatus'])->name('lms.payment.stripe.status');

    // Laundry payment routes
    Route::post('{userSlug?}/laundry/payment', [StripeController::class, 'laundryPayWithStripe'])->name('laundry.payment.stripe.store');
    Route::get('{userSlug?}/laundry/status', [StripeController::class, 'laundryGetStripeStatus'])->name('laundry.payment.stripe.status');

    // Parking payment routes
    Route::post('{userSlug}/parking/payment', [StripeController::class, 'parkingPayWithStripe'])->name('parking.payment.stripe.store');
    Route::get('{userSlug}/parking/status', [StripeController::class, 'parkingGetStripeStatus'])->name('parking.payment.stripe.status');

    // EventsManagement payment routes
    Route::post('{userSlug?}/events/payment', [StripeController::class, 'eventsPayWithStripe'])->name('events-management.payment.stripe.store');
    Route::get('{userSlug?}/events/status', [StripeController::class, 'eventsGetStripeStatus'])->name('events-management.payment.stripe.status');

    // Holidayz payment routes
    Route::post('{userSlug?}/holidayz/payment', [StripeController::class, 'holidayzPayWithStripe'])->name('holidayz.payment.stripe.store');
    Route::get('{userSlug?}/holidayz/status', [StripeController::class, 'holidayzGetStripeStatus'])->name('holidayz.payment.stripe.status');

    // Facilities payment routes
    Route::post('{userSlug}/facilities/payment', [StripeController::class, 'facilitiesPaymentWithStripe'])->name('facilities.payment.stripe.store');
    Route::get('{userSlug}/facilities/status', [StripeController::class, 'facilitiesGetStripeStatus'])->name('facilities.payment.stripe.status');

    // Vehicle Booking payment routes
    Route::post('{userSlug?}/vehicle-booking/payment', [StripeController::class, 'vehicleBookingPayWithStripe'])->name('vehicle-booking.payment.stripe.store');
    Route::get('{userSlug?}/vehicle-booking/status', [StripeController::class, 'vehicleBookingGetStripeStatus'])->name('vehicle-booking.payment.stripe.status');

    // Movie Booking payment routes
    Route::post('{userSlug?}/movie-booking/payment', [StripeController::class, 'movieBookingPayWithStripe'])->name('movie-booking.payment.stripe.store');
    Route::get('{userSlug?}/movie-booking/status', [StripeController::class, 'movieBookingGetStripeStatus'])->name('movie-booking.payment.stripe.status');

    // NGO donation payment routes
    Route::post('{userSlug?}/ngo/donation/payment', [StripeController::class, 'ngoDonationPayWithStripe'])->name('ngo.donation.payment.stripe.store');
    Route::get('{userSlug?}/ngo/donation/status', [StripeController::class, 'ngoDonationGetStripeStatus'])->name('ngo.donation.payment.stripe.status');

    // Coworking Space payment routes
    Route::post('{userSlug?}/coworking-space/payment', [StripeController::class, 'coworkingSpacePayWithStripe'])->name('coworking-space.payment.stripe.store');
    Route::get('{userSlug?}/coworking-space/status', [StripeController::class, 'coworkingSpaceGetStripeStatus'])->name('coworking-space.payment.stripe.status');

    // Sports Club payment routes
    Route::post('{userSlug?}/sports-club/payment', [StripeController::class, 'sportsClubPayWithStripe'])->name('sports-club.payment.stripe.store');
    Route::get('{userSlug?}/sports-club/status', [StripeController::class, 'sportsClubGetStripeStatus'])->name('sports-club.payment.stripe.status');

    // Sports Club Plan payment routes
    Route::post('{userSlug?}/sports-club-plan/payment', [StripeController::class, 'sportsClubPlanPayWithStripe'])->name('sports-club-plan.payment.stripe.store');
    Route::get('{userSlug?}/sports-club-plan/status', [StripeController::class, 'sportsClubPlanGetStripeStatus'])->name('sports-club-plan.payment.stripe.status');

    // InfluencerMarketing payment routes
    Route::post('{userSlug?}/influencer-marketing/payment', [StripeController::class, 'influencerMarketingPayWithStripe'])->name('influencer-marketing.payment.stripe.store');
    Route::get('{userSlug?}/influencer-marketing/status', [StripeController::class, 'influencerMarketingGetStripeStatus'])->name('influencer-marketing.payment.stripe.status');

    // Water Park Booking payment routes
    Route::post('{userSlug?}/water-park/payment', [StripeController::class, 'waterParkBookingPayWithStripe'])->name('water-park.payment.stripe.store');
    Route::get('{userSlug?}/water-park/status', [StripeController::class, 'waterParkBookingGetStripeStatus'])->name('water-park.payment.stripe.status');

    // TVStudio payment routes
    Route::post('{userSlug?}/tvstudio/payment', [StripeController::class, 'tvStudioPayWithStripe'])->name('tvstudio.payment.stripe.store');
    Route::get('{userSlug?}/tvstudio/status', [StripeController::class, 'tvStudioGetStripeStatus'])->name('tvstudio.payment.stripe.status');

    // ArtShowcase payment routes
    Route::post('{userSlug?}/art-showcase/payment', [StripeController::class, 'artShowcasePayWithStripe'])->name('art-showcase.payment.stripe.store');
    Route::get('{userSlug?}/art-showcase/status', [StripeController::class, 'artShowcaseGetStripeStatus'])->name('art-showcase.payment.stripe.status');

    Route::post('{userSlug?}/tattoo-studio/payment', [StripeController::class, 'tattooStudioBookingPayWithStripe'])->name('tattoo-studio.payment.stripe.store');
    Route::get('{userSlug?}/tattoo-studio/status', [StripeController::class, 'tattooStudioBookingGetStripeStatus'])->name('tattoo-studio.payment.stripe.status');

    // PhotoStudio payment routes
    Route::post('{userSlug?}/photo-studio/payment', [StripeController::class, 'photoStudioPayWithStripe'])->name('photo-studio.payment.stripe.store');
    Route::get('{userSlug?}/photo-studio/status', [StripeController::class, 'photoStudioGetStripeStatus'])->name('photo-studio.payment.stripe.status');

    // Ebook payment routes
    Route::post('{userSlug?}/ebook/payment', [StripeController::class, 'ebookPayWithStripe'])->name('ebook.payment.stripe.store');
    Route::get('{userSlug?}/ebook/status', [StripeController::class, 'ebookGetStripeStatus'])->name('ebook.payment.stripe.status');

    // YogaClasses payment routes
    Route::post('{userSlug?}/yoga-classes/payment', [StripeController::class, 'yogaClassesPayWithStripe'])->name('yoga-classes.payment.stripe.store');
    Route::get('{userSlug?}/yoga-classes/status', [StripeController::class, 'yogaClassesGetStripeStatus'])->name('yoga-classes.payment.stripe.status');

    // HairCareStudio payment routes
    Route::post('{userSlug?}/hair-care-studio/payment', [StripeController::class, 'hairCareStudioPayWithStripe'])->name('hair-care-studio.payment.stripe.store');
    Route::get('{userSlug?}/hair-care-studio/status', [StripeController::class, 'hairCareStudioGetStripeStatus'])->name('hair-care-studio.payment.stripe.status');

    // PetCare payment routes
    Route::post('{userSlug?}/pet-care/payment', [StripeController::class, 'petCarePayWithStripe'])->name('pet-care.payment.stripe.store');
    Route::get('{userSlug?}/pet-care/status', [StripeController::class, 'petCareGetStripeStatus'])->name('pet-care.payment.stripe.status');

    // Boutique Studio payment routes
    Route::post('{userSlug?}/boutique-studio/payment', [StripeController::class, 'boutiqueStudioPayWithStripe'])->name('boutique-studio.payment.stripe.store');
    Route::get('{userSlug?}/boutique-studio/status', [StripeController::class, 'boutiqueStudioGetStripeStatus'])->name('boutique-studio.payment.stripe.status');

    // InvestmentSystem payment routes
    Route::post('{userSlug?}/investment-system/payment', [StripeController::class, 'investmentSystemPayWithStripe'])->name('investment-system.payment.stripe.store');
    Route::get('{userSlug?}/investment-system/status', [StripeController::class, 'investmentSystemGetStripeStatus'])->name('investment-system.payment.stripe.status');

    // Jewellery Store payment routes
    Route::post('{userSlug?}/jewellery-store/payment', [StripeController::class, 'jewelleryStorePayWithStripe'])->name('jewellery-store.payment.stripe.store');
    Route::get('{userSlug?}/jewellery-store/status', [StripeController::class, 'jewelleryStoreGetStripeStatus'])->name('jewellery-store.payment.stripe.status');

    // FreelancingPlatform Wallet payment routes
        Route::post('{userSlug?}/freelancing/wallet/payment', [StripeController::class, 'freelancingWalletPayWithStripe'])->name('freelancing.wallet.payment.stripe.store');
        Route::get('{userSlug?}/freelancing/wallet/status', [StripeController::class, 'freelancingWalletGetStripeStatus'])->name('freelancing.wallet.payment.stripe.status');
});
