<?php

namespace Automas\DoubleEntry\Database\Seeders;

use Illuminate\Database\Seeder;
use Automas\LandingPage\Models\MarketplaceSetting;
use Illuminate\Support\Facades\File;

class MarketplaceSettingSeeder extends Seeder
{
    public function run()
    {
        // Get all available screenshots from marketplace directory
        $marketplaceDir = __DIR__ . '/../../marketplace';
        $screenshots = [];
        
        if (File::exists($marketplaceDir)) {
            $files = File::files($marketplaceDir);
            foreach ($files as $file) {
                if (in_array($file->getExtension(), ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                    $screenshots[] = '/packages/automas/DoubleEntry/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'DoubleEntry'], [
            'module' => 'DoubleEntry',
            'title' => 'Double Entry Module Marketplace',
            'subtitle' => 'Comprehensive double-entry accounting and financial reporting tools',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Double Entry Module for Automas ERP',
                        'subtitle' => 'Complete double-entry accounting system with balance sheets, profit & loss statements, and comprehensive financial reports.',
                        'primary_button_text' => 'Install Double Entry Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/DoubleEntry/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Double Entry Module',
                        'subtitle' => 'Professional accounting with powerful financial reporting tools'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Dedicated Double Entry Features',
                        'description' => 'Our double entry module provides comprehensive accounting capabilities for professional financial management.',
                        'subSections' => [
                            [
                                'title' => 'Balance Sheet & Year-End Close',
                                'description' => 'Generate comprehensive balance sheets with detailed asset, liability, and equity tracking. Perform year-end closing procedures with automated journal entries and comparative analysis across multiple periods.',
                                'keyPoints' => ['Automated balance sheet generation', 'Year-end closing process', 'Comparative balance sheets', 'Balance sheet notes and annotations'],
                                'screenshot' => '/packages/automas/DoubleEntry/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Profit & Loss and Trial Balance',
                                'description' => 'Comprehensive profit and loss statements with detailed revenue and expense tracking. Generate trial balance reports to verify ledger accuracy and ensure debits equal credits before financial statement preparation.',
                                'keyPoints' => ['Detailed P&L statements', 'Trial balance verification', 'Period-based reporting', 'Revenue and expense analysis'],
                                'screenshot' => '/packages/automas/DoubleEntry/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Financial Reports & Ledger Summary',
                                'description' => 'Access comprehensive financial reports including general ledger, journal entries, account statements, cash flow, and expense reports. View detailed ledger summaries with transaction history and account balances.',
                                'keyPoints' => ['General ledger reports', 'Account statements', 'Cash flow analysis', 'Expense tracking reports'],
                                'screenshot' => '/packages/automas/DoubleEntry/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Double Entry Module in Action',
                        'subtitle' => 'See how our accounting tools improve your financial management',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Double Entry Module?',
                        'subtitle' => 'Professional accounting with comprehensive financial reporting',
                        'benefits' => [
                            [
                                'title' => 'Complete Balance Sheets',
                                'description' => 'Generate detailed balance sheets with asset, liability, and equity tracking for accurate financial position reporting.',
                                'icon' => 'Scale',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Profit & Loss Reports',
                                'description' => 'Comprehensive income statements showing revenue, expenses, and net profit for any period with detailed breakdowns.',
                                'icon' => 'TrendingUp',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Trial Balance',
                                'description' => 'Verify ledger accuracy with trial balance reports ensuring debits equal credits before financial statement preparation.',
                                'icon' => 'CheckCircle',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Year-End Closing',
                                'description' => 'Streamlined year-end closing process with automated journal entries and balance sheet finalization.',
                                'icon' => 'Calendar',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Financial Reports',
                                'description' => 'Access general ledger, journal entries, account statements, cash flow, and expense reports for complete visibility.',
                                'icon' => 'FileText',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Comparative Analysis',
                                'description' => 'Compare balance sheets across multiple periods to track financial performance and identify trends over time.',
                                'icon' => 'BarChart',
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