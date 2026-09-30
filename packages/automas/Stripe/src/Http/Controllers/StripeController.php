<?php

namespace Automas\Stripe\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Plan;
use App\Models\Order;
use Automas\Stripe\Events\StripePaymentStatus;
use Automas\Bookings\Models\BookingAppointment;
use Automas\Bookings\Models\BookingPackage;
use Automas\Bookings\Models\BookingCustomer;
use Automas\LaundryManagement\Models\LaundryRequest;
use Automas\Holidayz\Models\HolidayzCart;
use Automas\Holidayz\Models\HolidayzRoomBooking;
use Automas\Holidayz\Models\HolidayzRoomBookingItem;
use Automas\Holidayz\Models\HolidayzCoupon;
use Automas\Holidayz\Models\HolidayzCouponUsage;
use Automas\Holidayz\Helpers\HolidayzAvailabilityHelper;

use Automas\LMS\Models\LMSCart;
use Automas\LMS\Models\LMSOrder;
use Automas\LMS\Models\LMSOrderItem;
use Automas\LMS\Models\LMSCoupon;
use Inertia\Inertia;
use Automas\BeautySpaManagement\Models\BeautyBooking;
use Automas\BeautySpaManagement\Models\BeautyService;
use Automas\BeautySpaManagement\Models\BeautyBookingReceipt;
use Automas\ArtShowcase\Events\CreateArtWorkOrderPayment;
use Automas\ArtShowcase\Models\ArtShowcaseArtWork;
use Automas\ArtShowcase\Models\ArtShowcaseArtWorkOrder;
use Automas\BeautySpaManagement\Events\BeautyBookingPayments;
use Automas\BeautySpaManagement\Models\BeautyServiceOffer;
use Automas\Bookings\Events\BookingAppointmentPayments;
use Automas\CoworkingSpaceManagement\Events\CoworkingBookingPayments;
use Automas\CoworkingSpaceManagement\Events\CoworkingMembershipPayments;
use Automas\CoworkingSpaceManagement\Http\Controllers\CoworkingMembershipController;
use Automas\CoworkingSpaceManagement\Models\CoworkingBooking;
use Automas\CoworkingSpaceManagement\Models\CoworkingMembership;
use Automas\CoworkingSpaceManagement\Models\CoworkingMembershipPlan;
use Automas\EventsManagement\Events\EventBookingPayments;
use Automas\ParkingManagement\Models\ParkingBooking;
use Automas\LaundryManagement\Events\LaundryBookingPayments;
use Automas\EventsManagement\Models\Event;
use Automas\EventsManagement\Models\EventBooking;
use Automas\EventsManagement\Models\EventBookingPayment;

use Automas\Facilities\Services\FacilitiesBookingService;
use Automas\Holidayz\Events\HolidayzBookingPayments;
use Automas\InfluencerMarketing\Models\InfluencerMarketingDeposit;
use Automas\InfluencerMarketing\Events\InfluencerMarketingPayment;
use Automas\LMS\Events\LMSOrderPayments;
use Automas\NGOManagment\Events\CreateNgoDonation;
use Automas\NGOManagment\Http\Controllers\DonationController;
use Automas\NGOManagment\Models\NgoCampaign;
use Automas\NGOManagment\Models\NgoDonation;
use Automas\NGOManagment\Models\NgoDonor;
use Automas\MovieShowBookingSystem\Events\MovieBookingPayments;
use Automas\MovieShowBookingSystem\Models\MovieBooking;
use Automas\ParkingManagement\Events\ParkingBookingPayments;
use Automas\SportsClubAndAcademyManagement\Events\SportsClubBookingPayments;
use Automas\SportsClubAndAcademyManagement\Events\SportsClubPlanPayments;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubAndGroundOrder;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubAssignedMembership;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubBookingFacility;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubFacility;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubGround;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubMember;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubMembershipPlan;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubMembershipPlanPayment;

use Automas\VehicleBookingManagement\Events\VehicleBookingPayments;
use Automas\VehicleBookingManagement\Models\VehicleBooking;
use Automas\WaterParkManagement\Events\WaterParkBookingPaymentStripe;
use Automas\WaterParkManagement\Models\WaterParkBooking;

use Automas\TVStudio\Services\TVStudioCheckoutService;
use Automas\TattooStudioManagement\Models\TattooAppointment;
use Automas\TattooStudioManagement\Events\TattooAppointmentPaymentStripe;

use Automas\PhotoStudioManagement\Models\PhotoStudioAppointment;
use Automas\PhotoStudioManagement\Models\PhotoStudioAppointmentPayment;
use Automas\PhotoStudioManagement\Models\PhotoStudioService;
use Automas\PhotoStudioManagement\Events\PhotoStudioAppointmentPayments;
use Automas\Ebook\Models\EbookBookOrder;
use Automas\Ebook\Events\EbookPayment;

use Automas\YogaClasses\Models\YogaClassesCart;
use Automas\YogaClasses\Models\YogaClassesOrder;
use Automas\YogaClasses\Models\YogaClassesPurchasedCourse;
use Automas\YogaClasses\Events\YogaClassesOrderPayments;

use Automas\HairAndCareStudio\Models\HairCareAppointment;
use Automas\HairAndCareStudio\Models\HairCarePayment;
use Automas\HairAndCareStudio\Events\HairCareStudioOrderPayments;

use Automas\PetCare\Models\PetCareGroomingPackage;
use Automas\PetCare\Models\PetCareMembership;
use Automas\PetCare\Events\PetCareMembershipPayments;
use Automas\PetCare\Models\PetCareService;
use Automas\PetCare\Models\PetCareBooking;
use Automas\PetCare\Events\PetCareBookingPayment;

use Automas\BoutiqueAndDesignerStudio\Models\BoutiqueBooking;
use Automas\BoutiqueAndDesignerStudio\Events\BoutiqueBookingPaymentStripe;
use Automas\Facilities\Events\FacilityBookingPayment;
use Automas\LaundryManagement\Models\LaundryInvoice;
use Automas\LaundryManagement\Models\LaundryPayment;

use Automas\InvestmentSystem\Models\InvestorDeposit;
use Automas\InvestmentSystem\Models\InvestmentPlan;
use Automas\InvestmentSystem\Models\InvestorTransaction;
use Automas\InvestmentSystem\Events\InvestorDepositPayment;

use Automas\JewelleryStoreManagement\Models\JewelleryStoreJewelleryBooking;
use Automas\JewelleryStoreManagement\Models\JewelleryStoreItem;
use Automas\JewelleryStoreManagement\Events\JewelleryStoreJewelleryBookingPayments;

use Automas\FreelancingPlatform\Models\FreelancingClientWallet;
use Automas\FreelancingPlatform\Models\FreelancingClientWalletTransaction;
use Automas\FreelancingPlatform\Events\ClientWalletPayment;

use Automas\Stripe\Services\StripeService;

class StripeController extends Controller
{
    public function planPayWithStripe(Request $request)
    {
        try {
            $plan = Plan::find($request->plan_id);
            $user = User::find($request->user_id);
            $admin_settings = getAdminAllSetting();
            $admin_currancy = !empty($admin_settings['defaultCurrency']) ? $admin_settings['defaultCurrency'] : '';
            $user_counter = !empty($request->user_counter_input) ? $request->user_counter_input : 0;
            $user_module = !empty($request->user_module_input) ? $request->user_module_input : '';
            $storage_limit = !empty($request->storage_limit_input) ? $request->storage_limit_input : 0;
            $duration = !empty($request->time_period) ? $request->time_period : 'Month';
            $user_module_price = 0;

            if (!empty($user_module) && $plan->custom_plan == 1) {
                $user_module_array = explode(',', $user_module);
                foreach ($user_module_array as $key => $value) {
                    $temp = ($duration == 'Year') ? ModulePriceByName($value)['yearly_price'] : ModulePriceByName($value)['monthly_price'];
                    $user_module_price = $user_module_price + $temp;
                }
            }
            $user_price = 0;
            if ($user_counter > 0) {
                $temp = ($duration == 'Year') ? $plan->price_per_user_yearly : $plan->price_per_user_monthly;
                $user_price = $user_counter * $temp;
            }

            $storage_price = 0;
            if ($storage_limit > 0 && $plan->custom_plan == 1) {
                $temp = ($duration == 'Year') ? $plan->price_per_storage_yearly : $plan->price_per_storage_monthly;
                $storage_price = $storage_limit * $temp;
            }

            $plan_price = ($duration == 'Year') ? $plan->package_price_yearly : $plan->package_price_monthly;
            $counter = [
                'user_counter' => $user_counter,
                'storage_limit' => $storage_limit,
            ];

            $stripe_session = '';
            $orderID = strtoupper(substr(uniqid(), -12));

            if ($plan) {
                /* Check for code usage */
                $plan->discounted_price = false;
                $price = $plan_price + $user_module_price + $user_price + $storage_price;

                if ($request->coupon_code) {
                    $validation = applyCouponDiscount($request->coupon_code, $price, auth()->id());
                    if ($validation['valid']) {
                        $price = $validation['final_amount'];
                    }
                }
                if ($price <= 0) {
                    $assignPlan = assignPlan($plan->id, $duration, $user_module, $counter, $request->user_id);
                    if ($assignPlan['is_success']) {
                        return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
                    } else {
                        return redirect()->route('plans.index')->with('error', __('Something went wrong, Please try again,'));
                    }
                }

                /* Initiate Stripe */
                $service = new StripeService();
                $stripe_session = $service->initiatePayment([
                    'amount' => $price,
                    'product_name' => $plan->name ?? 'Basic Package',
                    'description' => "{$plan->name} - {$duration}",
                    'callback_url' => route('payment.stripe.status', ['order_id' => $orderID,]),
                    'order_id' => $orderID,
                    'metadata' => [
                        'plan_id' => $plan->id,
                        'user_module' => $user_module,
                        'duration' => $duration,
                        'user_counter' => $user_counter,
                        'storage_limit' => $storage_limit,
                        'coupon_code' => $request->coupon_code,
                        'user_id' => $user->id,
                    ],
                ]);

                if (isset($stripe_session->url)) {

                    $order = new Order();
                    $order->order_id = $orderID;
                    $order->name = $user->name ?? '';
                    $order->email = $user->email ?? '';
                    $order->plan_name = !empty($plan->name) ? $plan->name : 'Basic Package';
                    $order->plan_id = $plan->id;
                    $order->price = !empty($price) ? $price : 0;
                    $order->currency = $admin_currancy;
                    $order->txn_id = $stripe_session->id;
                    $order->payment_type = 'Stripe';
                    $order->payment_status = 'pending';
                    $order->receipt = null;
                    $order->created_by = $user->id;
                    $order->save();

                    return redirect()->to($stripe_session->url);
                }
                return redirect()->route('plans.index')->with('error', __('Failed to create Stripe session.'));
            } else {
                return redirect()->route('plans.index')->with('error', __('The Plan has been deleted.'));
            }
        } catch (\Exception $e) {
            return redirect()->route('plans.index')->with('error', $e->getMessage());
        }
    }

    public function planGetStripeStatus(Request $request)
    {
        try {
            $Order = Order::where('order_id', $request->order_id)->first();
            $service = new StripeService();
            if (
                $Order
                && $request->return_type == 'success'
                && $service->verifyPayment($request)
            ) {

                $plan = Plan::find($request->plan_id);
                $counter = [
                    'user_counter' => $request['user_counter'] ?: 0,
                    'storage_limit' => $request['storage_limit'] ?: 0,
                ];
                $assignPlan = assignPlan($plan->id, $request->duration, $request->user_module, $counter, $request->user_id);

                if ($assignPlan['is_success']) {

                    $Order->payment_status = 'succeeded';
                    $Order->receipt = $request->receipt_url;
                    $Order->save();

                    if ($request->coupon_code) {
                        $coupon = Coupon::where('code', $request->coupon_code)->first();
                        if ($coupon) {
                            recordCouponUsage($coupon->id, $request->user_id, $request->order_id);
                        }
                    }
                    $type = 'Subscription';
                    try {
                        StripePaymentStatus::dispatch($plan, $type, $Order);
                    } catch (\Exception $e) {
                    }

                    return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
                }
            }

            $Order->payment_status = 'failed';
            $Order->save();
            return redirect()->route('plans.index')->with('error', __('Something went wrong, Please try again,'));
        } catch (\Exception $exception) {
            return redirect()->route('plans.index')->with('error', $exception->getMessage());
        }
    }

    public function bookingPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $package = BookingPackage::find($request->selectedPackageItem);
                if (!$package) {
                    return redirect()->back()->with('error', __('Package not found.'));
                }

                $price = $package->price ?? 0;
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));

                $stripeService = new StripeService($userSlug ?? null);
                $stripe_session = $stripeService->initiatePayment([
                    'amount' => $price,
                    'product_name' => $package->name ?? 'Booking Service',
                    'description' => 'Booking Service Payment',
                    'metadata' => [
                        'package_id' => $package->id,
                        'selectedDate' => $request->selectedDate,
                        'selectedItem' => $request->selectedItem,
                        'selectedPackageItem' => $request->selectedPackageItem,
                        'start_time' => $request->input('selectedTimeSlot.start_time'),
                        'end_time' => $request->input('selectedTimeSlot.end_time'),
                        'firstName' => $request->input('formData.firstName'),
                        'lastName' => $request->input('formData.lastName'),
                        'email' => $request->input('formData.email'),
                        'phone' => $request->input('formData.phone'),
                        'description' => $request->input('formData.description'),
                        'paymentOption' => $request->input('formData.paymentOption'),
                    ],
                    'callback_url' => route('booking.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('booking.home', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function bookingGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $package = BookingPackage::find($request->selectedPackageItem);
                    if (!$package) {
                        return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('Package not found.'));
                    }

                    $userId = $package->created_by ?? null;

                    $customer = BookingCustomer::where('email', $request->email)
                        ->where('created_by', $userId)
                        ->first();

                    if (!$customer) {
                        $customer = new BookingCustomer();
                        $customer->first_name = $request->firstName;
                        $customer->last_name = $request->lastName;
                        $customer->email = $request->email;
                        $customer->mobile_number = $request->phone;
                        $customer->description = $request->description ?? null;
                        $customer->created_by = $userId;
                        $customer->creator_id = $userId;
                        $customer->save();
                    }

                    $currentYear = date('Y');
                    $lastAppointment = BookingAppointment::where('created_by', $userId)
                        ->where('appointment_number', 'like', 'APT-' . $currentYear . '-' . $userId . '-%')
                        ->orderBy('appointment_number', 'desc')
                        ->first();

                    if ($lastAppointment) {
                        $lastNumber = (int) substr($lastAppointment->appointment_number, -4);
                        $nextNumber = $lastNumber + 1;
                    } else {
                        $nextNumber = 1;
                    }

                    $appointmentNumber = 'APT-' . $currentYear . '-' . $userId . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                    $appointment = new BookingAppointment();
                    $appointment->appointment_number = $appointmentNumber;
                    $appointment->date = $request->selectedDate;
                    $appointment->item_id = $request->selectedItem;
                    $appointment->package_id = $request->selectedPackageItem;
                    $appointment->customer_id = $customer->id;
                    $appointment->start_time = $request->start_time;
                    $appointment->end_time = $request->end_time;
                    $appointment->payment = 'Stripe';
                    $appointment->status = 'confirmed';
                    $appointment->payment_status = 'paid';
                    $appointment->payment_receipt = $request->receipt_url;
                    $appointment->online_payment_id = $request->session_id ?? null;
                    $appointment->created_by = $userId;
                    $appointment->creator_id = $userId;
                    $appointment->save();

                    try {
                        BookingAppointmentPayments::dispatch($appointment);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('success', __('Payment completed and appointment created successfully!'));
                } else {
                    return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
                }
            }
            return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $exception) {
            return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', $exception->getMessage());
        }
    }

    public function beautySpaPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;

                $service = BeautyService::where('id', $request->service)
                    ->where('created_by', $userId)
                    ->firstOrFail();

                $offers = BeautyServiceOffer::where('beauty_service_id', $service->id)
                    ->where('start_date', '<=', $request->date)
                    ->where('end_date', '>=', $request->date)
                    ->where('created_by', $userId)
                    ->get();
                $price = ($offers->isNotEmpty() ? $offers->sum('offer_price') : $service->price) * $request->person;
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount' => $price,
                    'product_name' => $service->name ?? 'Beauty Service',
                    'description' => 'Beauty Service Payment',
                    'metadata' => [
                        'service' => $request->service,
                        'date' => $request->date,
                        'time_slot' => $request->time_slot,
                        'person' => $request->person,
                        'gender' => $request->gender,
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone_number' => $request->phone_number,
                        'reference' => $request->reference,
                        'additional_notes' => $request->additional_notes,
                        'payment_option' => $request->payment_option
                    ],
                    'callback_url' => route('beauty-spa.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function beautySpaGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);
                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {

                    $service = BeautyService::where('id', $request->service)
                        ->where('created_by', $userId)
                        ->first();

                    $offers = BeautyServiceOffer::where('beauty_service_id', $service->id)
                        ->where('start_date', '<=', $request->date)
                        ->where('end_date', '>=', $request->date)
                        ->where('created_by', $userId)
                        ->get();

                    $servicePrice = ($offers->isNotEmpty() ? $offers->sum('offer_price') : $service->price) * $request->person;
                    $times = explode('-', $request->time_slot);

                    $booking = new BeautyBooking();
                    $booking->name = $request->name;
                    $booking->email = $request->email;
                    $booking->phone_number = $request->phone_number;
                    $booking->service = $request->service;
                    $booking->date = $request->date;
                    $booking->start_time = $times[0];
                    $booking->end_time = $times[1];
                    $booking->person = $request->person;
                    $booking->price = $servicePrice;
                    $booking->gender = $request->gender;
                    $booking->reference = $request->reference;
                    $booking->notes = $request->additional_notes;
                    $booking->payment_option = 'Stripe';
                    $booking->payment_status = 'paid';
                    $booking->stage_id = 0;
                    $booking->creator_id = null;
                    $booking->created_by = $userId;
                    $booking->save();

                    $beautyreceipt                  = new BeautyBookingReceipt();
                    $beautyreceipt->beauty_booking_id      = $booking->id;
                    $beautyreceipt->name            = $booking->name;
                    $beautyreceipt->service         = $booking->service;
                    $beautyreceipt->number          = $booking->number;
                    $beautyreceipt->gender          = $booking->gender;
                    $beautyreceipt->start_time      = $booking->start_time;
                    $beautyreceipt->end_time        = $booking->end_time;
                    $beautyreceipt->price           = $booking->price;
                    $beautyreceipt->payment_type    = 'Stripe';
                    $beautyreceipt->created_by      = $booking->created_by;
                    $beautyreceipt->save();

                    try {
                        BeautyBookingPayments::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('beauty-spa.booking-success', ['userSlug' => $userSlug, 'id' => encrypt($booking->id)])
                        ->with('success', __('Payment completed and booking confirmed successfully!'));
                }

                return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function lmsPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $student = auth('lms_student')->user();
                if (!$student) {
                    return redirect()->route('lms.frontend.login', ['userSlug' => $userSlug]);
                }

                $cartItems = LMSCart::where('created_by', $user->id)
                    ->where('student_id', $student->id)
                    ->with('course')
                    ->get();

                if ($cartItems->isEmpty()) {
                    return redirect()->route('lms.frontend.cart', ['userSlug' => $userSlug])
                        ->with('error', __('Your cart is empty'));
                }

                $originalTotal = $cartItems->sum('original_price');
                $subtotal = $cartItems->sum('price');
                $courseDiscount = $originalTotal - $subtotal;
                $couponDiscount = 0;
                $appliedCoupon = session('applied_coupon');

                if ($appliedCoupon) {
                    $coupon = LMSCoupon::where('id', $appliedCoupon['id'])
                        ->where('created_by', $user->id)
                        ->first();

                    if ($coupon && $coupon->isValid()) {
                        if (!$coupon->minimum_amount || $subtotal >= $coupon->minimum_amount) {
                            if ($coupon->type === 'percentage') {
                                $couponDiscount = ($subtotal * $coupon->value) / 100;
                            } else {
                                $couponDiscount = $coupon->value;
                            }
                            $couponDiscount = min($couponDiscount, $subtotal);
                        }
                    }
                }

                $total = $subtotal - $couponDiscount;

                if ($total <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount' => $total,
                    'product_name' => 'LMS Course Purchase',
                    'description' => 'Online Course Payment',
                    'metadata' => [
                        'original_total' => $originalTotal,
                        'payment_method' => $request->payment_method,
                        'payment_note' => $request->payment_note,
                        'subtotal' => $subtotal,
                        'course_discount' => $courseDiscount,
                        'coupon_discount' => $couponDiscount,
                        'total' => $total,
                        'applied_coupon' => $appliedCoupon
                    ],
                    'callback_url' => route('lms.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('lms.frontend.home', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function lmsGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $student = auth('lms_student')->user();
                if (!$student) {
                    return redirect()->route('lms.frontend.home', ['userSlug' => $userSlug])->with('error', __('Invalid session.'));
                }

                $stripeService = new StripeService($userSlug);
                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $cartItems = LMSCart::where('created_by', $user->id)
                        ->where('student_id', $student->id)
                        ->with('course')
                        ->get();

                    if ($cartItems->isEmpty()) {
                        return redirect()->route('lms.frontend.cart', ['userSlug' => $userSlug])
                            ->with('error', __('Your cart is empty'));
                    }

                    $order = new LMSOrder();
                    $order->order_number = LMSOrder::generateOrderNumber($user->id);
                    $order->student_id = $student->id;
                    $order->payment_method = 'Stripe';
                    $order->payment_status = 'paid';
                    $order->original_total = $request['original_total'];
                    $order->subtotal = $request['subtotal'];
                    $order->discount_amount = $request['course_discount'];
                    $order->coupon_discount = $request['coupon_discount'];
                    $order->total_discount = $request['course_discount'] + $request['coupon_discount'];
                    $order->total_amount = $request['total'];
                    $order->coupon_id = $request['applied_coupon'] ? $request['applied_coupon']['id'] : null;
                    $order->coupon_code = $request['applied_coupon'] ? $request['applied_coupon']['code'] : null;
                    $order->status = 'completed';
                    $order->notes = $request['payment_note'];
                    $order->order_date = now();
                    $order->payment_id = $request['session_id'] ?? null;
                    $order->creator_id = $user->id;
                    $order->created_by = $user->id;
                    $order->save();

                    foreach ($cartItems as $cartItem) {
                        $orderItem = new LMSOrderItem();
                        $orderItem->order_id = $order->id;
                        $orderItem->course_id = $cartItem->course_id;
                        $orderItem->quantity = $cartItem->quantity;
                        $orderItem->unit_price = $cartItem->price;
                        $orderItem->total_price = $cartItem->price * $cartItem->quantity;
                        $orderItem->save();
                    }

                    $cartItems->each->delete();
                    session()->forget('applied_coupon');

                    if ($order->coupon_id) {
                        LMSCoupon::where('id', $order->coupon_id)->increment('used_count');
                    }

                    try {
                        LMSOrderPayments::dispatch($order);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('lms.frontend.home', ['userSlug' => $userSlug])
                        ->with('success', __('Payment completed successfully! Order #:number', ['number' => $order->order_number]));
                }

                return redirect()->route('lms.frontend.checkout', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('lms.frontend.home', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function laundryPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {

                $price = floatval($request->total ?? 0);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount' => $price,
                    'product_name' => 'Laundry Service',
                    'description' => 'Laundry Service Payment',
                    'metadata' => [
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'address' => $request->address,
                        'location' => $request->location,
                        'numberOfItems' => $request->cloth_no,
                        'specialInstructions' => $request->instructions,
                        'pickupDate' => $request->pickup_date,
                        'pickupTime' => $request->pickupTime,
                        'deliveryDate' => $request->delivery_date,
                        'deliveryTime' => $request->deliveryTime,
                        'services' => json_decode($request->services, true) ?? [],
                        'total' => $request->total
                    ],
                    'callback_url' => route('laundry.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function laundryGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;

                $stripeService = new StripeService($userSlug);
                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {

                    $booking = new LaundryRequest();
                    $booking->name = $request->name;
                    $booking->email = $request->email;
                    $booking->phone = $request->phone;
                    $booking->address = $request->address;
                    $booking->location = $request->location;
                    $booking->cloth_no = $request->numberOfItems;
                    $booking->instructions = $request->specialInstructions;
                    $booking->pickup_date = $request->pickupDate . ' ' . $request->pickupTime;
                    $booking->delivery_date = $request->deliveryDate . ' ' . $request->deliveryTime;
                    $booking->services = $request->services;
                    $booking->payment_method = 'Stripe';
                    $booking->payment_id = $request->session_id ?? null;
                    $booking->status = 2;
                    $booking->total = $request->total;
                    $booking->created_by = $userId;
                    $booking->creator_id = $userId;
                    $booking->save();

                    $invoice = new LaundryInvoice();
                    $invoice->laundry_request_id = $booking->id;
                    $invoice->amount = $booking->total;
                    $invoice->status = 1;
                    $invoice->creator_id = $userId;
                    $invoice->created_by = $userId;
                    $invoice->save();

                    if ($invoice->status == 1) {
                        $payment = new LaundryPayment();
                        $payment->payment_amount = $invoice->amount;
                        $payment->invoice_id = $invoice->id;
                        $payment->payment_date = date('Y-m-d H:i:s');
                        $payment->status = 'cleared';
                        $payment->creator_id = $userId;
                        $payment->created_by = $userId;
                        $payment->save();
                    }
                    try {
                        LaundryBookingPayments::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('laundry-management.frontend.booking-success', [
                        'userSlug' => $userSlug,
                        'requestId' => encrypt($booking->id)
                    ]);
                }

                return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function parkingPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {

                $price = floatval($request->total_amount);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount' => $price,
                    'product_name' => 'Parking Slot - ' . $request->slot_name,
                    'description' => 'Parking Management Payment',
                    'metadata' => [
                        'slot_name'      => $request->slot_name,
                        'slot_type_id'   => $request->slot_type_id,
                        'date'           => $request->date,
                        'start_time'     => $request->start_time,
                        'end_time'       => $request->end_time,
                        'customer_name'  => $request->customer_name,
                        'customer_email' => $request->customer_email,
                        'customer_phone' => $request->customer_phone,
                        'vehicle_name'   => $request->vehicle_name,
                        'vehicle_number' => $request->vehicle_number,
                        'payment_option' => $request->payment_option,
                        'total_amount'   => $request->total_amount
                    ],
                    'callback_url' => route('parking.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function parkingGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {

                    $booking = new ParkingBooking();
                    $booking->slot_name = $request->slot_name;
                    $booking->slot_type_id = $request->slot_type_id;
                    $booking->booking_date = $request->date;
                    $booking->start_time = $request->start_time;
                    $booking->end_time = $request->end_time;
                    $booking->customer_name = $request->customer_name;
                    $booking->customer_email = $request->customer_email;
                    $booking->customer_phone = $request->customer_phone;
                    $booking->vehicle_name = $request->vehicle_name;
                    $booking->vehicle_number = $request->vehicle_number;
                    $booking->total_amount = $request->total_amount;
                    $booking->payment_method = 'Stripe';
                    $booking->payment_status = 'paid';
                    $booking->booking_status = 'confirmed';
                    $booking->creator_id = $userId;
                    $booking->created_by = $userId;
                    $booking->save();

                    try {
                        ParkingBookingPayments::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('parking-management.frontend.booking-success', ['userSlug' => $userSlug, 'id' => encrypt($booking->id)])
                        ->with('success', __('Payment completed and booking confirmed successfully!'));
                }

                return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function eventsPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $eventId = $request->event_id;
                $event = Event::where('id', $eventId)
                    ->where('created_by', $user->id)
                    ->firstOrFail();

                $price = floatval($request->total);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount' => $price,
                    'product_name' => $event->title ?? 'Event Booking',
                    'description' => 'Event Booking Payment',
                    'metadata' => [
                        'event_id'       => $eventId,
                        'fullName'       => $request->fullName,
                        'email'          => $request->email,
                        'phone'          => $request->phone,
                        'persons'        => $request->persons,
                        'total'          => $request->total,
                        'ticket_type_id' => $request->ticket_type_id,
                        'time_slot'      => $request->time_slot,
                        'selected_date'  => $request->selected_date,
                    ],
                    'callback_url' => route('events-management.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('events-management.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function eventsGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $event = Event::where('id', $request->event_id)
                        ->where('created_by', $user->id)
                        ->first();

                    $eventbooking = new EventBooking();
                    $eventbooking->event_id = $request->event_id;
                    $eventbooking->ticket_type_id = $request->ticket_type_id;
                    $eventbooking->time_slot = $request->time_slot;
                    $eventbooking->name = $request->fullName;
                    $eventbooking->email = $request->email;
                    $eventbooking->mobile = $request->phone;
                    $eventbooking->person = $request->persons;
                    $eventbooking->date = $request->selected_date;
                    $eventbooking->total_price = $request->total;
                    $eventbooking->price = $request->total / $request->persons;
                    $eventbooking->status = 'confirmed';
                    $eventbooking->created_by = $user->id;
                    $eventbooking->creator_id = $user->id;
                    $eventbooking->save();

                    $eventBookingPayment = new EventBookingPayment();
                    $eventBookingPayment->event_booking_id = $eventbooking->id;
                    $eventBookingPayment->booking_number = $eventbooking->booking_number;
                    $eventBookingPayment->event_name = $event->title;
                    $eventBookingPayment->customer_name = $request->fullName;
                    $eventBookingPayment->payment_date = now();
                    $eventBookingPayment->amount = $request->total;
                    $eventBookingPayment->payment_status = 'cleared';
                    $eventBookingPayment->payment_type = 'Stripe';
                    $eventBookingPayment->description = 'Payment via Stripe';
                    $eventBookingPayment->created_by = $user->id;
                    $eventBookingPayment->creator_id = $user->id;
                    $eventBookingPayment->save();

                    try {
                        EventBookingPayments::dispatch($eventbooking, $eventBookingPayment);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('events-management.frontend.ticket', ['userSlug' => $userSlug, 'id' => $eventbooking->id, 'paymentId' => $eventBookingPayment->id])
                        ->with('success', __('Payment completed and booking confirmed successfully!'));
                }

                return redirect()->route('events-management.frontend.payment', ['userSlug' => $userSlug, 'id' => $request->event_id])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('events-management.frontend.payment', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function holidayzPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $customer = auth('holidayz_customer')->user();
                if (!$customer) {
                    return redirect()->route('hotel.frontend.login', ['userSlug' => $userSlug]);
                }

                // Get cart items with relationships
                $cart = HolidayzCart::where('created_by', $user->id)
                    ->where('customer_id', $customer->id)
                    ->with(['items.room', 'items.facilities', 'items.taxes'])
                    ->first();

                if (!$cart || $cart->items->isEmpty()) {
                    return redirect()->route('hotel.frontend.cart', ['userSlug' => $userSlug])
                        ->with('error', __('Your cart is empty'));
                }

                // Check room availability for all cart items before payment
                foreach ($cart->items as $cartItem) {
                    $availableRooms = HolidayzAvailabilityHelper::getAvailableRoomCount(
                        $cartItem->room_id,
                        $cartItem->check_in_date->format('Y-m-d'),
                        $cartItem->check_out_date->format('Y-m-d'),
                        null,
                        $user->id
                    );

                    if ($cartItem->quantity > $availableRooms) {
                        return redirect()->route('hotel.frontend.cart', ['userSlug' => $userSlug])
                            ->with('error', __('Room ":room" is no longer available for the selected dates. Only :available rooms available.', [
                                'room' => $cartItem->room->room_type,
                                'available' => $availableRooms
                            ]));
                    }
                }

                // Calculate totals with proper coupon handling
                $subtotal = $cart->items->sum(function ($item) {
                    return $item->rent_per_night * $item->nights * $item->quantity;
                });

                $tax_amount = $cart->items->sum(function ($item) {
                    return $item->taxes->sum('pivot.tax_amount');
                });

                $facilities_amount = $cart->items->sum(function ($item) {
                    return $item->facilities->sum('pivot.total_amount');
                });

                $coupon_discount = 0;
                $applied_coupon = session('applied_coupon');

                if ($applied_coupon && isset($applied_coupon['id'])) {
                    $coupon = HolidayzCoupon::find($applied_coupon['id']);

                    if ($coupon && $coupon->created_by == $user->id && $coupon->isValid()) {
                        $coupon_discount = $applied_coupon['discount'] ?? 0;
                        $coupon_discount = min($coupon_discount, $subtotal);
                    } else {
                        session()->forget('applied_coupon');
                        $applied_coupon = null;
                    }
                }

                $total = $subtotal + $tax_amount + $facilities_amount - $coupon_discount;

                if ($total <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount' => $total,
                    'product_name' => 'Hotel Booking - ' . $cart->items->count() . ' room(s)',
                    'description' => 'Hotel Room Reservation Payment for ' . $cart->items->sum('nights') . ' night(s)',
                    'metadata' => [
                        'customer_id'       => $customer->id,
                        'subtotal'          => $subtotal,
                        'tax_amount'        => $tax_amount,
                        'facilities_amount' => $facilities_amount,
                        'coupon_discount'   => $coupon_discount,
                        'total'             => $total,
                        'applied_coupon'    => json_encode($applied_coupon),
                        'special_requests'  => $request->special_requests,
                    ],
                    'callback_url' => route('holidayz.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('hotel.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function holidayzGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $customer = auth('holidayz_customer')->user();

                if (!$customer) {
                    return redirect()->route('hotel.frontend.index', ['userSlug' => $userSlug])->with('error', __('Invalid session.'));
                }

                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $applied_coupon = json_decode($request->applied_coupon, true);

                    $cart = HolidayzCart::where('created_by', $user->id)
                        ->where('customer_id', $customer->id)
                        ->with(['items.room', 'items.facilities', 'items.taxes'])
                        ->first();

                    $booking = new HolidayzRoomBooking();
                    $booking->booking_date = now();
                    $booking->customer_id = $customer->id;
                    $booking->adults = $cart->items->sum('adults');
                    $booking->children = $cart->items->sum('children');
                    $booking->total_guests = $cart->items->sum('adults') + $cart->items->sum('children');
                    $booking->subtotal = $request->subtotal;
                    $booking->tax_amount = $request->tax_amount;
                    $booking->coupon_id = $applied_coupon['id'] ?? null;
                    $booking->discount_amount = $request->coupon_discount;
                    $booking->total_amount = $request->total;
                    $booking->paid_amount = $request->total;
                    $booking->balance_amount = 0;
                    $booking->payment_method = 'Stripe';
                    $booking->status = 'paid';
                    $booking->special_requests = $request->special_requests;
                    $booking->creator_id = $user->id;
                    $booking->created_by = $user->id;
                    $booking->save();

                    foreach ($cart->items as $cartItem) {
                        $bookingItem = new HolidayzRoomBookingItem();
                        $bookingItem->booking_id = $booking->id;
                        $bookingItem->room_id = $cartItem->room_id;
                        $bookingItem->check_in_date = $cartItem->check_in_date;
                        $bookingItem->check_out_date = $cartItem->check_out_date;
                        $bookingItem->quantity = $cartItem->quantity;
                        $bookingItem->adults = $cartItem->adults;
                        $bookingItem->children = $cartItem->children;
                        $bookingItem->rent_per_night = $cartItem->rent_per_night;
                        $bookingItem->nights = $cartItem->nights;
                        $bookingItem->discount_percentage = 0;
                        $bookingItem->discount_amount = 0;
                        $bookingItem->total_amount = $cartItem->rent_per_night * $cartItem->nights * $cartItem->quantity;
                        $bookingItem->save();

                        foreach ($cartItem->facilities as $facility) {
                            $bookingItem->facilities()->attach($facility->id, [
                                'price' => $facility->pivot->price,
                                'quantity' => $facility->pivot->quantity,
                                'total_amount' => $facility->pivot->total_amount
                            ]);
                        }

                        foreach ($cartItem->taxes as $tax) {
                            $bookingItem->taxes()->attach($tax->id, [
                                'tax_name' => $tax->pivot->tax_name ?? $tax->name,
                                'tax_rate' => $tax->pivot->tax_rate ?? $tax->rate,
                                'tax_amount' => $tax->pivot->tax_amount
                            ]);
                        }
                    }

                    // Record coupon usage if applicable
                    if ($applied_coupon) {
                        $couponId = $applied_coupon['id'];
                        $coupon = HolidayzCoupon::find($couponId);
                        if ($coupon) {
                            // Check if already recorded (prevent duplicates)
                            $existingUsage = HolidayzCouponUsage::where('coupon_id', $couponId)
                                ->where('customer_id', $customer->id)
                                ->exists();

                            if (!$existingUsage) {
                                $couponUsage = new HolidayzCouponUsage();
                                $couponUsage->coupon_id = $couponId;
                                $couponUsage->customer_id = $customer->id;
                                $couponUsage->used_at = now();
                                $couponUsage->creator_id = $coupon->creator_id;
                                $couponUsage->created_by = $coupon->created_by;
                                $couponUsage->save();

                                $coupon->increment('used_count');
                            }
                        }
                    }

                    // Clear cart and sessions
                    HolidayzCart::where('created_by', $user->id)
                        ->where('customer_id', $customer->id)
                        ->delete();

                    session()->forget('applied_coupon');

                    try {
                        HolidayzBookingPayments::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('hotel.frontend.booking-confirm', [
                        'userSlug' => $userSlug,
                        'encryptedBooking' => encrypt($booking->id)
                    ])->with('success', __('Payment completed successfully! Booking #:number', ['number' => $booking->booking_number]));
                }

                return redirect()->route('hotel.frontend.checkout', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('hotel.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function facilitiesPaymentWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {

                // Get booking data from service
                $bookingData = FacilitiesBookingService::prepareBookingData($request, $user->id);

                if (!$bookingData) {
                    return redirect()->back()->with('error', __('Invalid booking data.'));
                }

                $totalAmount = $bookingData['total_amount'];

                if ($totalAmount <= 0) {
                    return redirect()->back()->with('error', __('Invalid booking amount.'));
                }

                $orderID = 'FB-' . strtoupper(substr(uniqid(), -8));

                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $totalAmount,
                    'product_name' => 'Facility Booking',
                    'description'  => 'Facility booking payment',
                    'metadata'     => $bookingData,
                    'callback_url' => route('facilities.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id'     => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Payment processing failed. Please try again.'));
        }
    }

    public function facilitiesGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $booking = FacilitiesBookingService::createBooking($request, $user->id, 'Stripe');

                    FacilitiesBookingService::createPaymentEntry($booking, $user->id, [
                        'method' => 'Stripe',
                        'transaction_id' => $request->session_id ?? null,
                        'currency' => $stripeService->currency,
                    ]);

                    try {
                        FacilityBookingPayment::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('facilities.frontend.booking-success', ['userSlug' => $userSlug, 'booking_number' => $booking->booking_number])->with('success', __('Payment successful! Booking confirmed: ') . $booking->booking_number);
                }

                return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment verification failed.'));
        }
    }

    public function vehicleBookingPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $price = floatval($request->total_amount);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount' => $price,
                    'product_name' => 'Vehicle Booking',
                    'description' => 'Vehicle Booking Payment',
                    'metadata' => [
                        'email'            => $request->email,
                        'selected_seats'   => $request->selectedSeats,
                        'passengers'       => $request->passengers,
                        'route_id'         => $request->route_id,
                        'vehicle_id'       => $request->vehicle_id,
                        'booking_date'     => $request->booking_date,
                        'total_amount'     => $request->total_amount,
                        'special_requests' => $request->special_requests,
                    ],
                    'callback_url' => route('vehicle-booking.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function vehicleBookingGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $booking = new VehicleBooking();
                    $booking->booking_number = VehicleBooking::generateBookingNumber($userId);
                    $booking->email = $request->email;
                    $booking->selected_seats = $request->selected_seats;
                    $booking->passengers = $request->passengers;
                    $booking->route_id = $request->route_id;
                    $booking->vehicle_id = $request->vehicle_id;
                    $booking->booking_date = $request->booking_date;
                    $booking->total_amount = $request->total_amount;
                    $booking->payment_method = 'Stripe';
                    $booking->payment_status = 'paid';
                    $booking->booking_status = 'confirmed';
                    $booking->special_requests = $request->special_requests;
                    $booking->transaction_id = $request->session_id ?? null;
                    $booking->creator_id = $userId;
                    $booking->created_by = $userId;
                    $booking->save();

                    try {
                        VehicleBookingPayments::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('vehicle-booking.frontend.success', ['userSlug' => $userSlug, 'id' => encrypt($booking->id)])
                        ->with('success', __('Payment completed and booking confirmed successfully!'));
                }

                return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function movieBookingPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $bookingData = session('booking_data');
                if (!$bookingData) {
                    return redirect()->back()->with('error', __('Something went wrong, Please try again,'));
                }
                $request->merge($bookingData);

                $price = floatval($request->amount ?? 0);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => 'Movie Ticket Booking',
                    'description'  => 'Movie Show Booking Stripe Payment',
                    'metadata'     => [
                        'movie_id'       => $request->movie_id ?? null,
                        'show_id'        => $request->show_id ?? null,
                        'screen_id'      => $request->screen_id ?? null,
                        'date'           => $request->date ?? null,
                        'time'           => $request->time ?? null,
                        'seats'          => json_encode($request->seats ?? []),
                        'foods'          => json_encode($request->foods ?? []),
                        'pricing'        => json_encode($request->pricing ?? []),
                        'customer_name'  => $request->name,
                        'customer_email' => $request->email,
                        'customer_phone' => $request->phone,
                    ],
                    'callback_url' => route('movie-booking.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function movieBookingGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $seats   = json_decode($request->seats ?? '[]', true);
                    $foods   = json_decode($request->foods ?? '[]', true);
                    $pricing = json_decode($request->pricing ?? '[]', true);

                    $bookedSeats = array_map(fn($s) => ['seat' => $s['seat'], 'price' => $s['price']], $seats);
                    $bookedFoods = array_map(fn($f) => ['id' => $f['id'], 'price' => $f['price'], 'quantity' => $f['quantity']], $foods);

                    $booking                 = new MovieBooking();
                    $booking->booking_id     = strtoupper(uniqid());
                    $booking->movie_id       = $request->movie_id;
                    $booking->movie_show_id  = $request->show_id;
                    $booking->screen_id      = $request->screen_id;
                    $booking->customer_name  = $request->customer_name ?? '';
                    $booking->customer_email = $request->customer_email ?? '';
                    $booking->customer_phone = $request->customer_phone ?? '';
                    $booking->booking_date   = $request->date ?? '';
                    $booking->show_time      = $request->time;
                    $booking->total_seats    = $pricing['tickets'] ?? 0;
                    $booking->booked_seats   = $bookedSeats;
                    $booking->booked_foods   = $bookedFoods;
                    $booking->subtotal       = $pricing['subtotal'] ?? 0;
                    $booking->taxes          = $pricing['taxes'] ?? [];
                    $booking->tax_amount     = $pricing['taxAmount'] ?? 0;
                    $booking->total_amount   = $pricing['total'] ?? 0;
                    $booking->payment_method = 'Stripe';
                    $booking->payment_status = 'paid';
                    $booking->booking_status = 'confirmed';
                    $booking->creator_id     = $userId;
                    $booking->created_by     = $userId;
                    $booking->save();
                    try {
                        MovieBookingPayments::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('movie-booking.confirmation', ['userSlug' => $userSlug, 'id' => $booking->booking_id])
                        ->with('success', __('Payment completed and booking confirmed successfully!'));
                }

                return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ngoDonationPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $price = floatval($request->amount ?? 0);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid donation amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => 'NGO Donation',
                    'description'  => 'Donation Payment',
                    'metadata'     => [
                        'amount'        => $request->amount,
                        'campaign_id'   => $request->campaign_id,
                        'donor_name'    => $request->donor_name,
                        'donor_email'   => $request->donor_email,
                        'donor_message' => $request->donor_message,
                    ],
                    'callback_url' => route('ngo.donation.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ngoDonationGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $donor = NgoDonor::where('email', $request->donor_email)
                        ->where('created_by', $user->id)
                        ->first();

                    if (!$donor) {
                        $donor = new NgoDonor();
                        $donor->name = $request->donor_name;
                        $donor->email = $request->donor_email;
                        $donor->created_by = $user->id;
                        $donor->creator_id = $user->id;
                        $donor->save();
                    }

                    $donation = new NgoDonation();
                    $donation->donor_id = $donor->id;
                    $donation->campaign_id = ($request->campaign_id === 'general' || !$request->campaign_id) ? null : $request->campaign_id;
                    $donation->amount = $request->amount;
                    $donation->payment_method = 'Stripe';
                    $donation->status = 'paid';
                    $donation->transaction_id = $request->session_id ?? null;
                    $donation->donation_date = now();
                    $donation->notes = $request->donor_message;
                    $donation->created_by = $user->id;
                    $donation->creator_id = $user->id;
                    $donation->save();

                    $donor->increment('total_donations', $request->amount);

                    if ($donation->campaign_id) {
                        $campaign = NgoCampaign::find($donation->campaign_id);
                        if ($campaign) {
                            $campaign->increment('current_amount', $request->amount);
                        }
                    }

                    try {
                        CreateNgoDonation::dispatch($request, $donation);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])
                        ->with('success', __('Thank you for your donation! Your payment has been processed successfully.'));
                }

                return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])
                    ->with('error', __('Donation was cancelled.'));
            }

            return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function coworkingSpacePayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $paymentType = $request->input('type', 'membership');

                if ($paymentType === 'booking') {
                    $price = floatval($request->totalAmount);
                    if ($price <= 0) {
                        return redirect()->back()->with('error', __('Invalid payment amount.'));
                    }

                    $orderID = strtoupper(substr(uniqid(), -12));
                    $stripeService = new StripeService($userSlug);
                    $stripe_session = $stripeService->initiatePayment([
                        'amount'       => $price,
                        'product_name' => 'Coworking Space Booking',
                        'description'  => 'Coworking Space Booking Payment',
                        'metadata'     => [
                            'firstName'         => $request->firstName,
                            'lastName'          => $request->lastName,
                            'email'             => $request->email,
                            'phone'             => $request->phone,
                            'company'           => $request->company,
                            'specialRequests'   => $request->specialRequests,
                            'startDate'         => $request->startDate,
                            'endDate'           => $request->endDate,
                            'selectedAmenities' => $request->selectedAmenities,
                            'totalAmount'       => $request->totalAmount,
                            'duration'          => $request->duration,
                            'type'              => 'booking',
                        ],
                        'callback_url' => route('coworking-space.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                        'order_id' => $orderID,
                    ]);

                    if (isset($stripe_session->url)) {
                        return redirect()->to($stripe_session->url);
                    }

                    return redirect()->back()->with('error', __('Failed to create Stripe session.'));
                }

                $plan = CoworkingMembershipPlan::find($request->plan_id);
                if (!$plan) {
                    return redirect()->back()->with('error', __('Plan not found.'));
                }


                $price = floatval($plan->plan_price);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => $plan->plan_name ?? 'Coworking Membership',
                    'description'  => 'Coworking Space Membership Payment',
                    'metadata'     => [
                        'plan_id'     => $plan->id,
                        'member_name' => $request->member_name,
                        'email'       => $request->email,
                        'phone_no'    => $request->phone_no,
                        'type'        => 'membership',
                    ],
                    'callback_url' => route('coworking-space.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('coworking-space.purchase', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function coworkingSpaceGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $paymentType = $request->input('type', 'membership');

                    if ($paymentType === 'booking') {

                        $booking = new CoworkingBooking();
                        $booking->first_name = $request->firstName;
                        $booking->last_name = $request->lastName;
                        $booking->email = $request->email;
                        $booking->phone_no = $request->phone;
                        $booking->amenities = json_decode($request->selectedAmenities, true) ?? [];
                        $booking->start_date_time = $request->startDate;
                        $booking->end_date_time = $request->endDate;
                        $booking->amount = $request->totalAmount;
                        $booking->booking_duration = $request->duration;
                        $booking->payment_status = 'paid';
                        $booking->payment_method = 'Stripe';
                        $booking->special_requests = $request->specialRequests ?? '';
                        $booking->creator_id = $userId;
                        $booking->created_by = $userId;
                        $booking->save();

                        try {
                            CoworkingBookingPayments::dispatch($booking);
                        } catch (\Throwable $th) {
                        }

                        return redirect()->route('coworking-space.home', ['userSlug' => $userSlug])
                            ->with('success', __('Payment completed and booking confirmed successfully! Booking #:number', ['number' => $booking->booking_number]));
                    } elseif ($paymentType === 'membership') {

                        $plan = CoworkingMembershipPlan::find($request->plan_id);
                        if (!$plan) {
                            return redirect()->route('coworking-space.purchase', ['userSlug' => $userSlug])
                                ->with('error', __('Plan not found.'));
                        }

                        $membership = new CoworkingMembership();
                        $membership->member_name = $request->member_name;
                        $membership->email = $request->email;
                        $membership->phone_no = $request->phone_no;
                        $membership->membership_plan_id = $request->plan_id;
                        $membership->duration = $plan->duration;
                        $membership->price = $plan->plan_price;
                        $membershipController = new CoworkingMembershipController();
                        $membership->plan_expiry_date  = $membershipController->calculateExpiryDate($plan->duration);
                        $membership->plan_status = 'Active';
                        $membership->payment_method = 'Stripe';
                        $membership->payment_status = 'paid';
                        $membership->creator_id = $userId;
                        $membership->created_by = $userId;
                        $membership->save();

                        try {
                            CoworkingMembershipPayments::dispatch($membership);
                        } catch (\Throwable $th) {
                        }

                        return redirect()->route('coworking-space.purchase', ['userSlug' => $userSlug])
                            ->with('success', __('Payment completed and membership activated successfully!'));
                    }
                }

                return redirect()->route('coworking-space.home', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }
            return redirect()->route(($paymentType == 'membership') ? 'coworking-space.purchase' : 'coworking-space.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sportsClubPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $ground = SportsClubGround::findOrFail($request->ground_id);

                $totalAmount = floatval($request->total_amount);

                if ($totalAmount <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $totalAmount,
                    'product_name' => $ground->name ?? 'Sports Ground Booking',
                    'description'  => 'Sports Club Ground Booking Payment',
                    'metadata'     => [
                        'ground_id'            => $request->ground_id,
                        'name'                 => $request->name,
                        'email'                => $request->email,
                        'mobile_number'        => $request->mobile_number,
                        'booked_by'            => $request->booked_by,
                        'booking_date'         => $request->booking_date,
                        'start_time'           => $request->start_time,
                        'end_time'             => $request->end_time,
                        'start_date'           => $request->start_date,
                        'end_date'             => $request->end_date,
                        'facilities'           => json_encode($request->facilities ?? []),
                        'special_requirements' => $request->special_requirements,
                        'purpose'              => $request->purpose,
                        'total_amount'         => $request->total_amount,
                    ],
                    'callback_url' => route('sports-club.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sportsClubGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $booking = new SportsClubAndGroundOrder();
                    $booking->name = $request->name;
                    $booking->email = $request->email;
                    $booking->mobile_no = $request->mobile_number;
                    $booking->booked_by = $request->booked_by;
                    $booking->sports_club_id = $request->ground_id;
                    $booking->date = $request->booking_date;
                    $booking->start_date = $request->start_date ?? null;
                    $booking->end_date = $request->end_date ?? null;
                    $booking->start_time = $request->start_time ?? null;
                    $booking->end_time = $request->end_time ?? null;
                    $booking->total_amount = $request->total_amount;
                    $booking->notes = $request->special_requirements;
                    $booking->purpose = $request->purpose;
                    $booking->transaction_id = $request->session_id ?? null;
                    $booking->payment_type = 'Stripe';
                    $booking->payment_status = 'paid';
                    $booking->creator_id = $userId;
                    $booking->created_by = $userId;
                    $booking->save();

                    $facilities = json_decode($request->facilities ?? '[]', true);
                    if (!empty($facilities) && is_array($facilities)) {
                        foreach ($facilities as $facilityId) {
                            $facility = SportsClubFacility::find($facilityId);
                            if ($facility) {
                                $bookingFacility = new SportsClubBookingFacility();
                                $bookingFacility->booking_id = $booking->id;
                                $bookingFacility->facility_id = $facilityId;
                                $bookingFacility->facility_name = $facility->name;
                                $bookingFacility->facility_amount = $facility->amount;
                                $bookingFacility->creator_id = $userId;
                                $bookingFacility->created_by = $userId;
                                $bookingFacility->save();
                            }
                        }
                    }

                    try {
                        SportsClubBookingPayments::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    $encryptedBookingId = encrypt($booking->id);
                    $redirectUrl = route('sports-academy.booking', ['userSlug' => $userSlug]) . '?step=4&booking_id=' . $encryptedBookingId;

                    return redirect($redirectUrl)->with('success', __('Payment completed and booking confirmed successfully!'));
                }

                return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sportsClubPlanPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $plan = SportsClubMembershipPlan::findOrFail($request->plan_id);

                $totalAmount = floatval($plan->price);

                if ($totalAmount <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $totalAmount,
                    'product_name' => $plan->name ?? 'Sports Club Membership Plan',
                    'description'  => 'Sports Club Membership Plan Payment',
                    'metadata'     => [
                        'plan_id'    => $plan->id,
                        'user_email' => $request->user_email,
                    ],
                    'callback_url' => route('sports-club-plan.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sportsClubPlanGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $plan = SportsClubMembershipPlan::findOrFail($request->plan_id);
                    $member = SportsClubMember::where('created_by', $userId)
                        ->where('email', $request->user_email)
                        ->first();

                    if (!$member) {
                        return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Member not found.'));
                    }

                    $planPayment = new SportsClubMembershipPlanPayment();
                    $planPayment->member_id = $member->id;
                    $planPayment->membershipplan_id = $plan->id;
                    $planPayment->fee = $plan->price;
                    $planPayment->duration = $plan->duration;
                    $planPayment->date = now()->toDateString();
                    $planPayment->start_date = now()->toDateString();
                    $planPayment->end_date = $plan->calculateEndDate()->toDateString();
                    $planPayment->reference_number = $request->session_id ?? null;
                    $planPayment->status = 'accepted';
                    $planPayment->creator_id = $userId;
                    $planPayment->created_by = $userId;
                    $planPayment->save();

                    $assignment = new SportsClubAssignedMembership();
                    $assignment->member_id = $member->id;
                    $assignment->membershipplan_id = $plan->id;
                    $assignment->start_date = now()->toDateString();
                    $assignment->end_date = $plan->calculateEndDate()->toDateString();
                    $assignment->status = 'accepted';
                    $assignment->duration = $plan->duration;
                    $assignment->fee = $plan->price;
                    $assignment->payment_type = 'Stripe';
                    $assignment->creator_id = $userId;
                    $assignment->created_by = $userId;
                    $assignment->save();

                    try {
                        SportsClubPlanPayments::dispatch($request, $assignment);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('success', __('Payment completed and plan subscription confirmed successfully!'));
                }

                return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function influencerMarketingPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $amount = floatval($request->amount ?? 0);

                if ($amount <= 0) {
                    return redirect()->back()->with('error', __('Invalid deposit amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $amount,
                    'product_name' => 'Influencer Marketing',
                    'description'  => 'Influencer Marketing Deposit Payment',
                    'metadata'     => [
                        'amount'   => $amount,
                        'brand_id' => $request->brand_id,
                    ],
                    'callback_url' => route('influencer-marketing.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function influencerMarketingGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $deposit = new InfluencerMarketingDeposit();
                    $deposit->brand_id = $request->brand_id;
                    $deposit->amount = $request->amount;
                    $deposit->payment_type = 'Stripe';
                    $deposit->payment_status = 'paid';
                    $deposit->transaction_id = $request->session_id ?? null;
                    $deposit->created_by = $user->id;
                    $deposit->save();

                    try {
                        InfluencerMarketingPayment::dispatch($deposit);
                    } catch (\Exception $th) {
                    }

                    return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])
                        ->with('success', __('Deposit completed successfully!'));
                }

                return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function waterParkBookingPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $price = floatval($request->total_amount);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => 'Water Park Booking',
                    'description'  => 'Water Park Booking Payment',
                    'metadata'     => [
                        'email'        => $request->email,
                        'full_name'    => $request->full_name,
                        'phone'        => $request->phone,
                        'adults'       => $request->adults,
                        'children'     => $request->children,
                        'booking_date' => $request->booking_date,
                        'event_id'     => $request->event_id,
                        'total_amount' => $request->total_amount,
                    ],
                    'callback_url' => route('water-park.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('water-park.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function waterParkBookingGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $booking = new WaterParkBooking();
                    $booking->email = $request->email;
                    $booking->full_name = $request->full_name;
                    $booking->phone = $request->phone;
                    $booking->adults = $request->adults;
                    $booking->children = $request->children;
                    $booking->booking_date = $request->booking_date;
                    $booking->event_id = $request->event_id;
                    $booking->total_amount = $request->total_amount;
                    $booking->payment_method = 'Stripe';
                    $booking->payment_status = 'paid';
                    $booking->booking_status = 'confirmed';
                    $booking->transaction_id = $request->session_id ?? null;
                    $booking->creator_id = $userId;
                    $booking->created_by = $userId;
                    $booking->save();

                    try {
                        WaterParkBookingPaymentStripe::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('water-park.frontend.booking', ['userSlug' => $userSlug])
                        ->with('success', __('Payment completed and booking confirmed successfully!'));
                }

                return redirect()->route('water-park.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('water-park.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function tvStudioPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $customer = auth('tvstudio_customer')->user();
                if (!$customer) {
                    return redirect()->route('tvstudio.frontend.login', ['userSlug' => $userSlug]);
                }

                $orderData = TVStudioCheckoutService::prepareOrderData($customer->id, $user->id);

                $total = $orderData['total'];

                if ($total <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $total,
                    'product_name' => 'TV Studio Content Purchase',
                    'description'  => 'Movies & TV Shows Purchase',
                    'metadata'     => [
                        'customer_id'    => $customer->id,
                        'customer_name'  => $customer->first_name . ' ' . $customer->last_name,
                        'customer_email' => $customer->email,
                        'items_count'    => count($orderData['cart_items']),
                        'order_data'     => json_encode($orderData),
                    ],
                    'callback_url' => route('tvstudio.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('tvstudio.frontend.home', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function tvStudioGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $customer = auth('tvstudio_customer')->user();

                if (!$customer) {
                    return redirect()->route('tvstudio.frontend.home', ['userSlug' => $userSlug])->with('error', __('Invalid session.'));
                }

                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $orderData = json_decode($request->order_data, true);
                    TVStudioCheckoutService::createOrder(
                        $orderData,
                        $customer->id,
                        $user->id,
                        'Stripe',
                        $request->session_id ?? null
                    );

                    return redirect()->route('tvstudio.frontend.order-complete', ['userSlug' => $userSlug]);
                }

                return redirect()->route('tvstudio.frontend.home', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('tvstudio.frontend.home', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function artShowcasePayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {

                $artwork = ArtShowcaseArtWork::where('id', $request->art_work_id)
                    ->where('created_by', $user->id)
                    ->first();

                if (!$artwork) {
                    return redirect()->back()->with('error', __('Artwork not found.'));
                }

                if ($artwork->status !== 'available') {
                    return redirect()->back()->with('error', __('This artwork is no longer available for purchase.'));
                }

                $price = floatval($artwork->price ?? 0);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid artwork price.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => $artwork->name ?? 'Artwork Purchase',
                    'description'  => 'Art Showcase Artwork Purchase',
                    'metadata'     => [
                        'art_work_id' => $request->art_work_id,
                        'full_name' => $request->full_name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'address' => $request->address,
                    ],
                    'callback_url' => route('art-showcase.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function artShowcaseGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $artwork = ArtShowcaseArtWork::where('id', $request->art_work_id)
                        ->where('created_by', $user->id)
                        ->first();

                    if (!$artwork) {
                        return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])
                            ->with('error', __('Artwork not found.'));
                    }

                    if ($artwork->status !== 'available') {
                        return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])
                            ->with('error', __('This artwork is no longer available for purchase.'));
                    }

                    $purchase = new ArtShowcaseArtWorkOrder();
                    $purchase->art_work_id = $artwork->id;
                    $purchase->customer_full_name = $request->full_name;
                    $purchase->customer_email = $request->email;
                    $purchase->contact_number = $request->phone;
                    $purchase->address = $request->address;
                    $purchase->total_amount = $artwork->price;
                    $purchase->payment_type = 'Stripe';
                    $purchase->payment_status = 'paid';
                    $purchase->created_by = $user->id;
                    $purchase->creator_id = $user->id;
                    $purchase->save();

                    $artwork->status = 'sold';
                    $artwork->save();

                    try {
                        CreateArtWorkOrderPayment::dispatch($request, $purchase);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])
                        ->with('success', __('Payment completed successfully! Your artwork purchase has been confirmed.'));
                }

                return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function tattooStudioBookingPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {

                $price = floatval($request->total_amount);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => 'Tattoo Studio Booking',
                    'description'  => 'Tattoo Studio Booking Payment',
                    'metadata'     => [
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'instagram' => $request->instagram,
                        'date' => $request->date,
                        'time' => $request->time,
                        'duration' => $request->duration,
                        'placement' => $request->placement,
                        'inch' => $request->inch,
                        'details' => $request->details,
                        'tattoo_type' => $request->tattoo_type,
                        'selected_tattoo_id' => $request->selected_tattoo_id,
                        'custom_price' => $request->custom_price,
                        'total_amount' => $request->total_amount
                    ],
                    'callback_url' => route('tattoo-studio.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function tattooStudioBookingGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $booking                     = new TattooAppointment();
                    $booking->name               = $request->name;
                    $booking->email              = $request->email;
                    $booking->phone              = $request->phone;
                    $booking->instagram          = $request->instagram;
                    $booking->date               = $request->date;
                    $booking->time               = $request->time;
                    $booking->duration           = $request->duration;
                    $booking->placement          = $request->placement;
                    $booking->inch               = $request->inch;
                    $booking->details            = $request->details;
                    $booking->tattoo_type        = $request->tattoo_type;
                    $booking->selected_tattoo_id = $request->selected_tattoo_id;
                    $booking->custom_price       = $request->custom_price;
                    $booking->total_amount       = $request->total_amount;
                    $booking->payment_method     = 'Stripe';
                    $booking->payment_status     = 'paid';
                    $booking->appointment_status = 'confirmed';
                    $booking->transaction_id     = $request->session_id ?? null;
                    $booking->creator_id         = $user->id;
                    $booking->created_by         = $user->id;
                    $booking->save();

                    try {
                        TattooAppointmentPaymentStripe::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])
                        ->with('success', __('Payment completed and appointment confirmed successfully!'));
                }

                return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function photoStudioPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $price = floatval($request->price);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $service = PhotoStudioService::find($request->service_id);

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => $service->name ?? 'Photo Studio Appointment',
                    'description'  => 'Photo Studio Appointment Booking',
                    'metadata'     => [
                        'name'               => $request->name,
                        'email'              => $request->email,
                        'mobile_no'          => $request->mobile_no,
                        'service_id'         => $request->service_id,
                        'price'              => $request->price,
                        'booking_start_date' => $request->booking_start_date,
                        'booking_end_date'   => $request->booking_end_date,
                    ],
                    'callback_url' => route('photo-studio.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function photoStudioGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $service = PhotoStudioService::find($request->service_id);

                    $appointment = new PhotoStudioAppointment();
                    $appointment->name               = $request->name;
                    $appointment->email              = $request->email;
                    $appointment->mobile_no          = $request->mobile_no;
                    $appointment->service_id         = $request->service_id;
                    $appointment->price              = $request->price;
                    $appointment->booking_start_date = $request->booking_start_date;
                    $appointment->booking_end_date   = $request->booking_end_date;
                    $appointment->status             = 'pending';
                    $appointment->payment_status     = 'confirmed';
                    $appointment->creator_id         = $user->id;
                    $appointment->created_by         = $user->id;
                    $appointment->save();

                    $payment = new PhotoStudioAppointmentPayment();
                    $payment->appointment_id     = $appointment->id;
                    $payment->appointment_number = $appointment->appointment_number;
                    $payment->customer_name      = $request->name;
                    $payment->service_name       = $service->name ?? '';
                    $payment->payment_date       = now();
                    $payment->amount             = $request->price;
                    $payment->payment_status     = 'cleared';
                    $payment->payment_type       = 'Stripe';
                    $payment->description        = 'Payment via Stripe';
                    $payment->creator_id         = $user->id;
                    $payment->created_by         = $user->id;
                    $payment->save();

                    try {
                        PhotoStudioAppointmentPayments::dispatch($appointment, $payment);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])
                        ->with('success', __('Payment completed and appointment booked successfully!'));
                }

                return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ebookPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $customer = auth('ebook')->user();
                if (!$customer) {
                    return redirect()->route('ebook.frontend.login', ['userSlug' => $userSlug]);
                }

                $check = EbookBookOrder::CheckPreOrder($user, $customer);

                if ($check['success']) {

                    $total = $request->total ?? 0;
                    if ($total <= 0) {
                        return redirect()->back()->with('error', __('Invalid payment amount.'));
                    }

                    $orderID = strtoupper(substr(uniqid(), -12));
                    $stripeService = new StripeService($userSlug);
                    $stripe_session = $stripeService->initiatePayment([
                        'amount'       => $total,
                        'product_name' => 'Ebook Purchase',
                        'description'  => 'Digital Book Purchase',
                        'metadata'     => [
                            'customer_id'    => $customer->id,
                            'customer_name'  => $customer->full_name,
                            'customer_email' => $customer->email,
                        ],
                        'callback_url' => route('ebook.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID, 'customerId' => $customer->id]),
                        'order_id' => $orderID,
                    ]);

                    if (isset($stripe_session->url)) {
                        return redirect()->to($stripe_session->url);
                    }

                    return redirect()->back()->with('error', __('Failed to create Stripe session.'));
                }

                return redirect()->back()->with('error', $check['message']);
            }

            return redirect()->route('ebook.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ebookGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);
                $status = $request->return_type == 'success' && $stripeService->verifyPayment($request);

                if ($status) {
                    $order = EbookBookOrder::MakeOrder(
                        "Stripe",
                        $user,
                        $request->customerId ?? null,
                        $status,
                        $request->session_id ?? null
                    );

                    try {
                        EbookPayment::dispatch($order);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('ebook.frontend.index', ['userSlug' => $userSlug])
                        ->with('success', __('Payment completed successfully'));
                }

                return redirect()->route('ebook.frontend.checkout', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('ebook.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function yogaClassesPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $member = auth('yoga_member')->user();
                $instructor = auth('yoga_instructor')->user();

                if (!$member && !$instructor) {
                    return redirect()->route('yoga-classes.frontend.login', ['userSlug' => $userSlug]);
                }

                $cartQuery = YogaClassesCart::where('created_by', $user->id)
                    ->with(['course', 'course.instructors']);

                if ($member) {
                    $cartQuery->where('member_id', $member->id);
                } else {
                    $cartQuery->where('instructor_id', $instructor->id);
                }

                $cartItems = $cartQuery->get()->filter(function ($item) {
                    $course = $item->course;

                    return $course
                        && (string) $course->status === '1'
                        && (string) $course->approved_by_owner === '1';
                })->values();

                if ($cartItems->isEmpty()) {
                    return redirect()->route('yoga-classes.frontend.cart', ['userSlug' => $userSlug])
                        ->with('error', __('Your cart is empty'));
                }

                $subtotal = 0;
                $discount = 0;
                foreach ($cartItems as $cartItem) {
                    $course = $cartItem->course;
                    $showLatestPrice = in_array($course->show_latest_price, [true, 1, '1', 'true'], true);
                    $currentPrice = $showLatestPrice
                        ? (float) ($course->latest_price ?? $cartItem->price ?? 0)
                        : (float) ($course->regular_price ?? $cartItem->price ?? 0);
                    $comparePrice = (float) ($course->regular_price ?? 0);
                    $shouldShowOriginalPrice = $showLatestPrice && $comparePrice > 0 && $currentPrice > 0 && $comparePrice > $currentPrice;

                    $subtotal += $shouldShowOriginalPrice ? $comparePrice : $currentPrice;
                    $discount += $shouldShowOriginalPrice ? ($comparePrice - $currentPrice) : 0;
                }

                $total = $subtotal - $discount;

                if ($total <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripeSession = $stripeService->initiatePayment([
                    'amount'       => $total,
                    'product_name' => 'Yoga Classes Purchase',
                    'description'  => 'Yoga course payment',
                    'metadata'     => [
                        'actor_type'   => $member ? 'member' : 'instructor',
                        'actor_name'   => $member?->name ?? $instructor?->name ?? '',
                        'actor_email'  => $member?->email ?? $instructor?->email ?? '',
                        'course_count' => $cartItems->count(),
                        'payment_note' => $request->payment_note,
                        'total'        => $total,
                    ],
                    'callback_url' => route('yoga-classes.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripeSession->url)) {
                    return redirect()->to($stripeSession->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('yoga-classes.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function yogaClassesGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $member = auth('yoga_member')->user();
                $instructor = auth('yoga_instructor')->user();

                if (!$member && !$instructor) {
                    return redirect()->route('yoga-classes.frontend.index', ['userSlug' => $userSlug])->with('error', __('Invalid session.'));
                }

                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $cartQuery = YogaClassesCart::where('created_by', $user->id)
                        ->with(['course', 'course.instructors']);

                    if ($member) {
                        $cartQuery->where('member_id', $member->id);
                    } else {
                        $cartQuery->where('instructor_id', $instructor->id);
                    }

                    $cartItems = $cartQuery->get()->filter(function ($item) {
                        $course = $item->course;

                        return $course
                            && (string) $course->status === '1'
                            && (string) $course->approved_by_owner === '1';
                    })->values();

                    if ($cartItems->isEmpty()) {
                        return redirect()->route('yoga-classes.frontend.cart', ['userSlug' => $userSlug])
                            ->with('error', __('Your cart is empty'));
                    }

                    $transactionReference = $request->session_id  ?? null;
                    $courseSnapshots = [];
                    $subtotal = 0;
                    $discountAmount = 0;

                    foreach ($cartItems as $cartItem) {
                        $course = $cartItem->course;
                        $showLatestPrice = in_array($course->show_latest_price, [true, 1, '1', 'true'], true);
                        $currentPrice = $showLatestPrice
                            ? (float) ($course->latest_price ?? $cartItem->price ?? 0)
                            : (float) ($course->regular_price ?? $cartItem->price ?? 0);
                        $comparePrice = (float) ($course->regular_price ?? 0);
                        $shouldShowOriginalPrice = $showLatestPrice && $comparePrice > 0 && $currentPrice > 0 && $comparePrice > $currentPrice;
                        $lineSubtotal = $shouldShowOriginalPrice ? $comparePrice : $currentPrice;
                        $lineDiscount = $shouldShowOriginalPrice ? ($comparePrice - $currentPrice) : 0;

                        $subtotal += $lineSubtotal;
                        $discountAmount += $lineDiscount;

                        $courseSnapshots[] = [
                            'id' => $course->id,
                            'encrypted_id' => $course->getEncryptedId(),
                            'title' => $course->title,
                            'image' => $course->course_thumbnail,
                            'lessons' => $course->number_of_lessons,
                            'duration' => $course->duration,
                            'regular_price' => $course->regular_price,
                            'latest_price' => $course->latest_price,
                            'show_latest_price' => $course->show_latest_price,
                            'instructor_name' => $course?->instructors?->name ?? $instructor?->name,
                            'amount' => $lineSubtotal,
                            'discount_amount' => $lineDiscount,
                            'total_amount' => $currentPrice,
                        ];
                    }

                    $course_order = new YogaClassesOrder();
                    $course_order->order_number = YogaClassesOrder::generateOrderNumber($user->id);
                    $course_order->name = $member?->name ?? $instructor?->name ?? null;
                    $course_order->course = $courseSnapshots;
                    $course_order->instructor_id = $instructor?->id;
                    $course_order->member_id = $member?->id;
                    $course_order->amount = $subtotal;
                    $course_order->currency = $cartItems->first()?->currency ?: '';
                    $course_order->payment_method = 'Stripe';
                    $course_order->payment_status = 'paid';
                    $course_order->transaction_id = $transactionReference;
                    $course_order->receipt = $request->receipt_url ?? null;
                    $course_order->order_date = now();
                    $course_order->notes = $request->payment_note ?? null;
                    $course_order->discount_amount = $discountAmount;
                    $course_order->tax_amount = 0;
                    $course_order->total_amount = $subtotal - $discountAmount;
                    $course_order->created_by = $user->id;
                    $course_order->save();

                    // Add purchased courses to table
                    foreach ($courseSnapshots as $courseSnapshot) {
                        $purchased_courses = new YogaClassesPurchasedCourse();
                        $purchased_courses->member_id = $member?->id;
                        $purchased_courses->instructor_id = $instructor?->id;
                        $purchased_courses->course_id = $courseSnapshot['id'];
                        $purchased_courses->order_id = $course_order->id;
                        $purchased_courses->purchase_price = $courseSnapshot['total_amount'];
                        $purchased_courses->currency = $cartItems->first()?->currency ?: '';
                        $purchased_courses->purchased_at = now();
                        $purchased_courses->created_by = $user->id;
                        $purchased_courses->save();
                    }

                    $cartItems->each->delete();

                    try {
                        YogaClassesOrderPayments::dispatch($course_order);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('yoga-classes.frontend.order-success', ['userSlug' => $userSlug, 'reference' => $transactionReference])
                        ->with('success', __('Payment completed successfully! Order #:number', ['number' => $course_order->order_number]));
                }

                return redirect()->route('yoga-classes.frontend.checkout', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('yoga-classes.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function hairCareStudioPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {

                $price = floatval($request->total_amount);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => 'Hair Care Studio Booking',
                    'description'  => 'Hair Care Studio Booking Payment',
                    'metadata'     => [
                        'full_name'      => $request->full_name,
                        'email'          => $request->email,
                        'mobile_no'      => $request->mobile_no,
                        'service_id'     => $request->service_id,
                        'preferred_date' => $request->preferred_date,
                        'preferred_time' => $request->preferred_time,
                        'stylist_type'   => $request->stylist_type,
                        'charges'        => $request->charges,
                        'special_request' => $request->special_request,
                        'total_amount'   => $request->total_amount,
                    ],
                    'callback_url' => route('hair-care-studio.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function hairCareStudioGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $appointment = new HairCareAppointment();
                    $appointment->full_name = $request->full_name;
                    $appointment->email = $request->email;
                    $appointment->mobile_no = $request->mobile_no;
                    $appointment->service_id = $request->service_id;
                    $appointment->preferred_date = $request->preferred_date;
                    $appointment->preferred_time = $request->preferred_time;
                    $appointment->stylist_type = $request->stylist_type;
                    $appointment->charges = $request->charges;
                    $appointment->special_request = $request->special_request ?? null;
                    $appointment->payment_status = 'paid';
                    $appointment->creator_id = $user->id;
                    $appointment->created_by = $user->id;
                    $appointment->save();

                    $haircarepayment = new HairCarePayment();
                    $haircarepayment->appointment_id = $appointment->id;
                    $haircarepayment->payment_date = now();
                    $haircarepayment->amount = $request->charges;
                    $haircarepayment->transaction_id = $request->session_id ?? null;
                    $haircarepayment->payment_method = 'Stripe';
                    $haircarepayment->payment_status = 'cleared';
                    $haircarepayment->notes = 'Payment via Stripe';
                    $haircarepayment->creator_id = $user->id;
                    $haircarepayment->created_by = $user->id;
                    $haircarepayment->save();

                    try {
                        HairCareStudioOrderPayments::dispatch($haircarepayment);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                        ->with('success', __('Payment completed and appointment booked successfully!'));
                }

                return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function petCarePayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $paymentType = $request->input('type', 'membership');

                if ($paymentType === 'service') {
                    // Service Booking Payment
                    $service = PetCareService::find($request->service);
                    if (!$service) {
                        return redirect()->back()->with('error', __('Service not found.'));
                    }

                    $total = floatval($request->price ?? 0);
                    if ($total <= 0) {
                        return redirect()->back()->with('error', __('Invalid payment amount.'));
                    }

                    $bookingData = [
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone_number' => $request->phone_number,
                        'address' => $request->address,
                        'service' => $request->service,
                        'date' => $request->date,
                        'time_slot' => $request->time_slot,
                        'price' => $request->price,
                        'note' => $request->note,
                        'pet_name' => $request->pet_name,
                        'species_breed' => $request->species_breed,
                        'date_of_birth' => $request->date_of_birth,
                        'gender' => $request->gender,
                        'payment_method' => 'Stripe',
                        'type' => $paymentType,
                        'amount' => $total,
                    ];

                    $productName = $service->name ?? 'Pet Care Service';
                    $description = 'Pet Care Service Booking';
                } else {
                    // Membership Payment
                    $package = PetCareGroomingPackage::find($request->package_id);
                    if (!$package) {
                        return redirect()->back()->with('error', __('Package not found.'));
                    }

                    $total = floatval($package->price ?? 0);
                    if ($total <= 0) {
                        return redirect()->back()->with('error', __('Invalid payment amount.'));
                    }

                    $bookingData = [
                        'name' => $request->name,
                        'phone_no' => $request->phone_no,
                        'email' => $request->email,
                        'package_id' => $request->package_id,
                        'pet_name' => $request->pet_name,
                        'breed' => $request->breed,
                        'date_of_birth' => $request->date_of_birth,
                        'gender' => $request->gender,
                        'address' => $request->address,
                        'notes' => $request->notes,
                        'payment_method' => 'Stripe',
                        'type' => $paymentType,
                        'amount' => $total,
                    ];

                    $productName = $package->name ?? 'Pet Care Package';
                    $description = 'Pet Care Membership Subscription';
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $total,
                    'product_name' => $productName,
                    'description'  => $description,
                    'metadata'     => $bookingData,
                    'callback_url' => route('pet-care.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id' => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('pet-care.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function petCareGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $userId = $user->id;
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $paymentType = $request->type ?? 'membership';

                    if ($paymentType === 'service') {
                        $service = PetCareService::find($request->service);
                        if (!$service) {
                            return redirect()->route('pet-care.frontend.services', ['userSlug' => $userSlug])
                                ->with('error', __('Service not found.'));
                        }

                        $times = explode('-', $request->time_slot);

                        $booking = new PetCareBooking();
                        $booking->name = $request->name;
                        $booking->email = $request->email;
                        $booking->phone_number = $request->phone_number;
                        $booking->address = $request->address;
                        $booking->service = $request->service;
                        $booking->date = $request->date;
                        $booking->start_time = $times[0];
                        $booking->end_time = $times[1];
                        $booking->price = $request->price;
                        $booking->note = $request->note;
                        $booking->pet_name = $request->pet_name;
                        $booking->species_breed = $request->species_breed;
                        $booking->date_of_birth = $request->date_of_birth;
                        $booking->gender = $request->gender;
                        $booking->payment_method = 'Stripe';
                        $booking->payment_status = 'paid';
                        $booking->booking_status = 'completed';
                        $booking->creator_id = $userId;
                        $booking->created_by = $userId;
                        $booking->save();

                        try {
                            PetCareBookingPayment::dispatch($booking);
                        } catch (\Throwable $th) {
                        }

                        return redirect()->route('pet-care.frontend.services', ['userSlug' => $userSlug])
                            ->with('success', __('Payment completed successfully! Your service booking has been confirmed.'));
                    } else {

                        $membership = new PetCareMembership();
                        $membership->name = $request->name;
                        $membership->phone_no = $request->phone_no;
                        $membership->email = $request->email;
                        $membership->grooming_package_id = $request->package_id;
                        $membership->amount = $request->amount;
                        $membership->pet_name = $request->pet_name;
                        $membership->breed_species = $request->breed;
                        $membership->date_of_birth = $request->date_of_birth;
                        $membership->gender = $request->gender;
                        $membership->address = $request->address;
                        $membership->special_request = $request->notes;
                        $membership->payment_method = 'Stripe';
                        $membership->payment_status = 'paid';
                        $membership->membership_status = 'approved';
                        $membership->created_by = $userId;
                        $membership->creator_id = $userId;
                        $membership->save();

                        try {
                            PetCareMembershipPayments::dispatch($membership);
                        } catch (\Throwable $th) {
                        }

                        return redirect()->route('pet-care.frontend.pricing', ['userSlug' => $userSlug])
                            ->with('success', __('Payment completed successfully! Order #:number', ['number' => $membership->membership_id]));
                    }
                }

                $redirectRoute = $request->type === 'service' ? 'pet-care.frontend.services' : 'pet-care.frontend.pricing';
                return redirect()->route($redirectRoute, ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('pet-care.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function boutiqueStudioPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $price = floatval($request->total_amount);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => 'Boutique Studio Booking',
                    'description'  => 'Boutique & Designer Studio Booking Payment',
                    'metadata'     => [
                        'outfit_id'    => $request->outfit_id,
                        'pricing_type' => $request->pricing_type,
                        'size'         => $request->size,
                        'outfit_price' => $request->outfit_price,
                        'first_name'   => $request->first_name,
                        'last_name'    => $request->last_name,
                        'email'        => $request->email,
                        'phone'        => $request->phone,
                        'booking_date' => $request->booking_date,
                        'pickup_date'  => $request->pickup_date,
                        'return_date'  => $request->return_date,
                        'rental_days'  => $request->rental_days,
                        'address'      => $request->address,
                        'notes'        => $request->notes,
                        'services'     => json_decode($request->services ?? '[]', true),
                        'service_total' => $request->service_total,
                        'total_amount' => $request->total_amount,
                    ],
                    'callback_url' => route('boutique-studio.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id'     => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function boutiqueStudioGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $booking                 = new BoutiqueBooking();
                    $booking->outfit_id      = $request->outfit_id;
                    $booking->pricing_type   = $request->pricing_type;
                    $booking->size           = $request->size;
                    $booking->outfit_price   = $request->outfit_price;
                    $booking->first_name     = $request->first_name;
                    $booking->last_name      = $request->last_name;
                    $booking->email          = $request->email;
                    $booking->phone          = $request->phone;
                    $booking->booking_date   = $request->booking_date;
                    $booking->pickup_date    = $request->pickup_date;
                    $booking->return_date    = $request->return_date;
                    $booking->rental_days    = $request->rental_days;
                    $booking->address        = $request->address;
                    $booking->notes          = $request->notes;
                    $booking->services       = $request->services;
                    $booking->service_total  = $request->service_total;
                    $booking->total_amount   = $request->total_amount;
                    $booking->payment_method = 'Stripe';
                    $booking->payment_status = 'paid';
                    $booking->booking_status = 'confirmed';
                    $booking->transaction_id = $request->session_id ?? null;
                    $booking->creator_id     = $user->id;
                    $booking->created_by     = $user->id;
                    $booking->save();

                    try {
                        BoutiqueBookingPaymentStripe::dispatch($booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('boutique-and-designer-studio.frontend.booking-success', ['userSlug' => $userSlug, 'id' => encrypt($booking->id)])
                        ->with('success', __('Payment completed and booking confirmed successfully!'));
                }

                return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function investmentSystemPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $plan = InvestmentPlan::where('created_by', $user->id)
                    ->where('plan_status', '0')
                    ->find($request->plan_id);

                if (!$plan) {
                    return redirect()->back()->with('error', __('Investment plan not found.'));
                }

                $price = (float) ($request->amount ?? 0);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => $plan->plan_name ?? 'Investment Plan Deposit',
                    'description'  => 'Investment System Stripe Payment',
                    'metadata'     => [
                        'investor_id'     => $request['investor_id'],
                        'plan_id'         => $request->plan_id,
                        'amount'          => $request->amount,
                        'plan_duration'   => $request->plan_duration,
                        'annual_return'   => $request->annual_return,
                        'expected_return' => $request->expected_return,
                        'purchase_date'   => $request->purchase_date,
                        'expiry_date'     => $request->expiry_date,
                    ],
                    'callback_url' => route('investment-system.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id'     => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('investor.loginform', ['userSlug' => $userSlug]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function investmentSystemGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $plan = InvestmentPlan::where('created_by', $user->id)
                        ->where('plan_status', '0')
                        ->find($request->plan_id);

                    if (!$plan) {
                        return redirect()->route('investor.plans', ['userSlug' => $userSlug])
                            ->with('error', __('Investment plan not found.'));
                    }
                    $existingInvestment = InvestorDeposit::where('investor_id', $request->investor_id)->where('plan_id', $request->plan_id)->first();
                    if ($existingInvestment) {
                        $existingInvestment->plan_duration   = $request->plan_duration;
                        $existingInvestment->invested_amount = $request->amount;
                        $existingInvestment->amount          = $request->amount;
                        $existingInvestment->annual_return   = $request->annual_return;
                        $existingInvestment->expected_return = $request->expected_return;
                        $existingInvestment->payment_type    = 'Stripe';
                        $existingInvestment->status          = '1';
                        $existingInvestment->receipt         = $request->receipt_url ?? null;
                        $existingInvestment->purchase_date   = $request->purchase_date;
                        $existingInvestment->expiry_date     = $request->expiry_date;
                        $existingInvestment->save();
                        $investment = $existingInvestment;
                    } else {
                        $investment                  = new InvestorDeposit();
                        $investment->investor_id     = $request->investor_id;
                        $investment->plan_id         = $request->plan_id;
                        $investment->created_by      = $user->id ?? '';
                        $investment->plan_duration   = $request->plan_duration;
                        $investment->invested_amount = $request->amount;
                        $investment->amount          = $request->amount;
                        $investment->annual_return   = $request->annual_return;
                        $investment->expected_return = $request->expected_return;
                        $investment->payment_type    = 'Stripe';
                        $investment->status          = '1';
                        $investment->receipt         = $request->receipt_url ?? null;
                        $investment->purchase_date   = $request->purchase_date;
                        $investment->expiry_date     = $request->expiry_date;
                        $investment->save();
                    }

                    $transaction              = new InvestorTransaction();
                    $transaction->plan_id     = $investment->plan_id;
                    $transaction->investor_id = $investment->investor_id;
                    $transaction->trx_id      = 'TXN-' . strtoupper(uniqid());
                    $transaction->amount      = $investment->amount;
                    $transaction->type        = 'credit';
                    $transaction->detail      = 'Deposit Via - Stripe';
                    $transaction->created_by  = $user->id;
                    $transaction->save();

                    try {
                        InvestorDepositPayment::dispatch($investment, $transaction);
                    } catch (\Throwable $th) {
                    }
                    return redirect()->route('investor.transaction', ['userSlug' => $userSlug])->with('success', __('Payment completed and deposit created successfully!'));
                }

                return redirect()->route('investor.deposit', [
                    'userSlug' => $userSlug,
                    'id'       => $request->investor_id ?? 0,
                    'PlanId'   => $request->plan_id ?? 0,
                ])->with('error', __('Payment was failed.'));
            }

            return redirect()->route('investor.plans', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function jewelleryStorePayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $price = floatval($request->totalAmount ?? 0);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(substr(uniqid(), -12));
                $stripeService = new StripeService($userSlug);
                $stripe_session = $stripeService->initiatePayment([
                    'amount'       => $price,
                    'product_name' => 'Jewellery Booking',
                    'description'  => 'Jewellery Store Booking Payment',
                    'metadata'     => [
                        'firstName' => $request->firstName,
                        'lastName' => $request->lastName,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'reservationDate' => $request->reservationDate,
                        'quantity' => $request->quantity,
                        'pickupDate' => $request->pickupDate,
                        'returnDate' => $request->returnDate,
                        'address' => $request->address,
                        'specialRequirements' => $request->specialRequirements,
                        'paymentMethod' => $request->paymentMethod,
                        'subtotal' => floatval($request->subtotal),
                        'discount' => floatval($request->discount),
                        'tax' => floatval($request->tax),
                        'makingCharges' => floatval($request->makingCharges),
                        'totalAmount' => floatval($request->totalAmount),
                        'itemId' => $request->jewelleryItemId,
                    ],
                    'callback_url' => route('jewellery-store.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                    'order_id'     => $orderID,
                ]);

                if (isset($stripe_session->url)) {
                    return redirect()->to($stripe_session->url);
                }

                return redirect()->back()->with('error', __('Failed to create Stripe session.'));
            }

            return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function jewelleryStoreGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if ($user) {
                $stripeService = new StripeService($userSlug);

                if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                    $userId = $user->id;
                    $item = JewelleryStoreItem::where('id', $request->itemId)->first();

                    $booking = new JewelleryStoreJewelleryBooking();
                    $booking->customer_name = ($request->firstName ?? '') . ' ' . ($request->lastName ?? '');
                    $booking->email = $request->email ?? '';
                    $booking->contact = $request->phone ?? '';
                    $booking->reservation_date = $request->reservationDate ?? null;
                    $booking->date_of_sale = now();
                    $booking->quantity = $request->quantity ?? '';
                    $booking->gross_weight = $item['gross_weight'] ?? '';
                    $booking->net_weight = $item['net_weight'] ?? '';
                    $booking->stone_weight = $item['stone_weight'] ?? '';
                    $booking->making_charges = $request->makingCharges ?? 0;
                    $booking->metal = $item['metal'] ?? '';
                    $booking->sub_total = $request->subtotal ?? 0;
                    $booking->discount = $request->discount ?? 0;
                    $booking->taxes = $request->tax ?? 0;
                    $booking->grand_amount = $request->totalAmount ?? 0;
                    $booking->shipping_address = $request->address ?? '';
                    $booking->special_requirements = $request->specialRequirements ?? null;
                    $booking->payment_method = 'Stripe';
                    $booking->payment_status = 'paid';
                    $booking->item_id = $item['id'] ?? '';
                    $booking->creator_id = $userId;
                    $booking->created_by = $userId;
                    $booking->save();

                    if ($item && isset($request->quantity)) {
                        $quantityToDecrease = (int) $request->quantity;
                        $item->decrement('quantity', $quantityToDecrease);
                    }

                    try {
                        JewelleryStoreJewelleryBookingPayments::dispatch($request, $booking);
                    } catch (\Throwable $th) {
                    }

                    return redirect()->route('jewellery-store.frontend.order-status', ['userSlug' => $userSlug, 'bookingId' => $booking->id]);
                }

                return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', __('Payment was cancelled.'));
            }

            return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function freelancingWalletPayWithStripe(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if (!$user) {
                return redirect()->back()->with('error', __('User not found.'));
            }

            $client = auth('freelancer_client')->user();
            if (!$client) {
                return redirect()->route('freelancing.login', ['userSlug' => $userSlug])
                    ->with('error', __('Please login to continue.'));
            }

            $price = floatval($request->amount);
            if ($price <= 0) {
                return redirect()->back()->with('error', __('Invalid payment amount.'));
            }

            $orderID = strtoupper(substr(uniqid(), -12));
            $stripeService = new StripeService($userSlug);
            $stripe_session = $stripeService->initiatePayment([
                'amount'       => $price,
                'product_name' => 'Freelancing Wallet Top-up',
                'description'  => 'Freelancing Platform Wallet Add Funds',
                'metadata'     => [
                    'client_id'      => $client->id,
                    'customer_name'  => $client->name,
                    'customer_email' => $client->email,
                    'amount'         => $price,
                ],
                'callback_url' => route('freelancing.wallet.payment.stripe.status', ['userSlug' => $userSlug, 'order_id' => $orderID]),
                'order_id'     => $orderID,
            ]);

            if (isset($stripe_session->url)) {
                return redirect()->to($stripe_session->url);
            }

            return redirect()->back()->with('error', __('Failed to create Stripe session.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function freelancingWalletGetStripeStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if (!$user) {
                return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                    ->with('error', __('Invalid session.'));
            }

            $stripeService = new StripeService($userSlug);

            if ($request->return_type == 'success' && $stripeService->verifyPayment($request)) {
                $client = auth('freelancer_client')->user();
                if (!$client) {
                    return redirect()->route('freelancing.login', ['userSlug' => $userSlug])
                        ->with('error', __('Please login to continue.'));
                }

                $amount = floatval($request->amount);

                // Get or create wallet
                $wallet = $client->wallet;
                if (!$wallet) {
                    $wallet = new FreelancingClientWallet();
                    $wallet->client_id       = $client->id;
                    $wallet->balance         = 0.00;
                    $wallet->spent_balance   = 0.00;
                    $wallet->frozen_balance  = 0.00;
                    $wallet->total_withdrawn = 0.00;
                    $wallet->created_by      = $user->id;
                    $wallet->save();
                }

                $balanceBefore = (float) $wallet->balance;
                $balanceAfter  = $balanceBefore + $amount;

                // Create wallet transaction
                $transaction = new FreelancingClientWalletTransaction();
                $transaction->wallet_id      = $wallet->id;
                $transaction->client_id      = $client->id;
                $transaction->transaction_id = 'TXN_' . strtoupper(bin2hex(random_bytes(8)));
                $transaction->type           = 'credit';
                $transaction->amount         = $amount;
                $transaction->balance_before = $balanceBefore;
                $transaction->balance_after  = $balanceAfter;
                $transaction->status         = 'completed';
                $transaction->category       = 'deposit';
                $transaction->description    = 'Wallet deposit via Stripe';
                $transaction->payment_method = 'Stripe';
                $transaction->created_by     = $user->id;
                $transaction->save();

                // Update wallet balance
                $wallet->balance = $balanceAfter;
                $wallet->save();

                // Dispatch event
                ClientWalletPayment::dispatch($transaction);

                return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                    ->with('success', __('Payment completed successfully! Your wallet has been topped up.'));
            }

            return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                ->with('error', __('Payment was cancelled.'));
        } catch (\Exception $e) {
            return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                ->with('error', $e->getMessage());
        }
    }
}
