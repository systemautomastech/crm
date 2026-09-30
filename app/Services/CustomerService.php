<?php

namespace App\Services;

use App\Events\CreateUser;
use App\Models\User;
use Automas\Account\Events\CreateCustomer;
use Automas\Account\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CustomerService
{
    public function getCustomers()
    {
        if (module_is_active('Account')) {
            $customers = Customer::where('created_by', creatorId())->get()->map(function ($customer) {
                return [
                    'id' => $customer->user_id,
                    'name' => $customer->company_name ?? '',
                    'email' => $customer->contact_person_email ?? '',
                    'mobile_no' => $customer->contact_person_mobile ?? '',
                    'billing_address' => $customer->billing_address ?? '',
                    'shipping_address' => $customer->shipping_address ?? '',
                ];
            });
            return $customers;
        }

        return User::where('type', 'client')
            ->where('created_by', creatorId())
            ->get();
    }

    public function createCustomer(array $data): Customer
    {
        $companyName = $data['company_name'] ?? $data['contact_person_name'] ?? $data['customer_name'] ?? 'Client Customer';
        $email = trim($data['contact_person_email'] ?? $data['customer_email'] ?? $data['email'] ?? '');
        $phone = $data['contact_person_mobile'] ?? $data['mobile_no'] ?? $data['customer_phone'] ?? null;

        // 1. Resolve Billing & Shipping address arrays for JSON casting
        if (isset($data['billing_address']) && is_array($data['billing_address'])) {
            $billingAddress = $data['billing_address'];
        } else {
            $billingAddress = [
                'name'           => $data['billing_name'] ?? $companyName,
                'address_line_1' => is_string($data['billing_address'] ?? null) ? $data['billing_address'] : ($data['address'] ?? $data['customer_address'] ?? null),
                'address_line_2' => $data['billing_address_line_2'] ?? null,
                'city'           => $data['billing_city'] ?? $data['city'] ?? $data['customer_city'] ?? null,
                'state'          => $data['billing_state'] ?? $data['state'] ?? $data['customer_state'] ?? null,
                'zip_code'       => $data['billing_zip_code'] ?? $data['billing_postal_code'] ?? $data['zip_code'] ?? $data['customer_zip_code'] ?? null,
                'country'        => $data['billing_country'] ?? $data['country'] ?? $data['customer_country'] ?? null,
            ];
        }

        $sameAsBilling = $data['same_as_billing'] ?? true;

        if (isset($data['shipping_address']) && is_array($data['shipping_address'])) {
            $shippingAddress = $data['shipping_address'];
        } elseif ($sameAsBilling && empty($data['shipping_city']) && !isset($data['shipping_address_line_1'])) {
            $shippingAddress = $billingAddress;
        } else {
            $shippingAddress = [
                'name'           => $data['shipping_name'] ?? $companyName,
                'address_line_1' => is_string($data['shipping_address'] ?? null) ? $data['shipping_address'] : ($data['shipping_address_line_1'] ?? $data['address'] ?? $data['customer_address'] ?? null),
                'address_line_2' => $data['shipping_address_line_2'] ?? null,
                'city'           => $data['shipping_city'] ?? $data['city'] ?? $data['customer_city'] ?? null,
                'state'          => $data['shipping_state'] ?? $data['state'] ?? $data['customer_state'] ?? null,
                'zip_code'       => $data['shipping_zip_code'] ?? $data['shipping_postal_code'] ?? $data['zip_code'] ?? $data['customer_zip_code'] ?? null,
                'country'        => $data['shipping_country'] ?? $data['country'] ?? $data['customer_country'] ?? null,
            ];
        }

        // 2. Email Duplicity Check: Cannot assign another user matching email if email already exists in system
        if (!empty($email)) {
            $userExists = User::where('email', $email)->exists();
            $customerExists = Customer::where('contact_person_email', $email)
                ->where('created_by', creatorId())
                ->exists();

            if ($userExists || $customerExists) {
                throw ValidationException::withMessages([
                    'contact_person_email' => [__('The email already exists in the system.')],
                    'customer_email'       => [__('The email already exists in the system.')],
                    'email'                => [__('The email already exists in the system.')],
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'email' => [__('Email is mandatory.')],
            ]);
        }

        return DB::transaction(function () use ($data, $companyName, $email, $phone, $billingAddress, $shippingAddress, $sameAsBilling) {
            $role = Role::where('name', 'client')->first();

            $user = new User();
            $user->name = $companyName;
            $user->email = $email;
            $user->mobile_no = $phone;
            $user->password = Hash::make('12345678');
            $user->type = 'client';
            $user->is_enable_login = true;
            $user->lang = function_exists('company_setting') ? (company_setting('defaultLanguage') ?? 'en') : 'en';
            $user->email_verified_at = now();
            $user->creator_id = Auth::id() ?? creatorId();
            $user->created_by = creatorId();
            $user->save();

            if ($role) {
                $user->assignRole($role);
            }

            $customer = new Customer();
            $customer->user_id = $user->id;
            $customer->company_name = $companyName;
            $customer->contact_person_name = $companyName;
            $customer->contact_person_email = $email;
            $customer->contact_person_mobile = $phone;
            $customer->tax_number = $data['tax_number'] ?? null;
            $customer->payment_terms = $data['payment_terms'] ?? null;
            $customer->billing_address = $billingAddress;
            $customer->shipping_address = $shippingAddress;
            $customer->same_as_billing = $sameAsBilling;
            $customer->notes = $data['notes'] ?? null;
            $customer->creator_id = Auth::id() ?? creatorId();
            $customer->created_by = creatorId();
            $customer->save();

            return $customer;
        });
    }
}
