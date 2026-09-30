<?php

namespace Automas\Pos\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Pos\Models\PosBillingCounter;
use Automas\Pos\Http\Requests\StorePosBillingCounterRequest;
use Automas\Pos\Http\Requests\UpdatePosBillingCounterRequest;
use Automas\Pos\Events\CreatePosBillingCounter;
use Automas\Pos\Events\UpdatePosBillingCounter;
use Automas\Pos\Events\DestroyPosBillingCounter;


class PosBillingCounterController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('manage-pos-billing-counters')) {
            $query = PosBillingCounter::where('created_by', creatorId());

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->get('status') === 'active' ? 1 : 0);
            }

            $sortField    = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');

            $allowedSorts = ['name', 'code', 'status', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->latest();
            }

            $perPage  = $request->get('per_page', 10);
            $counters = $query->paginate($perPage)->withQueryString();

            return Inertia::render('Pos/PosCounter/Index', [
                'counters' => $counters,
            ]);
        }

        return redirect()->route('pos.index')->with('error', __('Permission denied'));
    }

    public function store(StorePosBillingCounterRequest $request)
    {
        if (Auth::user()->can('create-pos-billing-counters')) {
            $validated = $request->validated();

            $counter             = new PosBillingCounter();
            $counter->name        = $validated['name'];
            $counter->code        = $validated['code'];
            $counter->status      = $validated['status'] ?? true;
            $counter->description = $validated['description'] ?? null;
            $counter->creator_id  = Auth::id();
            $counter->created_by  = creatorId();
            $counter->save();

            CreatePosBillingCounter::dispatch($request, $counter);

            return back()->with('success', __('The pos Billing Counter has been created successfully.'));
        }

        return redirect()->route('pos.counters')->with('error', __('Permission denied'));
    }

    public function update(UpdatePosBillingCounterRequest $request, PosBillingCounter $pos_billing_counter)
    {
        if (Auth::user()->can('edit-pos-billing-counters') && $pos_billing_counter->created_by == creatorId()) {
            $validated = $request->validated();

            $pos_billing_counter->name        = $validated['name'];
            $pos_billing_counter->code        = $validated['code'];
            $pos_billing_counter->status      = $validated['status'] ?? true;
            $pos_billing_counter->description = $validated['description'] ?? null;
            $pos_billing_counter->save();

            UpdatePosBillingCounter::dispatch($request, $pos_billing_counter);

            return back()->with('success', __('The pos Billing Counter has been updated successfully.'));
        }

        return redirect()->route('pos.counters')->with('error', __('Permission denied'));
    }

    public function destroy(PosBillingCounter $pos_billing_counter)
    {
        if (Auth::user()->can('delete-pos-billing-counters') && $pos_billing_counter->created_by == creatorId()) {

            DestroyPosBillingCounter::dispatch($pos_billing_counter);

            $pos_billing_counter->delete();

            return back()->with('success', __('The pos Billing Counter has been deleted.'));
        }

        return redirect()->route('pos.counters')->with('error', __('Permission denied'));
    }
}
