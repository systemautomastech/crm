import {
    PhoneForwarded,
    PhoneIcon,
    Power,
    PowerOff,
} from 'lucide-react'

interface DialerForwardProps {
    forwardNumber: string
    forwardEnabled: boolean

    enableForwarding: () => void
    disableForwarding: () => void

    forwardCurrentCall: (
        destination?: string,
    ) => Promise<void>

    onForwardNumberChange: (
        value: string,
    ) => void
}

export default function DialerForward({
    forwardNumber,
    forwardEnabled,

    enableForwarding,
    disableForwarding,

    forwardCurrentCall,

    onForwardNumberChange,
}: DialerForwardProps) {
    const destination =
        forwardNumber.trim()

    const hasForwardNumber =
        destination.length > 0

    const handleTransfer =
        async (): Promise<void> => {
            if (!hasForwardNumber) {
                return
            }

            await forwardCurrentCall(
                destination,
            )
        }

    return (
        <div className="flex h-full min-h-0 flex-col">
            <div
                className="
                    shrink-0 border-b
                    border-border px-4 py-3
                "
            >
                <div className="flex items-center gap-2">
                    <PhoneForwarded className="size-4 text-primary" />

                    <h2 className="text-base font-semibold text-foreground">
                        Forward call
                    </h2>
                </div>
            </div>

            <div className="flex-1 space-y-4 px-4 py-4">
                <div className="space-y-2">
                    <label
                        htmlFor="forwardNumber"
                        className="text-sm font-medium text-foreground"
                    >
                        Extension or phone number
                    </label>

                    <input
                        id="forwardNumber"
                        value={forwardNumber}
                        onChange={(event) =>
                            onForwardNumberChange(
                                event.target.value,
                            )
                        }
                        onKeyDown={(event) => {
                            if (
                                event.key !==
                                'Enter'
                            ) {
                                return
                            }

                            event.preventDefault()
                            void handleTransfer()
                        }}
                        type="text"
                        inputMode="tel"
                        autoComplete="off"
                        placeholder="Enter extension or number"
                        className="
                            h-10 w-full rounded-md
                            border border-input
                            bg-background px-3 py-2
                            text-sm text-foreground
                            shadow-sm outline-none
                            transition-colors
                            placeholder:text-muted-foreground
                            focus-visible:border-ring
                            focus-visible:ring-2
                            focus-visible:ring-ring/20
                        "
                    />
                </div>

                <div className="grid grid-cols-2 gap-2">
                    {!forwardEnabled ? (
                        <button
                            type="button"
                            disabled={
                                !hasForwardNumber
                            }
                            onClick={
                                enableForwarding
                            }
                            className="
                                inline-flex h-10
                                items-center
                                justify-center gap-2
                                rounded-md border
                                border-input
                                bg-background px-3
                                text-sm font-medium
                                text-foreground
                                shadow-sm
                                transition-colors
                                hover:bg-accent
                                hover:text-accent-foreground
                                focus-visible:outline-none
                                focus-visible:ring-2
                                focus-visible:ring-ring
                                disabled:pointer-events-none
                                disabled:opacity-50
                            "
                        >
                            <Power className="size-4" />
                            Enable
                        </button>
                    ) : (
                        <button
                            type="button"
                            onClick={
                                disableForwarding
                            }
                            className="
                                inline-flex h-10
                                items-center
                                justify-center gap-2
                                rounded-md border
                                border-destructive/40
                                bg-background px-3
                                text-sm font-medium
                                text-destructive
                                shadow-sm
                                transition-colors
                                hover:bg-destructive
                                hover:text-destructive-foreground
                                focus-visible:outline-none
                                focus-visible:ring-2
                                focus-visible:ring-destructive/40
                            "
                        >
                            <PowerOff className="size-4" />
                            Disable
                        </button>
                    )}

                    <button
                        type="button"
                        disabled={
                            !hasForwardNumber
                        }
                        onClick={() => {
                            void handleTransfer()
                        }}
                        className="
                            inline-flex h-10
                            items-center
                            justify-center gap-2
                            rounded-md bg-primary
                            px-3 text-sm
                            font-medium
                            text-primary-foreground
                            shadow-sm
                            transition-colors
                            hover:bg-primary/90
                            focus-visible:outline-none
                            focus-visible:ring-2
                            focus-visible:ring-ring
                            disabled:pointer-events-none
                            disabled:opacity-50
                        "
                    >
                        <PhoneIcon className="size-4" />
                        Transfer
                    </button>
                </div>

                <div
                    className="
                        rounded-lg border
                        border-border
                        bg-muted/40 px-3 py-3
                    "
                >
                    <p className="text-xs leading-5 text-muted-foreground">
                        Enable forwarding to redirect future calls.
                        During an active call, press Transfer to send
                        the current call to the entered destination.
                    </p>
                </div>

                {forwardEnabled && (
                    <div
                        className="
                            flex items-center
                            gap-2 rounded-lg
                            border
                            border-emerald-500/20
                            bg-emerald-500/10
                            px-3 py-2
                            text-xs font-medium
                            text-emerald-700
                            dark:text-emerald-400
                        "
                    >
                        <span className="size-2 rounded-full bg-emerald-500" />

                        Call forwarding is enabled
                    </div>
                )}
            </div>
        </div>
    )
}