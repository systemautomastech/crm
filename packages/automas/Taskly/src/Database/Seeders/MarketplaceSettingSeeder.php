<?php

namespace Automas\Taskly\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/Taskly/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'Taskly'], [
            'module' => 'Taskly',
            'title' => 'Taskly Module Marketplace',
            'subtitle' => 'Comprehensive project and task management tools for your applications',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Taskly Module for Automas ERP',
                        'subtitle' => 'Streamline your project management workflow with comprehensive task tracking, bug management, and team collaboration tools.',
                        'primary_button_text' => 'Install Taskly Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/Taskly/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Taskly Module',
                        'subtitle' => 'Enhance your workflow with powerful project management tools'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Dedicated Taskly Features',
                        'description' => 'Our taskly module provides comprehensive project management capabilities for modern teams.',
                        'subSections' => [
                            [
                                'title' => 'Project & Task Management',
                                'description' => 'Complete project lifecycle management with milestone tracking, task assignments, and progress monitoring. Create projects, assign team members and clients, organize tasks with custom stages, and track progress with Kanban boards and calendar views.',
                                'keyPoints' => ['Project creation and tracking', 'Milestone management', 'Task stages and Kanban boards', 'Team member and client assignment'],
                                'screenshot' => '/packages/automas/Taskly/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Bug Tracking & Resolution',
                                'description' => 'Comprehensive bug tracking system with custom stages, priority management, and team collaboration. Report bugs, assign to team members, track resolution progress, and maintain detailed bug history with comments and status updates.',
                                'keyPoints' => ['Bug reporting and tracking', 'Custom bug stages', 'Priority and status management', 'Bug comments and collaboration'],
                                'screenshot' => '/packages/automas/Taskly/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Team Collaboration & Reporting',
                                'description' => 'Enhanced team collaboration with activity logs, file sharing, and comprehensive project reports. Track all project activities, share files, manage subtasks, and generate detailed reports on project progress, task completion, and team performance.',
                                'keyPoints' => ['Activity log tracking', 'File management system', 'Task subtasks and comments', 'Comprehensive project reports'],
                                'screenshot' => '/packages/automas/Taskly/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Taskly Module in Action',
                        'subtitle' => 'See how our project management tools improve your workflow',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Taskly Module?',
                        'subtitle' => 'Improve efficiency with comprehensive project management',
                        'benefits' => [
                            [
                                'title' => 'Complete Project Management',
                                'description' => 'Manage projects from start to finish with milestones, tasks, and team collaboration in one place.',
                                'icon' => 'FolderKanban',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Kanban & Calendar Views',
                                'description' => 'Visualize tasks and bugs with intuitive Kanban boards and calendar views for better planning.',
                                'icon' => 'LayoutGrid',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Bug Tracking System',
                                'description' => 'Track and resolve bugs efficiently with custom stages, priorities, and team collaboration.',
                                'icon' => 'Bug',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Team Collaboration',
                                'description' => 'Collaborate with team members and clients through comments, file sharing, and activity tracking.',
                                'icon' => 'Users',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Detailed Reporting',
                                'description' => 'Generate comprehensive reports on project progress, task completion, and team performance.',
                                'icon' => 'BarChart',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Customizable Stages',
                                'description' => 'Create custom task and bug stages to match your workflow and project requirements.',
                                'icon' => 'Settings',
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