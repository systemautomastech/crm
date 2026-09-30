<?php

namespace App\Http\Controllers;

use App\Events\CreateHelpdeskTicket;
use App\Events\UpdateHelpdeskTicket;
use App\Events\DestroyHelpdeskTicket;
use App\Http\Requests\StoreHelpdeskTicketRequest;
use App\Http\Requests\UpdateHelpdeskTicketRequest;
use App\Models\HelpdeskTicket;
use App\Models\HelpdeskCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmailTemplate;
use Inertia\Inertia;

class HelpdeskTicketController extends Controller
{
    public function today()
    {
        if(Auth::user()->can('manage-helpdesk-tickets')){
            $query = HelpdeskTicket::query()
                ->with(['category', 'creator', 'replies.creator'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-helpdesk-tickets')) {
                        // Get all tickets
                    } elseif(Auth::user()->can('manage-own-helpdesk-tickets')) {
                        $q->where('created_by', creatorId());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->whereIn('status', ['open', 'in_progress'])
                ->when(request('search'), fn($q) => $q->where(function($query) {
                    $query->where('title', 'like', '%' . request('search') . '%')
                          ->orWhere('ticket_id', 'like', '%' . request('search') . '%')
                          ->orWhere('description', 'like', '%' . request('search') . '%');
                }))
                ->latest();

            $tickets = $query->get();

            // Calculate statistics
            $stats = [
                'total' => $tickets->count(),
                'urgent' => $tickets->where('priority', 'urgent')->count(),
                'high' => $tickets->where('priority', 'high')->count(),
                'medium' => $tickets->where('priority', 'medium')->count(),
                'low' => $tickets->where('priority', 'low')->count(),
                'open' => $tickets->where('status', 'open')->count(),
                'in_progress' => $tickets->where('status', 'in_progress')->count(),
            ];

            $categories = HelpdeskCategory::where('is_active', true)
                ->get(['id', 'name', 'color']);

            return Inertia::render('helpdesk/tickets/today', [
                'tickets' => $tickets,
                'stats' => $stats,
                'categories' => $categories,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function index()
    {
        if(Auth::user()->can('manage-helpdesk-tickets')){
            $tickets = HelpdeskTicket::query()
                ->with(['category', 'creator'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-helpdesk-tickets')) {
                        // Get all tickets
                    } elseif(Auth::user()->can('manage-own-helpdesk-tickets')) {
                        $q->where('created_by', creatorId());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('title'), fn($q) => $q->where(function($query) {
                    $query->where('title', 'like', '%' . request('title') . '%')
                          ->orWhere('ticket_id', 'like', '%' . request('title') . '%');
                }))
                ->when(request('status'), fn($q) => $q->where('status', request('status')))
                ->when(request('priority'), fn($q) => $q->where('priority', request('priority')))
                ->when(request('category_id'), fn($q) => $q->where('category_id', request('category_id')))
                ->when(request('company_id') && Auth::user()->type === 'superadmin', fn($q) => $q->where('created_by', request('company_id')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            $categories = HelpdeskCategory::where('is_active', true)
                ->get(['id', 'name', 'color']);

            $companies = [];
            if(Auth::user()->type === 'superadmin') {
                $companies = User::where('type', 'company')
                    ->get(['id', 'name']);
            }

            return Inertia::render('helpdesk/tickets/index', [
                'tickets' => $tickets,
                'categories' => $categories,
                'companies' => $companies,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreHelpdeskTicketRequest $request)
    {
        if(Auth::user()->can('create-helpdesk-tickets')){
            $validated = $request->validated();

            $ticket = new HelpdeskTicket();
            $ticket->title = $validated['title'];
            $ticket->description = $validated['description'];
            $ticket->priority = $validated['priority'];
            $ticket->category_id = $validated['category_id'];

            $adminUser = User::where('type', 'superadmin')->first();

            if(Auth::user()->type === 'superadmin' && isset($validated['company_id'])) {
                $ticket->created_by = $validated['company_id'];

                $user = User::where('id',$validated['company_id'])->first();
                $toEmail = company_setting('company_email',$validated['company_id']) ?: $user->email;
                $name = company_setting('company_name', $validated['company_id']) ?: $user->name;
            } else {                
                $ticket->created_by = creatorId();
                $toEmail = $adminUser->email;
                $name = $adminUser->name;
            }
   
            $ticket->save();

            CreateHelpdeskTicket::dispatch($request, $ticket);

            try {
                if(admin_setting('Helpdesk Ticket') == 'on') {
                    $emailData = [
                        'company_name' => $name,        
                        'ticket_name' => $ticket->title ?? null,
                        'ticket_id' => $ticket->ticket_id ?? null,
                        'ticket_url' => route('helpdesk-tickets.show', $ticket->id) ?? null,
                        'ticket_description' => strip_tags($ticket->description ?? ''),
                        'ticket_category' => $ticket->category->name ?? null,
                        'ticket_priority' => $ticket->priority ?? null,
                    ];
                    $message = EmailTemplate::sendEmailTemplate('Helpdesk Ticket', [$toEmail], $emailData, $adminUser->id);
                    if($message['is_success'] == false && !empty($message['error'])) {
                        return back()
                            ->with('success', __('The ticket has been created successfully.'))
                            ->with('error', $message['error']);
                    }
                }
            } catch (\Throwable $th) {
                return back()->with('error', $th->getMessage());
            }

            return back()->with('success', __('The ticket has been created successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show(HelpdeskTicket $helpdeskTicket)
    {
        if(Auth::user()->can('view-helpdesk-tickets') && (Auth::user()->can('manage-any-helpdesk-tickets') ||  $helpdeskTicket->created_by === creatorId())){
            $helpdeskTicket->load(['category', 'creator', 'replies.creator']);

            return Inertia::render('helpdesk/tickets/show', [
                'ticket' => $helpdeskTicket,
            ]);
        }
        else{
            return redirect()->route('helpdesk-tickets.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateHelpdeskTicketRequest $request, HelpdeskTicket $helpdeskTicket)
    {
        if(Auth::user()->can('edit-helpdesk-tickets')){
            $validated = $request->validated();

            $helpdeskTicket->title = $validated['title'];
            $helpdeskTicket->description = $validated['description'];
            $helpdeskTicket->status = $validated['status'];
            $helpdeskTicket->priority = $validated['priority'];
            $helpdeskTicket->category_id = $validated['category_id'];


            if($validated['status'] === 'resolved' && !$helpdeskTicket->resolved_at) {
                $helpdeskTicket->resolved_at = now();
            }

            $helpdeskTicket->save();

            UpdateHelpdeskTicket::dispatch($request, $helpdeskTicket);

            return back()->with('success', __('The ticket has been updated successfully'));
        }
        else{
            return redirect()->route('helpdesk-tickets.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(HelpdeskTicket $helpdeskTicket)
    {
        if(Auth::user()->can('delete-helpdesk-tickets')){
            DestroyHelpdeskTicket::dispatch($helpdeskTicket);

            $helpdeskTicket->delete();

           return back()->with('success', __('The ticket has been deleted.'));
        }
        else{
            return redirect()->route('helpdesk-tickets.index')->with('error', __('Permission denied'));
        }
    }
}
