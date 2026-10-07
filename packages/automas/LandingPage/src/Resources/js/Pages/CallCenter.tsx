import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { getAdminSetting, getImagePath } from '@/utils/helpers';
import Header from './components/Header';
import Footer from './components/Footer';
import CookieConsent from "@/components/cookie-consent";
import {
    PhoneCall,
    PhoneForwarded,
    Mic,
    ShieldCheck,
    Zap,
    Users,
    Headphones,
    BarChart3,
    Clock,
    CheckCircle2,
    ArrowRight,
    Server,
    Radio,
    FileText,
    Activity,
    Layers,
    Lock,
    Volume2,
    Sparkles,
    PhoneIncoming,
    PhoneOff,
    Check
} from 'lucide-react';

interface CallCenterProps {
    settings?: any;
}

export default function CallCenter({ settings }: CallCenterProps) {
    const { t } = useTranslation();
    const { adminAllSetting } = usePage().props as any;

    const colors = settings?.config_sections?.colors || {
        primary: 'var(--color-primary, #10b981)',
        secondary: 'var(--color-secondary, #059669)',
        accent: 'var(--color-accent, #065f46)'
    };
    const primaryColor = colors.primary || 'var(--color-primary)';
    const secondaryColor = colors.secondary || 'var(--color-secondary)';
    const accentColor = colors.accent || 'var(--color-accent)';

    const companyName = settings?.company_name || 'Automas CRM';
    const favicon = getAdminSetting('favicon');
    const faviconUrl = favicon ? getImagePath(favicon) : null;

    const features = [
        {
            icon: PhoneCall,
            badge: 'Click-to-Call',
            title: '1-Click CRM Dialing',
            desc: 'Initiate outbound calls directly from customer profiles, deals, or lead boards with a single click. Zero manual number entry required.'
        },
        {
            icon: PhoneForwarded,
            badge: 'Smart Routing',
            title: 'Multi-Level IVR & Queueing',
            desc: 'Multi-level Interactive Voice Response (IVR) that automatically routes customer calls to the right department or available agent.'
        },
        {
            icon: Mic,
            badge: 'Compliance',
            title: 'Automatic Call Recording',
            desc: 'High-definition voice recording stored securely & attached directly with customer contact timelines for audit & staff evaluations.'
        },
        {
            icon: Headphones,
            badge: 'Supervision',
            title: 'Live Whisper & Barge-in',
            desc: 'Supervisors can monitor live calls silent, whisper guidance directly to agents, or barge-in during critical escalations.'
        },
        {
            icon: BarChart3,
            badge: 'Analytics',
            title: 'Real-time Call Analytics',
            desc: 'Live dashboard tracking average response time, peak call hours, SLA compliance, missed calls, and individual agent KPIs.'
        },
        {
            icon: Server,
            badge: 'SIP / IP-PBX',
            title: 'Cloud PBX & Trunk Sync',
            desc: 'Seamlessly connects with Asterisk, Freeswitch, Grandstream, or custom SIP trunk providers without expensive hardware.'
        }
    ];

    const benefits = [
        'Integrated directly with Automas CRM contacts & lead pipelines',
        'Automatic popup card on incoming call showing caller info & history',
        'Call log history & audio replay attached directly to customer deals',
        'Reduce average call handling time (AHT) by up to 35%',
        'Work from anywhere with web browser softphone capabilities'
    ];

    return (
        <div
            className="lp-root min-h-screen bg-white text-slate-900 antialiased selection:bg-emerald-500 selection:text-white relative"
            style={{
                '--color-primary': primaryColor,
                '--color-secondary': secondaryColor,
                '--color-accent': accentColor,
            } as React.CSSProperties}
        >
            <Head title={`Call Center & IP-PBX Solution - ${companyName}`}>
                <meta name="description" content="Next-generation Cloud Call Center & IP-PBX solution seamlessly integrated into your CRM." />
                {faviconUrl && <link rel="icon" type="image/x-icon" href={faviconUrl} />}
            </Head>

            <Header settings={settings} />

            <main>
                {/* Hero Banner */}
                <section className="relative overflow-hidden bg-slate-50 text-slate-900 pt-24 pb-24 sm:pt-32 sm:pb-32 border-b border-slate-200">
                    {/* Background Grid & Ambient Light Glows */}
                    <div className="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:32px_32px] opacity-40 pointer-events-none" />
                    <div className="absolute top-1/4 left-1/2 -translate-x-1/2 w-[700px] h-[350px] rounded-full blur-[120px] pointer-events-none opacity-20" style={{ backgroundColor: primaryColor }} />
                    <div className="absolute -top-10 right-10 w-96 h-96 rounded-full blur-[100px] pointer-events-none opacity-15" style={{ backgroundColor: secondaryColor }} />

                    <div className="relative max-w-7xl mx-auto px-6 lg:px-8 text-center">
                        {/* Top Pill Badge */}
                        <div className="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs sm:text-sm font-semibold mb-8 shadow-xs border" style={{ backgroundColor: `${primaryColor}12`, borderColor: `${primaryColor}30`, color: primaryColor }}>
                            <Radio className="w-4 h-4 animate-pulse" style={{ color: primaryColor }} />
                            <span>{t('Enterprise Cloud IP-PBX & Telephony')}</span>
                        </div>

                        {/* Title */}
                        <h1 className="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] mb-8 text-slate-900 max-w-4xl mx-auto">
                            {t('Enterprise Call Center &')} <br className="hidden sm:inline" />
                            <span className="text-transparent bg-clip-text" style={{ backgroundImage: `linear-gradient(135deg, ${primaryColor}, ${secondaryColor})` }}>
                                {t('Cloud IP-PBX Platform')}
                            </span>
                        </h1>

                        {/* Subtitle */}
                        <p className="text-lg sm:text-xl text-slate-600 font-normal leading-relaxed max-w-2xl mx-auto mb-10">
                            {t('Connect your phone infrastructure seamlessly with Automas CRM. Make 1-click calls, record audio logs, route queues, and boost customer support productivity.')}
                        </p>

                        {/* Action Buttons */}
                        <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a href={route('register')} className="px-8 py-4 rounded-full text-base font-semibold text-white shadow-lg transition-all flex items-center gap-2 group hover:opacity-90" style={{ backgroundColor: primaryColor }}>
                                <span>{t('Get Call Center Solution')}</span>
                                <ArrowRight className="w-5 h-5 transition-transform group-hover:translate-x-1" />
                            </a>
                            <a href="#features" className="px-8 py-4 rounded-full text-base font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 transition-all">
                                {t('Explore All Features')}
                            </a>
                        </div>
                    </div>
                </section>

                {/* Key Metrics Banner */}
                <section className="bg-white border-b border-slate-200 text-slate-900 py-10">
                    <div className="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        <div className="pt-4 md:pt-0">
                            <div className="text-3xl sm:text-4xl font-extrabold tracking-tight" style={{ color: primaryColor }}>99.99%</div>
                            <div className="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1.5">{t('Voice Uptime SLA')}</div>
                        </div>
                        <div className="pt-4 md:pt-0">
                            <div className="text-3xl sm:text-4xl font-extrabold tracking-tight" style={{ color: primaryColor }}>35%</div>
                            <div className="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1.5">{t('Reduced Call Handling Time')}</div>
                        </div>
                        <div className="pt-4 md:pt-0">
                            <div className="text-3xl sm:text-4xl font-extrabold tracking-tight" style={{ color: primaryColor }}>1-Click</div>
                            <div className="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1.5">{t('CRM Instant Dialing')}</div>
                        </div>
                        <div className="pt-4 md:pt-0">
                            <div className="text-3xl sm:text-4xl font-extrabold tracking-tight" style={{ color: primaryColor }}>HD Audio</div>
                            <div className="text-xs text-slate-500 uppercase tracking-widest font-semibold mt-1.5">{t('Auto Call Recording')}</div>
                        </div>
                    </div>
                </section>

                {/* Pixel-Perfect Interactive Preview / Live Call Simulation */}
                <section className="py-20 sm:py-28 bg-slate-50 border-b border-slate-200">
                    <div className="max-w-7xl mx-auto px-6 lg:px-8">
                        <div className="text-center max-w-2xl mx-auto mb-16">
                            <span className="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full mb-4 shadow-2xs" style={{ backgroundColor: `${primaryColor}15`, color: primaryColor }}>
                                <span className="text-[10px]">◆</span> {t('Built-In Softphone Experience')}
                            </span>
                            <h2 className="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                                {t('Seamless In-Browser Dialing & Popup Cards')}
                            </h2>
                            <p className="mt-4 text-slate-600 text-base">
                                {t('Never miss a client context. Instant popup cards display caller purchase history and CRM notes during every call.')}
                            </p>
                        </div>
                        {/* Softphone Interface Card */}
                        <div className="max-w-4xl mx-auto bg-white rounded-3xl p-6 sm:p-10 shadow-xl border border-slate-200 text-slate-900 relative overflow-hidden">
                            {/* Browser Bar */}
                            <div className="flex items-center justify-between pb-6 border-b border-slate-100 mb-8">
                                <div className="flex items-center gap-2">
                                    <span className="w-3 h-3 rounded-full bg-rose-400" />
                                    <span className="w-3 h-3 rounded-full bg-amber-400" />
                                    <span className="w-3 h-3 rounded-full bg-emerald-400" />
                                    <span className="ml-3 text-xs font-mono text-slate-500 font-medium">Automas Call Center Softphone v2.4</span>
                                </div>
                                <div className="flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold" style={{ backgroundColor: `${primaryColor}15`, color: primaryColor }}>
                                    <span className="w-2 h-2 rounded-full animate-pulse" style={{ backgroundColor: primaryColor }} />
                                    <span>{t('SIP Line Active')}</span>
                                </div>
                            </div>

                            <div className="grid md:grid-cols-12 gap-8 items-center">
                                {/* Left Side: Caller Card */}
                                <div className="md:col-span-7 bg-slate-50 rounded-2xl p-6 border border-slate-200/80 shadow-xs">
                                    <div className="flex items-start justify-between mb-4">
                                        <div>
                                            <div className="text-xs font-mono font-semibold uppercase tracking-wider mb-1" style={{ color: primaryColor }}>{t('INCOMING CALL')}</div>
                                            <div className="text-2xl font-bold text-slate-900">Kamrul Islam</div>
                                            <div className="text-sm text-slate-500 font-mono mt-0.5">+880 1712-987654</div>
                                        </div>
                                        <span className="px-3 py-1 rounded-full text-xs font-semibold" style={{ backgroundColor: `${primaryColor}15`, color: primaryColor }}>
                                            VIP Client
                                        </span>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 pt-4 border-t border-slate-200 text-xs">
                                        <div>
                                            <span className="text-slate-500 block">{t('Assigned Agent')}:</span>
                                            <span className="font-semibold text-slate-800">Tanvir Hassan</span>
                                        </div>
                                        <div>
                                            <span className="text-slate-500 block">{t('Last Deal Value')}:</span>
                                            <span className="font-semibold text-emerald-600">৳ 2,45,000</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Right Side: Controls */}
                                <div className="md:col-span-5 space-y-4">
                                    <div className="bg-slate-50 p-4 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                        <span className="text-slate-500">{t('Call Duration')}</span>
                                        <span className="font-mono text-base font-bold" style={{ color: primaryColor }}>00:04:12</span>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        <button className="flex items-center justify-center gap-2 p-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 border border-slate-200 transition-colors">
                                            <Mic className="w-4 h-4" style={{ color: primaryColor }} />
                                            <span>Mute</span>
                                        </button>
                                        <button className="flex items-center justify-center gap-2 p-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 border border-slate-200 transition-colors">
                                            <Volume2 className="w-4 h-4" style={{ color: primaryColor }} />
                                            <span>Hold</span>
                                        </button>
                                    </div>

                                    <button className="w-full flex items-center justify-center gap-2 p-4 rounded-xl bg-rose-500 hover:bg-rose-600 text-sm font-bold text-white shadow-md transition-colors">
                                        <PhoneOff className="w-5 h-5" />
                                        <span>End Call & Save CRM Log</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Feature Grid */}
                <section id="features" className="py-20 sm:py-28 bg-white">
                    <div className="max-w-7xl mx-auto px-6 lg:px-8">
                        <div className="text-center max-w-2xl mx-auto mb-16">
                            <span className="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full mb-4 shadow-2xs" style={{ backgroundColor: `${primaryColor}15`, color: primaryColor }}>
                                <span className="text-[10px]">◆</span> {t('Enterprise Features')}
                            </span>
                            <h2 className="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                                {t('Everything Your Call Center Needs')}
                            </h2>
                            <p className="mt-4 text-slate-600 text-base">
                                {t('Designed for high-performance sales, support teams, and customer success departments.')}
                            </p>
                        </div>

                        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {features.map((f, i) => {
                                const Icon = f.icon;
                                return (
                                    <div key={i} className="group p-8 rounded-2xl border border-slate-200 bg-white hover:shadow-xl transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
                                        <div>
                                            <div className="flex items-center justify-between mb-6">
                                                <div className="w-12 h-12 rounded-xl flex items-center justify-center transition-colors duration-300" style={{ backgroundColor: `${primaryColor}15`, color: primaryColor }}>
                                                    <Icon className="w-6 h-6" />
                                                </div>
                                                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">
                                                    {f.badge}
                                                </span>
                                            </div>
                                            <h3 className="text-xl font-bold text-slate-900 mb-3">{t(f.title)}</h3>
                                            <p className="text-slate-600 text-sm leading-relaxed">{t(f.desc)}</p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* Deep Integration Benefits List */}
                <section className="py-20 bg-slate-50 border-t border-slate-200">
                    <div className="max-w-7xl mx-auto px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <span className="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full mb-4 shadow-2xs" style={{ backgroundColor: `${primaryColor}15`, color: primaryColor }}>
                                <span className="text-[10px]">◆</span> {t('Unmatched Efficiency')}
                            </span>
                            <h2 className="mt-3 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                                {t('Why CRM-Integrated Telephony Outperforms Standalone Phones')}
                            </h2>
                            <p className="mt-4 text-slate-600 text-base leading-relaxed mb-8">
                                {t('Stop switching back and forth between standalone softphones and spreadsheets. Automas Call Center brings voice conversations directly into customer timelines.')}
                            </p>

                            <ul className="space-y-4">
                                {benefits.map((b, idx) => (
                                    <li key={idx} className="flex items-start gap-3 text-slate-700 text-sm font-semibold">
                                        <CheckCircle2 className="w-5 h-5 shrink-0 mt-0.5" style={{ color: primaryColor }} />
                                        <span>{t(b)}</span>
                                    </li>
                                ))}
                            </ul>

                            <div className="mt-10">
                                <a href={route('register')} className="px-8 py-4 rounded-full text-base font-semibold text-white shadow-lg transition-all inline-flex items-center gap-2 hover:opacity-90" style={{ backgroundColor: primaryColor }}>
                                    <span>{t('Start Free Trial Now')}</span>
                                    <ArrowRight className="w-4 h-4" />
                                </a>
                            </div>
                        </div>

                        {/* Integration Graphic Card */}
                        <div className="bg-white rounded-3xl p-8 shadow-xl border border-slate-200 space-y-6">
                            <div className="flex items-center gap-4 p-4 rounded-2xl border" style={{ backgroundColor: `${primaryColor}08`, borderColor: `${primaryColor}20` }}>
                                <div className="w-12 h-12 rounded-xl text-white flex items-center justify-center shrink-0" style={{ backgroundColor: primaryColor }}>
                                    <Zap className="w-6 h-6" />
                                </div>
                                <div>
                                    <div className="text-sm font-bold text-slate-900">{t('Instant Outbound Dialing')}</div>
                                    <div className="text-xs text-slate-500">{t('Click any number in lead cards to place direct IP calls.')}</div>
                                </div>
                            </div>

                            <div className="flex items-center gap-4 p-4 rounded-2xl border" style={{ backgroundColor: `${secondaryColor}08`, borderColor: `${secondaryColor}20` }}>
                                <div className="w-12 h-12 rounded-xl text-white flex items-center justify-center shrink-0" style={{ backgroundColor: secondaryColor }}>
                                    <Mic className="w-6 h-6" />
                                </div>
                                <div>
                                    <div className="text-sm font-bold text-slate-900">{t('Automatic Recording & Audio Logs')}</div>
                                    <div className="text-xs text-slate-500">{t('Recorded audio files saved directly under customer history.')}</div>
                                </div>
                            </div>

                            <div className="flex items-center gap-4 p-4 rounded-2xl border" style={{ backgroundColor: `${accentColor}08`, borderColor: `${accentColor}20` }}>
                                <div className="w-12 h-12 rounded-xl text-white flex items-center justify-center shrink-0" style={{ backgroundColor: accentColor }}>
                                    <BarChart3 className="w-6 h-6" />
                                </div>
                                <div>
                                    <div className="text-sm font-bold text-slate-900">{t('Real-Time Supervisor Dashboards')}</div>
                                    <div className="text-xs text-slate-500">{t('Monitor agent call duration, response times, and queue health.')}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <Footer settings={settings} />
            <CookieConsent settings={adminAllSetting || {}} />
        </div>
    );
}
