<?php

namespace Automas\Account\Http\Controllers;

use App\Models\SalesInvoiceSetup;
use Automas\Account\Models\CustomerPayment;
use Automas\Account\Models\CustomerPaymentAllocation;
use Automas\Account\Models\BankAccount;
use Automas\Account\Models\CreditNote;
use Automas\Account\Models\CreditNoteApplication;
use Automas\Account\Http\Requests\StoreCustomerPaymentRequest;
use Automas\Account\Services\JournalService;
use Automas\Account\Services\BankTransactionsService;
use App\Models\User;
use App\Models\SalesInvoice;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Automas\Account\Events\CreateCustomerPayment;
use Automas\Account\Events\UpdateCustomerPaymentStatus;
use Automas\Account\Events\DestroyCustomerPayment;
use App\Models\EmailTemplate;

class CustomerPaymentController extends Controller
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function index(Request $request)
    {
        if (Auth::user()->can('manage-customer-payments')) {
            $query = CustomerPayment::with(['customer', 'bankAccount', 'allocations.invoice', 'creditNoteApplications.creditNote'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-customer-payments')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-customer-payments')) {
                        $q->where('creator_id', Auth::id())->orWhere('customer_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });

            // Apply filters
            if ($request->customer_id) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->search) {
                $query->where('payment_number', 'like', '%' . $request->search . '%');
            }
            if ($request->date_from) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }
            if ($request->date_to) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }
            if ($request->bank_account_id) {
                $query->where('bank_account_id', $request->bank_account_id);
            }

            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $payments = $query->paginate($request->get('per_page', 10));
            $customers = User::where('type', 'client')->where('created_by', creatorId())->get();

            $bankAccounts = BankAccount::where('is_active', true)->where('created_by', creatorId())->get();

            return Inertia::render('Account/CustomerPayments/Index', [
                'payments' => $payments,
                'customers' => $customers,
                'bankAccounts' => $bankAccounts,
                'filters' => $request->only(['customer_id', 'status', 'search', 'bank_account_id'])
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreCustomerPaymentRequest $request)
    {
        if (Auth::user()->can('create-customer-payments')) {
            // Validate that at least one invoice allocation exists
            if (!$request->allocations || count($request->allocations) === 0) {
                return back()->with('error', __('At least one invoice allocation is required to create a payment.'));
            }

            // Validate credit note amount doesn't exceed invoice allocation amount
            if ($request->credit_notes) {
                $totalInvoiceAmount = collect($request->allocations)->sum('amount');
                $totalCreditNoteAmount = collect($request->credit_notes)->sum('amount');

                if ($totalCreditNoteAmount > $totalInvoiceAmount) {
                    return back()->with('error', __('Credit note amount cannot exceed the total invoice allocation amount.'));
                }
            }

            // Create payment
            $payment = new CustomerPayment();
            $payment->payment_date = $request->payment_date;
            $payment->customer_id = $request->customer_id;
            $payment->bank_account_id = $request->bank_account_id;
            $payment->reference_number = $request->reference_number;
            $payment->payment_amount = $request->payment_amount;
            $payment->notes = $request->notes;
            $payment->creator_id = Auth::id();
            $payment->created_by = creatorId();
            $payment->save();

            // Create allocations if provided
            if ($request->allocations) {
                foreach ($request->allocations as $allocation) {
                    $invoices = SalesInvoice::where('id', $allocation['invoice_id'])->first();
                    $paymentAllocation = new CustomerPaymentAllocation();
                    $paymentAllocation->payment_id = $payment->id;
                    $paymentAllocation->invoice_id = $allocation['invoice_id'];
                    $paymentAllocation->allocated_amount = $allocation['amount'];
                    $paymentAllocation->dues = $invoices->balance_amount - $allocation['amount'];
                    $paymentAllocation->creator_id = Auth::id();
                    $paymentAllocation->created_by = creatorId();
                    $paymentAllocation->save();
                }
            }

            // Handle credit notes if provided
            if ($request->credit_notes) {
                foreach ($request->credit_notes as $creditNote) {
                    $creditNoteModel = CreditNote::find($creditNote['credit_note_id']);
                    if (!$creditNoteModel)
                        continue;

                    // Create credit note application entry
                    CreditNoteApplication::create([
                        'credit_note_id' => $creditNote['credit_note_id'],
                        'payment_id' => $payment->id,
                        'applied_amount' => $creditNote['amount'],
                        'application_date' => $request->payment_date,
                        'creator_id' => Auth::id(),
                        'created_by' => creatorId()
                    ]);
                }
            }

            // Dispatch event
            CreateCustomerPayment::dispatch($request, $payment);

            return redirect()->route('account.customer-payments.index')->with('success', __('The customer payment has been created successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }



    public function getOutstandingInvoices($customerId)
    {
        $invoices = SalesInvoice::where('customer_id', $customerId)
            ->where('balance_amount', '>', 0)
            ->whereIn('status', ['posted', 'partial'])
            ->where('created_by', creatorId())
            ->get();

        $creditNotes = CreditNote::where('customer_id', $customerId)
            ->where('balance_amount', '>', 0)
            ->whereIn('status', ['approved', 'partial'])
            ->where('created_by', creatorId())
            ->get(['id', 'credit_note_number', 'balance_amount', 'total_amount', 'status']);

        return response()->json([
            'invoices' => $invoices,
            'creditNotes' => $creditNotes
        ]);
    }

    public function updateStatus(Request $request, CustomerPayment $customerPayment)
    {
        if (Auth::user()->can('cleared-customer-payments') && $customerPayment->created_by == creatorId()) {
            try {
                // Create journal entry and update invoices when payment is cleared
                if ($request->status === 'cleared') {
                    if ($customerPayment->payment_amount > 0) {
                        $this->journalService->createCustomerPaymentJournal($customerPayment);
                        $this->bankTransactionsService->createCustomerPayment($customerPayment);
                    }
                    // Update invoice balances
                    foreach ($customerPayment->allocations as $allocation) {
                        $invoice = $allocation->invoice;
                        $invoice->paid_amount += $allocation->allocated_amount;
                        $invoice->balance_amount = $invoice->total_amount - $invoice->paid_amount;

                        if ($invoice->balance_amount == 0) {
                            $invoice->status = 'paid';
                        } elseif ($invoice->paid_amount > 0) {
                            $invoice->status = 'partial';
                        }
                        $invoice->save();
                    }
                }

                $creditNoteApplication = CreditNoteApplication::where('payment_id', $customerPayment->id)->get();

                foreach ($creditNoteApplication as $creditNote) {
                    $creditNoteModel = CreditNote::find($creditNote['credit_note_id']);
                    $creditNoteModel->applied_amount += $creditNote['applied_amount'];
                    $creditNoteModel->balance_amount = $creditNoteModel->total_amount - $creditNoteModel->applied_amount;
                    $creditNoteModel->status = $creditNoteModel->balance_amount <= 0 ? 'applied' : 'partial';
                    $creditNoteModel->save();
                }

                $customerPayment->update(['status' => $request->status]);
                // Dispatch event
                UpdateCustomerPaymentStatus::dispatch($request, $customerPayment);
                if ($customerPayment->status == "cleared") {
                    // Send email notification
                    if (company_setting('Customer Payment') == 'on') {
                        $customerPayment->load('customer');
                        $emailData = [
                            'payment_number' => $customerPayment->payment_number ?? null,
                            'payment_date' => $customerPayment->payment_date ? \Carbon\Carbon::parse($customerPayment->payment_date)->format('d M Y') : null,
                            'customer_name' => $customerPayment->customer->name ?? null,
                            'payment_amount' => number_format($customerPayment->payment_amount, 2),
                            'reference_number' => $customerPayment->reference_number ?? null,
                        ];
                        $message = EmailTemplate::sendEmailTemplate('Customer Payment', [$customerPayment->customer->email ?? null], $emailData);
                        if ($message['is_success'] == false && !empty($message['error'])) {
                            return back()
                                ->with('success', __('The payment status are updated successfully.'))
                                ->with('error', $message['error']);
                        }
                    }
                }
                return back()->with('success', __('The payment status are updated successfully.'));
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function print(CustomerPayment $customerPayment)
    {
        if (Auth::user()->can('view-customer-payments') || Auth::user()->can('manage-customer-payments')) {
            $customerPayment->load(['customer', 'bankAccount', 'allocations.invoice', 'creditNoteApplications.creditNote']);

            $creatorId = $customerPayment->created_by ?? creatorId();
            $salesInvoiceSetting = SalesInvoiceSetup::getSettings($creatorId);

            return Inertia::render('Account/CustomerPayments/Print', [
                'payment' => $customerPayment,
                'salesInvoiceSetting' => $salesInvoiceSetting,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(CustomerPayment $customerPayment)
    {
        if (Auth::user()->can('delete-customer-payments') && $customerPayment->created_by == creatorId() && $customerPayment->status === 'pending') {

            // Dispatch event before deletion
            DestroyCustomerPayment::dispatch($customerPayment);

            $customerPayment->delete();
            return back()->with('success', __('The customer payment has been deleted.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
}
