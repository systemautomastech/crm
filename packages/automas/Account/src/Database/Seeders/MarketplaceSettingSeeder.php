<?php

namespace Automas\Account\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/Account/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'Account'], [
            'module' => 'Account',
            'title' => 'Account Module Marketplace',
            'subtitle' => 'Comprehensive accounting and financial management tools for your business',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Account Module for Automas ERP',
                        'subtitle' => 'Complete accounting solution with chart of accounts, bank management, revenue & expense tracking, and comprehensive financial reporting.',
                        'primary_button_text' => 'Install Account Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/Account/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Account Module',
                        'subtitle' => 'Enhance your business with powerful accounting tools'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Dedicated Account Features',
                        'description' => 'Our account module provides comprehensive financial management capabilities for modern businesses.',
                        'subSections' => [
                            [
                                'title' => 'Chart of Accounts & Banking',
                                'description' => 'Complete chart of accounts management with account types, categories, and opening balances. Manage multiple bank accounts, track transactions, process transfers, and maintain accurate financial records with automated journal entries.',
                                'keyPoints' => ['Chart of accounts setup', 'Multiple bank accounts', 'Bank transfers and transactions', 'Automated journal entries'],
                                'screenshot' => '/packages/automas/Account/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Revenue & Expense Management',
                                'description' => 'Track all revenues and expenses with category-based organization and approval workflows. Create revenue and expense entries, categorize transactions, manage approval processes, and post entries to maintain accurate financial records.',
                                'keyPoints' => ['Revenue tracking and posting', 'Expense management', 'Category-based organization', 'Approval workflows'],
                                'screenshot' => '/packages/automas/Account/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Vendor & Customer Management',
                                'description' => 'Comprehensive vendor and customer management with payment tracking, credit/debit notes, and payment allocations. Manage vendor payments, customer payments, create credit and debit notes, and track payment allocations for accurate receivables and payables.',
                                'keyPoints' => ['Vendor and customer records', 'Payment tracking', 'Credit and debit notes', 'Payment allocations'],
                                'screenshot' => '/packages/automas/Account/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Account Module in Action',
                        'subtitle' => 'See how our accounting tools improve your financial management',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Account Module?',
                        'subtitle' => 'Improve efficiency with comprehensive accounting management',
                        'benefits' => [
                            [
                                'title' => 'Complete Chart of Accounts',
                                'description' => 'Manage your complete chart of accounts with account types, categories, and opening balances for accurate financial tracking.',
                                'icon' => 'BookOpen',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Bank Account Management',
                                'description' => 'Track multiple bank accounts, process transfers, and manage transactions with automated reconciliation.',
                                'icon' => 'Building2',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Revenue & Expense Tracking',
                                'description' => 'Track all revenues and expenses with category-based organization and approval workflows for accurate financial records.',
                                'icon' => 'TrendingUp',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Vendor & Customer Management',
                                'description' => 'Manage vendors and customers with payment tracking, credit/debit notes, and payment allocations.',
                                'icon' => 'Users',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Financial Reporting',
                                'description' => 'Generate comprehensive financial reports including income statements, aging reports, and tax summaries.',
                                'icon' => 'FileText',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Journal Entry System',
                                'description' => 'Automated journal entry creation for all transactions with detailed tracking and audit trails.',
                                'icon' => 'FileEdit',
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