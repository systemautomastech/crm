<?php

namespace Automas\ProductService\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/ProductService/src/marketplace/' . $file->getFilename();
                }
            }
        }

        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'ProductService'], [
            'module' => 'ProductService',
            'title' => 'Product & Service Module Marketplace',
            'subtitle' => 'Comprehensive product and service management tools for your applications',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Product & Service Module for Automas ERP',
                        'subtitle' => 'Streamline your product and service management with comprehensive tools and automated inventory control.',
                        'primary_button_text' => 'Install Product & Service Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/ProductService/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Product & Service Module',
                        'subtitle' => 'Enhance your workflow with powerful product and service management tools'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Dedicated Product & Service Features',
                        'description' => 'Our product and service module provides comprehensive capabilities for modern business workflows.',
                        'subSections' => [
                            [
                                'title' => 'Product & Service Item Management',
                                'description' => 'Comprehensive product and service catalog management with detailed item tracking, categorization, and pricing control. Organize items by categories, manage SKUs, and maintain complete records of product specifications and service details.',
                                'keyPoints' => ['Complete item catalog management', 'Category-based organization', 'SKU and barcode tracking', 'Flexible pricing options'],
                                'screenshot' => '/packages/automas/ProductService/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Tax & Unit Configuration',
                                'description' => 'Flexible tax management system with customizable tax rates and unit of measurement configurations. Define multiple tax types, manage tax calculations, and configure units for accurate product measurements and billing.',
                                'keyPoints' => ['Multiple tax rate support', 'Automated tax calculations', 'Custom unit definitions', 'Tax compliance management'],
                                'screenshot' => '/packages/automas/ProductService/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Warehouse & Stock Management',
                                'description' => 'Advanced inventory tracking with multi-warehouse support and real-time stock monitoring. Track stock levels, manage warehouse locations, and automate stock updates through purchase and sales transactions.',
                                'keyPoints' => ['Multi-warehouse support', 'Real-time stock tracking', 'Automated stock updates', 'Low stock alerts'],
                                'screenshot' => '/packages/automas/ProductService/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Product & Service Module in Action',
                        'subtitle' => 'See how our product and service tools improve your workflow',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Product & Service Module?',
                        'subtitle' => 'Improve efficiency with comprehensive product and service management',
                        'benefits' => [
                            [
                                'title' => 'Complete Item Management',
                                'description' => 'Manage all your products and services with detailed specifications, pricing, and categorization for efficient catalog control.',
                                'icon' => 'Package',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Flexible Tax System',
                                'description' => 'Configure multiple tax rates and automate tax calculations for accurate billing and compliance with tax regulations.',
                                'icon' => 'Calculator',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Inventory Tracking',
                                'description' => 'Track stock levels in real-time across multiple warehouses with automated updates from sales and purchase transactions.',
                                'icon' => 'Database',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Category Organization',
                                'description' => 'Organize products and services into hierarchical categories for easy navigation and efficient catalog management.',
                                'icon' => 'FolderTree',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Unit Management',
                                'description' => 'Define custom units of measurement for accurate product quantification and standardized billing across your business.',
                                'icon' => 'Ruler',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Integration Ready',
                                'description' => 'Seamlessly integrate with sales, purchase, and accounting modules for complete business workflow automation.',
                                'icon' => 'Link',
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
