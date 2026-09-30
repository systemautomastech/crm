<?php

namespace Automas\Inventory\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/Inventory/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'Inventory'], [
            'module' => 'Inventory',
            'title' => 'Essential Features for Optimized Inventory Management',
            'subtitle' => 'The Inventory Add-On streamlines stock management across multiple warehouses with features like smart reorder alerts, flexible adjustments, and accurate COGS calculations',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Essential Features for Optimized Inventory Management',
                        'subtitle' => 'The Inventory Add-On streamlines stock management across multiple warehouses with features like smart reorder alerts, flexible adjustments, and accurate COGS calculations. Comprehensive reporting tools provide actionable insights to optimize inventory performance and decision-making.',
                        'primary_button_text' => 'Install Inventory Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/Inventory/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Smart Inventory Management',
                        'subtitle' => 'The Inventory Add-On helps businesses maintain control over stock across multiple warehouses, offering detailed product tracking with SKU codes, images, and quantities. It features smart reorder alerts to prevent stockouts and customizable max stock levels to avoid overstocking. Businesses can enable or disable inventory tracking for specific items, allowing for flexibility in management. The intuitive interface supports both list and grid views for easy navigation and catalog management.'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Comprehensive Inventory Features',
                        'description' => 'Streamline stock management with powerful automation and reporting tools.',
                        'subSections' => [
                            [
                                'title' => 'Flexible Adjustment Controls',
                                'description' => 'The adjustment system simplifies stock management by supporting various inventory modifications, including increases, decreases, and recounts. Each change is tracked with detailed documentation, such as date, location, and reason. Multiple items can be adjusted in a single transaction, saving time.',
                                'keyPoints' => ['Supports stock increases, decreases, and recounts seamlessly', 'Tracks adjustments with detailed documentation and approvals', 'Adjust multiple items in a single transaction', 'Automatic calculations reduce errors and ensure accuracy'],
                                'screenshot' => '/packages/automas/Inventory/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Accurate Cost Calculation',
                                'description' => 'The system provides accurate cost of goods sold (COGS) calculations using FIFO, LIFO, or Weighted Average methods, with FIFO ideal for businesses with perishable goods, LIFO reflecting recent purchase prices, and Weighted Average smoothing out price fluctuations for consistent reporting.',
                                'keyPoints' => ['Accurate COGS calculations with FIFO, LIFO, and Weighted Average', 'Seamless integration with purchase and sales transactions', 'Automatic updates to inventory and cost tracking', 'Consistent stock levels and valuation across warehouses'],
                                'screenshot' => '/packages/automas/Inventory/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Powerful Reporting Insights',
                                'description' => 'The system offers comprehensive reports for informed business decisions, including Stock Valuation, COGS, and Stock Movement reports. The Stock Valuation Report provides insights into inventory value by method and location. Low stock alerts help prevent stockouts.',
                                'keyPoints' => ['Detailed reports for better inventory performance analysis', 'Stock Valuation Report with method and location breakdowns', 'Low stock alerts to prevent stockouts efficiently', 'Flexible filtering for targeted analysis by the warehouse'],
                                'screenshot' => '/packages/automas/Inventory/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Inventory Module in Action',
                        'subtitle' => 'See how our inventory tools optimize your stock management',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Inventory Module?',
                        'subtitle' => 'Improve efficiency with comprehensive inventory management',
                        'benefits' => [
                            [
                                'title' => 'Multi-Warehouse Inventory Tracking',
                                'description' => 'Track stock across multiple warehouse locations effortlessly.',
                                'icon' => 'Package',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Stock Level Monitoring',
                                'description' => 'Set reorder alerts to prevent stockouts efficiently.',
                                'icon' => 'TrendingUp',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Multiple Valuation Methods',
                                'description' => 'Accurate COGS calculations with FIFO, LIFO, and Weighted Average.',
                                'icon' => 'Calculator',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Inventory Adjustments System',
                                'description' => 'Adjust multiple items in a single transaction seamlessly.',
                                'icon' => 'Edit',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Stock Valuation Reports',
                                'description' => 'Detailed reports for better inventory performance analysis.',
                                'icon' => 'BarChart',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Real-Time Stock Updates',
                                'description' => 'Automatic updates to inventory and cost tracking.',
                                'icon' => 'RefreshCw',
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