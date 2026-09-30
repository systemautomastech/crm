<?php

namespace Automas\Paypal\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/Paypal/src/marketplace/' . $file->getFilename();
                }
            }
        }

        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'Paypal'], [
            'module' => 'Paypal',
            'title' => 'PayPal Payment Gateway',
            'subtitle' => 'Secure and reliable PayPal payment processing for all your business needs',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'PayPal Payment Gateway Module',
                        'subtitle' => 'Accept secure payments through PayPal for subscriptions, bookings, and e-commerce transactions.',
                        'primary_button_text' => 'Install PayPal Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/Paypal/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'PayPal Payment Gateway',
                        'subtitle' => 'Complete payment solution for your business'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Comprehensive Payment Solutions',
                        'description' => 'Our PayPal module provides secure payment processing for various business scenarios.',
                        'subSections' => [
                            [
                                'title' => 'Subscription Payments',
                                'description' => 'Process plan subscriptions and recurring payments with automatic billing management.',
                                'keyPoints' => ['Plan Subscriptions', 'User Management', 'Coupon Support', 'Automated Billing'],
                                'screenshot' => '/packages/automas/Paypal/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Booking Payments',
                                'description' => 'Handle payments for various booking services including appointments, events, and facilities.',
                                'keyPoints' => ['Appointment Booking', 'Event Tickets', 'Hotel Reservations', 'Service Bookings'],
                                'screenshot' => '/packages/automas/Paypal/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'E-commerce Integration',
                                'description' => 'Seamless integration with LMS courses, beauty services, and other e-commerce platforms.',
                                'keyPoints' => ['Course Purchases', 'Service Payments', 'Cart Management', 'Order Processing'],
                                'screenshot' => '/packages/automas/Paypal/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'PayPal Integration in Action',
                        'subtitle' => 'See how PayPal enhances your payment processing',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose PayPal Gateway?',
                        'subtitle' => 'Trusted payment processing with global reach',
                        'benefits' => [
                            [
                                'title' => 'Secure Transactions',
                                'description' => 'Industry-leading security with PayPal\'s fraud protection and encryption.',
                                'icon' => 'Shield',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Global Acceptance',
                                'description' => 'Accept payments from customers worldwide with multi-currency support.',
                                'icon' => 'Globe',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Easy Integration',
                                'description' => 'Simple setup with comprehensive API integration for all modules.',
                                'icon' => 'Zap',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Multiple Use Cases',
                                'description' => 'Support for subscriptions, bookings, courses, and service payments.',
                                'icon' => 'Package',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Real-time Processing',
                                'description' => 'Instant payment confirmation with automated order management.',
                                'icon' => 'Clock',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Comprehensive Tracking',
                                'description' => 'Complete transaction history and payment status monitoring.',
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
