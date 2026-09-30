<?php

namespace Automas\Quotation\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/Quotation/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'Quotation'], [
            'module' => 'Quotation',
            'title' => 'Quotation Module Marketplace',
            'subtitle' => 'Comprehensive quotation tools for your applications',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Quotation Module for Automas ERP',
                        'subtitle' => 'Streamline your quotation workflow with comprehensive tools and automated management.',
                        'primary_button_text' => 'Install Quotation Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/Quotation/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Quotation Module',
                        'subtitle' => 'Enhance your workflow with powerful quotation tools'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Dedicated Quotation Features',
                        'description' => 'Our quotation module provides comprehensive capabilities for modern workflows.',
                        'subSections' => [
                            [
                                'title' => 'Smart Quotation Creation & Management',
                                'description' => 'Create professional quotations with automated numbering, detailed line items, and comprehensive tax calculations. The system generates unique quotation numbers following QT-YYYY-MM-XXX format, ensuring organized tracking and easy reference. Manage multiple products per quotation with individual pricing, discounts, and tax configurations while maintaining accurate subtotals and final amounts.',
                                'keyPoints' => ['Automated QT-YYYY-MM-XXX numbering system for organized tracking','Real-time warehouse inventory integration with stock checking','Multi-tax rate support with automatic calculations per line item'],
                                'screenshot' => '/packages/automas/Quotation/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Revision Management & Version Control',
                                'description' => 'Handle quotation revisions seamlessly with parent-child relationship tracking and automatic version numbering. Create new versions of existing quotations while preserving original data and maintaining complete audit trails. Duplicate quotations for similar projects, saving time on repetitive proposal creation while ensuring consistency across similar offerings.',
                                'keyPoints' => ['Parent-child relationship tracking with automatic version numbering','Advanced comparison tools showing version differences','Rollback capabilities to restore previous quotation versions'],
                                'screenshot' => '/packages/automas/Quotation/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Invoice Conversion & Financial Integration',
                                'description' => 'Convert accepted quotations directly into sales invoices with complete data transfer including line items, taxes, and customer details. Maintain financial accuracy through automated calculations and seamless integration with accounting workflows. Track conversion rates and quotation performance metrics to optimize sales processes and improve closing ratios.',
                                'keyPoints' => ['Direct conversion to sales invoices with complete data transfer','Advanced financial reporting with conversion rate analytics','Automated payment term management with reminder systems'],
                                'screenshot' => '/packages/automas/Quotation/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Quotation Module in Action',
                        'subtitle' => 'See how our quotation tools improve your workflow',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Quotation Module?',
                        'subtitle' => 'Improve efficiency with comprehensive quotation management',
                        'benefits' => [
                            [
                                'title' => 'Smart Creation System',
                                'description' => 'Automated numbering with warehouse integration and multi-tax calculations.',
                                'icon' => 'Settings',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Version Control',
                                'description' => 'Advanced revision tracking with comparison tools and rollback capabilities.',
                                'icon' => 'GitBranch',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Invoice Integration',
                                'description' => 'Direct conversion to invoices with financial reporting and analytics.',
                                'icon' => 'Receipt',
                                'color' => 'purple'
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