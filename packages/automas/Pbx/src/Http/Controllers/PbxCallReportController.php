<?php

namespace Automas\Pbx\Http\Controllers;

use App\Http\Controllers\Controller;
use Automas\Pbx\Models\PbxExtension;
use Automas\Pbx\Models\PbxSetting;
use Automas\Pbx\Services\PbxCallReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class PbxCallReportController extends Controller
{
    public function __construct(
        protected PbxCallReportService $callReportService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $canViewAll = $user->can('view all call logs');
        $canViewOwn = $user->can('view own call logs');

        abort_unless(
            $canViewAll || $canViewOwn,
            403,
            'You do not have permission to view call logs.'
        );

        /*
        |--------------------------------------------------------------------------
        | PBX Setting
        |--------------------------------------------------------------------------
        */

        $setting = PbxSetting::query()
            ->where('created_by', creatorId())
            ->where('is_enabled', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Accessible Extensions
        |--------------------------------------------------------------------------
        |
        | view all call logs:
        |     All active company extensions.
        |
        | view own call logs:
        |     Only extension(s) assigned to current user.
        |
        */

        $extensionsQuery = PbxExtension::query()
            ->with('user:id,name')
            ->where('created_by', creatorId())
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhere('is_active', 1)
                    ->orWhereNull('is_active');
            });

        if (!$canViewAll) {
            $extensionsQuery->where(
                'user_id',
                $user->id
            );
        }

        $extensions = $extensionsQuery
            ->orderBy('extension')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input('search', '')
        );

        $selectedExtension = trim(
            (string) $request->input('extension', '')
        );
        if (strtolower($selectedExtension) === 'all') {
            $selectedExtension = '';
        }

        $direction = trim(
            (string) $request->input('call_direction', $request->input('direction', ''))
        );
        if (strtolower($direction) === 'all') {
            $direction = '';
        }

        $status = trim(
            (string) $request->input('status', '')
        );
        if (strtolower($status) === 'all') {
            $status = '';
        }

        /*
        |--------------------------------------------------------------------------
        | Date Period Resolution (Authoritative Centralized Backend Resolver)
        |--------------------------------------------------------------------------
        */

        $period = $request->input('period');
        $from = $request->input('from');
        $to = $request->input('to');
        $dateRange = $request->input('date_range');

        $resolvedPeriod = $this->callReportService->resolveReportPeriod(
            $period,
            $from,
            $to,
            $dateRange
        );

        $from = $resolvedPeriod['from'];
        $to = $resolvedPeriod['to'];
        $period = $resolvedPeriod['period'];
        $dateRange = $resolvedPeriod['date_range'];

        /*
        |--------------------------------------------------------------------------
        | Validate Selected Extension
        |--------------------------------------------------------------------------
        |
        | Never trust extension coming from query string.
        |
        */

        if ($selectedExtension !== '') {
            $extensionAllowed = $extensions->contains(
                fn($extension) =>
                (string) $extension->extension ===
                    $selectedExtension
            );

            abort_unless(
                $extensionAllowed,
                403,
                'You do not have permission to view this extension.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Extensions Sent To PBX
        |--------------------------------------------------------------------------
        */

        $pbxSearch = $search ?: null;

        if ($selectedExtension !== '') {
            $extensionNumbers = [
                $selectedExtension,
            ];
        } else {
            // Check if search query matches an ERP agent/user name
            if ($search !== '') {
                $hasPhoneDigits = preg_match('/[0-9]{3,}/', $search);
                $matchingAgentExtensions = $extensions->filter(function ($ext) use ($search) {
                    $userName = (string) ($ext->user?->name ?? '');
                    $callerId = (string) ($ext->caller_id ?? '');
                    return ($userName !== '' && stripos($userName, $search) !== false)
                        || ($callerId !== '' && stripos($callerId, $search) !== false);
                });

                // Only isolate extensions if search strictly matched agent name (non-phone search)
                if ($matchingAgentExtensions->isNotEmpty() && !$hasPhoneDigits) {
                    $extensionNumbers = $matchingAgentExtensions
                        ->pluck('extension')
                        ->filter()
                        ->map(fn($extension) => (string) $extension)
                        ->values()
                        ->all();

                    $pbxSearch = null;
                } else {
                    $extensionNumbers = $extensions
                        ->pluck('extension')
                        ->filter()
                        ->map(fn($extension) => (string) $extension)
                        ->values()
                        ->all();
                }
            } else {
                $extensionNumbers = $extensions
                    ->pluck('extension')
                    ->filter()
                    ->map(fn($extension) => (string) $extension)
                    ->values()
                    ->all();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $page = max(
            $request->integer('page', 1),
            1
        );

        $perPage = min(
            max(
                $request->integer('per_page', 10),
                10
            ),
            100
        );

        /*
        |--------------------------------------------------------------------------
        | Fetch PBX Report
        |--------------------------------------------------------------------------
        */

        $result = $this->callReportService
            ->getCallLogs(
                $setting,
                $extensionNumbers,
                [
                    'search' => $pbxSearch,

                    'from' => $from,
                    'to' => $to,

                    'direction' =>
                    $direction ?: null,

                    'status' =>
                    $status ?: null,

                    'page' => $page,
                    'per_page' => $perPage,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Extension => ERP User Map
        |--------------------------------------------------------------------------
        */

        $extensionMap = $extensions->keyBy(
            fn($extension) =>
            (string) $extension->extension
        );

        /*
        |--------------------------------------------------------------------------
        | Format Current Page
        |--------------------------------------------------------------------------
        */

        $callRows = collect(
            $result['data'] ?? []
        )
            ->map(
                function (array $call) use (
                    $extensionMap
                ) {
                    $extensionNumber =
                        (string) (
                            $call['extension']
                            ?? ''
                        );

                    $extension =
                        $extensionMap->get(
                            $extensionNumber
                        );

                    return [
                        ...$call,

                        'extension' =>
                        $extensionNumber,

                        'user_id' =>
                        $extension?->user_id,

                        'user_name' =>
                        $extension
                            ?->user
                            ?->name,

                        'recording_url' => null,
                    ];
                }
            )
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Pagination Information From PBX (Authoritative Server-side Pagination)
        |--------------------------------------------------------------------------
        */

        $remotePagination =
            $result['pagination'] ?? [];

        $currentPage = max(
            (int) (
                $remotePagination['page']
                ?? $page
            ),
            1
        );

        $currentPerPage = max(
            (int) (
                $remotePagination['per_page']
                ?? $perPage
            ),
            1
        );

        $total = max(
            (int) (
                $remotePagination['total']
                ?? 0
            ),
            0
        );

        $lastPage = max(
            (int) (
                $remotePagination['last_page']
                ?? 1
            ),
            1
        );

        /*
        |--------------------------------------------------------------------------
        | WorkDo-Compatible Paginator
        |--------------------------------------------------------------------------
        |
        | We provide both Laravel-style top-level pagination properties and
        | meta so the shared Pagination component has everything it may need.
        |
        */

        $activeParams = [
            'period' => $period,
            'per_page' => $perPage,
        ];

        if ($period === 'custom') {
            $activeParams['from'] = $from;
            $activeParams['to'] = $to;
            $activeParams['date_range'] = $dateRange;
        } elseif ($period !== 'all_time') {
            $activeParams['from'] = $from;
            $activeParams['to'] = $to;
        }

        if ($search !== '') {
            $activeParams['search'] = $search;
        }
        if ($selectedExtension !== '') {
            $activeParams['extension'] = $selectedExtension;
        }
        if ($direction !== '') {
            $activeParams['call_direction'] = $direction;
        }
        if ($status !== '') {
            $activeParams['status'] = $status;
        }

        $calls = $this->buildPaginatorPayload(
            $callRows,
            $currentPage,
            $currentPerPage,
            $total,
            $lastPage,
            $request,
            $activeParams
        );

        /*
        |--------------------------------------------------------------------------
        | Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Pbx/CallReports/Index',
            [
                'calls' => $calls,

                'extensions' =>
                $extensions
                    ->map(
                        fn($extension) => [
                            'id' =>
                            $extension->id,

                            'extension' =>
                            (string)
                            $extension->extension,

                            'user_id' =>
                            $extension->user_id,

                            'user_name' =>
                            $extension
                                ?->user
                                ?->name,
                        ]
                    )
                    ->values(),

                'filters' => [
                    'search' =>
                    $search,

                    'extension' =>
                    $selectedExtension,

                    'call_direction' =>
                    $direction,

                    'status' =>
                    $status,

                    'period' =>
                    $period,

                    'from' =>
                    $from,

                    'to' =>
                    $to,

                    'date_range' =>
                    $dateRange,
                ],

                'callReportPermissions' => [
                    'view_all' =>
                    $canViewAll,

                    'view_own' =>
                    $canViewOwn,
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Call Summary & Analytics Dashboard
    |--------------------------------------------------------------------------
    */

    public function summary(Request $request)
    {
        $user = Auth::user();

        $canViewAll = $user->can('view all call logs');
        $canViewOwn = $user->can('view own call logs');

        abort_unless(
            $canViewAll || $canViewOwn,
            403,
            'You do not have permission to view call reports.'
        );

        $setting = PbxSetting::query()
            ->where('created_by', creatorId())
            ->where('is_enabled', true)
            ->firstOrFail();

        $extensionsQuery = PbxExtension::query()
            ->with('user:id,name')
            ->where('created_by', creatorId())
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhere('is_active', 1)
                    ->orWhereNull('is_active');
            });

        if (!$canViewAll) {
            $extensionsQuery->where('user_id', $user->id);
        }

        $extensions = $extensionsQuery
            ->orderBy('extension')
            ->get();

        $selectedExtension = trim((string) $request->input('extension', ''));
        if (strtolower($selectedExtension) === 'all') {
            $selectedExtension = '';
        }

        $direction = trim((string) $request->input('call_direction', $request->input('direction', '')));
        if (strtolower($direction) === 'all') {
            $direction = '';
        }

        $status = trim((string) $request->input('status', ''));
        if (strtolower($status) === 'all') {
            $status = '';
        }

        $search = trim((string) $request->input('search', ''));

        $dateRange = $request->input('date_range');
        $from = $request->input('from');
        $to = $request->input('to');
        $period = $request->input('period');

        $resolvedPeriod = $this->callReportService->resolveReportPeriod(
            $period,
            $from,
            $to,
            $dateRange
        );

        $from = $resolvedPeriod['from'];
        $to = $resolvedPeriod['to'];
        $period = $resolvedPeriod['period'];
        $dateRange = $resolvedPeriod['date_range'];

        if ($selectedExtension !== '') {
            $extensionAllowed = $extensions->contains(
                fn($extension) => (string) $extension->extension === $selectedExtension
            );

            abort_unless(
                $extensionAllowed,
                403,
                'You do not have permission to view this extension.'
            );

            $extensionNumbers = [$selectedExtension];
        } else {
            $extensionNumbers = $extensions
                ->pluck('extension')
                ->filter()
                ->map(fn($extension) => (string) $extension)
                ->values()
                ->all();
        }

        $result = $this->callReportService->getCallSummary(
            $setting,
            $extensionNumbers,
            [
                'from' => $from,
                'to' => $to,
                'extension' => $selectedExtension,
                'direction' => $direction ?: null,
                'status' => $status ?: null,
                'search' => $search ?: null,
            ]
        );

        $summary = array_merge($this->callReportService->emptySummary(), $result['summary'] ?? []);
        $charts = $this->callReportService->buildChartsDataFromSummary($result, $extensions->loadMissing('user')->all(), $selectedExtension);

        return Inertia::render(
            'Pbx/CallReports/Summary',
            [
                'summary' => $summary,
                'charts' => $charts,
                'extensions' => $extensions
                    ->map(
                        fn($extension) => [
                            'id' => $extension->id,
                            'extension' => (string) $extension->extension,
                            'user_id' => $extension->user_id,
                            'user_name' => $extension?->user?->name,
                        ]
                    )
                    ->values(),
                'filters' => [
                    'extension' => $selectedExtension,
                    'call_direction' => $direction,
                    'status' => $status,
                    'search' => $search,
                    'period' => $period,
                    'from' => $from,
                    'to' => $to,
                    'date_range' => $dateRange,
                ],
                'callReportPermissions' => [
                    'view_all' => $canViewAll,
                    'view_own' => $canViewOwn,
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Recording
    |--------------------------------------------------------------------------
    */

    public function recording(Request $request)
    {
        $user = Auth::user();

        $canViewAll =
            $user->can(
                'view all call logs'
            );

        $canViewOwn =
            $user->can(
                'view own call logs'
            );

        abort_unless(
            $canViewAll || $canViewOwn,
            403,
            'You do not have permission to access call recordings.'
        );

        $validated =
            $request->validate([
                'linkedid' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'extension' => [
                    'required',
                    'string',
                    'max:20',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | PBX Setting
        |--------------------------------------------------------------------------
        */

        $setting = PbxSetting::query()
            ->where(
                'created_by',
                creatorId()
            )
            ->where(
                'is_enabled',
                true
            )
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Verify Extension Access
        |--------------------------------------------------------------------------
        */

        $extensionQuery =
            PbxExtension::query()
            ->where(
                'created_by',
                creatorId()
            )
            ->where(
                'extension',
                $validated['extension']
            )
            ->where(
                'is_active',
                true
            );

        if (!$canViewAll) {
            $extensionQuery->where(
                'user_id',
                $user->id
            );
        }

        $extension =
            $extensionQuery
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Configuration
        |--------------------------------------------------------------------------
        */

        abort_if(
            empty($setting
                ->call_report_api_url),
            500,
            'Call report API URL is not configured.'
        );

        abort_if(
            empty($setting
                ->call_report_api_key),
            500,
            'Call report API key is not configured.'
        );

        /*
        |--------------------------------------------------------------------------
        | Recording API
        |--------------------------------------------------------------------------
        */

        $url = rtrim(
            $setting
                ->call_report_api_url,
            '/'
        ) . '/recording.php';

        $response = Http::withHeaders([
            'X-API-Key' =>
            $setting
                ->call_report_api_key,

            'Accept' => '*/*',
        ])
            ->connectTimeout(5)
            ->timeout(60)
            ->get(
                $url,
                [
                    'linkedid' =>
                    $validated['linkedid'],

                    'extension' =>
                    $extension->extension,
                ]
            );

        if ($response->failed()) {
            abort(
                $response->status() === 404
                    ? 404
                    : 502,
                'Recording could not be loaded.'
            );
        }

        $body =
            $response->body();

        $contentType =
            $response->header(
                'Content-Type'
            )
            ?? 'application/octet-stream';

        return response(
            $body,
            200,
            [
                'Content-Type' =>
                $contentType,

                'Content-Length' =>
                strlen($body),

                'Content-Disposition' =>
                'inline',

                'Cache-Control' =>
                'private, no-store, no-cache, must-revalidate',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WorkDo Paginator Payload
    |--------------------------------------------------------------------------
    */

    private function buildPaginatorPayload(
        array $data,
        int $page,
        int $perPage,
        int $total,
        int $lastPage,
        Request $request,
        array $activeFilters = []
    ): array {
        $from = $total > 0
            ? (($page - 1) * $perPage) + 1
            : null;

        $to = $total > 0
            ? min(
                $page * $perPage,
                $total
            )
            : null;

        $links = [];

        $links[] = [
            'url' =>
            $page > 1
                ? $this->pageUrl(
                    $request,
                    $page - 1,
                    $activeFilters
                )
                : null,

            'label' => '&laquo; Previous',

            'active' => false,
        ];

        /*
         * Keep pagination compact.
         */

        $startPage = max(
            1,
            $page - 2
        );

        $endPage = min(
            $lastPage,
            $page + 2
        );

        if ($startPage > 1) {
            $links[] = [
                'url' =>
                $this->pageUrl(
                    $request,
                    1,
                    $activeFilters
                ),

                'label' => '1',

                'active' =>
                $page === 1,
            ];

            if ($startPage > 2) {
                $links[] = [
                    'url' => null,
                    'label' => '...',
                    'active' => false,
                ];
            }
        }

        for (
            $number = $startPage;
            $number <= $endPage;
            $number++
        ) {
            $links[] = [
                'url' =>
                $this->pageUrl(
                    $request,
                    $number,
                    $activeFilters
                ),

                'label' =>
                (string) $number,

                'active' =>
                $number === $page,
            ];
        }

        if ($endPage < $lastPage) {
            if (
                $endPage
                < $lastPage - 1
            ) {
                $links[] = [
                    'url' => null,
                    'label' => '...',
                    'active' => false,
                ];
            }

            $links[] = [
                'url' =>
                $this->pageUrl(
                    $request,
                    $lastPage,
                    $activeFilters
                ),

                'label' =>
                (string) $lastPage,

                'active' =>
                $page === $lastPage,
            ];
        }

        $links[] = [
            'url' =>
            $page < $lastPage
                ? $this->pageUrl(
                    $request,
                    $page + 1,
                    $activeFilters
                )
                : null,

            'label' => 'Next &raquo;',

            'active' => false,
        ];

        return [
            'data' => $data,

            'current_page' =>
            $page,

            'first_page_url' =>
            $this->pageUrl(
                $request,
                1,
                $activeFilters
            ),

            'from' => $from,

            'last_page' =>
            $lastPage,

            'last_page_url' =>
            $this->pageUrl(
                $request,
                $lastPage,
                $activeFilters
            ),

            'links' => $links,

            'next_page_url' =>
            $page < $lastPage
                ? $this->pageUrl(
                    $request,
                    $page + 1,
                    $activeFilters
                )
                : null,

            'path' =>
            $request->url(),

            'per_page' =>
            $perPage,

            'prev_page_url' =>
            $page > 1
                ? $this->pageUrl(
                    $request,
                    $page - 1,
                    $activeFilters
                )
                : null,

            'to' => $to,

            'total' => $total,

            /*
             * Also provide meta for components
             * using API Resource paginator format.
             */
            'meta' => [
                'current_page' =>
                $page,

                'from' =>
                $from,

                'last_page' =>
                $lastPage,

                'links' =>
                $links,

                'path' =>
                $request->url(),

                'per_page' =>
                $perPage,

                'to' =>
                $to,

                'total' =>
                $total,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Page URL
    |--------------------------------------------------------------------------
    */

    private function pageUrl(
        Request $request,
        int $page,
        array $activeFilters = []
    ): string {
        $query = array_merge($activeFilters, $request->query());

        $query['page'] = $page;

        $cleanQuery = array_filter($query, fn($v) => $v !== null && $v !== '');

        return $request->url()
            . '?'
            . http_build_query($cleanQuery);
    }

}
