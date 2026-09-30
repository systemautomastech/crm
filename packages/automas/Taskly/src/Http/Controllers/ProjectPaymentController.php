<?php

namespace Automas\Taskly\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Taskly\Events\CreateProjectPayment;
use Automas\Taskly\Events\UpdateProjectPayment;
use Automas\Taskly\Events\DestroyProjectPayment;
use Automas\Taskly\Events\EditProjectPayment;
use Automas\Taskly\Events\PostProjectPayment;
use Automas\Taskly\Http\Requests\StoreProjectPaymentRequest;
use Automas\Taskly\Http\Requests\UpdateProjectPaymentRequest;
use Automas\Taskly\Models\Project;
use Automas\Taskly\Models\ProjectMilestone;
use Automas\Taskly\Models\ProjectPayment;
use Automas\Taskly\Models\ProjectPaymentItem;

class ProjectPaymentController extends Controller
{
    private function checkPaymentAccess(ProjectPayment $projectPayment)
    {
        if(Auth::user()->can('manage-any-project-payments')) {
            return true;
        } elseif(Auth::user()->can('manage-own-project-payments')) {
            if($projectPayment->creator_id != Auth::id() && $projectPayment->customer_id != Auth::id()) {
                return false;
            }
            if($projectPayment->creator_id != Auth::id() && Auth::user()->type == 'client' && $projectPayment->status == 'draft') {
                return false;
            }
            return true;
        }
        return false;
    }

    public function index(Request $request)
    {
        if(Auth::user()->can('manage-project-payments')){
            $query = ProjectPayment::with(['project', 'customer'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-project-payments')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-project-payments')) {
                        $q->where('creator_id', Auth::id())->orWhere('customer_id', Auth::id());
                        if(Auth::user()->type == 'client') {
                            $q->where('status', '!=', 'draft');
                        }
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });

            // Apply filters
            if ($request->project_id) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->customer_id) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->search) {
                $query->where('payment_number', 'like', '%' . $request->search . '%');
            }
            if ($request->date_range) {
                $dates = explode(' - ', $request->date_range);
                if (count($dates) === 2) {
                    $query->whereBetween('payment_date', [$dates[0], $dates[1]]);
                }
            }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['payment_number', 'payment_date', 'due_date', 'subtotal', 'discount_amount', 'total_amount', 'balance_amount', 'status', 'created_at'];
        if (!in_array($sortField, $allowedSortFields) || empty($sortField)) {
            $sortField = 'created_at';
        }

        // Calculate summary stats from filtered query (before pagination)
        $now = now();
        $yearly = (clone $query)->whereYear('payment_date', $now->year)->sum('total_amount');
        $monthly = (clone $query)->whereYear('payment_date', $now->year)->whereMonth('payment_date', $now->month)->sum('total_amount');
        $quarterly = (clone $query)->whereYear('payment_date', $now->year)
            ->whereBetween('payment_date', [$now->copy()->startOfQuarter()->toDateString(), $now->copy()->endOfQuarter()->toDateString()])
            ->sum('total_amount');
        $today = (clone $query)->whereDate('payment_date', $now->toDateString())->sum('total_amount');

        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $payments = $query->paginate($perPage);

        // Filter projects based on user type
        $projectsQuery = Project::where('created_by', creatorId())->select('id', 'name');
        if(Auth::user()->type == 'client') {
            $projectsQuery->whereHas('clients', function($q) {
                $q->where('client_id', Auth::id());
            });
        }
        $projects = $projectsQuery->get();

        $customers = User::where('type', 'client')->select('id', 'name', 'email')->where('created_by', creatorId())->get();

            return Inertia::render('Taskly/ProjectPayment/Index', [
                'payments' => $payments,
                'projects' => $projects,
                'customers' => $customers,
                'filters' => $request->only(['project_id', 'customer_id', 'status', 'search', 'date_range']),
                'stats' => [
                    'yearly' => (float) $yearly,
                    'monthly' => (float) $monthly,
                    'quarterly' => (float) $quarterly,
                    'today' => (float) $today,
                ]
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function create()
    {
        if(Auth::user()->can('create-project-payments')){
            $projects = Project::with('clients:id,name')
                ->where('created_by', creatorId())
                ->select('id', 'name')
                ->get();
            $customers = User::where('type', 'client')->select('id', 'name', 'email')->where('created_by', creatorId())->get();

            return Inertia::render('Taskly/ProjectPayment/Create', [
                'projects' => $projects,
                'customers' => $customers,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreProjectPaymentRequest $request)
    {
        if(Auth::user()->can('create-project-payments')){
            $totals = $this->calculateTotals($request->items);

            $payment = new ProjectPayment();
            $payment->payment_date = $request->payment_date;
            $payment->due_date = $request->due_date;
            $payment->project_id = $request->project_id;
            $payment->customer_id = $request->customer_id;
            $payment->payment_terms = $request->payment_terms;
            $payment->notes = $request->notes;
            $payment->subtotal = $totals['subtotal'];
            $payment->discount_amount = $totals['discount_amount'];
            $payment->total_amount = $totals['total_amount'];
            $payment->balance_amount = $totals['total_amount'];
            $payment->creator_id = Auth::id();
            $payment->created_by = creatorId();
            $payment->save();

            // Create payment items
            $this->createPaymentItems($payment->id, $request->items);

            try {
                CreateProjectPayment::dispatch($request, $payment);
            } catch (\Throwable $th) {
                return back()->with('error', $th->getMessage());
            }

            return redirect()->route('project-payments.index')->with('success', __('The project payment has been created successfully.'));

        }
        else{
            return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
        }
    }

    public function show(ProjectPayment $projectPayment)
    {
        if(Auth::user()->can('view-project-payments') && $projectPayment->created_by == creatorId()){
            if(!$this->checkPaymentAccess($projectPayment)) {
                return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
            }

            $projectPayment->load(['project', 'customer', 'items.milestone']);

            return Inertia::render('Taskly/ProjectPayment/View', [
                'payment' => $projectPayment
            ]);
        }
        else{
            return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
        }
    }

    public function edit(Request $request, ProjectPayment $projectPayment)
    {
        if(Auth::user()->can('edit-project-payments') && $projectPayment->created_by == creatorId()){
            if(!$this->checkPaymentAccess($projectPayment)) {
                return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
            }

            if ($projectPayment->status != 'draft') {
                return redirect()->route('project-payments.index')->with('error', __('Cannot update posted payment.'));
            }

            $projectPayment->load(['items']);

            EditProjectPayment::dispatch($request, $projectPayment);

            $projects = Project::with('clients:id,name')
                ->where('created_by', creatorId())
                ->select('id', 'name')
                ->get();
            $customers = User::where('type', 'client')->select('id', 'name', 'email')->where('created_by', creatorId())->get();

            return Inertia::render('Taskly/ProjectPayment/Edit', [
                'payment' => $projectPayment,
                'projects' => $projects,
                'customers' => $customers,
            ]);
        }
        else{
            return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateProjectPaymentRequest $request, ProjectPayment $projectPayment)
    {
        if(Auth::user()->can('edit-project-payments') && $projectPayment->created_by == creatorId()){
            if ($projectPayment->status != 'draft') {
                return redirect()->route('project-payments.index')->with('error', __('Cannot update posted payment.'));
            }
            $totals = $this->calculateTotals($request->items);

            $projectPayment->payment_date = $request->payment_date;
            $projectPayment->due_date = $request->due_date;
            $projectPayment->project_id = $request->project_id;
            $projectPayment->customer_id = $request->customer_id;
            $projectPayment->payment_terms = $request->payment_terms;
            $projectPayment->notes = $request->notes;
            $projectPayment->subtotal = $totals['subtotal'];
            $projectPayment->discount_amount = $totals['discount_amount'];
            $projectPayment->total_amount = $totals['total_amount'];
            $projectPayment->balance_amount = $totals['total_amount'];
            $projectPayment->save();

            // Delete existing items and recreate
            $projectPayment->items()->delete();
            $this->createPaymentItems($projectPayment->id, $request->items);

            // Dispatch event for packages to handle their fields
            UpdateProjectPayment::dispatch($request, $projectPayment);

            return redirect()->route('project-payments.index')->with('success', __('The project payment details are updated successfully.'));
        }
        else{
            return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(ProjectPayment $projectPayment)
    {
        if(Auth::user()->can('delete-project-payments')){
            if ($projectPayment->status === 'posted') {
                return back()->withErrors(['error' => __('Cannot delete posted payment.')]);
            }

            // Dispatch event before deletion
            DestroyProjectPayment::dispatch($projectPayment);

            $projectPayment->delete();

            return redirect()->route('project-payments.index')->with('success', __('The project payment has been deleted.'));
        }
        else{
            return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
        }
    }

    public function post(Request $request, ProjectPayment $projectPayment)
    {
        if(Auth::user()->can('post-project-payments')){
        if ($projectPayment->status !== 'draft') {
            return back()->withErrors(['error' => __('Only draft payments can be posted.')]);
        }

        try {
            PostProjectPayment::dispatch($request, $projectPayment);
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }

        $projectPayment->update(['status' => 'posted']);

        return back()->with('success', __('The project payment has been posted successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function print(ProjectPayment $projectPayment)
    {
        if (Auth::user()->can('view-project-payments') && $projectPayment->created_by == creatorId()) {
            if (!$this->checkPaymentAccess($projectPayment)) {
                return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
            }

            $projectPayment->load(['project:id,name', 'customer:id,name,email', 'items.milestone:id,title']);

            return Inertia::render('Taskly/ProjectPayment/Print', [
                'payment' => $projectPayment,
            ]);
        } else {
            return redirect()->route('project-payments.index')->with('error', __('Permission denied'));
        }
    }

    public function getProjectMilestones(Request $request)
    {
        if(Auth::user()->can('create-project-payments') || Auth::user()->can('edit-project-payments')){
            $projectId = $request->project_id;

            if (!$projectId) {
                return response()->json([]);
            }
            $milestones = ProjectMilestone::select('id', 'title', 'cost', 'status', 'progress')
                ->where('project_id', $projectId)
                ->get()
                ->map(function ($milestone) {
                    return [
                        'id' => $milestone->id,
                        'title' => $milestone->title,
                        'cost' => $milestone->cost,
                        'status' => $milestone->status,
                        'progress' => $milestone->progress,
                    ];
                });
            return response()->json($milestones);
        }
        else{
            return response()->json([], 403);
        }
    }

    private function calculateTotals($items)
    {
        $subtotal = 0;
        $totalDiscount = 0;

        foreach ($items as $item) {
            $lineTotal = $item['price'];
            $discountAmount = ($lineTotal * ($item['discount_percentage'] ?? 0)) / 100;

            $subtotal += $lineTotal;
            $totalDiscount += $discountAmount;
        }

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $totalDiscount,
            'total_amount' => $subtotal - $totalDiscount,
        ];
    }

    private function createPaymentItems($paymentId, $items)
    {
        foreach ($items as $itemData) {
            $price = $itemData['price'];
            $discountPercentage = $itemData['discount_percentage'] ?? 0;
            $discountAmount = ($price * $discountPercentage) / 100;
            $totalAmount = $price - $discountAmount;

            $item = new ProjectPaymentItem();
            $item->payment_id = $paymentId;
            $item->milestone_id = $itemData['milestone_id'];
            $item->price = $price;
            $item->discount_percentage = $discountPercentage;
            $item->discount_amount = $discountAmount;
            $item->total_amount = $totalAmount;
            $item->save();
        }
    }
}
