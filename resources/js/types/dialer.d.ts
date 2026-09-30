export { };

declare global {
    interface Window {
        Dialer?: {
            api: {
                webrtcConfig?: string;
                clickToCall?: string;
                callerLookup?: string;
                callEvents?: string;
                extensionsDirectory?: string;
            };

            assets: {
                ringtone?: string;
            };
        };

        CTI_PHONE?: {
            currentSession?: {
                terminate?: () =>
                    | boolean
                    | void
                    | Promise<boolean | void>;
            };

            call?: (
                number: string,
            ) =>
                | boolean
                | void
                | Promise<boolean | void>;

            answer?: () =>
                | boolean
                | void
                | Promise<boolean | void>;

            hangup?: () =>
                | boolean
                | void
                | Promise<boolean | void>;

            reject?: () =>
                | boolean
                | void
                | Promise<boolean | void>;

            sendDTMF?: (
                key: string,
            ) =>
                | boolean
                | void
                | Promise<boolean | void>;

            mute?: () =>
                | boolean
                | void
                | Promise<boolean | void>;

            unmute?: () =>
                | boolean
                | void
                | Promise<boolean | void>;

            hold?: () =>
                | boolean
                | void
                | Promise<boolean | void>;

            unhold?: () =>
                | boolean
                | void
                | Promise<boolean | void>;

            register?: () =>
                | boolean
                | void
                | Promise<boolean | void>;

            transfer?: (
                target: string,
            ) =>
                | boolean
                | void
                | Promise<boolean | void>;

            getExtension?: () => string | null;
        };

        CTI_PHONE_CALL?: (
            number: string,
        ) =>
            | boolean
            | void
            | Promise<boolean | void>;

        pbxClickToCall?: (
            number: string,
        ) =>
            | boolean
            | void
            | Promise<boolean | void>;
    }
}