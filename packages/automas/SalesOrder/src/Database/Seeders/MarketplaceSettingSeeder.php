<?php

namespace Automas\SalesOrder\Database\Seeders;

use Illuminate\Database\Seeder;
use Automas\LandingPage\Models\MarketplaceSetting;
use Illuminate\Support\Facades\File;

class MarketplaceSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists(MarketplaceSetting::class)) {
            return;
        }

        // Get all available screenshots from marketplace directory if exists
        $marketplaceDir = __DIR__ . '/../../marketplace';
        $screenshots = [];

        if (File::exists($marketplaceDir)) {
            $files = File::files($marketplaceDir);
            foreach ($files as $file) {
                if (in_array($file->getExtension(), ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                    $screenshots[] = '/packages/automas/SalesOrder/src/marketplace/' . $file->getFilename();
                }
            }
        }

        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'SalesOrder'], [
            'module'          => 'SalesOrder',
            'title'           => 'Sales Order Module Marketplace',
            'subtitle'        => 'Comprehensive sales order management and delivery workflows for Automas ERP',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant'               => 'hero1',
                        'title'                 => 'Sales Order Module for Automas ERP',
                        'subtitle'              => 'Streamline order tracking, fulfillment, partial deliveries, and delivery challans.',
                        'primary_button_text'   => 'Install Sales Order Module',
                        'primary_button_link'   => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image'                 => '/packages/automas/SalesOrder/src/marketplace/hero.png',
                    ],
                    'modules' => [
                        'variant'  => 'modules1',
                        'title'    => 'Sales Order Module',
                        'subtitle' => 'Enhance your workflow with robust order fulfillment tools',
                    ],
                    'dedication' => [
                        'variant'     => 'dedication1',
                        'title'       => 'Dedicated Sales Order Features',
                        'description' => 'Our sales order module delivers complete control from order confirmation to final customer delivery.',
                        'subSections' => [
                            [
                                'title'       => 'Order Creation & Management',
                                'description' => 'Create structured sales orders with customizable prefixes, detailed item breakdown, discount, and multi-tax calculations.',
                                'keyPoints'   => ['Automated SO numbering system', 'Real-time stock checking', 'Quotation-to-Order conversion'],
                                'screenshot'  => '/packages/automas/SalesOrder/src/marketplace/image1.png',
                            ],
                            [
                                'title'       => 'Delivery Challan & Partial Deliveries',
                                'description' => 'Support multiple partial deliveries per order, print official delivery challans, and maintain real-time delivery status.',
                                'keyPoints'   => ['Full & Partial delivery tracking', 'Printable delivery challan documents', 'Automatic delivery status recalculation'],
                                'screenshot'  => '/packages/automas/SalesOrder/src/marketplace/image2.png',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
