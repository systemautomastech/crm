<?php

namespace Automas\SmartReports\Database\Seeders;

use Illuminate\Database\Seeder;
use Automas\LandingPage\Models\MarketplaceSetting;
use Illuminate\Support\Facades\File;

class MarketplaceSettingSeeder extends Seeder
{
    public function run()
    {
        $marketplaceDir = __DIR__ . '/../../marketplace';
        $screenshots = [];

        if (File::exists($marketplaceDir)) {
            $files = File::files($marketplaceDir);
            foreach ($files as $file) {
                if (in_array($file->getExtension(), ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                    $screenshots[] = '/packages/automas/SmartReports/src/marketplace/' . $file->getFilename();
                }
            }
        }

        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'SmartReports'], [
            'module' => 'SmartReports',
            'title' => 'Smart Reports - Pre-Built Business Intelligence Reports',
            'subtitle' => 'Comprehensive pre-built reporting platform with smart filters covering HR, Sales, Purchase, CRM, Inventory, and Project modules. Generate actionable insights instantly with attendance summaries, payroll sheets, invoice tracking, deal pipelines, stock analysis, and project progress reports — all exportable to PDF and Excel.',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Smart Reports Module for Automas ERP',
                        'subtitle' => 'Generate pre-built business intelligence reports across all modules with smart filters, visual charts, and instant PDF/Excel export capabilities.',
                        'primary_button_text' => 'Install Smart Reports Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'View Features',
                        'secondary_button_link' => '#features',
                        'image' => '/packages/automas/SmartReports/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Smart Reports Module',
                        'subtitle' => 'Pre-built reports with smart filters for HR, Sales, Purchase, CRM, Inventory and Project modules'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Complete Business Intelligence Reporting Solution',
                        'description' => 'Smart Reports delivers 8 pre-built report types spanning your entire business — from HR attendance and payroll to sales invoices, purchase tracking, CRM deal pipelines, inventory stock levels, and project progress — all with smart filters, summary cards, charts, and export options.',
                        'subSections' => [
                            [
                                'title' => 'Attendance Summary Report',
                                'description' => 'Comprehensive employee attendance tracking with calendar view, table view, and smart filters. Monitor clock-in/out times, overtime, late arrivals, leave records, and attendance rates across departments and date ranges with grouped employee summaries and exportable data.',
                                'keyPoints' => [
                                    'Calendar and table view toggle for flexible attendance visualization',
                                    'Department and employee filters with date range selection',
                                    'Automatic leave record integration with approved leave tracking',
                                    'Grouped employee rows with expandable daily detail records',
                                ],
                                'screenshot' => '/packages/automas/SmartReports/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Payroll Sheet Report',
                                'description' => 'Detailed payroll breakdown with allowances, deductions, loans, and net pay per employee. Visualize salary comparisons with interactive line charts, filter by payroll period or department, and export grouped payroll data with complete breakdown for accounting and HR teams.',
                                'keyPoints' => [
                                    'Employee salary breakdown with gross pay, deductions, and net pay',
                                    'Allowances, manual overtimes, and attendance overtime tracking',
                                    'Filter by payroll period, employee, and department',
                                    'Grid and chart view modes with paginated employee cards',
                                ],
                                'screenshot' => '/packages/automas/SmartReports/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Leave Report',
                                'description' => 'Track employee leave applications with leave type, status, and balance analysis. Filter by leave type, department, date range, and approval status with summary statistics showing total leave days, approved leaves, and department-wise leave distribution.',
                                'keyPoints' => [
                                    'Leave application tracking with type, status, and date range filters',
                                    'Approved, pending, and rejected leave status breakdown',
                                    'Leave balance and consumption tracking per employee',
                                    'Export to PDF and Excel with grouped leave data',
                                ],
                                'screenshot' => '/packages/automas/SmartReports/src/marketplace/image3.png'
                            ],
                            [
                                'title' => 'Sales & Purchase Invoice Reports',
                                'description' => 'Dual invoice reporting covering both sales and purchase cycles. Track invoices by customer or vendor with total amount, paid amount, balance due, and collection percentage. Area charts visualize payment trends by customer, warehouse, or monthly period with expandable invoice detail rows.',
                                'keyPoints' => [
                                    'Group invoices by customer/vendor, warehouse, or monthly period',
                                    'Paid, partial, draft, and overdue status with color-coded badges',
                                    'Area chart visualization for payment trend analysis',
                                    'PDF and Excel export with grouped summary and detail columns',
                                ],
                                'screenshot' => '/packages/automas/SmartReports/src/marketplace/image4.png'
                            ],
                            [
                                'title' => 'Deal Pipeline & Product Stock Reports',
                                'description' => 'CRM deal pipeline analysis with stage tracking, win/loss rates, and deal values alongside inventory stock overview with warehouse-wise quantities, stock values, and low-stock alerts. Both reports support smart filters and visual charts for business decision-making.',
                                'keyPoints' => [
                                    'Deal pipeline stages with won, lost, and active deal tracking',
                                    'Assigned-to and pipeline filters for sales team analysis',
                                    'Product stock levels with warehouse-wise breakdown',
                                    'Visual charts for pipeline funnel and stock distribution',
                                    'Export reports with complete deal and inventory data',
                                ],
                                'screenshot' => '/packages/automas/SmartReports/src/marketplace/image5.png'
                            ],
                        
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Smart Reports in Action',
                        'subtitle' => 'See pre-built reports with smart filters, charts, and export capabilities across all business modules',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Smart Reports Module?',
                        'subtitle' => 'Instant business intelligence across all modules without custom report building',
                        'benefits' => [
                            [
                                'title' => '8 Pre-Built Reports',
                                'description' => 'Ready-to-use reports covering HR, Sales, Purchase, CRM, Inventory, and Projects.',
                                'icon' => 'BarChart3',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Smart Filters',
                                'description' => 'Date range, department, employee, status, and module-specific filter options.',
                                'icon' => 'Filter',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'PDF & Excel Export',
                                'description' => 'Export any report to PDF or Excel with grouped summaries and detail rows.',
                                'icon' => 'Download',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Visual Charts',
                                'description' => 'Area, bar, and line charts for trend analysis and data visualization.',
                                'icon' => 'TrendingUp',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Summary Cards',
                                'description' => 'At-a-glance KPI cards with totals, rates, and key metrics per report.',
                                'icon' => 'LayoutGrid',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Multi-Module Coverage',
                                'description' => 'Single module covering HRM, Sales, Purchase, CRM, Inventory and Taskly data.',
                                'icon' => 'Layers',
                                'color' => 'indigo'
                            ]
                        ]
                    ]
                ],
                'section_visibility' => [
                    'header' => true,
                    'hero' => true,
                    'modules' => true,
                    'dedication' => true,
                    'screenshots' => true,
                    'why_choose' => true,
                    'cta' => true,
                    'footer' => true
                ],
                'section_order' => ['header', 'hero', 'modules', 'dedication', 'screenshots', 'why_choose', 'cta', 'footer']
            ]
        ]);
    }
}
