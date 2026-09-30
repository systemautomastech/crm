<?php

use Illuminate\Support\Facades\Route;
use Automas\Paypal\Http\Controllers\PaypalController;
use Automas\Paypal\Http\Controllers\PaypalSettingsController;

Route::middleware('web')->prefix('paypal')->name('paypal.')->group(function () {
    // Setting
    Route::post('settings', [PaypalSettingsController::class, 'update'])->middleware(['auth', 'verified', 'PlanModuleCheck:Paypal'])->name('settings.update');

    // Plan
    Route::post('plan/payment', [PaypalController::class, 'planPayWithPaypal'])->middleware(['auth', 'verified'])->name('plan.store');
    Route::get('plan/status/{plan_id}', [PaypalController::class, 'planGetPaypalStatus'])->middleware(['auth', 'verified'])->name('plan.status');

    // Booking payment routes
    Route::post('{userSlug?}/booking/payment', [PaypalController::class, 'bookingPayWithPaypal'])->name('booking.payment.store');
    Route::get('{userSlug?}/booking/status', [PaypalController::class, 'bookingGetPaypalStatus'])->name('booking.payment.status');

    // BeautySpa payment routes
    Route::post('{userSlug?}/beauty-spa/payment', [PaypalController::class, 'beautySpaPayWithPaypal'])->name('beauty-spa.payment.store');
    Route::get('{userSlug?}/beauty-spa/status', [PaypalController::class, 'beautySpaGetPaypalStatus'])->name('beauty-spa.payment.status');

    // LMS payment routes
    Route::post('{userSlug?}/lms/payment', [PaypalController::class, 'lmsPayWithPaypal'])->name('lms.payment.store');
    Route::get('{userSlug?}/lms/status', [PaypalController::class, 'lmsGetPaypalStatus'])->name('lms.payment.status');

    // Parking payment routes
    Route::post('{userSlug}/parking/payment', [PaypalController::class, 'parkingPayWithPaypal'])->name('parking.payment.store');
    Route::get('{userSlug}/parking/status', [PaypalController::class, 'parkingGetPaypalStatus'])->name('parking.payment.status');

    // Laundry payment routes
    Route::post('{userSlug?}/laundry/payment', [PaypalController::class, 'laundryPayWithPaypal'])->name('laundry.payment.store');
    Route::get('{userSlug?}/laundry/status', [PaypalController::class, 'laundryGetPaypalStatus'])->name('laundry.payment.status');

    // Events payment routes
    Route::post('{userSlug?}/events/payment', [PaypalController::class, 'eventsPayWithPaypal'])->name('events-management.payment.store');
    Route::get('{userSlug?}/events/status', [PaypalController::class, 'eventsGetPaypalStatus'])->name('events-management.payment.status');

    // Holidayz payment routes
    Route::post('{userSlug?}/holidayz/payment', [PaypalController::class, 'holidayzPayWithPaypal'])->name('holidayz.payment.store');
    Route::get('{userSlug?}/holidayz/status', [PaypalController::class, 'holidayzGetPaypalStatus'])->name('holidayz.payment.status');

    // Vehicle Booking Payment Routes
    Route::post('{userSlug?}/vehicle-booking/payment', [PaypalController::class, 'vehicleBookingPayWithPaypal'])->name('vehicle-booking.payment.store');
    Route::get('{userSlug?}/vehicle-booking/status', [PaypalController::class, 'vehicleBookingGetPaypalStatus'])->name('vehicle-booking.payment.status');

    // Facilities Payment Routes
    Route::post('{userSlug}/facilities/payment', [PaypalController::class, 'facilitiesPaymentWithPaypal'])->name('facilities.payment.store');
    Route::get('{userSlug}/facilities/status', [PaypalController::class, 'facilitiesGetPaypalStatus'])->name('facilities.payment.status');

    // Movie Booking Payment Routes
    Route::post('{userSlug?}/movie-booking/payment', [PaypalController::class, 'movieBookingPayWithPaypal'])->name('movie-booking.payment.store');
    Route::get('{userSlug?}/movie-booking/status', [PaypalController::class, 'movieBookingGetPaypalStatus'])->name('movie-booking.payment.status');

    // NGO Donation Payment Routes
    Route::post('{userSlug?}/ngo/donation/payment', [PaypalController::class, 'ngoDonationPayWithPaypal'])->name('ngo.donation.payment.store');
    Route::get('{userSlug?}/ngo/donation/status', [PaypalController::class, 'ngoDonationGetPaypalStatus'])->name('ngo.donation.payment.status');

    // TV Studio Payment Routes
    Route::post('{userSlug?}/tvstudio/payment', [PaypalController::class, 'tvStudioPayWithPaypal'])->name('tvstudio.payment.store');
    Route::get('{userSlug?}/tvstudio/status', [PaypalController::class, 'tvStudioGetPaypalStatus'])->name('tvstudio.payment.status');

    // Sports Club Payment Routes
    Route::post('{userSlug?}/sports-club/payment', [PaypalController::class, 'sportsClubPayWithPaypal'])->name('sports-club.payment.store');
    Route::get('{userSlug?}/sports-club/status', [PaypalController::class, 'sportsClubGetPaypalStatus'])->name('sports-club.payment.status');

    // Sports Club Plan Payment Routes
    Route::post('{userSlug?}/sports-club-plan/payment', [PaypalController::class, 'sportsClubPlanPayWithPaypal'])->name('sports-club-plan.payment.store');
    Route::get('{userSlug?}/sports-club-plan/status', [PaypalController::class, 'sportsClubPlanGetPaypalStatus'])->name('sports-club-plan.payment.status');

    // Photo Studio Payment Routes
    Route::post('{userSlug?}/photo-studio/payment', [PaypalController::class, 'photoStudioPayWithPaypal'])->name('photo-studio.payment.store');
    Route::get('{userSlug?}/photo-studio/status', [PaypalController::class, 'photoStudioGetPaypalStatus'])->name('photo-studio.payment.status');

    // Investment System Payment Routes
    Route::post('{userSlug?}/investment-system/payment', [PaypalController::class, 'investmentSystemPayWithPaypal'])->name('investment-system.payment.store');
    Route::get('{userSlug?}/investment-system/status', [PaypalController::class, 'investmentSystemGetPaypalStatus'])->name('investment-system.payment.status');

    // PetCare Payment Routes
    Route::post('{userSlug?}/pet-care/payment', [PaypalController::class, 'petCarePayWithPaypal'])->name('pet-care.payment.store');
    Route::get('{userSlug?}/pet-care/status', [PaypalController::class, 'petCareGetPaypalStatus'])->name('pet-care.payment.status');

    // Coworking Space Payment Routes
    Route::post('{userSlug?}/coworking-space/payment', [PaypalController::class, 'coworkingSpacePayWithPaypal'])->name('coworking-space.payment.store');
    Route::get('{userSlug?}/coworking-space/status', [PaypalController::class, 'coworkingSpaceGetPaypalStatus'])->name('coworking-space.payment.status');

    // Influencer Marketing Payment Routes
    Route::post('{userSlug?}/influencer-marketing/payment', [PaypalController::class, 'influencerMarketingPayWithPaypal'])->name('influencer-marketing.payment.store');
    Route::get('{userSlug?}/influencer-marketing/status', [PaypalController::class, 'influencerMarketingGetPaypalStatus'])->name('influencer-marketing.payment.status');

    // Hair Care Studio Payment Routes
    Route::post('{userSlug?}/hair-care-studio/payment', [PaypalController::class, 'hairCareStudioPayWithPaypal'])->name('hair-care-studio.payment.store');
    Route::get('{userSlug?}/hair-care-studio/status', [PaypalController::class, 'hairCareStudioGetPaypalStatus'])->name('hair-care-studio.payment.status');

    // Art Showcase Payment Routes
    Route::post('{userSlug?}/art-showcase/payment', [PaypalController::class, 'artShowcasePayWithPaypal'])->name('art-showcase.payment.store');
    Route::get('{userSlug?}/art-showcase/status', [PaypalController::class, 'artShowcaseGetPaypalStatus'])->name('art-showcase.payment.status');

    // Yoga Classes Payment Routes
    Route::post('{userSlug?}/yoga-classes/payment', [PaypalController::class, 'yogaClassesPayWithPaypal'])->name('yoga-classes.payment.store');
    Route::get('{userSlug?}/yoga-classes/status', [PaypalController::class, 'yogaClassesGetPaypalStatus'])->name('yoga-classes.payment.status');

    // Tattoo Studio Payment Routes
    Route::post('{userSlug?}/tattoo-studio/payment', [PaypalController::class, 'tattooStudioPayWithPaypal'])->name('tattoo-studio.payment.store');
    Route::get('{userSlug?}/tattoo-studio/status', [PaypalController::class, 'tattooStudioGetPaypalStatus'])->name('tattoo-studio.payment.status');

    // Boutique Studio Payment Routes
    Route::post('{userSlug?}/boutique-studio/payment', [PaypalController::class, 'boutiqueStudioPayWithPaypal'])->name('boutique-studio.payment.store');
    Route::get('{userSlug?}/boutique-studio/status', [PaypalController::class, 'boutiqueStudioGetPaypalStatus'])->name('boutique-studio.payment.status');

    // Water Park Payment Routes
    Route::post('{userSlug?}/water-park/payment', [PaypalController::class, 'waterParkPayWithPaypal'])->name('water-park.payment.store');
    Route::get('{userSlug?}/water-park/status', [PaypalController::class, 'waterParkGetPaypalStatus'])->name('water-park.payment.status');

    // Ebook Payment Routes
    Route::post('{userSlug?}/ebook/payment', [PaypalController::class, 'ebookPayWithPaypal'])->name('ebook.payment.store');
    Route::get('{userSlug?}/ebook/status', [PaypalController::class, 'ebookGetPaypalStatus'])->name('ebook.payment.status');

    // Jewellery Store Payment Routes
    Route::post('{userSlug?}/jewellery-store/payment', [PaypalController::class, 'jewelleryPayWithPaypal'])->name('jewellery-store.payment.store');
    Route::get('{userSlug?}/jewellery-store/status', [PaypalController::class, 'jewelleryGetPaypalStatus'])->name('jewellery-store.payment.status');

    // FreelancingPlatform Wallet payment routes
    Route::post('{userSlug?}/freelancing/wallet/payment', [PaypalController::class, 'freelancingWalletPayWithPaypal'])->name('freelancing.wallet.payment.paypal.store');
    Route::get('{userSlug?}/freelancing/wallet/status', [PaypalController::class, 'freelancingWalletGetPaypalStatus'])->name('freelancing.wallet.payment.paypal.status');
});
