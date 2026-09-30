<?php

namespace Automas\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoiceReturn;
use App\Models\User;
use Automas\Account\Models\CreditNote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Automas\Account\Events\ApproveCreditNote;
use Automas\Account\Events\DestroyCreditNote;
use Automas\Account\Services\JournalService;
use App\Models\EmailTemplate;

class CreditNoteController extends Controller
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    private function checkCreditNoteAccess(CreditNote $creditNote)
    {
        if(Auth::user()->can('manage-any-credit-notes')) {
            return true;
        } elseif(Auth::user()->can('manage-own-credit-notes')) {
            if($creditNote->creator_id != Auth::id() && $creditNote->customer_id != Auth::id()) {
                return false;
            }
            if($creditNote->creator_id != Auth::id() && Auth::user()->type == 'client' && $creditNote->status == 'draft') {
                return false;
            }
            return true;
        }
        return false;
    }

    public function index(Request $request)
    {
        if(Auth::user()->can('manage-credit-notes')){
            $query = CreditNote::with(['customer', 'salesReturn', 'approvedBy'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-credit-notes')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-credit-notes')) {
                        $q->where('creator_id', Auth::id())->orWhere('customer_id', Auth::id());
                        if(Auth::user()->type == 'client') {
                            $q->where('status','!=', 'draft');
                        }
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });

            if ($request->customer_id) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->search) {
                $query->where('credit_note_number', 'like', '%' . $request->search . '%');
            }

            if ($request->sales_return_id) {
                $query->where('return_id', $request->sales_return_id);
            }

            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $creditNotes = $query->paginate($request->per_page ?? 10)->withQueryString();

            $customers = User::where('type', 'client')->where('created_by', creatorId())->get(['id', 'name']);
            $salesReturns = SalesInvoiceReturn::where('created_by', creatorId())->get(['id', 'return_number']);

            return Inertia::render('Account/CreditNotes/Index', [
                'creditNotes' => $creditNotes,
                'customers' => $customers,
                'salesReturns' => $salesReturns,
                'filters' => $request->only(['customer_id', 'status', 'sales_return_id'])
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show(CreditNote $creditNote)
    {
        if(Auth::user()->can('view-credit-notes') &&
           (Auth::user()->type == 'client' ? $creditNote->customer_id == Auth::id() : $creditNote->created_by == creatorId())){
            if(!$this->checkCreditNoteAccess($creditNote)) {
                return redirect()->route('account.credit-notes.index')->with('error', __('Permission denied'));
            }

            $creditNote->load(['customer', 'items.product', 'items.taxes', 'salesReturn', 'applications.payment']);

            return Inertia::render('Account/CreditNotes/View', [
                'creditNote' => $creditNote
            ]);
        }
        else{
            return redirect()->route('account.credit-notes.index')->with('error', __('Permission denied'));
        }
    }

    public function approve(CreditNote $creditNote)
    {
        if(Auth::user()->can('approve-credit-notes')){
            if ($creditNote->status !== 'draft') {
                return back()->with('error', __('Only draft credit notes can be approved.'));
            }
            try {
                // Create journal entries
                $this->journalService->createCreditNoteJournal($creditNote);
                $this->journalService->createCreditNoteCOGSJournal($creditNote);

                $creditNote->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id()
                ]);
                ApproveCreditNote::dispatch($creditNote);
                if(company_setting('Credit Note Approval') == 'on') {

                    $creditNote->load('customer', 'invoice', 'salesReturn');

                    $emailData = [
                        'credit_note_number' => $creditNote->credit_note_number ?? null,
                        'credit_note_date'   => $creditNote->credit_note_date ? \Carbon\Carbon::parse($creditNote->credit_note_date)->format('d M Y') : null,
                        'customer_name'      => $creditNote->customer->name ?? null,
                        'invoice_number'     => $creditNote->invoice->invoice_number ?? null,
                        'return_number'      => $creditNote->salesReturn->return_number ?? null,
                        'reason'             => $creditNote->reason ?? null,
                        'total_amount'       => number_format($creditNote->total_amount, 2),
                    ];
                    $message = EmailTemplate::sendEmailTemplate('Credit Note Approval', [$creditNote->customer->email ?? null], $emailData);
                    if($message['is_success'] == false && !empty($message['error'])) {
                        return back()
                            ->with('success', __('Credit note approved successfully.'))
                            ->with('error', $message['error']);
                    }
                }

                return back()->with('success', __('Credit note approved successfully.'));
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(CreditNote $creditNote)
    {
        if(Auth::user()->can('delete-credit-notes')){
            if ($creditNote->status !== 'draft') {
                return back()->with('error', __('Only draft credit notes can be deleted.'));
            }

            DestroyCreditNote::dispatch($creditNote);

            $creditNote->delete();
            return back()->with('success', __('Credit note deleted successfully.'));
        }
        else {
            return back()->with('error', __('Permission denied'));
        }
    }
}
