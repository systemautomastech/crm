<?php

namespace Automas\FacebookChat\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/FacebookChat/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'FacebookChat'], [
            'module' => 'FacebookChat',
            'title' => 'Facebook Chat Integration',
            'subtitle' => 'Centralize your Facebook Messenger communications through Automas ERP, delivering rapid responses and seamless customer engagement',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Facebook Chat Integration',
                        'subtitle' => 'The Facebook Chat Integration on Automas ERP allows businesses to handle all their customer conversations in one place without needing to switch between platforms. Instead of managing messages separately on Facebook Messenger, you can respond to customer queries, provide support, and engage with users directly through the Dash portal.',
                        'primary_button_text' => 'Install Facebook Chat Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/FacebookChat/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Effortless Facebook Integration Setup',
                        'subtitle' => 'Quick setup with Client ID and Access Token enables automatic message syncing, allowing instant responses without checking Messenger separately.'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Comprehensive Facebook Chat Features',
                        'description' => 'Our Facebook Chat module delivers powerful communication capabilities designed to streamline customer interactions and enhance engagement.',
                        'subSections' => [
                            [
                                'title' => 'Configuration & Setup',
                                'description' => 'Connecting your Facebook account with Automas ERP is quick and hassle-free. All you need to do is enter your Client ID and Access Token, and your Facebook Chat will be linked to the portal. Once set up, all incoming messages will automatically sync, allowing your team to respond instantly.',
                                'keyPoints' => ['Quick and hassle-free setup', 'Enter Client ID and Access Token', 'Automatic message synchronization', 'Instant response capability'],
                                'screenshot' => '/packages/automas/FacebookChat/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Centralized Conversation Management',
                                'description' => 'Handle all customer conversations in one place without needing to switch between platforms. Respond to customer queries, provide support, and engage with users directly through the Dash portal. This integration helps businesses keep track of all conversations, ensuring that no message is missed.',
                                'keyPoints' => ['All conversations in one place', 'No platform switching required', 'Track all conversations efficiently', 'Ensure no message is missed'],
                                'screenshot' => '/packages/automas/FacebookChat/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Instant Responses on Facebook Messenger',
                                'description' => 'When customers send a message, it is received directly in the Dash portal, allowing your team to reply without delay. Responses appear in Messenger chat instantly, so customers won\'t have to wait for assistance. Whether handling inquiries, sales queries, or support requests, communication is simple, efficient, and stress-free.',
                                'keyPoints' => ['Messages received directly in Dash portal', 'Reply without delay or switching apps', 'Responses appear instantly in Messenger', 'Quick and reliable customer assistance'],
                                'screenshot' => '/packages/automas/FacebookChat/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Facebook Chat Module in Action',
                        'subtitle' => 'See how our Facebook integration streamlines customer communication',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Facebook Chat Module?',
                        'subtitle' => 'Improve efficiency with comprehensive facebook chat management',
                        'benefits' => [
                            [
                                'title' => 'Centralized Communication',
                                'description' => 'Handle all Facebook conversations from the Dash portal.',
                                'icon' => 'MessageCircle',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Quick Setup',
                                'description' => 'Simple integration with Client ID and Access Token.',
                                'icon' => 'Zap',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Automatic Sync',
                                'description' => 'All incoming messages automatically sync to Dash portal.',
                                'icon' => 'RefreshCw',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'No Platform Switching',
                                'description' => 'Respond without checking Messenger separately.',
                                'icon' => 'Minimize2',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Instant Responses',
                                'description' => 'Reply without delay with responses appearing instantly.',
                                'icon' => 'Send',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Organized Tracking',
                                'description' => 'Keep track of all conversations, ensuring no message is missed.',
                                'icon' => 'List',
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