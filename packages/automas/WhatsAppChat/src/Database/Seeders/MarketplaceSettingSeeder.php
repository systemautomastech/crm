<?php

namespace Automas\WhatsAppChat\Database\Seeders;

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
                    $screenshots[] = '/packages/automas/WhatsAppChat/src/marketplace/' . $file->getFilename();
                }
            }
        }
        
        sort($screenshots);
        
        MarketplaceSetting::firstOrCreate(['module' => 'WhatsAppChat'], [
            'module' => 'WhatsAppChat',
            'title' => 'WhatsApp Chat Integration',
            'subtitle' => 'The WhatsApp Chat Integration helps businesses communicate with customers easily and efficiently',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'WhatsApp Chat Integration',
                        'subtitle' => 'Handle all your WhatsApp conversations directly from the portal. Manage customer queries, resolve issues, and share updates from one place. Stay organized, reduce response time, and ensure no message goes unanswered.',
                        'primary_button_text' => 'Install WhatsApp Chat Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/automas/WhatsAppChat/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Seamless WhatsApp Integration',
                        'subtitle' => 'Quick setup with just your Client ID and Access Token. All customer messages instantly sync to the portal, allowing you to respond without checking your phone. Keep all chats accessible in one place for efficient communication management.'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Comprehensive WhatsApp Chat Features',
                        'description' => 'Manage all your WhatsApp conversations from one convenient platform.',
                        'subSections' => [
                            [
                                'title' => 'Easy Setup for WhatsApp Integration',
                                'description' => 'Integrating WhatsApp is quick and simple. You only need to enter your Client ID and Access Token to connect your WhatsApp account. Once this is done, all messages from your customers will instantly sync into the portal.',
                                'keyPoints' => ['Quick setup with Client ID and Access Token', 'Instant message synchronization to portal', 'All chats accessible in one place', 'Track and record every conversation efficiently'],
                                'screenshot' => '/packages/automas/WhatsAppChat/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Get Quick Responses on WhatsApp via Automas ERP',
                                'description' => 'Now, reaching out to us on WhatsApp is more convenient than ever! Whenever you send us a message, we receive it directly in our portal, allowing our team to reply instantly. There\'s no waiting or unnecessary delays, our responses will appear in your WhatsApp chat right away.',
                                'keyPoints' => ['Messages received directly in portal', 'Instant team replies without delays', 'Responses appear in WhatsApp immediately', 'Quick, reliable, and accessible communication'],
                                'screenshot' => '/packages/automas/WhatsAppChat/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Portal Messaging and Contact Management',
                                'description' => 'Once configured, the WhatsApp chat portal displays all your conversations in one centralized location. If you send a message from the portal, the message will be sent to the user\'s WhatsApp Chat, and if the user sends any message through WhatsApp, it will be visible on the portal.',
                                'keyPoints' => ['All conversations in centralized location', 'Create contacts from portal or user management', 'Automatic sorting by recent activity', 'Quick search function for specific contacts'],
                                'screenshot' => '/packages/automas/WhatsAppChat/src/marketplace/image3.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'WhatsApp Chat Module in Action',
                        'subtitle' => 'See how our WhatsApp integration streamlines customer communication',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose WhatsApp Chat Module?',
                        'subtitle' => 'Improve efficiency with comprehensive whatsappchat management',
                        'benefits' => [
                            [
                                'title' => 'Centralized Communication',
                                'description' => 'Handle all WhatsApp conversations directly from the portal.',
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
                                'title' => 'Instant Sync',
                                'description' => 'All customer messages sync instantly to the portal.',
                                'icon' => 'RefreshCw',
                                'color' => 'purple'
                            ],
                            [
                                'title' => 'Contact Management',
                                'description' => 'Add and manage contacts directly from the portal.',
                                'icon' => 'Users',
                                'color' => 'red'
                            ],
                            [
                                'title' => 'Real-Time Responses',
                                'description' => 'Reply instantly with responses appearing in WhatsApp immediately.',
                                'icon' => 'Send',
                                'color' => 'yellow'
                            ],
                            [
                                'title' => 'Organized Messaging',
                                'description' => 'Stay organized with automatic sorting by recent activity.',
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