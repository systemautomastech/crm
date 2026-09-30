<?php

namespace Automas\LandingPage\Database\Seeders;

use Illuminate\Database\Seeder;
use Automas\LandingPage\Models\LandingPageSetting;
use Illuminate\Support\Facades\Log;

class LandingPageSettingSeeder extends Seeder
{
    public function run()
    {
        if (LandingPageSetting::exists()) {
            return;
        }

        try {
            LandingPageSetting::create($this->getDefaultSettings());
        } catch (\Exception $e) {
            Log::error('Failed to seed landing page settings: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getDefaultSettings(): array
    {
        return [
            'company_name' => 'Automas ERP',
            'contact_email' => 'support@automas.com',
            'contact_phone' => '+1 (555) 123-4567',
            'contact_address' => '123 Business Ave, City, State 12345',
            'config_sections' => $this->getDefaultConfigSections()
        ];
    }

    private function getDefaultConfigSections(): array
    {
        return [
            'sections' => $this->getDefaultSections(),
            'section_visibility' => $this->getDefaultVisibility(),
            'section_order' => $this->getDefaultOrder(),
            'colors' => $this->getDefaultColors()
        ];
    }

    private function getDefaultSections(): array
    {
        return [
            'hero' => [
                'variant' => 'hero1',
                'title' => 'Transform Your Business with Automas ERP',
                'subtitle' => 'An all-in-one SaaS and ERP platform built to simplify operations, boost productivity, and support business growth at every stage.',
                'primary_button_text' => 'Start Free Trial',
                'primary_button_link' => route('register'),
                'secondary_button_text' => 'Login',
                'secondary_button_link' => route('login'),
                'highlight_text' => 'Automas ERP',
                'image' => '/packages/automas/LandingPage/src/marketplace/hero.png'
            ],
            'header' => [
                'variant' => 'header1',
                'company_name' => 'Automas ERP',
                'cta_text' => 'Get Started',
                'enable_addon_link' => true,
                'enable_pricing_link' => true,
                'navigation_items' => [
                    ['text' => 'Home', 'href' => route('landing.page')]
                ]
            ],
            'stats' => [
                'variant' => 'stats1',
                'stats' => [
                    ['label' => 'Businesses Trust Us', 'value' => '10,000+'],
                    ['label' => 'Uptime Guarantee', 'value' => '99.9%'],
                    ['label' => 'Customer Support', 'value' => '24/7'],
                    ['label' => 'Countries Worldwide', 'value' => '50+']
                ]
            ],
            'features' => [
                'variant' => 'features1',
                'title' => 'Powerful Features',
                'subtitle' => 'Everything your business needs in one integrated platform',
                'features' => $this->getDefaultFeatures()
            ],
            'modules' => [
                'variant' => 'modules1',
                'title' => 'Complete Business Solutions',
                'subtitle' => 'Discover our comprehensive modules designed to streamline every aspect of your business operations',
                'modules' => [
                    [
                        'key' => 'taskly',
                        'label' => 'Project',
                        'title' => 'Project Management System',
                        'description' => 'Organize and track projects efficiently with comprehensive project management tools. Manage tasks, milestones, and deadlines with team collaboration in one centralized platform. Track progress with Gantt charts and Kanban boards, assign tasks and set priorities, monitor project timelines and deliverables, and generate detailed project reports. Perfect for teams of any size.',
                        'image' => '/packages/automas/LandingPage/src/marketplace/image1.png'
                    ],
                    [
                        'key' => 'account',
                        'label' => 'Accounting',
                        'title' => 'Complete Accounting & Financial Management',
                        'description' => 'Streamline your financial operations with our comprehensive accounting system. Manage invoices, bills, and payments, track income and expenses, perform bank account reconciliation, and generate detailed financial reports. Professional invoice generation, vendor and customer management, tax calculations and compliance, with real-time financial analytics.',
                        'image' => '/packages/automas/LandingPage/src/marketplace/image2.png'
                    ],
                    [
                        'key' => 'hrm',
                        'label' => 'HRM',
                        'title' => 'Human Resource Management System',
                        'description' => 'Complete employee management solution for modern businesses. Manage employee records and profiles, attendance and leave management, payroll processing and automation, and performance evaluations. Handle department and designation management, recruitment process handling, employee benefits management, and comprehensive HR reporting.',
                        'image' => '/packages/automas/LandingPage/src/marketplace/image3.png'
                    ],
                    [
                        'key' => 'lead',
                        'label' => 'CRM',
                        'title' => 'Customer Relationship Management',
                        'description' => 'Build stronger customer relationships and boost sales with our powerful CRM system. Manage leads and contacts, track sales pipeline, handle deal and opportunity management, and monitor customer interaction tracking. Automate follow-ups, analyze sales performance, forecast revenue, and maintain customer communication history.',
                        'image' => '/packages/automas/LandingPage/src/marketplace/image4.png'
                    ],
                    [
                        'key' => 'pos',
                        'label' => 'POS',
                        'title' => 'Point of Sale System',
                        'description' => 'Fast, reliable point-of-sale solution for retail and service businesses. Process transactions quickly, manage inventory in real-time, handle multiple payment methods, and generate instant receipts. Track product stock, support barcode scanning, handle returns and exchanges, and generate comprehensive sales reports.',
                        'image' => '/packages/automas/LandingPage/src/marketplace/image5.png'
                    ],
                    [
                        'key' => 'productservice',
                        'label' => 'Product & Service',
                        'title' => 'Product & Service Management',
                        'description' => 'Efficiently manage your complete products and services catalog. Organize product categories, manage inventory levels, implement pricing strategies and variations, and handle product attributes. Manage stock across multiple locations, set up automated reorder points, track product performance, and maintain detailed product specifications.',
                        'image' => '/packages/automas/LandingPage/src/marketplace/image6.png'
                    ]
                ]
            ],
            'benefits' => [
                'variant' => 'benefits1',
                'title' => 'Why Choose Automas ERP?',
                'benefits' => [
                    ['title' => 'Complete Project Management', 'description' => 'Organize and track all your projects in one place with powerful task management, team collaboration, and progress tracking tools.'],
                    ['title' => 'Integrated Financial System', 'description' => 'Manage your finances seamlessly with comprehensive accounting, invoicing, expense tracking, and real-time financial reporting.'],
                    ['title' => 'Efficient HR Management', 'description' => 'Streamline employee management with automated payroll, attendance tracking, leave management, and performance evaluation tools.'],
                    ['title' => 'Powerful CRM Tools', 'description' => 'Build stronger customer relationships with lead management, sales pipeline tracking, and automated follow-up systems.'],
                    ['title' => 'Modern POS Solution', 'description' => 'Process sales quickly with our intuitive point-of-sale system featuring inventory management and multiple payment options.'],
                    ['title' => 'Scalable & Secure', 'description' => 'Enterprise-grade security with cloud-based infrastructure that grows with your business needs and ensures data protection.']
                ]
            ],
            'gallery' => [
                'variant' => 'gallery1',
                'title' => 'See Automas ERP in Action',
                'subtitle' => 'Explore our intuitive interface and powerful features through real screenshots of our platform',
                'images' => ['/packages/automas/LandingPage/src/marketplace/image1.png', '/packages/automas/LandingPage/src/marketplace/image2.png', '/packages/automas/LandingPage/src/marketplace/image3.png', '/packages/automas/LandingPage/src/marketplace/image4.png']
            ],
            'cta' => [
                'variant' => 'cta1',
                'title' => 'Ready to Transform Your Business?',
                'subtitle' => 'Join thousands of businesses already using Automas ERP to streamline their operations.',
                'primary_button' => 'Start Free Trial',
                'secondary_button' => 'Contact Sales'
            ],
            'addons' => [
                'title' => 'Premium Addons',
                'subtitle' => 'Extend your Automas ERP with powerful premium modules designed to enhance your business operations',
                'per_page' => 20,
                'default_price_type' => 'monthly',
                'card_variant' => 'card1',
                'show_search' => true,
                'show_category' => true,
                'show_price' => true,
                'show_sort' => true,
                'empty_message' => 'No addons available. Check back later for new premium addons and modules.'
            ],
            'pricing' => [
                'title' => 'Subscription Setting',
                'subtitle' => 'Choose the perfect subscription plan for your business needs',
                'default_subscription_type' => 'pre-package',
                'default_price_type' => 'monthly',
                'show_pre_package' => true,
                'show_usage_subscription' => true,
                'show_monthly_yearly_toggle' => true,
                'empty_message' => 'No plans available. Check back later for new pricing plans.'
            ],
            'footer' => [
                'variant' => 'footer1',
                'description' => 'The complete business management solution for modern enterprisesThe complete business management solution for modern enterprises.',
                'email' => 'support@automas.com',
                'phone' => '+1 (555) 123-4567',
                'newsletter_title' => 'Join Our Community',
                'newsletter_description' => 'We build modern web tools to help you jump-start your daily business work.',
                'newsletter_button_text' => 'Subscribe',
                'copyright_text' => '',
                'navigation_sections' => [
                    [
                        'title' => 'Product',
                        'links' => [
                            ['text' => 'Features', 'href' => '#features'],
                            ['text' => 'Pricing', 'href' => '#pricing'],
                            ['text' => 'Demo', 'href' => '#demo']
                        ]
                    ],
                    [
                        'title' => 'Company',
                        'links' => [
                            ['text' => 'About', 'href' => '#about'],
                            ['text' => 'Contact', 'href' => '#contact'],
                            ['text' => 'Support', 'href' => '#support']
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getDefaultFeatures(): array
    {
        return [
            ['title' => 'Project Management', 'description' => 'Organize and track projects efficiently. Manage tasks, milestones, and deadlines with team collaboration. Track progress with Gantt charts and Kanban boards.', 'icon' => 'FolderOpen'],
            ['title' => 'Accounting', 'description' => 'Manage finances with ease and accuracy. Handle invoices, bills, and payments. Track income and expenses and generate detailed financial reports.', 'icon' => 'Calculator'],
            ['title' => 'HRM', 'description' => 'Simplify employee management and payroll. Manage employee records and profiles, attendance and leave management, and payroll processing automation.', 'icon' => 'UserCheck'],
            ['title' => 'CRM', 'description' => 'Strengthen customer relationships and improve sales. Manage leads and contacts, track sales pipeline, and handle deal and opportunity management.', 'icon' => 'Users'],
            ['title' => 'POS', 'description' => 'Fast and reliable point-of-sale solution. Process transactions quickly, manage inventory in real-time, and handle multiple payment methods.', 'icon' => 'CreditCard'],
            ['title' => 'Product & Service', 'description' => 'Manage your products and services catalog efficiently. Organize product categories, manage inventory levels, and implement pricing strategies.', 'icon' => 'Package']
        ];
    }

    private function getDefaultVisibility(): array
    {
        return [
            'header' => true,
            'hero' => true,
            'stats' => true,
            'features' => true,
            'modules' => true,
            'benefits' => true,
            'gallery' => true,
            'cta' => true,
            'footer' => true,
            'addons' => true,
            'pricing' => true
        ];
    }

    private function getDefaultOrder(): array
    {
        return ['header', 'hero', 'stats', 'features', 'modules', 'benefits', 'gallery', 'cta', 'footer'];
    }

    private function getDefaultColors(): array
    {
        return [
            'primary' => '#10b981',
            'secondary' => '#059669',
            'accent' => '#065f46'
        ];
    }
}
