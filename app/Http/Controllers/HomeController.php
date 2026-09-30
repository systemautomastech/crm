<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\HelpdeskTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function Dashboard(Request $request)
    {
        if(Auth::user()->type === 'superadmin') {
            return $this->superAdminDashboard();
        }

        return $this->regularDashboard();
    }

    private function superAdminDashboard()
    {
        $orderData = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(price) as payments')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = [
                'month' => $months[$i-1],
                'orders' => $orderData[$i]->count ?? 0,
                'payments' => $orderData[$i]->payments ?? 0
            ];
        }

        // Tickets pending admin response (tickets where last reply is from client/company)
        $adminUserId = Auth::id();
        
        $weeklyPendingTickets = HelpdeskTicket::with(['category', 'creator', 'replies' => function($query) {
                $query->latest()->limit(1);
            }])
            ->whereIn('status', ['open', 'in_progress'])
            ->where(function($query) use ($adminUserId) {
                // Tickets with no replies at all
                $query->whereDoesntHave('replies')
                    // OR tickets where last reply is NOT from superadmin
                    ->orWhereHas('replies', function($q) use ($adminUserId) {
                        $q->whereRaw('id = (SELECT MAX(id) FROM helpdesk_replies WHERE ticket_id = helpdesk_tickets.id)')
                          ->where('created_by', '!=', $adminUserId);
                    });
            })
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($ticket) => [
                'id' => $ticket->id,
                'ticket_id' => $ticket->ticket_id,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category?->name,
                'category_color' => $ticket->category?->color,
                'creator' => $ticket->creator?->name,
                'created_at' => $ticket->created_at->diffForHumans(),
                'last_reply_at' => $ticket->replies->first()?->created_at?->diffForHumans() ?? $ticket->created_at->diffForHumans(),
                'days_pending' => $ticket->replies->first() 
                    ? $ticket->replies->first()->created_at->diffInDays(now())
                    : $ticket->created_at->diffInDays(now())
            ]);

        // Monthly ticket trends
        $ticketMonthlyData = HelpdeskTicket::selectRaw('MONTH(created_at) as month, COUNT(*) as created')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $resolvedMonthlyData = HelpdeskTicket::selectRaw('MONTH(resolved_at) as month, COUNT(*) as resolved')
            ->whereYear('resolved_at', now()->year)
            ->whereNotNull('resolved_at')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $ticketChartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $ticketChartData[] = [
                'month' => $months[$i-1],
                'created' => $ticketMonthlyData[$i]->created ?? 0,
                'resolved' => $resolvedMonthlyData[$i]->resolved ?? 0
            ];
        }

        // Recent helpdesk tickets
        $recentTickets = HelpdeskTicket::with(['category', 'creator'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($ticket) => [
                'id' => $ticket->id,
                'ticket_id' => $ticket->ticket_id,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category?->name,
                'category_color' => $ticket->category?->color,
                'creator' => $ticket->creator?->name,
                'created_at' => $ticket->created_at->diffForHumans()
            ]);

        return Inertia::render('SuperAdminDashboard', [
            'stats' => [
                'order_payments' => Order::sum('price') ?? 0,
                'total_orders' => Order::count(),
                'total_plans' => Plan::where('custom_plan', false)->count(),
                'total_companies' => User::where('type', 'company')->count(),
            ],
            'chartData' => $chartData,
            'ticketChartData' => $ticketChartData,
            'recentTickets' => $recentTickets,
            'weeklyPendingTickets' => $weeklyPendingTickets
        ]);
    }

    private function regularDashboard()
    {
        $packagesPath = base_path('packages/automas');

        // find dashboard menu from all  active package and redirect if found
        if (is_dir($packagesPath)) {
            foreach (glob($packagesPath . '/*/src/Resources/js/menus/company-menu.ts') as $menuFile) {
                preg_match('/packages\/automas\/([^\/]+)\//', $menuFile, $moduleMatch);
                $moduleName = $moduleMatch[1] ?? null;
                    $content = file_get_contents($menuFile);
                    if (preg_match("/parent:\s*['\"]dashboard['\"]/", $content)) {
                        preg_match("/href:\s*route\(['\"]([^'\"]+)['\"]/", $content, $routeMatch);
                        preg_match("/permission:\s*['\"]([^'\"]+)['\"]/", $content, $permMatch);
                        if (!empty($routeMatch[1]) && !empty($permMatch[1]) &&  Module_is_active($moduleName) && Auth::user()->can($permMatch[1])) {
                            return redirect()->route($routeMatch[1]);
                        }
                }
            }
        }

        return Inertia::render('dashboard');
    }
}
