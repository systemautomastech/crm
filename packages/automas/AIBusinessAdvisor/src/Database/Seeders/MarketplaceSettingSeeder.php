<?php

namespace Automas\AIBusinessAdvisor\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/AIBusinessAdvisor/src/marketplace/' . $file->getFilename();
                }
            }
        }

        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'AIBusinessAdvisor'], [
            'module' => 'AIBusinessAdvisor',
            'title' => 'AI Business Advisor System',
            'subtitle' => 'Intelligent business insights and recommendations powered by AI analysis to optimize your business performance across all departments',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'AI Business Advisor',
                        'subtitle' => 'Unlock powerful AI-driven insights to make data-driven business decisions. The AI Business Advisor analyzes your organizational metrics across financial, team, sales, project, and operational dimensions to provide actionable recommendations for continuous business improvement.',
                        'primary_button_text' => 'Install AI Business Advisor',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/AIBusinessAdvisor/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Key Features',
                        'subtitle' => 'Business Health Scoring, AI-Generated Insights, Smart Recommendations, Real-time Alerts, Trend Analysis'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Comprehensive AI Business Intelligence',
                        'description' => 'Our AI Business Advisor module provides sophisticated analysis and intelligent recommendations to drive business growth and operational excellence.',
                        'subSections' => [
                            [
                                'title' => 'Business Health Scoring System',
                                'description' => 'Comprehensive health assessment across multiple business dimensions including financial performance, team productivity, sales effectiveness, project delivery, and operational efficiency. Receive an overall score with detailed breakdowns for each area.',
                                'keyPoints' => ['Multi-dimensional scoring', 'Historical trend tracking', 'Comparative analysis', 'Real-time metrics aggregation'],
                                'screenshot' => '/packages/automas/AIBusinessAdvisor/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'AI-Powered Insights Generation',
                                'description' => 'Automatic AI analysis of your business data to generate meaningful insights. Insights are categorized by severity (critical, warning, info, positive) and tied to specific business modules for actionable understanding.',
                                'keyPoints' => ['Severity-based prioritization', 'Module-specific insights', 'Read and dismiss tracking', 'Automated analysis pipeline'],
                                'screenshot' => '/packages/automas/AIBusinessAdvisor/src/marketplace/hero.png'
                            ],
                            [
                                'title' => 'Intelligent Recommendations Engine',
                                'description' => 'Smart recommendations generated from AI analysis to help you improve specific business areas. Track recommendation status (pending, done, dismissed) and prioritize high-impact actions with automatic action tracking.',
                                'keyPoints' => ['Priority-based ranking', 'Status management', 'Action timestamps', 'Related module linking'],
                                'screenshot' => '/packages/automas/AIBusinessAdvisor/src/marketplace/image1.png'
                            ],
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'AI Business Advisor in Action',
                        'subtitle' => 'Experience intelligent business analysis with comprehensive metrics and actionable insights',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose AI Business Advisor?',
                        'subtitle' => 'Leverage AI intelligence for strategic business decision-making and continuous improvement',
                        'benefits' => [
                            [
                                'title' => 'Health Scoring',
                                'description' => 'Multi-dimensional business health assessment across finance, team, sales, projects, and operations.',
                                'icon' => 'TrendingUp',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'AI Insights',
                                'description' => 'Automatically generated insights from comprehensive business data analysis.',
                                'icon' => 'Brain',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Smart Recommendations',
                                'description' => 'Actionable recommendations prioritized by impact and importance.',
                                'icon' => 'Lightbulb',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Real-time Alerts',
                                'description' => 'Instant notifications for critical business events and issues.',
                                'icon' => 'AlertCircle',
                                'color' => 'orange'
                            ],
                            [
                                'title' => 'Trend Analysis',
                                'description' => 'Historical tracking and trend analysis for strategic forecasting.',
                                'icon' => 'BarChart3',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Multi-Department Integration',
                                'description' => 'Seamless integration with all business modules for unified insights.',
                                'icon' => 'GitBranch',
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