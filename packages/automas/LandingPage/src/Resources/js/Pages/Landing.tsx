import { useEffect } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { getAdminSetting, getImagePath } from '@/utils/helpers';
import CookieConsent from "@/components/cookie-consent";
import Header from './components/Header';
import Hero from './components/Hero';
import Stats from './components/Stats';
import Features from './components/Features';
import Modules from './components/Modules';
import Benefits from './components/Benefits';
import Gallery from './components/Gallery';
import CTA from './components/CTA';
import Footer from './components/Footer';

interface LandingProps {
    plans?: any[];
    activeModules?: any[];
    settings?: {
        company_name?: string;
        contact_email?: string;
        contact_phone?: string;
        contact_address?: string;
        config_sections?: {
            sections?: { [key: string]: any };
            section_visibility?: { [key: string]: boolean };
            section_order?: string[];
            colors?: { primary: string; secondary: string; accent: string };
        };
    };
}

export default function Landing({ settings, plans, activeModules }: LandingProps) {
    const { adminAllSetting } = usePage().props as any;
    const favicon = getAdminSetting('favicon');
    const faviconUrl = favicon ? getImagePath(favicon) : null;

    useEffect(() => {
        const initGSAP = () => {
            const windowGsap = (window as any).gsap;
            const windowScrollTrigger = (window as any).ScrollTrigger;

            if (windowGsap) {
                if (windowScrollTrigger) {
                    windowGsap.registerPlugin(windowScrollTrigger);
                }

                // Hero Entrance Animation Timeline
                const heroTl = windowGsap.timeline({ defaults: { ease: 'power3.out' } });

                heroTl.fromTo('.gsap-hero-title',
                    { y: 35, opacity: 0 },
                    { y: 0, opacity: 1, duration: 0.9, delay: 0.2 }
                )
                    .fromTo('.gsap-hero-subtitle',
                        { y: 25, opacity: 0 },
                        { y: 0, opacity: 1, duration: 0.8 },
                        '-=0.6'
                    )
                    .fromTo('.gsap-hero-cta',
                        { y: 20, opacity: 0 },
                        { y: 0, opacity: 1, duration: 0.7 },
                        '-=0.5'
                    )
                    .fromTo('.gsap-hero-stage',
                        { y: 45, opacity: 0, scale: 0.97 },
                        { y: 0, opacity: 1, scale: 1, duration: 1 },
                        '-=0.4'
                    );

                // ScrollTrigger Card Animations
                if (windowScrollTrigger) {
                    const cards = windowGsap.utils.toArray('.gsap-card-reveal');
                    cards.forEach((card: any) => {
                        windowGsap.fromTo(card,
                            { y: 40, opacity: 0 },
                            {
                                y: 0,
                                opacity: 1,
                                duration: 0.8,
                                ease: 'power3.out',
                                scrollTrigger: {
                                    trigger: card,
                                    start: 'top 85%',
                                    toggleActions: 'play none none none'
                                }
                            }
                        );
                    });
                }
            }
        };

        // Try initializing immediately or after script load delay
        if ((window as any).gsap) {
            initGSAP();
        } else {
            const timer = setTimeout(initGSAP, 400);
            return () => clearTimeout(timer);
        }
    }, []);

    const isSectionVisible = (key: string) =>
        settings?.config_sections?.section_visibility?.[key] !== false;

    const sectionOrder = settings?.config_sections?.section_order ||
        ['header', 'hero', 'stats', 'features', 'modules', 'benefits', 'gallery', 'cta', 'footer'];

    const renderSection = (sectionKey: string) => {
        if (!isSectionVisible(sectionKey)) return null;
        switch (sectionKey) {
            case 'header': return <Header key={sectionKey} settings={settings} />;
            case 'hero': return <Hero key={sectionKey} settings={settings} />;
            case 'stats': return <Stats key={sectionKey} settings={settings} />;
            case 'features': return <Features key={sectionKey} settings={settings} />;
            case 'modules': return <Modules key={sectionKey} settings={settings} />;
            case 'benefits': return <Benefits key={sectionKey} settings={settings} />;
            case 'gallery': return <Gallery key={sectionKey} settings={settings} />;
            case 'cta': return <CTA key={sectionKey} settings={settings} plans={plans} activeModules={activeModules} />;
            case 'footer': return <Footer key={sectionKey} settings={settings} />;
            default: return null;
        }
    };

    const colorScheme = settings?.config_sections?.colors || {
        primary: '#130774',
        secondary: '#0b55b7',
        accent: '#130674'
    };

    return (
        <div
            className="min-h-screen bg-slate-50 font-['Plus_Jakarta_Sans',sans-serif] relative"
            style={{
                '--color-primary': colorScheme.primary || '#130774',
                '--color-secondary': colorScheme.secondary || '#0b55b7',
                '--color-accent': colorScheme.accent || '#130674',
            } as React.CSSProperties}
        >
            {/* STICKY BACKGROUND LAYER (Stays sticky behind all scrolling sections) */}
            <div className="sticky top-0 h-screen w-full pointer-events-none z-0 overflow-hidden -mb-[100vh]">
                {/* Primary Top-Left Ambient Orb */}
                <div
                    className="absolute w-[650px] h-[650px] rounded-full blur-[140px] top-10 -left-40"
                    style={{ backgroundColor: colorScheme.primary || '#130774', opacity: 0.14 }}
                />
                {/* Secondary Bottom-Right Ambient Orb */}
                <div
                    className="absolute w-[600px] h-[600px] rounded-full blur-[140px] bottom-10 -right-20"
                    style={{ backgroundColor: colorScheme.secondary || '#0b55b7', opacity: 0.14 }}
                />
                {/* Sticky Grid Texture */}
                <div
                    className="absolute inset-0 opacity-[0.25]"
                    style={{
                        backgroundImage: 'radial-gradient(rgba(148,163,184,0.2) 1px, transparent 1px)',
                        backgroundSize: '36px 36px'
                    }}
                />
            </div>

            <Head title={`${settings?.company_name || 'Automas ERP'} - All-in-One Business Management Solution`}>
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />

                {/* GSAP & ScrollTrigger CDN */}
                <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

                {faviconUrl && <link rel="icon" type="image/x-icon" href={faviconUrl} />}
            </Head>

            {/* Render sections in order */}
            <div className="relative z-10 bg-transparent">
                {sectionOrder.map(sectionKey => renderSection(sectionKey))}
            </div>

            <CookieConsent settings={adminAllSetting || {}} />
        </div>
    );
}