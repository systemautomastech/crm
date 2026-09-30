<?php

namespace Automas\Lead\Http\Controllers;

use App\Models\User;
use Automas\Lead\Models\ClientDeal;
use Automas\Lead\Models\Deal;
use Automas\Lead\Models\DealCall;
use Automas\Lead\Models\DealStage;
use Automas\Lead\Models\DealTask;
use Automas\Lead\Models\Lead;
use Automas\Lead\Models\LeadCall;
use Automas\Lead\Models\LeadStage;
use Automas\Lead\Models\LeadTask;
use Automas\Lead\Models\Pipeline;
use Automas\Lead\Models\UserDeal;
use Automas\Lead\Models\UserLead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private function getDateRange(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $now = \Carbon\Carbon::now();

        switch ($period) {
            case 'prev_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $start = $now->copy()->subMonths(3)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'last_6_months':
                $start = $now->copy()->subMonths(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = \Carbon\Carbon::parse($request->get('start_date'))->startOfDay();
                    $end = \Carbon\Carbon::parse($request->get('end_date'))->endOfDay();
                } else {
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                }
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }

        return [$start, $end];
    }

    public function index(Request $request)
    {
        if (Auth::user()->can('manage-crm-dashboard')) {
            $user = Auth::user();

            if ($user->type == 'client') {
                return $this->clientDashboard($request);
            }

            if ($user->type != 'company') {
                return $this->userDashboard($request);
            }

            [$start, $end] = $this->getDateRange($request);

            $deal = Deal::where('created_by', creatorId());
            $lead = Lead::where('created_by', creatorId());

            // 1. Top Cards Data (Real database values)
            $totalLeads = (clone $lead)->count();
            $monthLeads = (clone $lead)->whereBetween('created_at', [$start, $end])->count();
            $daysCount = max(1, $start->diffInDays(now()->lt($end) ? now() : $end) + 1);
            $avgDailyLeads = round($monthLeads / $daysCount, 1);

            // Lead Stats (Today, Yesterday, This Month, Prev Month)
            $todayLeads = (clone $lead)->whereDate('created_at', today())->count();
            $yesterdayLeads = (clone $lead)->whereDate('created_at', today()->subDay())->count();
            $thisMonthLeads = (clone $lead)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $prevMonthLeads = (clone $lead)->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();

            // Deal Stats (Today, Yesterday, This Month, Prev Month)
            $todayDeals = (clone $deal)->whereDate('created_at', today())->count();
            $yesterdayDeals = (clone $deal)->whereDate('created_at', today()->subDay())->count();
            $thisMonthDeals = (clone $deal)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $prevMonthDeals = (clone $deal)->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();

            // Daily Lead Trend (Past 14 days)
            $dailyLeadTrend = [];
            for ($i = 13; $i >= 0; $i--) {
                $dt = today()->subDays($i);
                $cnt = (clone $lead)->whereDate('created_at', $dt->toDateString())->count();
                $dailyLeadTrend[] = [
                    'date' => $dt->format('M d'),
                    'leads' => $cnt,
                ];
            }

            $activeDeals = (clone $deal)->where('is_active', true)->whereNotIn('status', ['Won', 'Loss'])->count();
            $openDealValue = (clone $deal)->where('is_active', true)->whereNotIn('status', ['Won', 'Loss'])->sum('price');

            $wonDealAmount = (clone $deal)->where('status', 'Won')->whereBetween('updated_at', [$start, $end])->sum('price');
            $wonDealsThisMonth = (clone $deal)->where('status', 'Won')->whereBetween('updated_at', [$start, $end])->count();
            if ($wonDealAmount == 0 && !$request->has('period')) {
                $wonDealAmount = (clone $deal)->where('status', 'Won')->sum('price');
                $wonDealsThisMonth = (clone $deal)->where('status', 'Won')->count();
            }

            // Calls today / period
            $dealCallsToday = DealCall::whereHas('deal', fn($q) => $q->where('created_by', creatorId()))->whereBetween('created_at', [$start, $end])->get();
            $leadCallsToday = LeadCall::whereHas('lead', fn($q) => $q->where('created_by', creatorId()))->whereBetween('created_at', [$start, $end])->get();
            $callsToday = $dealCallsToday->count() + $leadCallsToday->count();
            $connectedCallsToday = $dealCallsToday->where('call_result', '!=', 'missed')->count() + $leadCallsToday->where('call_result', '!=', 'missed')->count();
            $missedCallsToday = $dealCallsToday->where('call_result', 'missed')->count() + $leadCallsToday->where('call_result', 'missed')->count();

            // Follow-ups today & overdue tasks
            $dealTasksToday = DealTask::whereHas('deal', fn($q) => $q->where('created_by', creatorId()))->whereDate('date', today())->get();
            $leadTasksToday = LeadTask::whereHas('lead', fn($q) => $q->where('created_by', creatorId()))->whereDate('date', today())->get();
            $followupsToday = $dealTasksToday->count() + $leadTasksToday->count();
            $followupDealsToday = $dealTasksToday->count();
            $followupLeadsToday = $leadTasksToday->count();

            $overdueDealTasks = DealTask::whereHas('deal', fn($q) => $q->where('created_by', creatorId()))->where('date', '<', today())->where('status', 0)->count();
            $overdueLeadTasks = LeadTask::whereHas('lead', fn($q) => $q->where('created_by', creatorId()))->where('date', '<', today())->where('status', 0)->count();
            $overdueTasks = $overdueDealTasks + $overdueLeadTasks;

            // 2. Lead Overview (Pipeline wise - filtered by date range)
            $allPipelines = Pipeline::where('created_by', creatorId())->get();
            $leadOverview = [];
            foreach ($allPipelines as $pipe) {
                $cnt = Lead::where('created_by', creatorId())->where('pipeline_id', $pipe->id)->whereBetween('created_at', [$start, $end])->count();
                $leadOverview[] = [
                    'id' => $pipe->id,
                    'name' => $pipe->name,
                    'count' => $cnt,
                ];
            }
            if (array_sum(array_column($leadOverview, 'count')) === 0 && !$request->has('period')) {
                $leadOverview = [];
                foreach ($allPipelines as $pipe) {
                    $cnt = Lead::where('created_by', creatorId())->where('pipeline_id', $pipe->id)->count();
                    $leadOverview[] = [
                        'id' => $pipe->id,
                        'name' => $pipe->name,
                        'count' => $cnt,
                    ];
                }
            }
            usort($leadOverview, fn($a, $b) => $b['count'] <=> $a['count']);

            // 3. Deal Pipeline (Pipeline wise - filtered by date range)
            $dealPipeline = [];
            foreach ($allPipelines as $pipe) {
                $dDeals = Deal::where('created_by', creatorId())->where('pipeline_id', $pipe->id)->whereBetween('created_at', [$start, $end])->get();
                $dealPipeline[] = [
                    'id' => $pipe->id,
                    'name' => $pipe->name,
                    'deals' => $dDeals->count(),
                    'amount' => (float) $dDeals->sum('price'),
                ];
            }
            if (array_sum(array_column($dealPipeline, 'amount')) === 0 && !$request->has('period')) {
                $dealPipeline = [];
                foreach ($allPipelines as $pipe) {
                    $dDeals = Deal::where('created_by', creatorId())->where('pipeline_id', $pipe->id)->get();
                    $dealPipeline[] = [
                        'id' => $pipe->id,
                        'name' => $pipe->name,
                        'deals' => $dDeals->count(),
                        'amount' => (float) $dDeals->sum('price'),
                    ];
                }
            }
            usort($dealPipeline, fn($a, $b) => $b['amount'] <=> $a['amount']);

            // 4. Top Performers (Filtered by date range)
            $teamUsers = User::where('created_by', creatorId())->orWhere('id', creatorId())->select('id', 'name', 'avatar')->get();
            $teamPerformance = [];
            foreach ($teamUsers as $tUser) {
                $userWonDeals = Deal::whereHas('users', fn($q) => $q->where('users.id', $tUser->id))
                    ->where('status', 'Won')
                    ->whereBetween('updated_at', [$start, $end])
                    ->get();
                if ($userWonDeals->isEmpty() && !$request->has('period')) {
                    $userWonDeals = Deal::whereHas('users', fn($q) => $q->where('users.id', $tUser->id))->where('status', 'Won')->get();
                }
                $userCalls = DealCall::where('user_id', $tUser->id)->whereBetween('created_at', [$start, $end])->count()
                    + LeadCall::where('user_id', $tUser->id)->whereBetween('created_at', [$start, $end])->count();
                $userOverdue = DealTask::whereHas('deal.users', fn($q) => $q->where('users.id', $tUser->id))->where('date', '<', today())->where('status', 0)->count()
                    + LeadTask::whereHas('lead.userLeads', fn($q) => $q->where('user_id', $tUser->id))->where('date', '<', today())->where('status', 0)->count();

                if ($userWonDeals->count() > 0 || $userCalls > 0 || (float)$userWonDeals->sum('price') > 0) {
                    $teamPerformance[] = [
                        'id' => $tUser->id,
                        'name' => $tUser->name,
                        'avatar' => $tUser->avatar,
                        'connectedCalls' => $userCalls,
                        'wonDeals' => $userWonDeals->count(),
                        'wonAmount' => (float) $userWonDeals->sum('price'),
                        'overdue' => $userOverdue,
                    ];
                }
            }
            usort($teamPerformance, fn($a, $b) => $b['wonAmount'] <=> $a['wonAmount']);
            $teamPerformance = array_slice($teamPerformance, 0, 5);

            // 5. Upcoming Follow-ups
            $upcomingFollowups = [];
            $upcomingLeadTasks = LeadTask::with(['lead'])->whereDate('date', '>=', today())->orderBy('date', 'asc')->take(4)->get();
            foreach ($upcomingLeadTasks as $lt) {
                $upcomingFollowups[] = [
                    'id' => 'lead_' . $lt->id,
                    'type' => 'LEAD',
                    'title' => $lt->name,
                    'subtitle' => ($lt->lead?->name ?? 'Lead') . ($lt->lead?->subject ? ' · ' . $lt->lead->subject : ''),
                    'time' => $lt->time ? (is_string($lt->time) ? date('h:i A', strtotime($lt->time)) : $lt->time->format('h:i A')) : '10:00 AM',
                ];
            }
            $upcomingDealTasks = DealTask::with(['deal'])->whereDate('date', '>=', today())->orderBy('date', 'asc')->take(4)->get();
            foreach ($upcomingDealTasks as $dt) {
                $upcomingFollowups[] = [
                    'id' => 'deal_' . $dt->id,
                    'type' => 'DEAL',
                    'title' => $dt->name,
                    'subtitle' => ($dt->deal?->name ?? 'Deal'),
                    'time' => $dt->time ? (is_string($dt->time) ? date('h:i A', strtotime($dt->time)) : $dt->time->format('h:i A')) : '11:00 AM',
                ];
            }

            // 6. Top Products (Filtered by date range)
            $topProducts = [];
            if (class_exists(\Automas\ProductService\Models\ProductServiceItem::class)) {
                $products = \Automas\ProductService\Models\ProductServiceItem::where('created_by', creatorId())->get();
                $wonDeals = Deal::where('created_by', creatorId())->where('status', 'Won')->whereBetween('updated_at', [$start, $end])->get();
                if ($wonDeals->isEmpty() && !$request->has('period')) {
                    $wonDeals = Deal::where('created_by', creatorId())->where('status', 'Won')->get();
                }

                foreach ($products as $prod) {
                    $wonValue = 0;
                    foreach ($wonDeals as $wDeal) {
                        $pList = is_array($wDeal->products) ? $wDeal->products : (is_string($wDeal->products) ? json_decode($wDeal->products, true) : []);
                        if (!empty($pList) && (in_array($prod->id, $pList) || in_array((string)$prod->id, $pList) || in_array($prod->name, $pList))) {
                            $wonValue += (float) $wDeal->price;
                        }
                    }
                    if ($wonValue > 0) {
                        $topProducts[] = [
                            'id' => $prod->id,
                            'name' => $prod->name,
                            'wonValue' => $wonValue,
                        ];
                    }
                }
                usort($topProducts, fn($a, $b) => $b['wonValue'] <=> $a['wonValue']);
                $topProducts = array_slice($topProducts, 0, 5);
            }

            // 6.b Top Sellers in Product Category
            // 6.b Top Sellers in Product Category Leaderboard (Real DB Won Deals Calculation)
            $productCategories = [];
            if (class_exists(\Automas\ProductService\Models\ProductServiceCategory::class)) {
                $productCategories = \Automas\ProductService\Models\ProductServiceCategory::where('created_by', creatorId())
                    ->get(['id', 'name', 'color'])
                    ->toArray();
            }

            $sellerUsersList = User::where('created_by', creatorId())
                ->orWhere('id', creatorId())
                ->get(['id', 'name', 'avatar'])
                ->toArray();
            $sellerUsersMap = collect($sellerUsersList)->keyBy('id');

            $wonDealsCategory = Deal::where('created_by', creatorId())
                ->where('status', 'Won')
                ->whereBetween('updated_at', [$start, $end])
                ->with(['users'])
                ->get();
            if ($wonDealsCategory->isEmpty() && !$request->has('period')) {
                $wonDealsCategory = Deal::where('created_by', creatorId())
                    ->where('status', 'Won')
                    ->with(['users'])
                    ->get();
            }

            $productItemsMap = [];
            if (class_exists(\Automas\ProductService\Models\ProductServiceItem::class)) {
                $productItemsMap = \Automas\ProductService\Models\ProductServiceItem::where('created_by', creatorId())
                    ->with('category')
                    ->get()
                    ->keyBy('id');
            }

            $matrixSellers = [];
            $categoryTotals = [];
            $distinctCategories = [];
            $totalProductsSold = 0;
            $totalSalesValue = 0;
            $activeSellersSet = [];
            $categorySellersMap = [];

            foreach ($wonDealsCategory as $wDeal) {
                $dealPrice = (float) $wDeal->price;
                $totalSalesValue += $dealPrice;

                $dealUsers = $wDeal->users->count() > 0 ? $wDeal->users : collect([$sellerUsersMap[$wDeal->created_by] ?? User::find($wDeal->created_by)]);
                $dealUsers = $dealUsers->filter();

                $rawProducts = $wDeal->products;
                if (is_string($rawProducts)) {
                    $rawProducts = json_decode($rawProducts, true) ?: array_filter(array_map('trim', explode(',', $rawProducts)));
                }
                if (!is_array($rawProducts)) {
                    $rawProducts = [];
                }
                $pList = array_values(array_filter($rawProducts));

                if (empty($pList)) {
                    $pCategories = [['id' => 0, 'name' => 'General', 'color' => '#6366f1', 'prodName' => 'General Item']];
                } else {
                    $pCategories = [];
                    foreach ($pList as $pId) {
                        $pObj = is_numeric($pId) ? ($productItemsMap[$pId] ?? null) : null;
                        $cId = $pObj ? ($pObj->category_id ?? 0) : 0;
                        $cName = ($pObj && $pObj->category) ? $pObj->category->name : 'General';
                        $cColor = ($pObj && $pObj->category && $pObj->category->color) ? $pObj->category->color : '#6366f1';
                        $prodName = $pObj ? $pObj->name : (is_string($pId) && !is_numeric($pId) ? $pId : 'Product #' . $pId);
                        $pCategories[] = ['id' => $cId, 'name' => $cName, 'color' => $cColor, 'prodName' => $prodName];
                    }
                }

                $prodCount = max(1, count($pCategories));
                $amountPerProd = $dealPrice / $prodCount;

                foreach ($dealUsers as $u) {
                    if (!$u) continue;
                    $activeSellersSet[$u->id] = true;

                    if (!isset($matrixSellers[$u->id])) {
                        $nameParts = explode(' ', trim($u->name));
                        $initials = '';
                        foreach ($nameParts as $np) {
                            if (!empty($np)) $initials .= strtoupper($np[0]);
                        }
                        $matrixSellers[$u->id] = [
                            'id' => $u->id,
                            'name' => $u->name,
                            'avatar' => substr($initials, 0, 2) ?: 'U',
                            'values' => [],
                            'amounts' => [],
                            'totalAmount' => 0,
                            'totalUnits' => 0,
                        ];
                    }

                    foreach ($pCategories as $catInfo) {
                        $cName = $catInfo['name'];
                        $distinctCategories[$cName] = true;
                        $totalProductsSold += 1;

                        if (!isset($matrixSellers[$u->id]['values'][$cName])) {
                            $matrixSellers[$u->id]['values'][$cName] = 0;
                            $matrixSellers[$u->id]['amounts'][$cName] = 0;
                        }
                        $matrixSellers[$u->id]['values'][$cName] += 1;
                        $matrixSellers[$u->id]['amounts'][$cName] += $amountPerProd;
                        $matrixSellers[$u->id]['totalUnits'] += 1;
                        $matrixSellers[$u->id]['totalAmount'] += $amountPerProd;

                        if (!isset($categoryTotals[$cName])) {
                            $categoryTotals[$cName] = [
                                'totalAmount' => 0,
                                'totalUnits' => 0,
                                'sellers' => [],
                            ];
                        }
                        $categoryTotals[$cName]['totalAmount'] += $amountPerProd;
                        $categoryTotals[$cName]['totalUnits'] += 1;

                        if (!isset($categoryTotals[$cName]['sellers'][$u->id])) {
                            $categoryTotals[$cName]['sellers'][$u->id] = [
                                'name' => $u->name,
                                'units' => 0,
                                'amount' => 0,
                            ];
                        }
                        $categoryTotals[$cName]['sellers'][$u->id]['units'] += 1;
                        $categoryTotals[$cName]['sellers'][$u->id]['amount'] += $amountPerProd;

                        // Maintain categorySellers legacy array
                        $key = $u->id . '_' . $catInfo['id'] . '_' . $cName;
                        if (!isset($categorySellersMap[$key])) {
                            $categorySellersMap[$key] = [
                                'userId' => $u->id,
                                'userName' => $u->name,
                                'userAvatar' => $u->avatar,
                                'categoryId' => $catInfo['id'],
                                'categoryName' => $cName,
                                'categoryColor' => $catInfo['color'],
                                'productName' => $catInfo['prodName'],
                                'dealsCount' => 0,
                                'totalAmount' => 0,
                            ];
                        }
                        $categorySellersMap[$key]['dealsCount'] += 1;
                        $categorySellersMap[$key]['totalAmount'] += $amountPerProd;
                    }
                }
            }

            $sellersList = array_values($matrixSellers);
            usort($sellersList, fn($a, $b) => $b['totalAmount'] <=> $a['totalAmount'] ?: $b['totalUnits'] <=> $a['totalUnits']);

            $badgeColors = ['bg-amber-400 text-white', 'bg-slate-300 text-slate-700 dark:bg-slate-700 dark:text-slate-200', 'bg-amber-600 text-white'];
            $avatarColors = ['bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-300', 'bg-purple-100 text-purple-600 dark:bg-purple-950 dark:text-purple-300', 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300', 'bg-sky-100 text-sky-600 dark:bg-sky-950 dark:text-sky-300', 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'];

            foreach ($sellersList as $idx => &$sRow) {
                $sRow['rank'] = $idx + 1;
                $sRow['badgeColor'] = $badgeColors[$idx] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400';
                $sRow['avatarColor'] = $avatarColors[$idx % count($avatarColors)];
            }

            $categoriesList = array_keys($distinctCategories);
            if (empty($categoriesList) && class_exists(\Automas\ProductService\Models\ProductServiceCategory::class)) {
                $categoriesList = \Automas\ProductService\Models\ProductServiceCategory::where('created_by', creatorId())->pluck('name')->toArray();
            }

            $categoryLeadersList = [];
            foreach ($categoryTotals as $catName => $cData) {
                $sMap = array_values($cData['sellers']);
                usort($sMap, fn($a, $b) => $b['units'] <=> $a['units'] ?: $b['amount'] <=> $a['amount']);
                $topLeader = $sMap[0] ?? null;

                if ($topLeader) {
                    $categoryLeadersList[] = [
                        'category' => $catName,
                        'leader' => $topLeader['name'],
                        'units' => $topLeader['units'] . ' units',
                        'amount' => (float) $topLeader['amount'],
                    ];
                }
            }

            $categoryLeaderboard = [
                'categories' => $categoriesList,
                'sellers' => $sellersList,
                'leaders' => $categoryLeadersList,
                'summary' => [
                    'totalProductsSold' => $totalProductsSold,
                    'totalSalesValue' => (float) $totalSalesValue,
                    'activeSellersCount' => count($activeSellersSet),
                    'productCategoriesCount' => count($categoriesList),
                ],
            ];

            $categorySellers = array_values($categorySellersMap);
            usort($categorySellers, fn($a, $b) => $b['dealsCount'] <=> $a['dealsCount'] ?: $b['totalAmount'] <=> $a['totalAmount']);

            // 7. Calendars: Follow-up Calendar Events & Tasks Calendar Events
            $followupEvents = [];
            $upcomingLeadTasks = LeadTask::with(['lead'])->whereDate('date', '>=', today())->orderBy('date', 'asc')->get();
            foreach ($upcomingLeadTasks as $lt) {
                $dStr = $lt->date ? $lt->date->format('Y-m-d') : now()->format('Y-m-d');
                $followupEvents[] = [
                    'id' => 'lead_f_' . $lt->id,
                    'title' => 'LEAD: ' . $lt->name . ($lt->lead?->name ? ' (' . $lt->lead->name . ')' : ''),
                    'startDate' => $dStr,
                    'endDate' => $dStr,
                    'time' => $lt->time ? (is_string($lt->time) ? $lt->time : $lt->time->format('H:i')) : '10:00',
                    'color' => '#10b981',
                    'type' => 'Lead Follow-up',
                ];
            }
            $upcomingDealTasks = DealTask::with(['deal'])->whereDate('date', '>=', today())->orderBy('date', 'asc')->get();
            foreach ($upcomingDealTasks as $dt) {
                $dStr = $dt->date ? $dt->date->format('Y-m-d') : now()->format('Y-m-d');
                $followupEvents[] = [
                    'id' => 'deal_f_' . $dt->id,
                    'title' => 'DEAL: ' . $dt->name . ($dt->deal?->name ? ' (' . $dt->deal->name . ')' : ''),
                    'startDate' => $dStr,
                    'endDate' => $dStr,
                    'time' => $dt->time ? (is_string($dt->time) ? $dt->time : $dt->time->format('H:i')) : '11:00',
                    'color' => '#3b82f6',
                    'type' => 'Deal Follow-up',
                ];
            }

            $taskEvents = [];
            $allLeadTasks = LeadTask::with(['lead'])->get();
            foreach ($allLeadTasks as $lt) {
                $dStr = $lt->date ? $lt->date->format('Y-m-d') : now()->format('Y-m-d');
                $taskEvents[] = [
                    'id' => 'lead_t_' . $lt->id,
                    'title' => $lt->name,
                    'startDate' => $dStr,
                    'endDate' => $dStr,
                    'time' => $lt->time ? (is_string($lt->time) ? $lt->time : $lt->time->format('H:i')) : '09:00',
                    'status' => $lt->status ? 'completed' : 'pending',
                    'color' => $lt->status ? '#10b981' : '#f59e0b',
                    'type' => 'Lead Task',
                ];
            }
            $allDealTasks = DealTask::with(['deal'])->get();
            foreach ($allDealTasks as $dt) {
                $dStr = $dt->date ? $dt->date->format('Y-m-d') : now()->format('Y-m-d');
                $taskEvents[] = [
                    'id' => 'deal_t_' . $dt->id,
                    'title' => $dt->name,
                    'startDate' => $dStr,
                    'endDate' => $dStr,
                    'time' => $dt->time ? (is_string($dt->time) ? $dt->time : $dt->time->format('H:i')) : '09:00',
                    'status' => $dt->status ? 'completed' : 'pending',
                    'color' => $dt->status ? '#10b981' : '#3b82f6',
                    'type' => 'Deal Task',
                ];
            }

            // 8. Sales Performance Trend
            $salesPerformance = [];
            $diffMonths = max(1, (int) round($start->diffInMonths($end)));
            if ($diffMonths < 2) $diffMonths = 6;
            for ($i = $diffMonths - 1; $i >= 0; $i--) {
                $dt = $end->copy()->subMonths($i);
                $mLabel = $dt->format('M Y');
                $mVal = Deal::where('created_by', creatorId())
                    ->where('status', 'Won')
                    ->whereMonth('updated_at', $dt->month)
                    ->whereYear('updated_at', $dt->year)
                    ->sum('price');
                $salesPerformance[] = [
                    'month' => $mLabel,
                    'value' => (float) $mVal,
                ];
            }

            // 9. Lead Sources breakdown (Filtered by date range)
            $leadSources = [
                ['name' => 'Website', 'count' => Lead::where('created_by', creatorId())->whereBetween('created_at', [$start, $end])->where('sources', 'like', '%website%')->count(), 'color' => '#3b82f6'],
                ['name' => 'Facebook', 'count' => Lead::where('created_by', creatorId())->whereBetween('created_at', [$start, $end])->where('sources', 'like', '%facebook%')->count(), 'color' => '#14b8a6'],
                ['name' => 'Referrals', 'count' => Lead::where('created_by', creatorId())->whereBetween('created_at', [$start, $end])->where('sources', 'like', '%referral%')->count(), 'color' => '#a855f7'],
                ['name' => 'Phone', 'count' => Lead::where('created_by', creatorId())->whereBetween('created_at', [$start, $end])->where('sources', 'like', '%phone%')->count(), 'color' => '#f59e0b'],
                ['name' => 'Other', 'count' => Lead::where('created_by', creatorId())->whereBetween('created_at', [$start, $end])->where(function($q) {
                    $q->whereNull('sources')->orWhere('sources', '');
                })->count(), 'color' => '#94a3b8'],
            ];
            if (array_sum(array_column($leadSources, 'count')) === 0 && !$request->has('period')) {
                $leadSources = [
                    ['name' => 'Website', 'count' => Lead::where('created_by', creatorId())->where('sources', 'like', '%website%')->count(), 'color' => '#3b82f6'],
                    ['name' => 'Facebook', 'count' => Lead::where('created_by', creatorId())->where('sources', 'like', '%facebook%')->count(), 'color' => '#14b8a6'],
                    ['name' => 'Referrals', 'count' => Lead::where('created_by', creatorId())->where('sources', 'like', '%referral%')->count(), 'color' => '#a855f7'],
                    ['name' => 'Phone', 'count' => Lead::where('created_by', creatorId())->where('sources', 'like', '%phone%')->count(), 'color' => '#f59e0b'],
                    ['name' => 'Other', 'count' => Lead::where('created_by', creatorId())->where(function($q) {
                        $q->whereNull('sources')->orWhere('sources', '');
                    })->count(), 'color' => '#94a3b8'],
                ];
            }

            // 10. Win rate & Needs attention metrics (Filtered by date range)
            $totalWonCount = Deal::where('created_by', creatorId())->where('status', 'Won')->whereBetween('updated_at', [$start, $end])->count();
            $totalLostCount = Deal::where('created_by', creatorId())->where('status', 'Loss')->whereBetween('updated_at', [$start, $end])->count();
            if ($totalWonCount === 0 && $totalLostCount === 0 && !$request->has('period')) {
                $totalWonCount = Deal::where('created_by', creatorId())->where('status', 'Won')->count();
                $totalLostCount = Deal::where('created_by', creatorId())->where('status', 'Loss')->count();
            }
            $totalClosedCount = $totalWonCount + $totalLostCount;
            $winRate = $totalClosedCount > 0 ? (int) round(($totalWonCount / $totalClosedCount) * 100) : 0;

            $uncontactedLeads = Lead::where('created_by', creatorId())->whereDoesntHave('calls')->count();
            $unassignedLeads = Lead::where('created_by', creatorId())->whereDoesntHave('userLeads')->count();
            $inactiveDeals = Deal::where('created_by', creatorId())->where('is_active', false)->count();

            $needsAttention = [
                'uncontactedLeads' => $uncontactedLeads,
                'unassignedLeads' => $unassignedLeads,
                'inactiveDeals' => $inactiveDeals,
                'followupLeadsToday' => $followupLeadsToday,
                'overdueLeadTasks' => $overdueLeadTasks,
            ];

            $winRateStats = [
                'winRate' => $winRate,
                'wonCount' => $totalWonCount,
                'lostCount' => $totalLostCount,
            ];

            $pipelines = Pipeline::where('created_by', creatorId())->get(['id', 'name']);

            return Inertia::render('Lead/Dashboard/CompanyDashboard', [
                'stats' => [
                    'totalLeads' => $totalLeads,
                    'monthLeads' => $monthLeads,
                    'todayLeads' => $todayLeads,
                    'yesterdayLeads' => $yesterdayLeads,
                    'thisMonthLeads' => $thisMonthLeads,
                    'prevMonthLeads' => $prevMonthLeads,
                    'todayDeals' => $todayDeals,
                    'yesterdayDeals' => $yesterdayDeals,
                    'thisMonthDeals' => $thisMonthDeals,
                    'prevMonthDeals' => $prevMonthDeals,
                    'avgDailyLeads' => $avgDailyLeads,
                    'activeDeals' => $activeDeals,
                    'openDealValue' => (float) $openDealValue,
                    'wonDealAmount' => (float) $wonDealAmount,
                    'wonDealsThisMonth' => $wonDealsThisMonth,
                    'callsToday' => $callsToday,
                    'connectedCallsToday' => $connectedCallsToday,
                    'missedCallsToday' => $missedCallsToday,
                    'followupsToday' => $followupsToday,
                    'followupLeadsToday' => $followupLeadsToday,
                    'followupDealsToday' => $followupDealsToday,
                    'overdueTasks' => $overdueTasks,
                    'overdueLeadTasks' => $overdueLeadTasks,
                ],
                'leadOverview' => $leadOverview,
                'dealPipeline' => $dealPipeline,
                'dailyLeadTrend' => $dailyLeadTrend,
                'teamPerformance' => $teamPerformance,
                'upcomingFollowups' => $upcomingFollowups,
                'topProducts' => $topProducts,
                'categoryLeaderboard' => $categoryLeaderboard,
                'categorySellers' => $categorySellers,
                'productCategories' => $productCategories,
                'sellerUsers' => $sellerUsersList,
                'salesPerformance' => $salesPerformance,
                'leadSources' => $leadSources,
                'winRateStats' => $winRateStats,
                'needsAttention' => $needsAttention,
                'pipelines' => $pipelines,
                'message' => __('Lead Dashboard - Manage your leads and deals efficiently.'),
            ]);
        }

        return back()->with('error', __('Permission denied'));
    }

    private function clientDashboard(Request $request)
    {
        $user = Auth::user();

        // Get assigned deals for client
        $assignedDealIds = ClientDeal::where('client_id', $user->id)->pluck('deal_id');
        $deals = Deal::whereIn('id', $assignedDealIds);

        // Get all stats from deals
        $totalDeals = $deals->count();
        $activeDealCount = $deals->where('status', 'Active')->count();
        $wonDealCount = $deals->where('status', 'Won')->count();
        $lossDealCount = $deals->where('status', 'Loss')->count();
        $totalDealValue = $deals->sum('price');

        // Recent deals assigned to client
        $recentDeals = $deals->with('stage')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Deal status chart
        $dealStatusChart = [
            ['name' => 'Active', 'value' => $activeDealCount],
            ['name' => 'Won', 'value' => $wonDealCount],
            ['name' => 'Loss', 'value' => $lossDealCount],
        ];

        // Calendar events from assigned deal tasks and lead tasks
        $calendarEvents = [];
        $clientDeals = ClientDeal::where('client_id', $user->id)->with('deal.tasks')->get();
        foreach ($clientDeals as $clientDeal) {
            foreach ($clientDeal->deal->tasks as $task) {
                $calendarEvents[] = [
                    'id' => 'deal_' . $task->id,
                    'title' => $task->name,
                    'startDate' => $task->date->format('Y-m-d'),
                    'endDate' => $task->date->format('Y-m-d'),
                    'time' => $task->time ? $task->time->format('H:i') : '09:00',
                    'status' => $task->status ? 'completed' : 'pending',
                    'name' => $clientDeal->deal->name,
                    'color' => $task->status ? '#10b981' : '#f59e0b',
                    'type' => 'Deal Task',
                ];
            }
        }

        return Inertia::render('Lead/Dashboard/ClientDashboard', [
            'stats' => [
                'total_deals' => $totalDeals,
                'active_deals' => $activeDealCount,
                'won_deals' => $wonDealCount,
                'total_value' => $totalDealValue,
            ],
            'recentDeals' => $recentDeals,
            'calendarEvents' => $calendarEvents,
            'dealStatusChart' => $dealStatusChart,
            'message' => __('Client Dashboard - View your assigned deals.'),
        ]);
    }

    private function userDashboard(Request $request)
    {
        $user = Auth::user();

        // leads, deals, calls

        // Get assigned deals and leads for user
        $assignedDealIds = UserDeal::where('user_id', $user->id)->pluck('deal_id');
        $assignedLeadIds = UserLead::where('user_id', $user->id)->pluck('lead_id');

        // Lead stats (assigned to user)
        $assignedLeads = Lead::whereIn('id', $assignedLeadIds);
        $todayLeads = (clone $assignedLeads)->whereDate('created_at', now()->toDateString())->count();
        $yesterdayLeads = (clone $assignedLeads)->whereDate('created_at', now()->subDay()->toDateString())->count();
        $monthlyLeads = (clone $assignedLeads)->whereMonth('created_at', now()->month)->count();
        $totalLeads = (clone $assignedLeads)->count();
        $avgDailyLeads = round($monthlyLeads / max(1, now()->day), 1);

        // Deal stats (assigned to user)
        $assignedDeals = Deal::whereIn('id', $assignedDealIds);
        $convertedDeals = (clone $assignedLeads)->where('is_converted', '>', 0)->count();
        $activeDeals = (clone $assignedDeals)->where('status', 'Active')->count();
        $wonDeals = (clone $assignedDeals)->where('status', 'Won')->count();
        $lostDeals = (clone $assignedDeals)->where('status', 'Loss')->count();

        // Call stats (calls for assigned deals/leads or created by user)
        $dealCalls = DealCall::whereIn('deal_id', $assignedDealIds);
        $leadCalls = LeadCall::whereIn('lead_id', $assignedLeadIds);
        $todayCalls = (clone $dealCalls)->whereDate('created_at', today())->count() + (clone $leadCalls)->whereDate('created_at', today())->count();
        $yesterdayCalls = (clone $dealCalls)->whereDate('created_at', today()->subDay())->count() + (clone $leadCalls)->whereDate('created_at', today()->subDay())->count();
        $monthlyCalls = (clone $dealCalls)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() + (clone $leadCalls)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $totalCalls = (clone $dealCalls)->count() + (clone $leadCalls)->count();

        // Task statistics
        $completedTasks = DealTask::whereIn('deal_id', $assignedDealIds)
            ->where('status', 1)->count() +
            LeadTask::whereIn('lead_id', $assignedLeadIds)
                ->where('status', 1)->count();

        $pendingTasks = DealTask::whereIn('deal_id', $assignedDealIds)
            ->where('status', 0)->count() +
            LeadTask::whereIn('lead_id', $assignedLeadIds)
                ->where('status', 0)->count();

        // Recent assigned deals
        $recentDeals = Deal::whereIn('id', $assignedDealIds)
            ->with('stage')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent assigned leads
        $recentLeads = Lead::whereIn('id', $assignedLeadIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'name', 'subject', 'created_at']);

        // Calendar events from assigned tasks
        $calendarEvents = [];
        $followupEvents = [];

        // Upcoming follow-up tasks for assigned leads
        $upcomingLeadTasks = LeadTask::whereIn('lead_id', $assignedLeadIds)
            ->whereDate('date', '>=', today())
            ->orderBy('date', 'asc')
            ->get();
        foreach ($upcomingLeadTasks as $lt) {
            $dStr = $lt->date ? $lt->date->format('Y-m-d') : now()->format('Y-m-d');
            $followupEvents[] = [
                'id' => 'lead_f_' . $lt->id,
                'title' => 'LEAD: ' . $lt->name,
                'startDate' => $dStr,
                'endDate' => $dStr,
                'time' => $lt->time ? (is_string($lt->time) ? $lt->time : $lt->time->format('H:i')) : '10:00',
                'color' => '#10b981',
                'type' => 'Lead Follow-up',
                'subtitle' => $lt->lead?->name ? $lt->lead->name : 'Lead Task',
            ];
        }

        // Upcoming follow-up tasks for assigned deals
        $upcomingDealTasks = DealTask::whereIn('deal_id', $assignedDealIds)
            ->whereDate('date', '>=', today())
            ->orderBy('date', 'asc')
            ->get();
        foreach ($upcomingDealTasks as $dt) {
            $dStr = $dt->date ? $dt->date->format('Y-m-d') : now()->format('Y-m-d');
            $followupEvents[] = [
                'id' => 'deal_f_' . $dt->id,
                'title' => 'DEAL: ' . $dt->name,
                'startDate' => $dStr,
                'endDate' => $dStr,
                'time' => $dt->time ? (is_string($dt->time) ? $dt->time : $dt->time->format('H:i')) : '11:00',
                'color' => '#3b82f6',
                'type' => 'Deal Follow-up',
                'subtitle' => $dt->deal?->name ? $dt->deal->name : 'Deal Task',
            ];
        }

        // Deal tasks
        $userDeals = UserDeal::where('user_id', $user->id)->with('deal.tasks')->get();
        foreach ($userDeals as $userDeal) {
            foreach ($userDeal->deal->tasks as $task) {
                $calendarEvents[] = [
                    'id' => 'deal_' . $task->id,
                    'title' => $task->name,
                    'startDate' => $task->date->format('Y-m-d'),
                    'endDate' => $task->date->format('Y-m-d'),
                    'time' => $task->time ? $task->time->format('H:i') : '09:00',
                    'status' => $task->status ? 'completed' : 'pending',
                    'name' => $userDeal->deal->name,
                    'color' => $task->status ? '#10b981' : '#f59e0b',
                    'type' => 'Deal Task',
                    'subtitle' => $userDeal->deal->name,
                ];
            }
        }

        // Lead tasks
        $userLeads = UserLead::where('user_id', $user->id)->with('lead.tasks')->get();
        foreach ($userLeads as $userLead) {
            foreach ($userLead->lead->tasks as $task) {
                $calendarEvents[] = [
                    'id' => 'lead_' . $task->id,
                    'title' => $task->name,
                    'startDate' => $task->date->format('Y-m-d'),
                    'endDate' => $task->date->format('Y-m-d'),
                    'time' => $task->time ? $task->time->format('H:i') : '09:00',
                    'status' => $task->status ? 'completed' : 'pending',
                    'name' => $userLead->lead->name,
                    'color' => $task->status ? '#10b981' : '#3b82f6',
                    'type' => 'Lead Task',
                    'subtitle' => $userLead->lead->name,
                ];
            }
        }

        // Total amount from assigned deals
        $totalAmount = Deal::whereIn('id', $assignedDealIds)->sum('price');

        // Task status chart
        $taskStatusChart = [
            ['name' => 'Completed', 'value' => $completedTasks],
            ['name' => 'Pending', 'value' => $pendingTasks],
        ];

        return Inertia::render('Lead/Dashboard/UserDashboard', [
            'stats' => [
                'todayLeads' => $todayLeads,
                'yesterdayLeads' => $yesterdayLeads,
                'avgDailyLeads' => $avgDailyLeads,
                'monthlyLeads' => $monthlyLeads,
                'totalLeads' => $totalLeads,

                'convertedDeals' => $convertedDeals,
                'activeDeals' => $activeDeals,
                'wonDeals' => $wonDeals,
                'lostDeals' => $lostDeals,

                'todayCalls' => $todayCalls,
                'yesterdayCalls' => $yesterdayCalls,
                'monthlyCalls' => $monthlyCalls,
                'totalCalls' => $totalCalls,
                'completedTasks' => $completedTasks,
                'pendingTasks' => $pendingTasks,
                'totalAmount' => $totalAmount,
            ],
            'recentDeals' => $recentDeals,
            'recentLeads' => $recentLeads,
            'calendarEvents' => $calendarEvents,
            'followupEvents' => $followupEvents,
            'taskStatusChart' => $taskStatusChart,
            'message' => __('User Dashboard - View your assigned leads and deals.'),
        ]);
    }
}
