<?php

namespace Automas\Paypal\Services;

use App\Models\User;
use InvalidArgumentException;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalPaymentService
{
    protected PayPalClient $provider;

    protected string $clientId;

    protected string $clientSecret;

    protected string $mode;

    public string $currency;


    public function __construct($userSlug = null)
    {
        $user = User::where('slug', $userSlug)->first();
        $settings = $user ? getCompanyAllSetting($user->id) : getAdminAllSetting();

        $this->currency = strtoupper($settings['defaultCurrency'] ?? '');
        $this->clientId = $settings['paypal_client_id'] ?? null;
        $this->clientSecret = $settings['paypal_secret_key'] ?? null;
        $this->mode = $settings['paypal_mode'] ?? 'sandbox';

        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new InvalidArgumentException(__('PayPal credentials are not configured.'));
        }

        config(['paypal' => [
            'mode' => $this->mode,
            'sandbox' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
            'live' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
            'payment_action' => 'Sale',
            'currency' => $this->currency,
            'locale' => app()->getLocale() ?: 'en_US',
            'validate_ssl' => false,
            'notify_url' => '',
        ]]);

        $this->provider = new PayPalClient();
        $this->provider->setApiCredentials(config('paypal'));
    }

    /**
     * Authenticate PayPal client.
     */
    private function authenticate(): void
    {
        $this->provider->getAccessToken();
    }

    /**
     * Create PayPal order.
     */
    public function createOrder(array $data): array
    {
        try {

            $requiredFields = ['amount', 'callback_url'];

            $missingFields = array_filter($requiredFields, fn($field) => empty($data[$field]));

            if (! empty($missingFields)) {
                throw new InvalidArgumentException(__('Missing required fields: :fields', ['fields' => implode(', ', $missingFields),]));
            }

            if (! is_numeric($data['amount']) || (float) $data['amount'] <= 0) {
                throw new InvalidArgumentException(__('Invalid payment amount.'));
            }

            $this->authenticate();

            $response = $this->provider->createOrder([
                'intent' => 'CAPTURE',

                'application_context' => [
                    'return_url' => $data['callback_url'],
                    'cancel_url' => $data['callback_url'],
                ],

                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $this->currency,
                            'value' => number_format(
                                (float) $data['amount'],
                                2,
                                '.',
                                ''
                            ),
                        ],
                    ],
                ],
            ]);

            if (! empty($response['id'])) {
                return [
                    'success' => true,
                    'order_id' => $response['id'],
                    'approve_url' => $this->getApproveUrl($response),
                    'data' => $response,
                ];
            }

            return $this->handleError($response);
        } catch (\Throwable $e) {
            return $this->exceptionError($e);
        }
    }

    /**
     * Capture PayPal order.
     */
    public function captureOrder(string $token): array
    {
        if ($token === '') {
            return [
                'success' => false,
                'error' => __('Payment token is required.'),
            ];
        }

        try {
            $this->authenticate();

            $response = $this->provider->capturePaymentOrder($token);

            if (
                in_array(
                    $response['status'] ?? '',
                    ['COMPLETED', 'APPROVED']
                )
            ) {
                return [
                    'success' => true,
                    'status' => $response['status'],
                    'transaction_id' => $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                    'data' => $response,
                ];
            }

            return $this->handleError($response);
        } catch (\Throwable $e) {
            return $this->exceptionError($e);
        }
    }

    /**
     * Get approval URL from PayPal response.
     */
    private function getApproveUrl(array $response): ?string
    {
        if (empty($response['links'])) {
            return null;
        }

        foreach ($response['links'] as $link) {
            if (($link['rel'] ?? '') === 'approve') {
                return $link['href'] ?? null;
            }
        }

        return null;
    }

    /**
     * Handle PayPal API errors.
     */
    private function handleError(array $response): array
    {
        $message = $response['error']['message'] ?? $response['error']['details'][0]['description'] ?? $response['message'] ?? __('Something went wrong. Please try again.');

        return [
            'success' => false,
            'error' => $message,
            'response' => $response,
        ];
    }

    /**
     * Handle exceptions.
     */
    private function exceptionError(\Throwable $e): array
    {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}
