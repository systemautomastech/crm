import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent } from "@/components/ui/card";
import SetupSidebar from './Sidebar';
import GeneralSettings from './GeneralSettings/Index';
import LogoTemplates from './LogoTemplates/Index';
import Pages from './Pages/Index';

interface Props {
    settings?: Record<string, any> | null;
    pages?: any[];
}

export default function Index({ settings, pages = [] }: Props) {
    const { t } = useTranslation();
    const [activeSection, setActiveSection] = useState('general-settings');

    const isClickScrollingRef = React.useRef(false);

    const handleNavClick = (id: string) => {
        if (id === 'subjects') {
            router.get(route('proposal-setup.subjects.index'));
            return;
        }
        setActiveSection(id);
        isClickScrollingRef.current = true;
        const element = document.getElementById(id);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => {
                isClickScrollingRef.current = false;
            }, 800);
        } else {
            isClickScrollingRef.current = false;
        }
    };

    useEffect(() => {
        const hash = window.location.hash.replace('#', '');
        if (hash && ['general-settings', 'logo-template', 'pages'].includes(hash)) {
            setActiveSection(hash);
            setTimeout(() => {
                const element = document.getElementById(hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        }

        const handleScroll = () => {
            if (isClickScrollingRef.current) return;

            const sections = ['general-settings', 'logo-template', 'pages'];
            const scrollPosition = window.scrollY + 250;

            for (let i = sections.length - 1; i >= 0; i--) {
                const sectionId = sections[i];
                const element = document.getElementById(sectionId);
                if (element && element.offsetTop <= scrollPosition) {
                    setActiveSection(sectionId);
                    break;
                }
            }
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Sales Proposals'), url: route('sales-proposals.index') },
                { label: t('System Setup') },
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('Proposal System Setup')} />

            <div className="flex flex-col md:flex-row gap-8 pb-32">
                <SetupSidebar activeSection={activeSection} onNavClick={handleNavClick} />

                <div className="flex-1 space-y-8">
                    {/* 1. General Settings Section */}
                    <section id="general-settings" className="scroll-mt-6">
                        <Card className="shadow-sm">
                            <CardContent className="p-6">
                                <GeneralSettings settings={settings} />
                            </CardContent>
                        </Card>
                    </section>

                    {/* 2. Logo & Template Section */}
                    <section id="logo-template" className="scroll-mt-6">
                        <Card className="shadow-sm">
                            <CardContent className="p-6">
                                <LogoTemplates settings={settings} />
                            </CardContent>
                        </Card>
                    </section>

                    {/* 3. Proposal Pages Section */}
                    <section id="pages" className="scroll-mt-6">
                        <Card className="shadow-sm">
                            <CardContent className="p-6">
                                <Pages pages={pages} settings={settings || {}} />
                            </CardContent>
                        </Card>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
