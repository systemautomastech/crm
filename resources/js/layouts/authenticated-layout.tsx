import { PropsWithChildren, ReactNode, Fragment } from "react";
import { AppSidebar } from "@/components/app-sidebar";
import { SidebarInset, SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { Separator } from "@/components/ui/separator";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbLink,
    BreadcrumbSeparator,
    BreadcrumbEllipsis,
} from "@/components/ui/breadcrumb";
import { NavUser } from "@/components/nav-user";
import { usePage, Head, Link, router } from "@inertiajs/react";
import { PageProps } from "@/types";
import { BrandProvider, useBrand } from "@/contexts/brand-context";
import CookieConsent from "@/components/cookie-consent";
import { useFavicon } from "@/hooks/use-favicon";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { UserX } from "lucide-react";
import { useFormFields } from '@/hooks/useFormFields';
import { getImagePath } from '@/utils/helpers';

function AuthenticatedLayoutContent({
    header,
    children,
    breadcrumbs,
    pageTitle,
    pageActions
}: PropsWithChildren<{
    header?: ReactNode;
    breadcrumbs?: Array<{ label: string, url?: string }>;
    pageTitle?: string;
    pageActions?: ReactNode;
    className?: string;
}>) {
    const { t } = useTranslation();
    const { auth, companyAllSetting, adminAllSetting } = usePage<PageProps>().props as any;
    const { settings } = useBrand();
    useFavicon();

    const generalAlerts = useFormFields('generalAlert', {}, () => { }, {});


    return (
        <>
            <Head title={adminAllSetting?.metaTitle}>
                {adminAllSetting?.metaKeywords && (
                    <meta name="keywords" content={adminAllSetting.metaKeywords} />
                )}
                {adminAllSetting?.metaDescription && (
                    <meta name="description" content={adminAllSetting.metaDescription} />
                )}
                {adminAllSetting?.metaImage && (
                    <meta property="og:image" content={getImagePath(adminAllSetting.metaImage)} />
                )}
            </Head>
            <div
                className={settings.layoutDirection === 'rtl' ? 'rtl' : 'ltr'}
                data-theme={settings.themeMode}
                dir={settings.layoutDirection === 'rtl' ? 'rtl' : 'ltr'}
                style={{ direction: settings.layoutDirection === 'rtl' ? 'rtl' : 'ltr' }}
            >
                <SidebarProvider defaultOpen={true}>
                    <AppSidebar />

                    <SidebarInset className="overflow-visible"
                        style={{ direction: settings.layoutDirection === 'rtl' ? 'rtl' : 'ltr' }}
                        dir={settings.layoutDirection === 'rtl' ? 'rtl' : 'ltr'}
                    >
                        <header
                            className={`bg-background/95 backdrop-blur-md flex h-16 shrink-0 items-center gap-2 px-3 sm:px-6 py-2 border-b shadow-sm mb-2 justify-between overflow-hidden`}
                        >
                            {/* Sidebar + Breadcrumb */}
                            <div className={`flex items-center gap-2 min-w-0 flex-1 ${settings.layoutDirection === "rtl" ? "order-2 flex-row-reverse" : "order-1"}`}>
                                {/* SidebarTrigger */}
                                <SidebarTrigger className={`shrink-0 -ml-1 ${settings.layoutDirection === "rtl" ? "order-3" : "order-1"}`} />

                                {/* Separator */}
                                <Separator orientation="vertical" className="mr-2 h-4 shrink-0 order-2" />

                                {/* Breadcrumb */}
                                <Breadcrumb className={`min-w-0 overflow-hidden ${settings.layoutDirection === "rtl" ? "order-1" : "order-3"}`}>
                                    <BreadcrumbList className={`flex flex-nowrap text-sm ${settings.layoutDirection === "rtl" ? "justify-end" : "justify-start"}`}>
                                        {/* Dashboard — hidden on tiny screens when there are breadcrumbs */}
                                        <BreadcrumbItem className={breadcrumbs && breadcrumbs.length > 0 ? 'hidden sm:inline-flex' : 'inline-flex'}>
                                            <BreadcrumbLink asChild>
                                                <Link href={route("dashboard")}>{t('Dashboard')}</Link>
                                            </BreadcrumbLink>
                                        </BreadcrumbItem>

                                        {breadcrumbs && breadcrumbs.length > 0 && (() => {
                                            const last = breadcrumbs[breadcrumbs.length - 1];
                                            const middle = breadcrumbs.slice(0, -1);
                                            return (
                                                <>
                                                    {/* On small screens: ellipsis that collapses Dashboard + middle crumbs */}
                                                    {middle.length > 0 && (
                                                        <>
                                                            {/* Small screen: show ellipsis only */}
                                                            <BreadcrumbSeparator className={`sm:hidden ${settings.layoutDirection === 'rtl' ? 'rotate-180' : ''}`} />
                                                            <BreadcrumbItem className="sm:hidden">
                                                                <BreadcrumbEllipsis className="h-4 w-4" />
                                                            </BreadcrumbItem>

                                                            {/* Medium+ screen: show all middle crumbs */}
                                                            {middle.map((crumb, index) => (
                                                                <Fragment key={index}>
                                                                    <BreadcrumbSeparator className={`hidden sm:block ${settings.layoutDirection === 'rtl' ? 'rotate-180' : ''}`} />
                                                                    <BreadcrumbItem className="hidden sm:inline-flex">
                                                                        {crumb.url ? (
                                                                            <BreadcrumbLink asChild>
                                                                                <Link href={crumb.url}>{crumb.label}</Link>
                                                                            </BreadcrumbLink>
                                                                        ) : (
                                                                            <BreadcrumbPage>{crumb.label}</BreadcrumbPage>
                                                                        )}
                                                                    </BreadcrumbItem>
                                                                </Fragment>
                                                            ))}
                                                        </>
                                                    )}

                                                    {/* Last crumb — always shown, truncated if needed */}
                                                    <BreadcrumbSeparator className={settings.layoutDirection === 'rtl' ? 'rotate-180' : ''} />
                                                    <BreadcrumbItem className="min-w-0">
                                                        {last.url ? (
                                                            <BreadcrumbLink asChild className="truncate max-w-[140px] sm:max-w-xs block">
                                                                <Link href={last.url}>{last.label}</Link>
                                                            </BreadcrumbLink>
                                                        ) : (
                                                            <BreadcrumbPage className="truncate max-w-[140px] sm:max-w-xs block">{last.label}</BreadcrumbPage>
                                                        )}
                                                    </BreadcrumbItem>
                                                </>
                                            );
                                        })()}
                                    </BreadcrumbList>
                                </Breadcrumb>
                            </div>

                            {/* NavUser */}
                            <div
                                className={`flex items-center gap-3 shrink-0 ${settings.layoutDirection === "rtl" ? "order-1 flex-row-reverse" : "order-2"}`}
                            >
                                {/* Leave Impersonation Button */}
                                {auth.impersonating && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => router.post(route('users.leave-impersonation'))}
                                        className="text-orange-600 border-orange-600 hover:bg-transparent hover:text-orange-600"
                                    >
                                        <UserX className="h-4 w-4 mr-2" />
                                        {t('Leave Login As User')}
                                    </Button>
                                )}
                                <NavUser user={auth.user} inHeader={true} />
                            </div>
                        </header>

                        <main className="p-4 md:pt-0 h-full">
                            {pageTitle && (
                                <div className="flex items-center mb-6" dir={settings.layoutDirection}>
                                    <h1 className="text-xl font-semibold text-gray-900 dark:text-white flex-1">{pageTitle}</h1>
                                    <div className="flex-shrink-0">{pageActions}</div>
                                </div>
                            )}
                            {children}
                        </main>
                    </SidebarInset>
                </SidebarProvider>
                <CookieConsent settings={adminAllSetting || {}} />
                {generalAlerts.map((alert) => (
                    <div key={alert.id}>{alert.component}</div>
                ))}
            </div>
        </>
    );
}

export default function AuthenticatedLayout(props: PropsWithChildren<{
    header?: ReactNode;
    breadcrumbs?: Array<{ label: string, url?: string }>;
    pageTitle?: string;
    pageActions?: ReactNode;
    className?: string;
}>) {
    return (
        <BrandProvider>
            <AuthenticatedLayoutContent {...props} />
        </BrandProvider>
    );
}
