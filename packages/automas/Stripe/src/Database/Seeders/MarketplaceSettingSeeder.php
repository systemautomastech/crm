<?php

namespace Automas\Stripe\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/Stripe/src/marketplace/' . $file->getFilename();
                }
            }
        }

        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'Stripe'], [
            'module' => 'Stripe',
            'title' => 'Stripe Payment Gateway',
            'subtitle' => 'Secure payment processing for all your business needs',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Stripe Payment Gateway for Automas ERP',
                        'subtitle' => 'Accept payments securely with the world\'s most trusted payment processor.',
                        'primary_button_text' => 'Install Stripe Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/Stripe/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Stripe Payment Module',
                        'subtitle' => 'Complete payment solution for subscriptions and bookings'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Comprehensive Payment Features',
                        'description' => 'Our Stripe module provides secure payment processing for all your business modules.',
                        'subSections' => [
                            [
                                'title' => 'Plan Subscriptions',
                                'description' => 'Process subscription payments for company plans with automated billing.',
                                'keyPoints' => ['Monthly/Yearly Plans', 'Coupon Support', 'User Scaling', 'Storage Limits'],
                                'screenshot' => '/packages/automas/Stripe/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Service Bookings',
                                'description' => 'Accept payments for various booking services including beauty, events, and facilities.',
                                'keyPoints' => ['Beauty Spa Bookings', 'Event Tickets', 'Facility Reservations', 'Vehicle Bookings'],
                                'screenshot' => '/packages/automas/Stripe/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'E-commerce Integration',
                                'description' => 'Seamless payment processing for LMS courses and hotel bookings.',
                                'keyPoints' => ['Course Purchases', 'Hotel Reservations', 'Cart Management', 'Coupon Discounts'],
                                'screenshot' => '/packages/automas/Stripe/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Stripe Payment in Action',
                        'subtitle' => 'See how Stripe integrates seamlessly with your business',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Stripe Payment Gateway?',
                        'subtitle' => 'Trusted by millions of businesses worldwide',
                        'benefits' => [
                            [
                                'title' => 'Secure Processing',
                                'description' => 'PCI DSS compliant with advanced fraud protection and encryption.',
                                'icon' => 'Shield',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Global Currency Support',
                                'description' => 'Accept payments in 135+ currencies with automatic conversion.',
                                'icon' => 'Globe',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Real-time Processing',
                                'description' => 'Instant payment confirmation with webhook notifications.',
                                'icon' => 'Zap',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Easy Integration',
                                'description' => 'Simple setup with comprehensive API documentation.',
                                'icon' => 'Settings',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Payment Analytics',
                                'description' => 'Detailed reporting and transaction insights.',
                                'icon' => 'BarChart3',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Mobile Optimized',
                                'description' => 'Responsive checkout experience across all devices.',
                                'icon' => 'Smartphone',
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
