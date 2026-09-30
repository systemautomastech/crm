<?php

namespace Automas\Hrm\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/Hrm/src/marketplace/' . $file->getFilename();
                }
            }
        }

        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'Hrm'], [
            'module' => 'Hrm',
            'title' => 'Hrm Module Marketplace',
            'subtitle' => 'Comprehensive hrm tools for your applications',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Hrm Module for Automas ERP',
                        'subtitle' => 'Streamline your hrm workflow with comprehensive tools and automated management.',
                        'primary_button_text' => 'Install Hrm Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/Hrm/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Hrm Module',
                        'subtitle' => 'Enhance your workflow with powerful hrm tools'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Dedicated HRM Features',
                        'description' => 'Our HRM module provides comprehensive human resource management capabilities for modern organizations.',
                        'subSections' => [
                            [
                                'title' => 'Employee Management',
                                'description' => 'Complete employee lifecycle management from onboarding to offboarding with detailed profiles and documentation.',
                                'keyPoints' => ['Employee Profiles', 'Document Management', 'Role Assignment', 'Performance Tracking'],
                                'screenshot' => '/packages/automas/Hrm/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Attendance & Leave Management',
                                'description' => 'Automated attendance tracking with comprehensive leave management system and policy enforcement.',
                                'keyPoints' => ['Time Tracking', 'Leave Requests', 'Policy Management', 'Automated Approvals'],
                                'screenshot' => '/packages/automas/Hrm/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Payroll & Benefits',
                                'description' => 'Streamlined payroll processing with tax calculations, benefits management, and compliance reporting.',
                                'keyPoints' => ['Salary Processing', 'Tax Calculations', 'Benefits Tracking', 'Compliance Reports'],
                                'screenshot' => '/packages/automas/Hrm/src/marketplace/image3.png'
                            ],
                            [
                                'title' => 'Performance & Training',
                                'description' => 'Comprehensive performance evaluation system with training management and skill development tracking.',
                                'keyPoints' => ['Performance Reviews', 'Goal Setting', 'Training Programs', 'Skill Assessment'],
                                'screenshot' => '/packages/automas/Hrm/src/marketplace/image4.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Hrm Module in Action',
                        'subtitle' => 'See how our hrm tools improve your workflow',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose HRM Module?',
                        'subtitle' => 'Transform your human resource management with powerful automation and insights',
                        'benefits' => [
                            [
                                'title' => 'Real-time Analytics',
                                'description' => 'Get comprehensive HR analytics and insights for data-driven decision making.',
                                'icon' => 'BarChart3',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Employee Portal',
                                'description' => 'Empower employees with self-service portals for leave requests, profile updates, and more.',
                                'icon' => 'UserCheck',
                                'color' => 'purple'
                            ],

                            [
                                'title' => 'Digital Payslip Generation',
                                'description' => 'Generate and distribute digital payslips with detailed salary breakdowns and tax deductions.',
                                'icon' => 'Receipt',
                                'color' => 'blue'
                            ],
                             [
                                'title' => 'Compliance Management',
                                'description' => 'Ensure regulatory compliance with automated reporting and policy enforcement.',
                                'icon' => 'Shield',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Document Management',
                                'description' => 'Centralized document storage with version control and secure access management.',
                                'icon' => 'FileText',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Payroll Integration',
                                'description' => 'Seamlessly integrate with payroll systems for accurate and timely salary processing.',
                                'icon' => 'CreditCard',
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
