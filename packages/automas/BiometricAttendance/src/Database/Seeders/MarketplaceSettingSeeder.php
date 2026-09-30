<?php

namespace Automas\BiometricAttendance\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/BiometricAttendance/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'BiometricAttendance'], [
            'module' => 'BiometricAttendance',
            'title' => 'Biometric Attendance Management System',
            'subtitle' => 'Advanced biometric attendance tracking with ZKTeco integration for accurate employee time management and automated payroll processing. Streamline workforce management with real-time synchronization and comprehensive reporting capabilities.',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Biometric Attendance System for Automas ERP',
                        'subtitle' => 'Transform your workforce management with advanced biometric attendance tracking and seamless ZKTeco integration.',
                        'primary_button_text' => 'Install Biometric Attendance',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'View Features',
                        'secondary_button_link' => '#features',
                        'image' => '/packages/automas/BiometricAttendance/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Biometric Attendance Management',
                        'subtitle' => 'Professional workforce tracking with ZKTeco biometric device integration'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Complete Biometric Attendance Solution',
                        'description' => 'Our biometric attendance system delivers comprehensive workforce management with ZKTeco integration and advanced tracking capabilities.',
                        'subSections' => [
                            [
                                'title' => 'ZKTeco Device Integration',
                                'description' => 'Seamless integration with ZKTeco biometric devices for accurate fingerprint-based attendance tracking. Connect multiple devices and synchronize attendance data in real-time with secure API communication.',
                                'keyPoints' => [
                                    'Direct ZKTeco API integration with secure token authentication',
                                    'Real-time attendance data synchronization from biometric devices',
                                    'Support for multiple biometric terminals and locations',
                                    'Fingerprint verification with device status monitoring',
                                    'Automated data fetching with configurable sync intervals',
                                    'Secure communication protocols for data transmission'
                                ],
                                'screenshot' => '/packages/automas/BiometricAttendance/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Attendance Management & Tracking',
                                'description' => 'Comprehensive attendance management with automated time calculations and shift-based tracking. Monitor employee presence with detailed reporting and bulk synchronization capabilities.',
                                'keyPoints' => [
                                    'Automated clock-in and clock-out time recording',
                                    'Shift-based attendance tracking with overtime calculations',
                                    'Break time monitoring and total working hours computation',
                                    'Bulk attendance synchronization for date ranges',
                                    'Employee-wise attendance filtering and search functionality',
                                    'Detailed attendance history with punch state tracking'
                                ],
                                'screenshot' => '/packages/automas/BiometricAttendance/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Reporting & Analytics',
                                'description' => 'Advanced reporting system with comprehensive analytics for workforce management. Generate detailed attendance reports with overtime calculations and employee performance metrics.',
                                'keyPoints' => [
                                    'Comprehensive attendance reports with date range filtering',
                                    'Overtime hours calculation and payroll integration',
                                    'Employee performance analytics and attendance patterns',
                                    'Shift compliance monitoring and late arrival tracking',
                                    'Export functionality for payroll and HR systems',
                                    'Real-time dashboard with attendance status overview'
                                ],
                                'screenshot' => '/packages/automas/BiometricAttendance/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Biometric Attendance System in Action',
                        'subtitle' => 'Experience advanced workforce management with ZKTeco biometric integration',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Biometric Attendance System?',
                        'subtitle' => 'Transform workforce management with accurate biometric tracking and automation',
                        'benefits' => [
                            [
                                'title' => 'ZKTeco Integration',
                                'description' => 'Seamless integration with ZKTeco biometric devices for accurate tracking.',
                                'icon' => 'GitBranch',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Automated Calculations',
                                'description' => 'Automatic overtime, break hours, and payroll calculations.',
                                'icon' => 'Activity',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Real-time Sync',
                                'description' => 'Real-time synchronization with biometric devices and attendance data.',
                                'icon' => 'Play',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Comprehensive Reports',
                                'description' => 'Detailed attendance reports with analytics and export capabilities.',
                                'icon' => 'FileText',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Shift Management',
                                'description' => 'Advanced shift-based tracking with compliance monitoring.',
                                'icon' => 'CheckCircle',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Bulk Operations',
                                'description' => 'Bulk attendance synchronization and batch processing capabilities.',
                                'icon' => 'Users',
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