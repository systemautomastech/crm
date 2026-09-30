<?php

namespace Automas\Account\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Account\Models\Customer;
use Automas\Account\Http\Requests\StoreCustomerRequest;
use Automas\Account\Http\Requests\UpdateCustomerRequest;
use Automas\Account\Events\CreateCustomer;
use Automas\Account\Events\UpdateCustomer;
use Automas\Account\Events\DestroyCustomer;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Events\CreateUser;

class CustomerController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-customers')) {
            $customers = Customer::query()
                ->with('user:id,name,avatar,is_disable')
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-customers')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-customers')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('company_name'), fn($q) => $q->where('company_name', 'like', '%' . request('company_name') . '%'))
                ->when(request('customer_code'), fn($q) => $q->where('customer_code', 'like', '%' . request('customer_code') . '%'))
                ->when(request('tax_number'), fn($q) => $q->where('tax_number', 'like', '%' . request('tax_number') . '%'))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $users = User::where('type', 'client')
                ->where('created_by', creatorId())
                ->where('is_disable', 0)
                ->whereNotIn('id', Customer::pluck('user_id')->filter())
                ->select('id', 'name', 'email', 'mobile_no')
                ->get();

            return Inertia::render('Account/Customers/Index', [
                'customers' => $customers,
                'users' => $users,
            ]);
        }
        return back()->with('error', __('Permission denied'));
    }

    public function store(StoreCustomerRequest $request)
    {
        if (Auth::user()->can('create-customers')) {
            $validated = $request->validated();

            if (!empty($validated['user_id'])) {
                $user = User::findOrFail($validated['user_id']);
            } else {
                $user = $this->storeUser($request, $validated);
            }

            $customer = new Customer();
            $customer->user_id = $user->id ?? null;
            $customer->company_name = $validated['company_name'];
            $customer->contact_person_name = $validated['contact_person_name'];
            $customer->contact_person_email = $validated['contact_person_email'] ?? null;
            $customer->contact_person_mobile = $validated['contact_person_mobile'] ?? null;
            $customer->tax_number = $validated['tax_number'] ?? null;
            $customer->payment_terms = $validated['payment_terms'] ?? null;
            $customer->billing_address = $validated['billing_address'];
            $customer->shipping_address = $validated['same_as_billing'] ? $validated['billing_address'] : $validated['shipping_address'];
            $customer->same_as_billing = $validated['same_as_billing'] ?? false;
            $customer->notes = $validated['notes'] ?? null;
            $customer->creator_id = Auth::id();
            $customer->created_by = creatorId();
            $customer->save();

            CreateCustomer::dispatch($request, $customer);

            return redirect()->back()->with('success', __('The customer has been created successfully.'));
        }
        return redirect()->back()->with('error', __('Permission denied'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        if (Auth::user()->can('edit-customers')) {
            $validated = $request->validated();

            $customer->company_name = $validated['company_name'];
            $customer->contact_person_name = $validated['contact_person_name'];
            $customer->contact_person_email = $validated['contact_person_email'] ?? null;
            $customer->contact_person_mobile = $validated['contact_person_mobile'] ?? null;
            $customer->tax_number = $validated['tax_number'] ?? null;
            $customer->payment_terms = $validated['payment_terms'] ?? null;
            $customer->billing_address = $validated['billing_address'];
            $customer->shipping_address = $validated['same_as_billing'] ? $validated['billing_address'] : ($validated['shipping_address'] ?? null);
            $customer->same_as_billing = $validated['same_as_billing'] ?? false;
            $customer->notes = $validated['notes'] ?? null;
            $customer->save();

            if ($customer->user_id && $customer->user) {
                $customer->user->update([
                    'name' => $validated['company_name'],
                    'email' => $validated['contact_person_email'],
                    'mobile_no' => $validated['contact_person_mobile'] ?? null,
                ]);
            }

            UpdateCustomer::dispatch($request, $customer);

            return back()->with('success', __('The customer details are updated successfully.'));
        }
        return back()->with('error', __('Permission denied'));
    }

    public function destroy(Customer $customer)
    {
        if (Auth::user()->can('delete-customers')) {
            DestroyCustomer::dispatch($customer);
            $customer->delete();
            return back()->with('success', __('The customer has been deleted.'));
        }
        return back()->with('error', __('Permission denied'));
    }

    private function storeUser(StoreCustomerRequest $request, array $validated): User
    {
        $role = Role::findByName('client');

        $user = new User();
        $user->name = $validated['company_name'];
        $user->email = $validated['contact_person_email'];
        $user->mobile_no = $validated['contact_person_mobile'] ?? null;
        $user->password = Hash::make('12345678');
        $user->type = 'client';
        $user->is_enable_login = true;
        $user->lang = company_setting('defaultLanguage') ?? 'en';
        $user->email_verified_at = now();
        $user->creator_id = Auth::id();
        $user->created_by = creatorId();
        $user->save();

        $user->assignRole($role);

        CreateUser::dispatch($request, $user);

        return $user;
    }
}
