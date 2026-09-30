<?php

namespace Automas\Pos\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/Pos/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'Pos'], [
            'module' => 'Pos',
            'title' => 'Pos Module Marketplace',
            'subtitle' => 'Comprehensive pos tools for your applications',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Pos Module for Automas ERP',
                        'subtitle' => 'Streamline your pos workflow with comprehensive tools and automated management.',
                        'primary_button_text' => 'Install Pos Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/Pos/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Pos Module',
                        'subtitle' => 'Enhance your workflow with powerful pos tools'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Dedicated POS Features',
                        'description' => 'Our POS module provides comprehensive point-of-sale capabilities for modern retail operations.',
                        'subSections' => [
                            [
                                'title' => 'Smart POS Sales Management',
                                'description' => 'Create and manage point-of-sale transactions with automated sale numbering, customer selection, and warehouse integration. Process sales with real-time inventory checking, product selection by category, and comprehensive tax calculations. Track all sales with detailed item information, quantities, pricing, and payment processing for complete transaction management.',
                                'keyPoints' => ['Automated POS sale numbering with #POS format', 'Real-time inventory checking and stock management', 'Category-based product selection and filtering', 'Comprehensive tax calculation and payment processing'],
                                'screenshot' => '/packages/automas/Pos/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Advanced Product & Inventory Control',
                                'description' => 'Manage products with warehouse-specific stock levels, category organization, and dynamic pricing. Access real-time product information including SKU, sale prices, stock quantities, and tax configurations. Filter products by categories and warehouses while maintaining accurate inventory levels with automatic stock updates during sales transactions.',
                                'keyPoints' => ['Warehouse-specific stock level management', 'Category-based product organization and filtering', 'Dynamic pricing with tax configuration support', 'Real-time inventory updates during transactions'],
                                'screenshot' => '/packages/automas/Pos/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Barcode Generation & Receipt Management',
                                'description' => 'Generate professional barcodes for products with customizable copy quantities and warehouse selection. Print detailed receipts with complete transaction information, customer details, itemized listings, tax breakdowns, and discount applications. Manage receipt printing with professional formatting for customer records and business documentation.',
                                'keyPoints' => ['Professional barcode generation with copy control', 'Detailed receipt printing with transaction breakdown', 'Customer information and itemized product listings', 'Tax calculations and discount application tracking'],
                                'screenshot' => '/packages/automas/Pos/src/marketplace/image3.png'
                            ],
                            [
                                'title' => 'Sales Order Tracking & Management',
                                'description' => 'Monitor and manage all POS sales orders with comprehensive filtering and search capabilities. Track sales by customer, warehouse, and date ranges with detailed order information including item counts, totals, and payment status. Access complete sales history with sorting options and pagination for efficient order management and customer service.',
                                'keyPoints' => ['Comprehensive sales order filtering and search', 'Customer and warehouse-based order tracking', 'Detailed order history with payment status', 'Efficient pagination and sorting capabilities'],
                                'screenshot' => '/packages/automas/Pos/src/marketplace/image4.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Pos Module in Action',
                        'subtitle' => 'See how our pos tools improve your workflow',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose POS Module?',
                        'subtitle' => 'Improve efficiency with comprehensive point-of-sale management',
                        'benefits' => [
                            [
                                'title' => 'Smart Sales Processing',
                                'description' => 'Automated sale numbering with real-time inventory and tax calculations.',
                                'icon' => 'ShoppingCart',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Inventory Control',
                                'description' => 'Warehouse-specific stock management with category-based product filtering.',
                                'icon' => 'Package',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Receipt & Barcode',
                                'description' => 'Professional receipt printing and barcode generation for complete documentation.',
                                'icon' => 'Printer',
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