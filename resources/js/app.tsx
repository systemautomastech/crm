import "./bootstrap";
import "../css/app.css";
import "../css/rtl.css";
import "./i18n";

import { Suspense } from "react";
import { createRoot } from "react-dom/client";
import {
    createInertiaApp,
    router,
} from "@inertiajs/react";
import axios from "axios";
import { Toaster } from "sonner";

import { ThemeProvider } from "@/components/theme-provider";

import WebPhone from "../../packages/automas/Pbx/src/Resources/js/Pages/dialer/components/WebPhone";

window.Dialer = {
    api: {
        webrtcConfig: route("pbx.webrtc-config"),
        clickToCall: route("pbx.click-to-call"),
        callerLookup: route("pbx.caller-lookup"),
        callEvents: route("pbx.call-events.store"),
        extensionsDirectory: route("pbx.extensions.directory"),
    },

    assets: {
        ringtone: route("pbx.ringtone"),
    },
};

const refreshToken = async (): Promise<void> => {
    try {
        const response = await fetch(window.location.href, {
            method: "GET",
        });

        const html = await response.text();
        const parser = new DOMParser();
        const documentResponse = parser.parseFromString(
            html,
            "text/html",
        );

        const newToken = documentResponse
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (!newToken) {
            return;
        }

        document
            .querySelector('meta[name="csrf-token"]')
            ?.setAttribute("content", newToken);

        axios.defaults.headers.common[
            "X-CSRF-TOKEN"
        ] = newToken;
    } catch {
        // Silent refresh failure
    }
};

router.on("before", () => {
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (!token) {
        void refreshToken();
    }
});

router.on("error", async (event) => {
    const errors = event.detail.errors;

    const has419Error =
        errors &&
        (
            errors[419] ||
            errors["419"] ||
            Object.values(errors).some((error) =>
                String(error).includes("419"),
            )
        );

    if (has419Error) {
        await refreshToken();
    }
});

const originalFetch = window.fetch.bind(window);

window.fetch = async (
    input: RequestInfo | URL,
    init?: RequestInit,
): Promise<Response> => {
    const method = init?.method?.toUpperCase() ?? "GET";

    if (method !== "GET") {
        let token = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (!token) {
            await refreshToken();

            token = document
                .querySelector(
                    'meta[name="csrf-token"]',
                )
                ?.getAttribute("content");
        }

        if (token) {
            const headers = new Headers(init?.headers);

            headers.set("X-CSRF-TOKEN", token);

            init = {
                ...init,
                headers,
            };
        }
    }

    let response = await originalFetch(input, init);

    if (response.status === 419) {
        await refreshToken();

        const newToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (newToken) {
            const headers = new Headers(init?.headers);

            headers.set("X-CSRF-TOKEN", newToken);

            init = {
                ...init,
                headers,
            };
        }

        response = await originalFetch(input, init);
    }

    return response;
};

/*
|--------------------------------------------------------------------------
| Inertia application
|--------------------------------------------------------------------------
*/

createInertiaApp({
    title: (title) => {
        const appElement =
            document.getElementById("app");

        const initialPage = JSON.parse(
            appElement?.dataset.page || "{}",
        );

        const pageProps = initialPage?.props ?? {};

        let customTitle: string | undefined;

        if (
            pageProps?.auth?.user?.type ===
            "superadmin"
        ) {
            customTitle =
                pageProps?.adminAllSetting?.titleText;
        } else if (pageProps?.auth?.user?.type) {
            customTitle =
                pageProps?.companyAllSetting?.titleText;
        } else {
            customTitle =
                pageProps?.adminAllSetting?.titleText;
        }

        const appName =
            customTitle ||
            import.meta.env.VITE_APP_NAME ||
            "Laravel";

        return `${title} - ${appName}`;
    },

    resolve: (name) => {
        const allPages = {
            ...import.meta.glob("./pages/**/*.tsx"),

            ...import.meta.glob([
                "../../packages/automas/*/src/Resources/js/Pages/**/*.tsx",

                // Do not treat reusable components as Inertia pages
                "!../../packages/automas/*/src/Resources/js/Pages/**/components/**/*.tsx",
            ]),
        };

        const applicationPagePath = `./pages/${name}.tsx`;

        if (allPages[applicationPagePath]) {
            return allPages[applicationPagePath]();
        }

        const [packageName, ...pagePath] = name.split("/");

        const packagePagePath =
            `../../packages/automas/${packageName}` +
            `/src/Resources/js/Pages/` +
            `${pagePath.join("/")}.tsx`;

        if (allPages[packagePagePath]) {
            return allPages[packagePagePath]();
        }

        throw new Error(`Page not found: ${name}`);
    },

    setup({ el, App, props }) {
        /*
         * Keeps compatibility with existing code that reads
         * window.page.
         */
        (window as Window & {
            page?: typeof props;
        }).page = props;

        const root = createRoot(el);

        const initialPageProps =
            props.initialPage.props as {
                auth?: {
                    user?: {
                        permissions?: string[];
                    };
                };
            };

        type AuthUser = {
            type?: string;
            permissions?: string[];
        };

        const authUser = initialPageProps.auth?.user as AuthUser | undefined;

        const showDialer =
            authUser?.type !== 'company' &&
            authUser?.permissions?.includes('use dialer');

        root.render(
            <ThemeProvider
                attribute="class"
                defaultTheme="light"
                enableSystem
                disableTransitionOnChange
            >
                <Suspense fallback={null}>
                    <App {...props} />

                    {showDialer && <WebPhone />}
                </Suspense>

                <Toaster
                    position="top-center"
                    richColors
                    expand
                />
            </ThemeProvider>,
        );
    },

    progress: {
        color: "#ff3300e3",
    },
});