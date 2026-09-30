<?php

namespace Automas\Paypal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Plan;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Automas\Paypal\Events\PaypalPaymentStatus;
use Automas\Paypal\Services\PaypalPaymentService;
use Automas\ArtShowcase\Events\CreateArtWorkOrderPayment;
use Automas\ArtShowcase\Models\ArtShowcaseArtWork;
use Automas\ArtShowcase\Models\ArtShowcaseArtWorkOrder;
use Automas\Bookings\Models\BookingAppointment;
use Automas\Bookings\Models\BookingPackage;
use Automas\Bookings\Models\BookingCustomer;

use Automas\Holidayz\Helpers\HolidayzAvailabilityHelper;
use Automas\Holidayz\Models\HolidayzCart;
use Automas\Holidayz\Models\HolidayzRoomBooking;
use Automas\Holidayz\Models\HolidayzRoomBookingItem;
use Automas\Holidayz\Models\HolidayzCoupon;
use Automas\Holidayz\Models\HolidayzCouponUsage;

use Automas\LMS\Models\LMSCart;
use Automas\LMS\Models\LMSOrder;
use Automas\LMS\Models\LMSOrderItem;
use Automas\LMS\Models\LMSCoupon;
use Automas\BeautySpaManagement\Models\BeautyBooking;
use Automas\BeautySpaManagement\Events\BeautyBookingPayments;
use Automas\BeautySpaManagement\Models\BeautyService;
use Automas\BeautySpaManagement\Models\BeautyBookingReceipt;
use Automas\BeautySpaManagement\Models\BeautyServiceOffer;
use Automas\Bookings\Events\BookingAppointmentPayments;
use Automas\CoworkingSpaceManagement\Events\CoworkingBookingPayments;
use Automas\CoworkingSpaceManagement\Events\CoworkingMembershipPayments;
use Automas\CoworkingSpaceManagement\Models\CoworkingBooking;
use Automas\CoworkingSpaceManagement\Models\CoworkingMembership;
use Automas\CoworkingSpaceManagement\Models\CoworkingMembershipPlan;
use Automas\EventsManagement\Events\EventBookingPayments;
use Automas\ParkingManagement\Models\ParkingBooking;
use Automas\EventsManagement\Models\Event;
use Automas\EventsManagement\Models\EventBooking;
use Automas\EventsManagement\Models\EventBookingPayment;
use Automas\Facilities\Services\FacilitiesBookingService;
use Automas\Holidayz\Events\HolidayzBookingPayments;
use Automas\InfluencerMarketing\Models\InfluencerMarketingDeposit;
use Automas\InfluencerMarketing\Events\InfluencerMarketingPayment;
use Automas\LaundryManagement\Events\LaundryBookingPayments;
use Automas\LaundryManagement\Models\LaundryRequest;
use Automas\LMS\Events\LMSOrderPayments;
use Automas\MovieShowBookingSystem\Events\MovieBookingPayments;
use Automas\MovieShowBookingSystem\Models\MovieBooking;
use Automas\ParkingManagement\Events\ParkingBookingPayments;
use Automas\Facilities\Events\FacilityBookingPayment;
use Automas\VehicleBookingManagement\Events\VehicleBookingPayments;
use Automas\VehicleBookingManagement\Models\VehicleBooking;
use Automas\NGOManagment\Events\CreateNgoDonation;
use Automas\NGOManagment\Models\NgoCampaign;
use Automas\NGOManagment\Models\NgoDonation;
use Automas\NGOManagment\Models\NgoDonor;
use Automas\SportsClubAndAcademyManagement\Events\SportsClubBookingPayments;
use Automas\SportsClubAndAcademyManagement\Events\SportsClubPlanPayments;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubAndGroundOrder;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubAssignedMembership;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubBookingFacility;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubFacility;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubMember;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubMembershipPlan;
use Automas\SportsClubAndAcademyManagement\Models\SportsClubMembershipPlanPayment;
use Automas\WaterParkManagement\Events\WaterParkBookingPaymentPaypal;
use Automas\WaterParkManagement\Models\WaterParkBooking;

use Automas\TVStudio\Services\TVStudioCheckoutService;
use Automas\TattooStudioManagement\Models\TattooAppointment;
use Automas\TattooStudioManagement\Events\TattooAppointmentPaymentPaypal;

use Automas\PhotoStudioManagement\Models\PhotoStudioService;
use Automas\PhotoStudioManagement\Models\PhotoStudioAppointment;
use Automas\PhotoStudioManagement\Models\PhotoStudioAppointmentPayment;
use Automas\PhotoStudioManagement\Events\PhotoStudioAppointmentPayments;
use Automas\YogaClasses\Models\YogaClassesCart;
use Automas\YogaClasses\Models\YogaClassesOrder;
use Automas\YogaClasses\Models\YogaClassesPurchasedCourse;
use Automas\YogaClasses\Events\YogaClassesOrderPayments;

use Automas\Ebook\Models\EbookBookOrder;
use Automas\Ebook\Events\EbookPayment;
use Automas\HairAndCareStudio\Events\HairCareStudioOrderPayments;
use Automas\HairAndCareStudio\Models\HairCareAppointment;
use Automas\HairAndCareStudio\Models\HairCarePayment;

use Automas\PetCare\Models\PetCareGroomingPackage;
use Automas\PetCare\Models\PetCareMembership;
use Automas\PetCare\Events\PetCareMembershipPayments;
use Automas\PetCare\Models\PetCareService;
use Automas\PetCare\Models\PetCareBooking;
use Automas\PetCare\Events\PetCareBookingPayment;

use Automas\BoutiqueAndDesignerStudio\Models\BoutiqueBooking;
use Automas\BoutiqueAndDesignerStudio\Events\BoutiqueBookingPaymentPaypal;
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

class PaypalController extends Controller
{

    public function planPayWithPaypal(Request $request)
    {
        try {
            $plan = Plan::find($request->plan_id);
            $user = User::find($request->user_id);

            if ($plan && $user) {
                $admin_settings = getAdminAllSetting();
                $admin_currency = !empty($admin_settings['defaultCurrency']) ? $admin_settings['defaultCurrency'] : '';

                $user_counter = !empty($request->user_counter_input) ? $request->user_counter_input : 0;
                $user_module = !empty($request->user_module_input) ? $request->user_module_input : '';
                $storage_limit = !empty($request->storage_limit_input) ? $request->storage_limit_input : 0;
                $duration = !empty($request->time_period) ? $request->time_period : 'Month';

                $user_module_price = 0;
                if (!empty($user_module) && $plan->custom_plan == 1) {
                    $user_module_array = explode(',', $user_module);
                    foreach ($user_module_array as $value) {
                        $temp = ($duration == 'Year') ? ModulePriceByName($value)['yearly_price'] : ModulePriceByName($value)['monthly_price'];
                        $user_module_price += $temp;
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

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
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
                        return redirect()->route('plans.index')->with('success', __('Plan activated successfully!'));
                    } else {
                        return redirect()->route('plans.index')->with('error', __('Something went wrong, please try again.'));
                    }
                }

                $paypalService = new PaypalPaymentService();

                $response = $paypalService->createOrder([
                    'amount' => $price,
                    'callback_url' => route('paypal.plan.status', [
                        $plan->id,
                        'amount' => $price,
                        'user_module' => $user_module,
                        'counter' => $counter,
                        'duration' => $duration,
                        'coupon_code' => $request->coupon_code,
                        'order_id' => $orderID,
                    ]),
                ]);

                if ($response['success']) {
                    $order = new Order();
                    $order->order_id = $orderID;
                    $order->name = $user->name ?: '';
                    $order->email = $user->email ?: '';
                    $order->plan_name = !empty($plan->name) ? $plan->name : 'Basic Package';
                    $order->plan_id = $plan->id;
                    $order->price = !empty($price) ? $price : 0;
                    $order->currency = $admin_currency;
                    $order->txn_id = $orderID ?: null;
                    $order->payment_type = 'Paypal';
                    $order->payment_status = 'pending';
                    $order->created_by = $user->id;
                    $order->save();

                    return redirect($response['approve_url']);
                }
                return redirect()->route('plans.index')->with('error', $response['error'] ?? __('Payment initialization failed.'));
            }

            return redirect()->route('plans.index')->with('error', __('The plan has been deleted.'));
        } catch (\Exception $e) {
            return redirect()->route('plans.index')->with('error', $e->getMessage());
        }
    }

    public function planGetPaypalStatus(Request $request, $plan_id)
    {
        try {
            $user = Auth::user();
            $plan = Plan::find($plan_id);
            $order = Order::where('order_id', $request->order_id)->first();

            if ($plan && $order) {
                $paypalService = new PaypalPaymentService();
                $response = $paypalService->captureOrder($request['token']);

                if ($response['success']) {
                    $counter = [
                        'user_counter' => $request->counter['user_counter'] ?: 0,
                        'storage_limit' => $request->counter['storage_limit'] ?: 0,
                    ];

                    $assignPlan = assignPlan($plan->id, $request->duration, $request->user_module, $counter, $user->id);

                    if ($assignPlan['is_success']) {
                        if ($request->coupon_code) {
                            $coupon = Coupon::where('code', $request->coupon_code)->first();
                            if ($coupon) {
                                recordCouponUsage($coupon->id, $user->id, $request->order_id);
                            }
                        }



                        $type = 'Subscription';
                        try {
                            PaypalPaymentStatus::dispatch($plan, $type, $order);
                        } catch (\Exception $exception) {
                        }

                        $order->txn_id = $response['transaction_id'];
                        $order->payment_status = 'succeeded';
                        $order->save();
                        return redirect()->route('plans.index')->with('success', __('Plan activated successfully.'));
                    }
                }

                $order->payment_status = 'failed';
                $order->save();
            }

            return redirect()->route('plans.index')->with('error', __('Payment failed.'));
        } catch (\Exception $e) {
            return redirect()->route('plans.index')->with('error', $e->getMessage());
        }
    }

    public function bookingPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $package = BookingPackage::find($request->selectedPackageItem);

                if ($package) {
                    $price = $package->price ?? 0;

                    if ($price > 0) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        $paypalService = new PaypalPaymentService($userSlug);

                        $response = $paypalService->createOrder([
                            'amount' => $price,
                            'callback_url' => route('paypal.booking.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, [
                                'selectedDate' => $request->selectedDate,
                                'selectedStaff' => $request->selectedStaff,
                                'selectedItem' => $request->selectedItem,
                                'selectedPackageItem' => $request->selectedPackageItem,
                                'selectedTimeSlot' => [
                                    'start_time' => $request->input('selectedTimeSlot.start_time'),
                                    'end_time' => $request->input('selectedTimeSlot.end_time'),
                                    'label' => $request->input('selectedTimeSlot.label')
                                ],
                                'formData' => [
                                    'firstName' => $request->input('formData.firstName'),
                                    'lastName' => $request->input('formData.lastName'),
                                    'email' => $request->input('formData.email'),
                                    'phone' => $request->input('formData.phone'),
                                    'description' => $request->input('formData.description'),
                                    'paymentOption' => $request->input('formData.paymentOption')
                                ],
                                'order_id' => $orderID,
                            ]);

                            return redirect($response['approve_url']);
                        }
                        return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                    }

                    return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
                }

                return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('Package not found.'));
            }

            return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function bookingGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $package = BookingPackage::find($bookingData['selectedPackageItem']);

                        if ($package) {
                            $userId = $package->created_by;

                            $customer = BookingCustomer::where('email', $bookingData['formData']['email'])
                                ->where('created_by', $userId)
                                ->first();

                            if (!$customer) {
                                $customer = new BookingCustomer();
                                $customer->first_name = $bookingData['formData']['firstName'];
                                $customer->last_name = $bookingData['formData']['lastName'];
                                $customer->email = $bookingData['formData']['email'];
                                $customer->mobile_number = $bookingData['formData']['phone'];
                                $customer->description = $bookingData['formData']['description'] ?? null;
                                $customer->created_by = $userId;
                                $customer->creator_id = $userId;
                                $customer->save();
                            }

                            $currentYear = date('Y');
                            $lastAppointment = BookingAppointment::where('created_by', $userId)
                                ->where('appointment_number', 'like', 'APT-' . $currentYear . '-' . $userId . '-%')
                                ->orderBy('appointment_number', 'desc')
                                ->first();

                            $nextNumber = $lastAppointment ? ((int) substr($lastAppointment->appointment_number, -4)) + 1 : 1;
                            $appointmentNumber = 'APT-' . $currentYear . '-' . $userId . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                            $appointment = new BookingAppointment();
                            $appointment->appointment_number = $appointmentNumber;
                            $appointment->date = $bookingData['selectedDate'];
                            $appointment->item_id = $bookingData['selectedItem'];
                            $appointment->package_id = $bookingData['selectedPackageItem'];
                            $appointment->staff_id = $bookingData['selectedStaff'];
                            $appointment->customer_id = $customer->id;
                            $appointment->start_time = $bookingData['selectedTimeSlot']['start_time'];
                            $appointment->end_time = $bookingData['selectedTimeSlot']['end_time'];
                            $appointment->payment = 'Paypal';
                            $appointment->status = 'confirmed';
                            $appointment->payment_status = 'paid';
                            $appointment->online_payment_id = $response['transaction_id'];
                            $appointment->created_by = $userId;
                            $appointment->creator_id = $userId;
                            $appointment->save();

                            try {
                                BookingAppointmentPayments::dispatch($appointment);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('success', __('The Booking has been created successfully.'));
                        }

                        return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('Package not found.'));
                    }

                    return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('Something went wrong, Please try again.'));
            }

            return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', __('User not found.'));
        } catch (\Exception $exception) {
            return redirect()->route('booking.home', ['userSlug' => $userSlug])->with('error', $exception->getMessage());
        }
    }


    public function beautySpaPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $service = BeautyService::where('id', $request->service)
                    ->where('created_by', $user->id)
                    ->first();

                if ($service) {
                    $offer = BeautyServiceOffer::where('beauty_service_id', $service->id)
                        ->where('start_date', '<=', $request->date)
                        ->where('end_date', '>=', $request->date)
                        ->where('created_by', $user->id)
                        ->get();

                    $price = $offer->isNotEmpty() ? $offer->first()->offer_price : $service->price;
                    $totalPrice = $price * $request->person;

                    if ($totalPrice > 0) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        $paypalService = new PaypalPaymentService($userSlug);

                        $response = $paypalService->createOrder([
                            'amount' => $totalPrice,
                            'callback_url' => route('paypal.beauty-spa.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, [
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
                                'payment_option' => $request->payment_option,
                                'order_id' => $orderID,
                            ]);

                            return redirect($response['approve_url']);
                        }
                        return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                    }

                    return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
                }

                return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', __('Service not found.'));
            }

            return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function beautySpaGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $service = BeautyService::where('id', $bookingData['service'])
                            ->where('created_by', $user->id)
                            ->first();

                        if ($service) {
                            $offer = BeautyServiceOffer::where('beauty_service_id', $service->id)
                                ->where('start_date', '<=', $bookingData['date'])
                                ->where('end_date', '>=', $bookingData['date'])
                                ->where('created_by', $user->id)
                                ->get();

                            $price = $offer->isNotEmpty() ? $offer->first()->offer_price : $service->price;
                            $servicePrice = $price * $bookingData['person'];
                            $times = explode('-', $bookingData['time_slot']);

                            $booking = new BeautyBooking();
                            $booking->name = $bookingData['name'];
                            $booking->email = $bookingData['email'];
                            $booking->phone_number = $bookingData['phone_number'];
                            $booking->service = $bookingData['service'];
                            $booking->date = $bookingData['date'];
                            $booking->start_time = $times[0];
                            $booking->end_time = $times[1];
                            $booking->person = $bookingData['person'];
                            $booking->price = $servicePrice;
                            $booking->gender = $bookingData['gender'];
                            $booking->reference = $bookingData['reference'];
                            $booking->notes = $bookingData['additional_notes'];
                            $booking->payment_option = 'Paypal';
                            $booking->payment_status = 'paid';
                            $booking->stage_id = 0;
                            $booking->creator_id = null;
                            $booking->created_by = $user->id;
                            $booking->save();

                            $beautyreceipt = new BeautyBookingReceipt();
                            $beautyreceipt->beauty_booking_id = $booking->id;
                            $beautyreceipt->name = $booking->name;
                            $beautyreceipt->service = $booking->service;
                            $beautyreceipt->number = $booking->phone_number;
                            $beautyreceipt->gender = $booking->gender;
                            $beautyreceipt->start_time = $booking->start_time;
                            $beautyreceipt->end_time = $booking->end_time;
                            $beautyreceipt->price = $booking->price;
                            $beautyreceipt->payment_type = 'Paypal';
                            $beautyreceipt->created_by = $booking->created_by;
                            $beautyreceipt->save();

                            try {
                                BeautyBookingPayments::dispatch($booking);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('beauty-spa.booking-success', ['userSlug' => $userSlug, 'id' => encrypt($booking->id)])
                                ->with('success', __('Payment completed and booking confirmed successfully!'));
                        }

                        return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', __('Service not found.'));
                    }

                    return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('beauty-spa.booking', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function lmsPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $student = auth('lms_student')->user();

                if ($student) {
                    $cartItems = LMSCart::where('created_by', $user->id)
                        ->where('student_id', $student->id)
                        ->with('course')
                        ->get();

                    if ($cartItems->isNotEmpty()) {
                        $originalTotal = $cartItems->sum('original_price');
                        $subtotal = $cartItems->sum('price');
                        $courseDiscount = $originalTotal - $subtotal;
                        $couponDiscount = 0;

                        $appliedCoupon = Session::get('applied_coupon');
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

                        if ($total > 0) {
                            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                            $paypalService = new PaypalPaymentService($userSlug);

                            $response = $paypalService->createOrder([
                                'amount' => $total,
                                'callback_url' => route('paypal.lms.payment.status', [
                                    'userSlug' => $userSlug,
                                    'order_id' => $orderID,
                                ]),
                            ]);

                            if ($response['success']) {
                                Session::put($orderID, [
                                    'original_total' => $originalTotal,
                                    'payment_method' => 'Paypal',
                                    'payment_note' => $request->payment_note,
                                    'subtotal' => $subtotal,
                                    'course_discount' => $courseDiscount,
                                    'coupon_discount' => $couponDiscount,
                                    'total' => $total,
                                    'applied_coupon' => $appliedCoupon,
                                    'order_id' => $orderID,
                                ]);

                                return redirect($response['approve_url']);
                            }
                            return redirect()->route('lms.frontend.checkout', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                        }

                        return redirect()->back()->with('error', __('Invalid payment amount.'));
                    }

                    return redirect()->route('lms.frontend.cart', ['userSlug' => $userSlug])
                        ->with('error', __('Your cart is empty'));
                }

                return redirect()->route('lms.frontend.login', ['userSlug' => $userSlug]);
            }

            return redirect()->back()->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function lmsGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $student = auth('lms_student')->user();

                if ($student) {
                    $orderData = Session::get($request->get('order_id', ''));
                    Session::forget($request->get('order_id', ''));

                    if ($orderData) {
                        $paypalService = new PaypalPaymentService($userSlug);
                        $response = $paypalService->captureOrder($request['token']);

                        if ($response['success']) {
                            $cartItems = LMSCart::where('created_by', $user->id)
                                ->where('student_id', $student->id)
                                ->with('course')
                                ->get();

                            $order = new LMSOrder();
                            $order->order_number = LMSOrder::generateOrderNumber($user->id);
                            $order->student_id = $student->id;
                            $order->payment_method = 'Paypal';
                            $order->payment_status = 'paid';
                            $order->original_total = $orderData['original_total'];
                            $order->subtotal = $orderData['subtotal'];
                            $order->discount_amount = $orderData['course_discount'];
                            $order->coupon_discount = $orderData['coupon_discount'];
                            $order->total_discount = $orderData['course_discount'] + $orderData['coupon_discount'];
                            $order->total_amount = $orderData['total'];
                            $order->coupon_id = $orderData['applied_coupon']['id'] ?? null;
                            $order->coupon_code = $orderData['applied_coupon']['code'] ?? null;
                            $order->status = 'completed';
                            $order->notes = $orderData['payment_note'];
                            $order->order_date = now();
                            $order->payment_id = $response['transaction_id'];
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
                            Session::forget('applied_coupon');

                            if ($order->coupon_id) {
                                LMSCoupon::where('id', $order->coupon_id)->increment('used_count');
                            }

                            try {
                                LMSOrderPayments::dispatch($order);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('lms.frontend.home', ['userSlug' => $userSlug])
                                ->with('success', __('The order has been created successfully.'));
                        }

                        return redirect()->route('lms.frontend.checkout', ['userSlug' => $userSlug])
                            ->with('error', __('Payment was cancelled or failed.'));
                    }

                    return redirect()->route('lms.frontend.checkout', ['userSlug' => $userSlug])
                        ->with('error', __('Something went wrong. Please try again.'));
                }

                return redirect()->route('lms.frontend.login', ['userSlug' => $userSlug]);
            }

            return redirect()->route('lms.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', __('User not found.'));
        } catch (\Exception $exception) {
            return redirect()->route('lms.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function parkingPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->total_amount);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.parking.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
                            'slot_name' => $request->slot_name,
                            'slot_type_id' => $request->slot_type_id,
                            'date' => $request->date,
                            'start_time' => $request->start_time,
                            'end_time' => $request->end_time,
                            'customer_name' => $request->customer_name,
                            'customer_email' => $request->customer_email,
                            'customer_phone' => $request->customer_phone,
                            'vehicle_name' => $request->vehicle_name,
                            'vehicle_number' => $request->vehicle_number,
                            'payment_option' => $request->payment_option,
                            'total_amount' => $request->total_amount,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
            }

            return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function parkingGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $booking = new ParkingBooking();
                        $booking->slot_name = $bookingData['slot_name'];
                        $booking->slot_type_id = $bookingData['slot_type_id'];
                        $booking->booking_date = $bookingData['date'];
                        $booking->start_time = $bookingData['start_time'];
                        $booking->end_time = $bookingData['end_time'];
                        $booking->customer_name = $bookingData['customer_name'];
                        $booking->customer_email = $bookingData['customer_email'];
                        $booking->customer_phone = $bookingData['customer_phone'];
                        $booking->vehicle_name = $bookingData['vehicle_name'];
                        $booking->vehicle_number = $bookingData['vehicle_number'];
                        $booking->total_amount = $bookingData['total_amount'];
                        $booking->payment_method = 'Paypal';
                        $booking->payment_status = 'paid';
                        $booking->booking_status = 'confirmed';
                        $booking->creator_id = $user->id;
                        $booking->created_by = $user->id;
                        $booking->save();

                        try {
                            ParkingBookingPayments::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('parking-management.frontend.booking-success', ['userSlug' => $userSlug, 'id' => encrypt($booking->id)])
                            ->with('success', __('Payment completed and booking confirmed successfully!'));
                    }

                    return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('parking-management.frontend.booking', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function laundryPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->total ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.laundry.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
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
                            'total' => $request->total,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
            }

            return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function laundryGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $booking = new LaundryRequest();
                        $booking->name = $bookingData['name'];
                        $booking->email = $bookingData['email'];
                        $booking->phone = $bookingData['phone'];
                        $booking->address = $bookingData['address'];
                        $booking->location = $bookingData['location'];
                        $booking->cloth_no = $bookingData['numberOfItems'];
                        $booking->instructions = $bookingData['specialInstructions'];
                        $booking->pickup_date = $bookingData['pickupDate'] . ' ' . $bookingData['pickupTime'];
                        $booking->delivery_date = $bookingData['deliveryDate'] . ' ' . $bookingData['deliveryTime'];
                        $booking->services = $bookingData['services'];
                        $booking->payment_method = 'Paypal';
                        $booking->payment_id = $response['transaction_id'];
                        $booking->status = 2;
                        $booking->total = $bookingData['total'];
                        $booking->created_by = $user->id;
                        $booking->creator_id = $user->id;
                        $booking->save();

                        $invoice = new LaundryInvoice();
                        $invoice->laundry_request_id = $booking->id;
                        $invoice->amount = $booking->total;
                        $invoice->status = 1;
                        $invoice->creator_id = $user->id;
                        $invoice->created_by = $user->id;
                        $invoice->save();

                        if ($invoice->status == 1) {
                            $payment = new LaundryPayment();
                            $payment->payment_amount = $invoice->amount;
                            $payment->invoice_id = $invoice->id;
                            $payment->payment_date = date('Y-m-d H:i:s');
                            $payment->status = 'cleared';
                            $payment->creator_id = $user->id;
                            $payment->created_by = $user->id;
                            $payment->save();
                        }

                        try {
                            LaundryBookingPayments::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('laundry-management.frontend.booking-success', [
                            'userSlug' => $userSlug,
                            'requestId' => encrypt($booking->id)
                        ])->with('success', __('The laundry request has been created successfully.'));
                    }

                    return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('laundry-management.frontend.booking', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function eventsPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $eventId = $request->event_id;
                $event = Event::where('id', $eventId)
                    ->where('created_by', $user->id)
                    ->first();

                if ($event) {
                    $price = floatval($request->total);

                    if ($price > 0) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        $paypalService = new PaypalPaymentService($userSlug);

                        $response = $paypalService->createOrder([
                            'amount' => $price,
                            'callback_url' => route('paypal.events-management.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, [
                                'event_id' => $eventId,
                                'fullName' => $request->fullName,
                                'email' => $request->email,
                                'phone' => $request->phone,
                                'persons' => $request->persons,
                                'total' => $request->total,
                                'ticket_type_id' => $request->ticket_type_id,
                                'time_slot' => $request->time_slot,
                                'selected_date' => $request->selected_date,
                                'order_id' => $orderID,
                            ]);

                            return redirect($response['approve_url']);
                        }
                        return redirect()->route('events-management.frontend.payment', ['userSlug' => $userSlug, 'id' => $eventId])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                    }

                    return redirect()->route('events-management.frontend.payment', ['userSlug' => $userSlug, 'id' => $eventId])->with('error', __('Invalid payment amount.'));
                }

                return redirect()->route('events-management.frontend.index', ['userSlug' => $userSlug])->with('error', __('Event not found.'));
            }

            return redirect()->route('events-management.frontend.index', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function eventsGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $event = Event::where('id', $bookingData['event_id'])
                            ->where('created_by', $user->id)
                            ->first();

                        if ($event) {
                            $booking = new EventBooking();
                            $booking->event_id = $bookingData['event_id'];
                            $booking->ticket_type_id = $bookingData['ticket_type_id'];
                            $booking->time_slot = $bookingData['time_slot'];
                            $booking->name = $bookingData['fullName'];
                            $booking->email = $bookingData['email'];
                            $booking->mobile = $bookingData['phone'];
                            $booking->person = $bookingData['persons'];
                            $booking->date = $bookingData['selected_date'];
                            $booking->total_price = $bookingData['total'];
                            $booking->price = $bookingData['total'] / $bookingData['persons'];
                            $booking->status = 'confirmed';
                            $booking->created_by = $user->id;
                            $booking->creator_id = $user->id;
                            $booking->save();

                            $eventBookingPayment = new EventBookingPayment();
                            $eventBookingPayment->event_booking_id = $booking->id;
                            $eventBookingPayment->booking_number = $booking->booking_number;
                            $eventBookingPayment->event_name = $event->title;
                            $eventBookingPayment->customer_name = $bookingData['fullName'];
                            $eventBookingPayment->payment_date = now();
                            $eventBookingPayment->amount = $bookingData['total'];
                            $eventBookingPayment->payment_status = 'cleared';
                            $eventBookingPayment->payment_type = 'PayPal';
                            $eventBookingPayment->description = 'Payment via PayPal';
                            $eventBookingPayment->created_by = $user->id;
                            $eventBookingPayment->creator_id = $user->id;
                            $eventBookingPayment->save();

                            try {
                                EventBookingPayments::dispatch($booking, $eventBookingPayment);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('events-management.frontend.ticket', ['userSlug' => $userSlug, 'id' => $booking->id, 'paymentId' => $eventBookingPayment->id])
                                ->with('success', __('The event booking has been created successfully.'));
                        }

                        return redirect()->route('events-management.frontend.index', ['userSlug' => $userSlug])->with('error', __('Event not found.'));
                    }

                    return redirect()->route('events-management.frontend.payment', ['userSlug' => $userSlug, 'id' => $bookingData['event_id']])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('events-management.frontend.index', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('events-management.frontend.index', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('events-management.frontend.index', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function holidayzPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $customer = auth('holidayz_customer')->user();

                if ($customer) {
                    $cart = HolidayzCart::where('created_by', $user->id)
                        ->where('customer_id', $customer->id)
                        ->with(['items.room', 'items.facilities', 'items.taxes'])
                        ->first();

                    if ($cart && $cart->items->isNotEmpty()) {
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

                        if ($total > 0) {
                            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                            $paypalService = new PaypalPaymentService($userSlug);

                            $response = $paypalService->createOrder([
                                'amount' => $total,
                                'callback_url' => route('paypal.holidayz.payment.status', [
                                    'userSlug' => $userSlug,
                                    'order_id' => $orderID,
                                ]),
                            ]);

                            if ($response['success']) {
                                Session::put($orderID, [
                                    'payment_method' => 'Paypal',
                                    'subtotal' => $subtotal,
                                    'tax_amount' => $tax_amount,
                                    'facilities_amount' => $facilities_amount,
                                    'coupon_discount' => $coupon_discount,
                                    'total' => $total,
                                    'applied_coupon' => $applied_coupon,
                                    'special_requests' => $request->special_requests,
                                    'order_id' => $orderID,
                                ]);

                                return redirect($response['approve_url']);
                            }
                            return redirect()->route('hotel.frontend.checkout', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                        }

                        return redirect()->route('hotel.frontend.checkout', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
                    }

                    return redirect()->route('hotel.frontend.cart', ['userSlug' => $userSlug])
                        ->with('error', __('Your cart is empty'));
                }

                return redirect()->route('hotel.frontend.login', ['userSlug' => $userSlug]);
            }

            return redirect()->route('hotel.frontend.index', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function holidayzGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $customer = auth('holidayz_customer')->user();

                if ($customer) {
                    $orderData = Session::get($request->get('order_id', ''));
                    Session::forget($request->get('order_id', ''));

                    if ($orderData) {
                        $paypalService = new PaypalPaymentService($userSlug);
                        $response = $paypalService->captureOrder($request['token']);

                        if ($response['success']) {
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
                            $booking->subtotal = $orderData['subtotal'];
                            $booking->tax_amount = $orderData['tax_amount'];
                            $booking->coupon_id = $orderData['applied_coupon']['id'] ?? null;
                            $booking->discount_amount = $orderData['coupon_discount'];
                            $booking->total_amount = $orderData['total'];
                            $booking->paid_amount = $orderData['total'];
                            $booking->balance_amount = 0;
                            $booking->payment_method = 'Paypal';
                            $booking->status = 'paid';
                            $booking->special_requests = $orderData['special_requests'];
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

                            if ($orderData['applied_coupon']) {
                                $couponId = $orderData['applied_coupon']['id'];
                                $coupon = HolidayzCoupon::find($couponId);
                                if ($coupon) {
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

                            HolidayzCart::where('created_by', $user->id)
                                ->where('customer_id', $customer->id)
                                ->delete();

                            session()->forget('applied_coupon');

                            try {
                                HolidayzBookingPayments::dispatch($booking);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('hotel.frontend.booking-confirm', [
                                'userSlug' => $userSlug,
                                'encryptedBooking' => encrypt($booking->id)
                            ])->with('success', __('The hotel booking has been created successfully.'));
                        }

                        return redirect()->route('hotel.frontend.checkout', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                    }

                    return redirect()->route('hotel.frontend.checkout', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
                }

                return redirect()->route('hotel.frontend.login', ['userSlug' => $userSlug]);
            }

            return redirect()->route('hotel.frontend.index', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('hotel.frontend.checkout', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function facilitiesPaymentWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = FacilitiesBookingService::prepareBookingData($request, $user->id);

                if ($bookingData) {
                    $totalAmount = $bookingData['total_amount'];

                    if ($totalAmount > 0) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        $paypalService = new PaypalPaymentService($userSlug);

                        $response = $paypalService->createOrder([
                            'amount' => $totalAmount,
                            'callback_url' => route('paypal.facilities.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, array_merge($bookingData, ['order_id' => $orderID]));

                            return redirect($response['approve_url']);
                        }
                        return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                    }

                    return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Invalid booking amount.'));
                }

                return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Invalid booking data.'));
            }

            return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function facilitiesGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $booking = FacilitiesBookingService::createBooking($bookingData, $user->id, 'Paypal');

                        FacilitiesBookingService::createPaymentEntry($booking, $user->id, [
                            'method' => 'Paypal',
                            'transaction_id' => $response['transaction_id'],
                            'currency' => $paypalService->currency
                        ]);

                        try {
                            FacilityBookingPayment::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('facilities.frontend.booking-success', [
                            'userSlug' => $userSlug,
                            'booking_number' => $booking->booking_number
                        ])->with('success', __('The facility booking has been created successfully.'));
                    }

                    return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('facilities.frontend.booking', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function vehicleBookingPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->total_amount ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.vehicle-booking.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
                            'email' => $request->email,
                            'selected_seats' => $request->selectedSeats,
                            'passengers' => $request->passengers,
                            'route_id' => $request->route_id,
                            'vehicle_id' => $request->vehicle_id,
                            'booking_date' => $request->booking_date,
                            'total_amount' => $request->total_amount,
                            'special_requests' => $request->special_requests,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
            }

            return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function vehicleBookingGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $booking = new VehicleBooking();
                        $booking->booking_number = VehicleBooking::generateBookingNumber($user->id);
                        $booking->email = $bookingData['email'];
                        $booking->selected_seats = $bookingData['selected_seats'];
                        $booking->passengers = $bookingData['passengers'];
                        $booking->route_id = $bookingData['route_id'];
                        $booking->vehicle_id = $bookingData['vehicle_id'];
                        $booking->booking_date = $bookingData['booking_date'];
                        $booking->total_amount = $bookingData['total_amount'];
                        $booking->payment_method = 'Paypal';
                        $booking->payment_status = 'paid';
                        $booking->booking_status = 'confirmed';
                        $booking->special_requests = $bookingData['special_requests'];
                        $booking->transaction_id = $response['transaction_id'];
                        $booking->creator_id = $user->id;
                        $booking->created_by = $user->id;
                        $booking->save();

                        try {
                            VehicleBookingPayments::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('vehicle-booking.frontend.success', ['userSlug' => $userSlug, 'id' => encrypt($booking->id)])
                            ->with('success', __('The vehicle booking has been created successfully.'));
                    }

                    return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('vehicle-booking.frontend.booking', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function movieBookingPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get('booking_data');

                if ($bookingData) {
                    $bookingData['customer'] = [
                        'name'  => $request->name,
                        'email' => $request->email,
                        'phone' => $request->phone
                    ];

                    $price = floatval($request->amount ?? 0);

                    if ($price > 0) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        $paypalService = new PaypalPaymentService($userSlug);

                        $response = $paypalService->createOrder([
                            'amount' => $price,
                            'callback_url' => route('paypal.movie-booking.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, array_merge($bookingData, ['order_id' => $orderID]));
                            Session::forget('booking_data');

                            return redirect($response['approve_url']);
                        }
                        return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                    }

                    return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
                }

                return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function movieBookingGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $bookedSeats = array_map(function ($seat) {
                            return [
                                'seat' => $seat['seat'],
                                'price' => $seat['price']
                            ];
                        }, $bookingData['seats'] ?? []);

                        $bookedFoods = array_map(function ($food) {
                            return [
                                'id' => $food['id'],
                                'price' => $food['price'],
                                'quantity' => $food['quantity']
                            ];
                        }, $bookingData['foods'] ?? []);

                        $booking                 = new MovieBooking();
                        $booking->booking_id     = strtoupper(uniqid());
                        $booking->movie_id       = $bookingData['movie_id'];
                        $booking->movie_show_id  = $bookingData['show_id'];
                        $booking->screen_id      = $bookingData['screen_id'];
                        $booking->customer_name  = $bookingData['customer']['name'] ?? '';
                        $booking->customer_email = $bookingData['customer']['email'] ?? '';
                        $booking->customer_phone = $bookingData['customer']['phone'] ?? '';
                        $booking->booking_date   = $bookingData['date'] ?? '';
                        $booking->show_time      = $bookingData['time'];
                        $booking->total_seats    = $bookingData['pricing']['tickets'] ?? 0;
                        $booking->booked_seats   = $bookedSeats;
                        $booking->booked_foods   = $bookedFoods;
                        $booking->subtotal       = $bookingData['pricing']['subtotal'] ?? 0;
                        $booking->taxes          = $bookingData['pricing']['taxes'] ?? [];
                        $booking->tax_amount     = $bookingData['pricing']['taxAmount'] ?? 0;
                        $booking->total_amount   = $bookingData['pricing']['total'] ?? 0;
                        $booking->payment_method = 'Paypal';
                        $booking->payment_status = 'paid';
                        $booking->booking_status = 'confirmed';
                        $booking->creator_id     = $user->id;
                        $booking->created_by     = $user->id;
                        $booking->save();

                        try {
                            MovieBookingPayments::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('movie-booking.confirmation', ['userSlug' => $userSlug, 'id' => $booking->booking_id])
                            ->with('success', __('The movie booking has been created successfully.'));
                    }

                    return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('movie-booking.home', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function ngoDonationPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->amount ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.ngo.donation.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
                            'amount' => $request->amount,
                            'campaign_id' => $request->campaign_id,
                            'donor_name' => $request->donor_name,
                            'donor_email' => $request->donor_email,
                            'donor_message' => $request->donor_message,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])->with('error', __('Invalid donation amount.'));
            }

            return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ngoDonationGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $donationData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($donationData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $donor = NgoDonor::where('email', $donationData['donor_email'])
                            ->where('created_by', $user->id)
                            ->first();

                        if (!$donor) {
                            $donor = new NgoDonor();
                            $donor->name = $donationData['donor_name'];
                            $donor->email = $donationData['donor_email'];
                            $donor->created_by = $user->id;
                            $donor->creator_id = $user->id;
                            $donor->save();
                        }

                        $donation = new NgoDonation();
                        $donation->donor_id = $donor->id;
                        $donation->campaign_id = ($donationData['campaign_id'] === 'general' || !$donationData['campaign_id']) ? null : $donationData['campaign_id'];
                        $donation->amount = $donationData['amount'];
                        $donation->payment_method = 'Paypal';
                        $donation->status = 'paid';
                        $donation->transaction_id = $response['transaction_id'];
                        $donation->donation_date = now();
                        $donation->notes = $donationData['donor_message'];
                        $donation->created_by = $user->id;
                        $donation->creator_id = $user->id;
                        $donation->save();

                        $donor->increment('total_donations', $donationData['amount']);

                        if ($donation->campaign_id) {
                            $campaign = NgoCampaign::find($donation->campaign_id);
                            if ($campaign) {
                                $campaign->increment('current_amount', $donationData['amount']);
                            }
                        }

                        try {
                            CreateNgoDonation::dispatch(new Request($donationData), $donation);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])
                            ->with('success', __('The donation has been created successfully.'));
                    }

                    return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])->with('error', __('Donation was cancelled or failed.'));
                }

                return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('ngo.frontend.index', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function coworkingSpacePayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                $paypalService = new PaypalPaymentService($userSlug);
                $paymentType = $request->input('type', 'membership');

                if ($paymentType === 'booking') {
                    $price = floatval($request->totalAmount ?? 0);

                    if ($price > 0) {
                        $response = $paypalService->createOrder([
                            'amount' => $price,
                            'callback_url' => route('paypal.coworking-space.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, [
                                'firstName' => $request->firstName,
                                'lastName' => $request->lastName,
                                'email' => $request->email,
                                'phone' => $request->phone,
                                'company' => $request->company,
                                'specialRequests' => $request->specialRequests,
                                'startDate' => $request->startDate,
                                'endDate' => $request->endDate,
                                'selectedAmenities' => json_decode($request->selectedAmenities, true) ?? [],
                                'totalAmount' => $request->totalAmount,
                                'duration' => $request->duration,
                                'payment_method' => 'Paypal',
                                'type' => 'booking',
                                'order_id' => $orderID,
                            ]);

                            return redirect($response['approve_url']);
                        }

                        return redirect()->back()->with('error', __('Payment initialization failed.'));
                    }

                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                } else {
                    $plan = CoworkingMembershipPlan::find($request->plan_id);

                    if ($plan) {
                        $price = floatval($plan->plan_price);

                        if ($price > 0) {
                            $response = $paypalService->createOrder([
                                'amount' => $price,
                                'callback_url' => route('paypal.coworking-space.payment.status', [
                                    'userSlug' => $userSlug,
                                    'order_id' => $orderID,
                                ]),
                            ]);

                            if ($response['success']) {
                                Session::put($orderID, [
                                    'member_name' => $request->member_name,
                                    'email' => $request->email,
                                    'phone_no' => $request->phone_no,
                                    'plan_id' => $request->plan_id,
                                    'payment_method' => 'Paypal',
                                    'type' => 'membership',
                                    'order_id' => $orderID,
                                ]);

                                return redirect($response['approve_url']);
                            }

                            return redirect()->back()->with('error', __('Payment initialization failed.'));
                        }

                        return redirect()->back()->with('error', __('Invalid payment amount.'));
                    }

                    return redirect()->back()->with('error', __('Plan not found.'));
                }
            }

            return redirect()->back()->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function coworkingSpaceGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $data = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($data) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $paymentType = $data['type'] ?? 'membership';

                        if ($paymentType === 'booking') {
                            $booking = new CoworkingBooking();
                            $booking->first_name = $data['firstName'];
                            $booking->last_name = $data['lastName'];
                            $booking->email = $data['email'];
                            $booking->phone_no = $data['phone'];
                            $booking->amenities = $data['selectedAmenities'];
                            $booking->start_date_time = $data['startDate'];
                            $booking->end_date_time = $data['endDate'];
                            $booking->amount = $data['totalAmount'];
                            $booking->booking_duration = $data['duration'];
                            $booking->payment_status = 'paid';
                            $booking->payment_method = 'Paypal';
                            $booking->special_requests = $data['specialRequests'];
                            $booking->creator_id = $user->id;
                            $booking->created_by = $user->id;
                            $booking->save();

                            try {
                                CoworkingBookingPayments::dispatch($booking);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('coworking-space.home', ['userSlug' => $userSlug])
                                ->with('success', __('The coworking space booking has been created successfully.'));
                        } else {
                            $plan = CoworkingMembershipPlan::find($data['plan_id']);

                            $membership = new CoworkingMembership();
                            $membership->member_name = $data['member_name'];
                            $membership->email = $data['email'];
                            $membership->phone_no = $data['phone_no'];
                            $membership->membership_plan_id = $data['plan_id'];
                            $membership->duration = $plan->duration;
                            $membership->price = $plan->plan_price;
                            $membership->plan_expiry_date = $membership->calculateExpiryDate($plan->duration);
                            $membership->plan_status = 'Active';
                            $membership->payment_method = 'Paypal';
                            $membership->payment_status = 'paid';
                            $membership->creator_id = $user->id;
                            $membership->created_by = $user->id;
                            $membership->save();

                            try {
                                CoworkingMembershipPayments::dispatch($membership);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('coworking-space.purchase', ['userSlug' => $userSlug])->with('success', __('The coworking space membership has been created successfully.'));
                        }
                    }

                    $paymentType = $data['type'] ?? 'membership';
                    $redirectRoute = $paymentType === 'booking' ? 'coworking-space.booking' : 'coworking-space.purchase';
                    return redirect()->route($redirectRoute, ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('coworking-space.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('coworking-space.booking', ['userSlug' => $userSlug])->with('error', __('User not found.'));
        } catch (\Exception $exception) {
            return redirect()->route('coworking-space.booking', ['userSlug' => $userSlug])->with('error', $exception->getMessage());
        }
    }

    public function sportsClubPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $totalAmount = floatval($request->total_amount);

                if ($totalAmount > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $totalAmount,
                        'callback_url' => route('paypal.sports-club.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
                            'ground_id' => $request->ground_id,
                            'name' => $request->name,
                            'email' => $request->email,
                            'mobile_number' => $request->mobile_number,
                            'booked_by' => $request->booked_by,
                            'booking_date' => $request->booking_date,
                            'start_time' => $request->start_time,
                            'end_time' => $request->end_time,
                            'start_date' => $request->start_date,
                            'end_date' => $request->end_date,
                            'facilities' => $request->facilities ?? [],
                            'special_requirements' => $request->special_requirements,
                            'purpose' => $request->purpose,
                            'total_amount' => $request->total_amount,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
            }

            return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sportsClubGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $booking = new SportsClubAndGroundOrder();
                        $booking->sports_club_id = $bookingData['ground_id'];
                        $booking->name = $bookingData['name'];
                        $booking->email = $bookingData['email'];
                        $booking->mobile_no = $bookingData['mobile_number'];
                        $booking->booked_by = $bookingData['booked_by'];
                        $booking->date = $bookingData['booking_date'];
                        $booking->start_time = $bookingData['start_time'];
                        $booking->end_time = $bookingData['end_time'];
                        $booking->start_date = $bookingData['start_date'];
                        $booking->end_date = $bookingData['end_date'];
                        $booking->requirements_desc = $bookingData['special_requirements'];
                        $booking->purpose = $bookingData['purpose'];
                        $booking->total_amount = $bookingData['total_amount'];
                        $booking->payment_type = 'Paypal';
                        $booking->payment_status = 'paid';
                        $booking->creator_id = $user->id;
                        $booking->created_by = $user->id;
                        $booking->save();

                        if (!empty($bookingData['facilities']) && is_array($bookingData['facilities'])) {
                            foreach ($bookingData['facilities'] as $facilityId) {
                                $facility = SportsClubFacility::find($facilityId);
                                if ($facility) {
                                    $bookingFacility = new SportsClubBookingFacility();
                                    $bookingFacility->booking_id = $booking->id;
                                    $bookingFacility->facility_id = $facilityId;
                                    $bookingFacility->facility_name = $facility->name;
                                    $bookingFacility->facility_amount = $facility->amount;
                                    $bookingFacility->creator_id = $user->id;
                                    $bookingFacility->created_by = $user->id;
                                    $bookingFacility->save();
                                }
                            }
                        }

                        try {
                            SportsClubBookingPayments::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])
                            ->with('success', __('The sports club booking has been created successfully.'));
                    }

                    return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('sports-academy.booking', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function sportsClubPlanPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $plan = SportsClubMembershipPlan::find($request->plan_id);

                if ($plan) {
                    $price = floatval($plan->price ?? 0);

                    if ($price > 0) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        $paypalService = new PaypalPaymentService($userSlug);

                        $response = $paypalService->createOrder([
                            'amount' => $price,
                            'callback_url' => route('paypal.sports-club-plan.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, [
                                'user_email' => $request->user_email,
                                'plan_id' => $request->plan_id,
                                'order_id' => $orderID,
                            ]);

                            return redirect($response['approve_url']);
                        }
                        return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                    }

                    return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
                }

                return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Plan not found.'));
            }

            return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sportsClubPlanGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $planPaymentData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($planPaymentData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $plan = SportsClubMembershipPlan::find($planPaymentData['plan_id']);

                        if ($plan) {
                            $member = SportsClubMember::where('email', $planPaymentData['user_email'])
                                ->where('created_by', $user->id)
                                ->first();

                            if ($member) {
                                $planPayment = new SportsClubMembershipPlanPayment();
                                $planPayment->member_id = $member->id;
                                $planPayment->membershipplan_id = $plan->id;
                                $planPayment->fee = $plan->price;
                                $planPayment->duration = $plan->duration;
                                $planPayment->date = now()->toDateString();
                                $planPayment->start_date = now()->toDateString();
                                $planPayment->end_date = $plan->calculateEndDate()->toDateString();
                                $planPayment->reference_number = $response['transaction_id'];
                                $planPayment->status = 'accepted';
                                $planPayment->creator_id = $user->id;
                                $planPayment->created_by = $user->id;
                                $planPayment->save();

                                $assignment = new SportsClubAssignedMembership();
                                $assignment->member_id = $member->id;
                                $assignment->membershipplan_id = $plan->id;
                                $assignment->start_date = now()->toDateString();
                                $assignment->end_date = $plan->calculateEndDate()->toDateString();
                                $assignment->status = 'accepted';
                                $assignment->duration = $plan->duration;
                                $assignment->fee = $plan->price;
                                $assignment->payment_type = 'Paypal';
                                $assignment->creator_id = $user->id;
                                $assignment->created_by = $user->id;
                                $assignment->save();

                                try {
                                    SportsClubPlanPayments::dispatch($request, $assignment);
                                } catch (\Exception $exception) {
                                }

                                return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])
                                    ->with('success', __('The sports club plan subscription has been created successfully.'));
                            }

                            return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Member not found.'));
                        }

                        return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Plan not found.'));
                    }

                    return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('sports-academy.plans', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function influencerMarketingPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->amount ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.influencer-marketing.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
                            'brand_id' => $request->brand_id,
                            'amount' => $request->amount,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->back()->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->back()->with('error', __('Invalid payment amount.'));
            }

            return redirect()->back()->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function influencerMarketingGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $depositData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($depositData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $deposit                    = new InfluencerMarketingDeposit();
                        $deposit->brand_id          = $depositData['brand_id'];
                        $deposit->amount            = $depositData['amount'];
                        $deposit->payment_type      = 'Paypal';
                        $deposit->payment_status    = 'paid';
                        $deposit->transaction_id    = $response['transaction_id'];
                        $deposit->created_by        = $user->id;
                        $deposit->save();

                        try {
                            InfluencerMarketingPayment::dispatch($deposit);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])
                            ->with('success', __('The deposit has been created successfully.'));
                    }

                    return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('influencer-marketing.frontend.dashboard', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function waterParkPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->total_amount ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.water-park.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
                            'full_name' => $request->full_name,
                            'email' => $request->email,
                            'phone' => $request->phone,
                            'booking_date' => $request->booking_date,
                            'event_id' => $request->event_id,
                            'adults' => $request->adults,
                            'children' => $request->children,
                            'total_amount' => $request->total_amount,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->back()->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->back()->with('error', __('Invalid payment amount.'));
            }

            return redirect()->back()->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function waterParkGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $booking = new WaterParkBooking();
                        $booking->full_name = $bookingData['full_name'];
                        $booking->email = $bookingData['email'];
                        $booking->phone = $bookingData['phone'];
                        $booking->booking_date = $bookingData['booking_date'];
                        $booking->event_id = $bookingData['event_id'];
                        $booking->adults = $bookingData['adults'];
                        $booking->children = $bookingData['children'];
                        $booking->total_amount = $bookingData['total_amount'];
                        $booking->payment_method = 'Paypal';
                        $booking->payment_status = 'paid';
                        $booking->booking_status = 'confirmed';
                        $booking->transaction_id = $response['transaction_id'];
                        $booking->creator_id = $user->id;
                        $booking->created_by = $user->id;
                        $booking->save();

                        try {
                            WaterParkBookingPaymentPaypal::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('water-park.frontend.booking', ['userSlug' => $userSlug])
                            ->with('success', __('The water park booking has been created successfully.'));
                    }

                    return redirect()->route('water-park.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }
            }

            return redirect()->route('water-park.frontend.booking', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('water-park.frontend.booking', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function tvStudioPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $customer = auth('tvstudio_customer')->user();

                if ($customer) {
                    $orderData = TVStudioCheckoutService::prepareOrderData($customer->id, $user->id);
                    $total = $orderData['total'];

                    if ($total > 0) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        $paypalService = new PaypalPaymentService($userSlug);

                        $response = $paypalService->createOrder([
                            'amount' => $total,
                            'callback_url' => route('paypal.tvstudio.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, array_merge($orderData, ['order_id' => $orderID]));

                            return redirect($response['approve_url']);
                        }
                        return redirect()->back()->with('error', $response['error'] ?? __('Payment initialization failed.'));
                    }

                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                return redirect()->route('tvstudio.frontend.login', ['userSlug' => $userSlug]);
            }

            return redirect()->back()->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function tvStudioGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $customer = auth('tvstudio_customer')->user();

                if ($customer) {
                    $orderData = Session::get($request->get('order_id', ''));
                    Session::forget($request->get('order_id', ''));

                    if ($orderData) {
                        $paypalService = new PaypalPaymentService($userSlug);
                        $response = $paypalService->captureOrder($request['token']);

                        if ($response['success']) {
                            $order = TVStudioCheckoutService::createOrder(
                                $orderData,
                                $customer->id,
                                $user->id,
                                'Paypal',
                                $response['transaction_id']
                            );

                            return redirect()->route('tvstudio.frontend.order-complete', [
                                'userSlug'   => $userSlug,
                                'booking_id' => encrypt($order->id)
                            ])->with('success', __('The order has been created successfully.'));
                        }

                        return redirect()->route('tvstudio.frontend.home', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                    }

                    return redirect()->route('tvstudio.frontend.home', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
                }

                return redirect()->route('tvstudio.frontend.login', ['userSlug' => $userSlug]);
            }

            return redirect()->route('tvstudio.frontend.home', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('tvstudio.frontend.home', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function artShowcasePayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $artwork = ArtShowcaseArtWork::where('id', $request->art_work_id)
                    ->where('created_by', $user->id)
                    ->first();

                if ($artwork && $artwork->status === 'available') {
                    $price = floatval($artwork->price ?? 0);

                    if ($price > 0) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        $paypalService = new PaypalPaymentService($userSlug);

                        $response = $paypalService->createOrder([
                            'amount' => $price,
                            'callback_url' => route('paypal.art-showcase.payment.status', [
                                'userSlug' => $userSlug,
                                'order_id' => $orderID,
                            ]),
                        ]);

                        if ($response['success']) {
                            Session::put($orderID, [
                                'art_work_id' => $request->art_work_id,
                                'full_name' => $request->full_name,
                                'email' => $request->email,
                                'phone' => $request->phone,
                                'address' => $request->address,
                                'order_id' => $orderID,
                            ]);

                            return redirect($response['approve_url']);
                        }
                        return redirect()->back()->with('error', $response['error'] ?? __('Payment initialization failed.'));
                    }

                    return redirect()->back()->with('error', __('Invalid artwork price.'));
                }

                return redirect()->back()->with('error', __('Artwork not found or not available.'));
            }

            return redirect()->back()->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function artShowcaseGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $purchaseData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($purchaseData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $artwork = ArtShowcaseArtWork::where('id', $purchaseData['art_work_id'])
                            ->where('created_by', $user->id)
                            ->first();

                        if ($artwork && $artwork->status === 'available') {
                            $order = new ArtShowcaseArtWorkOrder();
                            $order->art_work_id = $artwork->id;
                            $order->customer_full_name = $purchaseData['full_name'];
                            $order->customer_email = $purchaseData['email'];
                            $order->contact_number = $purchaseData['phone'];
                            $order->address = $purchaseData['address'];
                            $order->total_amount = $artwork->price;
                            $order->payment_type = 'Paypal';
                            $order->payment_status = 'paid';
                            $order->creator_id = $user->id;
                            $order->created_by = $user->id;
                            $order->save();

                            $artwork->status = 'sold';
                            $artwork->save();

                            try {
                                CreateArtWorkOrderPayment::dispatch($request, $order);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])
                                ->with('success', __('The artwork order has been created successfully.'));
                        }

                        return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])->with('error', __('Artwork not found or not available.'));
                    }

                    return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('art-gallery.frontend.artworks', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function tattooStudioPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->total_amount ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.tattoo-studio.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
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
                            'total_amount' => $request->total_amount,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->back()->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->back()->with('error', __('Invalid payment amount.'));
            }

            return redirect()->back()->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function tattooStudioGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $booking                     = new TattooAppointment();
                        $booking->name               = $bookingData['name'];
                        $booking->email              = $bookingData['email'];
                        $booking->phone              = $bookingData['phone'];
                        $booking->instagram          = $bookingData['instagram'];
                        $booking->date               = $bookingData['date'];
                        $booking->time               = $bookingData['time'];
                        $booking->duration           = $bookingData['duration'];
                        $booking->placement          = $bookingData['placement'];
                        $booking->inch               = $bookingData['inch'];
                        $booking->details            = $bookingData['details'];
                        $booking->tattoo_type        = $bookingData['tattoo_type'];
                        $booking->selected_tattoo_id = $bookingData['selected_tattoo_id'];
                        $booking->custom_price       = $bookingData['custom_price'];
                        $booking->total_amount       = $bookingData['total_amount'];
                        $booking->payment_method     = 'Paypal';
                        $booking->payment_status     = 'paid';
                        $booking->appointment_status = 'confirmed';
                        $booking->transaction_id     = $response['transaction_id'];
                        $booking->creator_id         = $user->id;
                        $booking->created_by         = $user->id;
                        $booking->save();

                        try {
                            TattooAppointmentPaymentPaypal::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])
                            ->with('success', __('The tattoo appointment has been created successfully.'));
                    }

                    return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('tattoo-studio.frontend.appointment', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function photoStudioPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->price ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.photo-studio.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
                            'name' => $request->name,
                            'email' => $request->email,
                            'mobile_no' => $request->mobile_no,
                            'service_id' => $request->service_id,
                            'price' => $request->price,
                            'booking_start_date' => $request->booking_start_date,
                            'booking_end_date' => $request->booking_end_date,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }
                    return redirect()->back()->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->back()->with('error', __('Invalid payment amount.'));
            }

            return redirect()->back()->with('error', __('User not found.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function photoStudioGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $service = PhotoStudioService::find($bookingData['service_id']);

                        $appointment                     = new PhotoStudioAppointment();
                        $appointment->name               = $bookingData['name'];
                        $appointment->email              = $bookingData['email'];
                        $appointment->mobile_no          = $bookingData['mobile_no'];
                        $appointment->service_id         = $bookingData['service_id'];
                        $appointment->price              = $bookingData['price'];
                        $appointment->booking_start_date = $bookingData['booking_start_date'];
                        $appointment->booking_end_date   = $bookingData['booking_end_date'];
                        $appointment->status             = 'pending';
                        $appointment->payment_status     = 'confirmed';
                        $appointment->creator_id         = $user->id;
                        $appointment->created_by         = $user->id;
                        $appointment->save();

                        $payment                     = new PhotoStudioAppointmentPayment();
                        $payment->appointment_id     = $appointment->id;
                        $payment->appointment_number = $appointment->appointment_number;
                        $payment->customer_name      = $bookingData['name'];
                        $payment->service_name       = $service->name ?? '';
                        $payment->payment_date       = now();
                        $payment->amount             = $bookingData['price'];
                        $payment->payment_status     = 'cleared';
                        $payment->payment_type       = 'PayPal';
                        $payment->description        = 'Payment via PayPal';
                        $payment->creator_id         = $user->id;
                        $payment->created_by         = $user->id;
                        $payment->save();

                        try {
                            PhotoStudioAppointmentPayments::dispatch($appointment, $payment);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])
                            ->with('success', __('The photo studio appointment has been created successfully.'));
                    }

                    return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->route('photo-studio-management.frontend.appointment', ['userSlug' => $userSlug])->with('error', $e->getMessage());
        }
    }

    public function ebookPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $customer = auth('ebook')->user();

                if ($customer) {
                    $check = EbookBookOrder::CheckPreOrder($user, $customer);

                    if ($check['success']) {
                        $price = floatval($request->total ?? 0);

                        if ($price > 0) {
                            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                            $paypalService = new PaypalPaymentService($userSlug);

                            $response = $paypalService->createOrder([
                                'amount' => $price,
                                'callback_url' => route('paypal.ebook.payment.status', [
                                    'userSlug' => $userSlug,
                                    'order_id' => $orderID,
                                ]),
                            ]);

                            if ($response['success']) {
                                Session::put($orderID, [
                                    'customerId' => $customer->id,
                                    'order_id' => $orderID,
                                ]);

                                return redirect($response['approve_url']);
                            }
                            return redirect()->route('ebook.frontend.checkout', ['userSlug' => $userSlug])->with('error', $response['error'] ?? __('Payment initialization failed.'));
                        }

                        return redirect()->route('ebook.frontend.checkout', ['userSlug' => $userSlug])->with('error', __('Invalid payment amount.'));
                    }

                    return redirect()->route('ebook.frontend.checkout', ['userSlug' => $userSlug])->with('error', $check['message'] ?? __('Something went wrong.'));
                }

                return redirect()->route('ebook.frontend.login', ['userSlug' => $userSlug]);
            }

            return redirect()->route('ebook.frontend.login', ['userSlug' => $userSlug]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ebookGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $orderData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($orderData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $order = EbookBookOrder::MakeOrder(
                            "Paypal",
                            $user,
                            $orderData['customerId'] ?? null,
                            true,
                            $response['transaction_id'] ?? null
                        );

                        try {
                            EbookPayment::dispatch($order);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('ebook.frontend.index', ['userSlug' => $userSlug])
                            ->with('success', __('Payment completed successfully'));
                    }

                    return redirect()->route('ebook.frontend.index', ['userSlug' => $userSlug])
                        ->with('error', __('Payment was cancelled or failed.'));
                }
            }

            return redirect()->route('ebook.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $exception) {
            return redirect()->route('ebook.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function yogaClassesPayWithPaypal(Request $request, $userSlug = null)
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

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                $paypalService = new PaypalPaymentService($userSlug);

                $response = $paypalService->createOrder([
                    'amount' => $total,
                    'callback_url' => route('paypal.yoga-classes.payment.status', [
                        'userSlug' => $userSlug,
                        'order_id' => $orderID,
                    ]),
                ]);

                if ($response['success']) {
                    Session::put($orderID, [
                        'payment_method' => 'Paypal',
                        'payment_note' => $request->payment_note,
                        'total' => $total,
                        'order_id' => $orderID,
                    ]);

                    return redirect($response['approve_url']);
                }

                return redirect()->route('yoga-classes.frontend.checkout', ['userSlug' => $userSlug])
                    ->with('error', $response['error'] ?? __('Payment initialization failed.'));
            }

            return redirect()->route('yoga-classes.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function yogaClassesGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $member = auth('yoga_member')->user();
                $instructor = auth('yoga_instructor')->user();

                if (!$member && !$instructor) {
                    return redirect()->route('yoga-classes.frontend.login', ['userSlug' => $userSlug]);
                }

                $orderData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($orderData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
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
                        $course_order->currency = $cartItems->first()?->currency ?: 'USD';
                        $course_order->payment_method = 'Paypal';
                        $course_order->payment_status = 'paid';
                        $course_order->transaction_id = $response['transaction_id'];
                        $course_order->receipt = null;
                        $course_order->order_date = now();
                        $course_order->notes = $orderData['payment_note'] ?? null;
                        $course_order->discount_amount = $discountAmount;
                        $course_order->tax_amount = 0;
                        $course_order->total_amount = $subtotal - $discountAmount;
                        $course_order->created_by = $user->id;
                        $course_order->save();

                        foreach ($courseSnapshots as $courseSnapshot) {
                            $purchased_courses = new YogaClassesPurchasedCourse();
                            $purchased_courses->member_id = $member?->id;
                            $purchased_courses->instructor_id = $instructor?->id;
                            $purchased_courses->course_id = $courseSnapshot['id'];
                            $purchased_courses->order_id = $course_order->id;
                            $purchased_courses->purchase_price = $courseSnapshot['total_amount'];
                            $purchased_courses->currency = $cartItems->first()?->currency ?: 'USD';
                            $purchased_courses->purchased_at = now();
                            $purchased_courses->created_by = $user->id;
                            $purchased_courses->save();
                        }

                        $cartItems->each->delete();

                        try {
                            YogaClassesOrderPayments::dispatch($course_order);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('yoga-classes.frontend.order-success', ['userSlug' => $userSlug, 'reference' => $response['transaction_id']])
                            ->with('success', __('Payment completed successfully! Order #:number', ['number' => $course_order->order_number]));
                    }

                    return redirect()->route('yoga-classes.frontend.checkout', ['userSlug' => $userSlug])
                        ->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('yoga-classes.frontend.checkout', ['userSlug' => $userSlug])
                    ->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('yoga-classes.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $exception) {
            return redirect()->route('yoga-classes.frontend.checkout', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function hairCareStudioPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->total_amount ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $paypalService = new PaypalPaymentService($userSlug);

                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.hair-care-studio.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
                            'full_name' => $request->full_name,
                            'email' => $request->email,
                            'mobile_no' => $request->mobile_no,
                            'service_id' => $request->service_id,
                            'preferred_date' => $request->preferred_date,
                            'preferred_time' => $request->preferred_time,
                            'stylist_type' => $request->stylist_type,
                            'charges' => $request->charges,
                            'special_request' => $request->special_request,
                            'total_amount' => $request->total_amount,
                            'order_id' => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }

                    return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                        ->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', __('Invalid payment amount.'));
            }

            return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function hairCareStudioGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $appointment = new HairCareAppointment();
                        $appointment->full_name = $bookingData['full_name'];
                        $appointment->email = $bookingData['email'];
                        $appointment->mobile_no = $bookingData['mobile_no'];
                        $appointment->service_id = $bookingData['service_id'];
                        $appointment->preferred_date = $bookingData['preferred_date'];
                        $appointment->preferred_time = $bookingData['preferred_time'];
                        $appointment->stylist_type = $bookingData['stylist_type'];
                        $appointment->charges = $bookingData['charges'];
                        $appointment->special_request = $bookingData['special_request'] ?? null;
                        $appointment->payment_status = 'paid';
                        $appointment->creator_id = $user->id;
                        $appointment->created_by = $user->id;
                        $appointment->save();

                        $haircarepayment = new HairCarePayment();
                        $haircarepayment->appointment_id = $appointment->id;
                        $haircarepayment->payment_date = now();
                        $haircarepayment->amount = $bookingData['charges'];
                        $haircarepayment->transaction_id = $response['transaction_id'];
                        $haircarepayment->payment_method = 'Paypal';
                        $haircarepayment->payment_status = 'cleared';
                        $haircarepayment->notes = 'Payment via PayPal';
                        $haircarepayment->creator_id = $user->id;
                        $haircarepayment->created_by = $user->id;
                        $haircarepayment->save();

                        try {
                            HairCareStudioOrderPayments::dispatch($haircarepayment);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                            ->with('success', __('Payment completed and appointment booked successfully!'));
                    }

                    return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                        ->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $exception) {
            return redirect()->route('hair-care-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function petCarePayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $paymentType = $request->input('type', 'membership');

                if ($paymentType === 'service') {
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
                        'payment_method' => 'Paypal',
                        'type' => $paymentType,
                        'amount' => $total,
                    ];
                } else {
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
                        'payment_method' => 'Paypal',
                        'type' => $paymentType,
                        'amount' => $total,
                    ];
                }

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $paypalService = new PaypalPaymentService($userSlug);
                $response = $paypalService->createOrder([
                    'amount' => $total,
                    'callback_url' => route('paypal.pet-care.payment.status', [
                        'userSlug' => $userSlug,
                        'order_id' => $orderID,
                    ]),
                ]);

                if ($response['success']) {
                    Session::put($orderID, array_merge($bookingData, ['order_id' => $orderID]));
                    return redirect($response['approve_url']);
                }

                return redirect()->route('pet-care.frontend.index', ['userSlug' => $userSlug])
                    ->with('error', $response['error'] ?? __('Payment initialization failed.'));
            }

            return redirect()->route('pet-care.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function petCareGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $paymentType = $bookingData['type'] ?? 'membership';

                        if ($paymentType === 'service') {
                            $service = PetCareService::find($bookingData['service']);
                            if (!$service) {
                                return redirect()->route('pet-care.frontend.services', ['userSlug' => $userSlug])
                                    ->with('error', __('Service not found.'));
                            }
                            $times = explode('-', $bookingData['time_slot']);
                            $booking = new PetCareBooking();
                            $booking->name = $bookingData['name'];
                            $booking->email = $bookingData['email'];
                            $booking->phone_number = $bookingData['phone_number'];
                            $booking->address = $bookingData['address'];
                            $booking->service = $bookingData['service'];
                            $booking->date = $bookingData['date'];
                            $booking->start_time = $times[0];
                            $booking->end_time = $times[1];
                            $booking->price = $bookingData['price'];
                            $booking->note = $bookingData['note'];
                            $booking->pet_name = $bookingData['pet_name'];
                            $booking->species_breed = $bookingData['species_breed'];
                            $booking->date_of_birth = $bookingData['date_of_birth'];
                            $booking->gender = $bookingData['gender'];
                            $booking->payment_method = 'Paypal';
                            $booking->payment_status = 'paid';
                            $booking->booking_status = 'completed';
                            $booking->created_by = $user->id;
                            $booking->creator_id = $user->id;
                            $booking->save();

                            try {
                                PetCareBookingPayment::dispatch($booking);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('pet-care.frontend.services', ['userSlug' => $userSlug])
                                ->with('success', __('Payment completed successfully! Your service booking has been confirmed.'));
                        } else {
                            $membership = new PetCareMembership();
                            $membership->name = $bookingData['name'];
                            $membership->phone_no = $bookingData['phone_no'];
                            $membership->email = $bookingData['email'];
                            $membership->grooming_package_id = $bookingData['package_id'];
                            $membership->amount = $bookingData['amount'];
                            $membership->pet_name = $bookingData['pet_name'];
                            $membership->breed_species = $bookingData['breed'];
                            $membership->date_of_birth = $bookingData['date_of_birth'];
                            $membership->gender = $bookingData['gender'];
                            $membership->address = $bookingData['address'];
                            $membership->special_request = $bookingData['notes'];
                            $membership->payment_method = 'Paypal';
                            $membership->payment_status = 'paid';
                            $membership->membership_status = 'approved';
                            $membership->created_by = $user->id;
                            $membership->creator_id = $user->id;
                            $membership->save();

                            try {
                                PetCareMembershipPayments::dispatch($membership);
                            } catch (\Exception $exception) {
                            }

                            return redirect()->route('pet-care.frontend.pricing', ['userSlug' => $userSlug])
                                ->with('success', __('Payment completed successfully! Order #:number', ['number' => $membership->membership_id]));
                        }
                    }

                    $paymentType = $bookingData['type'] ?? 'membership';
                    $redirectRoute = $paymentType === 'service' ? 'pet-care.frontend.services' : 'pet-care.frontend.pricing';
                    return redirect()->route($redirectRoute, ['userSlug' => $userSlug])
                        ->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('pet-care.frontend.index', ['userSlug' => $userSlug])
                    ->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('pet-care.frontend.index', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $exception) {
            return redirect()->route('pet-care.frontend.index', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function boutiqueStudioPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->total_amount ?? 0);

                if ($price > 0) {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->createOrder([
                        'amount' => $price,
                        'callback_url' => route('paypal.boutique-studio.payment.status', [
                            'userSlug' => $userSlug,
                            'order_id' => $orderID,
                        ]),
                    ]);

                    if ($response['success']) {
                        Session::put($orderID, [
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
                            'order_id'     => $orderID,
                        ]);

                        return redirect($response['approve_url']);
                    }

                    return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                        ->with('error', $response['error'] ?? __('Payment initialization failed.'));
                }

                return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', __('Invalid payment amount.'));
            }

            return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function boutiqueStudioGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $booking                 = new BoutiqueBooking();
                        $booking->outfit_id      = $bookingData['outfit_id'];
                        $booking->pricing_type   = $bookingData['pricing_type'];
                        $booking->size           = $bookingData['size'];
                        $booking->outfit_price   = $bookingData['outfit_price'];
                        $booking->first_name     = $bookingData['first_name'];
                        $booking->last_name      = $bookingData['last_name'];
                        $booking->email          = $bookingData['email'];
                        $booking->phone          = $bookingData['phone'];
                        $booking->booking_date   = $bookingData['booking_date'];
                        $booking->pickup_date    = $bookingData['pickup_date'];
                        $booking->return_date    = $bookingData['return_date'];
                        $booking->rental_days    = $bookingData['rental_days'];
                        $booking->address        = $bookingData['address'];
                        $booking->notes          = $bookingData['notes'];
                        $booking->services       = $bookingData['services'];
                        $booking->service_total  = $bookingData['service_total'];
                        $booking->total_amount   = $bookingData['total_amount'];
                        $booking->payment_method = 'Paypal';
                        $booking->payment_status = 'paid';
                        $booking->booking_status = 'confirmed';
                        $booking->transaction_id = $response['transaction_id'];
                        $booking->creator_id     = $user->id;
                        $booking->created_by     = $user->id;
                        $booking->save();

                        try {
                            BoutiqueBookingPaymentPaypal::dispatch($booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('boutique-and-designer-studio.frontend.booking-success', ['userSlug' => $userSlug, 'id' => encrypt($booking->id)])
                            ->with('success', __('Payment completed and booking confirmed successfully!'));
                    }

                    return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                        ->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $exception) {
            return redirect()->route('boutique-and-designer-studio.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function investmentSystemPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $plan = InvestmentPlan::where('created_by', $user->id)
                    ->where('plan_status', '0')
                    ->find($request->plan_id);

                if (!$plan) {
                    return redirect()->route('investor.plans', ['userSlug' => $userSlug])
                        ->with('error', __('Investment plan not found.'));
                }

                $amount = (float) ($request->amount ?? 0);
                if ($amount <= 0) {
                    return redirect()->route('investor.deposit', [
                        'userSlug' => $userSlug,
                        'id'       => $request->investor_id,
                        'PlanId'   => $request->plan_id,
                    ])->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $paypalService = new PaypalPaymentService($userSlug);
                $response = $paypalService->createOrder([
                    'amount' => $amount,
                    'callback_url' => route('paypal.investment-system.payment.status', [
                        'userSlug'    => $userSlug,
                        'order_id'    => $orderID,
                        'investor_id' => $request->investor_id,
                    ]),
                ]);

                if ($response['success']) {
                    Session::put($orderID, [
                        'investor_id'     => $request->investor_id,
                        'plan_id'         => $request->plan_id,
                        'amount'          => $amount,
                        'plan_duration'   => $request->plan_duration,
                        'annual_return'   => $request->annual_return,
                        'expected_return' => $request->expected_return,
                        'purchase_date'   => $request->purchase_date,
                        'expiry_date'     => $request->expiry_date,
                        'order_id'        => $orderID,
                    ]);

                    return redirect($response['approve_url']);
                }

                return redirect()->route('investor.deposit', [
                    'userSlug' => $userSlug,
                    'id'       => $request->investor_id,
                    'PlanId'   => $request->plan_id,
                ])->with('error', $response['error'] ?? __('Payment initialization failed.'));
            }

            return redirect()->route('investor.loginform', ['userSlug' => $userSlug]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function investmentSystemGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $depositData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($depositData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $plan = InvestmentPlan::where('created_by', $user->id)
                            ->where('plan_status', '0')
                            ->find($depositData['plan_id']);

                        if (!$plan) {
                            return redirect()->route('investor.plans', ['userSlug' => $userSlug])
                                ->with('error', __('Investment plan not found.'));
                        }

                        $existingInvestment = InvestorDeposit::where('investor_id', $depositData['investor_id'])
                            ->where('plan_id', $depositData['plan_id'])
                            ->first();

                        if ($existingInvestment) {
                            $existingInvestment->plan_duration   = $depositData['plan_duration'];
                            $existingInvestment->invested_amount = $depositData['amount'];
                            $existingInvestment->amount          = $depositData['amount'];
                            $existingInvestment->annual_return   = $depositData['annual_return'];
                            $existingInvestment->expected_return = $depositData['expected_return'];
                            $existingInvestment->payment_type    = 'Paypal';
                            $existingInvestment->status          = '1';
                            $existingInvestment->receipt         = '';
                            $existingInvestment->purchase_date   = $depositData['purchase_date'];
                            $existingInvestment->expiry_date     = $depositData['expiry_date'];
                            $existingInvestment->save();
                            $investment = $existingInvestment;
                        } else {
                            $investment                  = new InvestorDeposit();
                            $investment->investor_id     = $depositData['investor_id'];
                            $investment->plan_id         = $depositData['plan_id'];
                            $investment->created_by      = $user->id;
                            $investment->plan_duration   = $depositData['plan_duration'];
                            $investment->invested_amount = $depositData['amount'];
                            $investment->amount          = $depositData['amount'];
                            $investment->annual_return   = $depositData['annual_return'];
                            $investment->expected_return = $depositData['expected_return'];
                            $investment->payment_type    = 'Paypal';
                            $investment->status          = '1';
                            $investment->receipt         = '';
                            $investment->purchase_date   = $depositData['purchase_date'];
                            $investment->expiry_date     = $depositData['expiry_date'];
                            $investment->save();
                        }

                        $transaction              = new InvestorTransaction();
                        $transaction->plan_id     = $investment->plan_id;
                        $transaction->investor_id = $investment->investor_id;
                        $transaction->trx_id      = 'TXN-' . strtoupper(uniqid());
                        $transaction->amount      = $investment->amount;
                        $transaction->type        = 'credit';
                        $transaction->detail      = 'Deposit Via - Paypal';
                        $transaction->created_by  = $user->id;
                        $transaction->save();

                        try {
                            InvestorDepositPayment::dispatch($investment, $transaction);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('investor.transaction', ['userSlug' => $userSlug])
                            ->with('success', __('Payment completed and deposit created successfully!'));
                    }

                    return redirect()->route('investor.deposit', [
                        'userSlug' => $userSlug,
                        'id'       => $depositData['investor_id'],
                        'PlanId'   => $depositData['plan_id'],
                    ])->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('investor.plans', ['userSlug' => $userSlug])
                    ->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('investor.loginform', ['userSlug' => $userSlug]);
        } catch (\Exception $exception) {
            return redirect()->route('investor.plans', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function jewelleryPayWithPaypal(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $price = floatval($request->input('totalAmount') ?? 0);
                if ($price <= 0) {
                    return redirect()->back()->with('error', __('Invalid payment amount.'));
                }

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $paypalService = new PaypalPaymentService($userSlug);
                $response = $paypalService->createOrder([
                    'amount' => $price,
                    'callback_url' => route('paypal.jewellery-store.payment.status', [
                        'userSlug' => $userSlug,
                        'order_id' => $orderID,
                    ]),
                ]);

                if ($response['success']) {
                    Session::put($orderID, [
                        'firstName'            => $request->input('firstName'),
                        'lastName'             => $request->input('lastName'),
                        'email'                => $request->input('email'),
                        'phone'                => $request->input('phone'),
                        'reservationDate'      => $request->input('reservationDate'),
                        'pickupDate'           => $request->input('pickupDate'),
                        'returnDate'           => $request->input('returnDate'),
                        'address'              => $request->input('address'),
                        'specialRequirements'  => $request->input('specialRequirements'),
                        'paymentMethod'        => $request->input('paymentMethod'),
                        'jewelleryItemId'      => $request->input('jewelleryItemId'),
                        'quantity'             => $request->input('quantity'),
                        'subtotal'             => $request->input('subtotal'),
                        'discount'             => $request->input('discount'),
                        'tax'                  => $request->input('tax'),
                        'makingCharges'        => $request->input('makingCharges'),
                        'totalAmount'          => $request->input('totalAmount'),
                        'order_id'             => $orderID,
                    ]);

                    return redirect($response['approve_url']);
                }

                return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', $response['error'] ?? __('Payment initialization failed.'));
            }

            return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $exception) {
            return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function jewelleryGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();

            if ($user) {
                $bookingData = Session::get($request->get('order_id', ''));
                Session::forget($request->get('order_id', ''));

                if ($bookingData) {
                    $paypalService = new PaypalPaymentService($userSlug);
                    $response = $paypalService->captureOrder($request['token']);

                    if ($response['success']) {
                        $item = JewelleryStoreItem::where('id', $bookingData['jewelleryItemId'])->first();

                        $booking = new JewelleryStoreJewelleryBooking();
                        $booking->customer_name        = ($bookingData['firstName'] ?? '') . ' ' . ($bookingData['lastName'] ?? '');
                        $booking->email                = $bookingData['email'] ?? '';
                        $booking->contact              = $bookingData['phone'] ?? '';
                        $booking->reservation_date     = $bookingData['reservationDate'] ?? null;
                        $booking->date_of_sale         = now();
                        $booking->quantity             = $bookingData['quantity'] ?? '';
                        $booking->gross_weight         = $item['gross_weight'] ?? '';
                        $booking->net_weight           = $item['net_weight'] ?? '';
                        $booking->stone_weight         = $item['stone_weight'] ?? '';
                        $booking->making_charges       = $bookingData['makingCharges'] ?? 0;
                        $booking->metal                = $item['metal'] ?? '';
                        $booking->sub_total            = $bookingData['subtotal'] ?? 0;
                        $booking->discount             = $bookingData['discount'] ?? 0;
                        $booking->taxes                = $bookingData['tax'] ?? 0;
                        $booking->grand_amount         = $bookingData['totalAmount'] ?? 0;
                        $booking->shipping_address     = $bookingData['address'] ?? '';
                        $booking->special_requirements = $bookingData['specialRequirements'] ?? null;
                        $booking->payment_method       = 'Paypal';
                        $booking->payment_status       = 'paid';
                        $booking->item_id              = $item['id'] ?? '';
                        $booking->transaction_id       = $response['transaction_id'];
                        $booking->creator_id           = $user->id;
                        $booking->created_by           = $user->id;
                        $booking->save();

                        if ($item && isset($bookingData['quantity'])) {
                            $item->decrement('quantity', (int) $bookingData['quantity']);
                        }

                        try {
                            JewelleryStoreJewelleryBookingPayments::dispatch(new Request($bookingData), $booking);
                        } catch (\Exception $exception) {
                        }

                        return redirect()->route('jewellery-store.frontend.order-status', ['userSlug' => $userSlug, 'bookingId' => $booking->id])
                            ->with('success', __('Payment completed and booking confirmed successfully!'));
                    }

                    return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                        ->with('error', __('Payment was cancelled or failed.'));
                }

                return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                    ->with('error', __('Something went wrong. Please try again.'));
            }

            return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', __('Something went wrong. Please try again.'));
        } catch (\Exception $exception) {
            return redirect()->route('jewellery-store.frontend.booking', ['userSlug' => $userSlug])
                ->with('error', $exception->getMessage());
        }
    }

    public function freelancingWalletPayWithPaypal(Request $request, $userSlug = null)
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

            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            $paypalService = new PaypalPaymentService($userSlug);

            $response = $paypalService->createOrder([
                'amount' => $price,
                'callback_url' => route('paypal.freelancing.wallet.payment.paypal.status', [
                    'userSlug' => $userSlug,
                    'order_id' => $orderID,
                ]),
            ]);

            if ($response['success']) {
                Session::put($orderID, [
                    'amount' => $price,
                    'client_id' => $client->id,
                    'order_id' => $orderID,
                ]);
                return redirect($response['approve_url']);
            }

            return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                ->with('error', $response['error'] ?? __('Payment initialization failed.'));
        } catch (\Exception $e) {
            return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                ->with('error', $e->getMessage());
        }
    }

    public function freelancingWalletGetPaypalStatus(Request $request, $userSlug = null)
    {
        try {
            $user = User::where('slug', $userSlug)->first();
            if (!$user) {
                return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                    ->with('error', __('User not found.'));
            }

            $walletData = Session::get($request->get('order_id', ''));
            Session::forget($request->get('order_id', ''));

            if (!$walletData) {
                return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                    ->with('error', __('Wallet data not found.'));
            }

            $paypalService = new PaypalPaymentService($userSlug);
            $response = $paypalService->captureOrder($request['token']);

            if ($response['success']) {
                $client = auth('freelancer_client')->user();
                if (!$client) {
                    return redirect()->route('freelancing.login', ['userSlug' => $userSlug])
                        ->with('error', __('Please login to continue.'));
                }

                $amount = floatval($walletData['amount']);

                // Get or create wallet
                $wallet = $client->wallet;
                if (!$wallet) {
                    $wallet = new FreelancingClientWallet();
                    $wallet->client_id = $client->id;
                    $wallet->balance = 0.00;
                    $wallet->spent_balance = 0.00;
                    $wallet->frozen_balance = 0.00;
                    $wallet->total_withdrawn = 0.00;
                    $wallet->created_by = $user->id;
                    $wallet->save();
                }

                $balanceBefore = (float) $wallet->balance;
                $balanceAfter = $balanceBefore + $amount;

                // Create wallet transaction
                $transaction = new FreelancingClientWalletTransaction();
                $transaction->wallet_id = $wallet->id;
                $transaction->client_id = $client->id;
                $transaction->transaction_id = 'TXN_' . strtoupper(bin2hex(random_bytes(8)));
                $transaction->type = 'credit';
                $transaction->amount = $amount;
                $transaction->balance_before = $balanceBefore;
                $transaction->balance_after = $balanceAfter;
                $transaction->status = 'completed';
                $transaction->category = 'deposit';
                $transaction->description = 'Wallet deposit via PayPal';
                $transaction->payment_method = 'PayPal';
                $transaction->created_by = $user->id;
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
                ->with('error', $response['error'] ?? __('Payment was cancelled or failed.'));
        } catch (\Exception $e) {
            return redirect()->route('freelancing.wallet.index', ['userSlug' => $userSlug])
                ->with('error', $e->getMessage());
        }
    }
}
