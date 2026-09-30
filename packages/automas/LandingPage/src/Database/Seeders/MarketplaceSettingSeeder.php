<?php

namespace Automas\LandingPage\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/LandingPage/src/marketplace/' . $file->getFilename();
                }
            }
        }

        // Sort screenshots to ensure consistent order
        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'LandingPage'], [
            'module' => 'LandingPage',
            'title' => 'Landing Page Builder',
            'subtitle' => 'Create stunning landing pages with our powerful drag-and-drop builder',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Landing Page Builder for Automas ERP',
                        'subtitle' => 'Create stunning, high-converting landing pages with our powerful drag-and-drop builder. No coding required - just drag, drop, and publish professional pages in minutes.',
                        'primary_button_text' => 'Install Builder',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'View Demo',
                        'secondary_button_link' => '#demo',
                        'image' => '/packages/automas/LandingPage/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'card_variant' => 'card1',
                        'title' => 'Landing Page Module',
                        'subtitle' => 'Professional landing page creation tools for your business'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Dedicated Landing Page Features',
                        'description' => 'Our landing page builder provides all the tools you need to create high-converting pages with professional design and powerful functionality.',
                        'subSections' => [
                            [
                                'title' => 'Drag & Drop Builder',
                                'description' => 'Create pages visually with our intuitive drag-and-drop interface. Build professional landing pages without any coding knowledge using our comprehensive library of pre-built blocks and elements.',
                                'keyPoints' => ['Visual drag-and-drop editor', 'Pre-built content blocks', 'Real-time preview', 'Mobile responsive design'],
                                'screenshot' => '/packages/automas/LandingPage/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Professional Templates',
                                'description' => 'Choose from dozens of professionally designed templates for any industry. Each template is fully customizable and optimized for conversions with modern layouts and SEO best practices.',
                                'keyPoints' => ['Industry-specific templates', 'Fully customizable designs', 'Modern responsive layouts', 'SEO optimized structure'],
                                'screenshot' => '/packages/automas/LandingPage/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Advanced Analytics',
                                'description' => 'Track visitor behavior and conversion rates with built-in analytics. Monitor page performance, conduct A/B tests, and generate detailed reports to optimize your landing pages for maximum results.',
                                'keyPoints' => ['Conversion tracking', 'Visitor behavior analytics', 'A/B testing capabilities', 'Performance reports'],
                                'screenshot' => '/packages/automas/LandingPage/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Landing Page Builder in Action',
                        'subtitle' => 'See how our landing page tools create professional pages',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Landing Page Builder?',
                        'subtitle' => 'Create professional landing pages with ease and efficiency',
                        'benefits' => [
                            [
                                'title' => 'Easy to Use',
                                'description' => 'No coding required - create professional pages with simple drag and drop.',
                                'icon' => 'MousePointer',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Mobile Responsive',
                                'description' => 'All pages automatically adapt to any screen size and device.',
                                'icon' => 'Smartphone',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'SEO Optimized',
                                'description' => 'Built-in SEO tools help your pages rank higher in search results.',
                                'icon' => 'Search',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Fast Loading',
                                'description' => 'Optimized code ensures your pages load quickly for better user experience.',
                                'icon' => 'Zap',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Analytics Included',
                                'description' => 'Track performance and optimize your pages with detailed analytics.',
                                'icon' => 'BarChart',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Regular Updates',
                                'description' => 'Get new features, templates, and improvements with regular updates.',
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
