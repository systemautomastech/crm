<?php

namespace Automas\Stripe\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Session as FacadesSession;
use InvalidArgumentException;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeService
{
    private ?string $stripeKey;

    private ?string $stripeSecret;

    public string $currency;

    public function __construct($userSlug = null)
    {
        $user = User::where('slug', $userSlug)->first() ?? null;
        $setting = $user ? getCompanyAllSetting($user->id) : getAdminAllSetting();

        $this->currency = $setting['defaultCurrency'] ?? '';
        $this->stripeKey = $setting['stripe_key'] ?? null;
        $this->stripeSecret = $setting['stripe_secret'] ?? null;

        if (empty($this->stripeSecret)) {
            throw new InvalidArgumentException(__('The Stripe Secret Key is required.'));
        }
    }

    /**
     * Initiate Stripe Payment Session
     */
    public function initiatePayment(array $paymentData)
    {
        try {
            Stripe::setApiKey($this->stripeSecret);

            $price = $paymentData['amount'] ?? 0;
            $amount = $this->stripeCheckoutUnitAmount((float) $price, $this->currency);

            $session_data = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $this->currency,
                        'unit_amount' => (int) $amount,
                        'product_data' => [
                            'name' => $paymentData['product_name'] ?? 'Payment',
                            'description' => $paymentData['description'] ?? '',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $paymentData['callback_url'] . (parse_url($paymentData['callback_url'], PHP_URL_QUERY) ? '&' : '?') . 'return_type=success',
                'cancel_url' => $paymentData['callback_url'] . (parse_url($paymentData['callback_url'], PHP_URL_QUERY) ? '&' : '?') . 'return_type=cancel',
            ];

            $stripe_session = Session::create($session_data);

            FacadesSession::put($paymentData['order_id'], array_merge($paymentData['metadata'] ?? [], ['session_id' => $stripe_session->id, 'session_data' => $paymentData['session_data'] ?? null]));

            return (object) [
                'id' => $stripe_session->id,
                'url' => $stripe_session->url,
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Verify Stripe Payment
     */
    public function verifyPayment($request)
    {
        try {
            if (isset($request->order_id)) {
                $stripe = new StripeClient($this->stripeSecret);
                $session = FacadesSession::get($request->order_id);
                if (isset($session['session_id'])) {
                    $checkoutSession = $stripe->checkout->sessions->retrieve($session['session_id'], []);
                    $request->merge($session);

                    if (isset($checkoutSession->payment_intent)) {
                        $paymentIntents = $stripe->paymentIntents->retrieve($checkoutSession->payment_intent, []);

                        if (! empty($paymentIntents->latest_charge)) {
                            $charge = $stripe->charges->retrieve($paymentIntents->latest_charge, []);
                            $request->merge(['receipt_url' => $charge->receipt_url ?? '', 'session_id' => $checkoutSession->id]);

                            return true;
                        }
                    }
                }
            }

            return false;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage() ?? __('Payment verification failed.'));
        }
    }

    /**
     * Helper to get correct unit amount for Stripe
     */
    private function stripeCheckoutUnitAmount(float $amount, string $currencyCode)
    {
        $code = strtoupper(trim($currencyCode));
        $zeroDecimal = [
            'MGA',
            'BIF',
            'CLP',
            'PYG',
            'DJF',
            'RWF',
            'GNF',
            'UGX',
            'JPY',
            'VND',
            'VUV',
            'XAF',
            'KMF',
            'KRW',
            'XOF',
            'XPF',
            'BRL',
        ];

        return in_array($code, $zeroDecimal, true)
            ? number_format($amount, 2, '.', '')
            : number_format($amount, 2, '.', '') * 100;
    }
}
