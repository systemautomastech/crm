import {
    useEffect,
    useState,
} from 'react'
import {
    Minus,
    PhoneCall,
    PhoneIcon,
    X,
} from 'lucide-react'

import type { CallStore } from '../../store/callStore'

import IncomingCallerDetails from './IncomingCallerDetails'

import { useCallerLookup } from '../../hooks/useCallerLookup'

interface IncomingCallPopupProps {
    callStore: CallStore
    onAnswer: () => void
    onReject: () => void
}

export default function IncomingCallPopup({
    callStore,
    onAnswer,
    onReject,
}: IncomingCallPopupProps) {
    const [isMinimized, setIsMinimized] =
        useState(false)

    const { performCallerLookup } =
        useCallerLookup(callStore)

    const incomingNumber =
        callStore.incomingNumber ||
        callStore.caller ||
        ''

    useEffect(() => {
        if (!incomingNumber) {
            return
        }

        /*
         * Clear information from the previous call
         * before searching for the new caller.
         */
        callStore.callerInfo = null
        callStore.contactName = ''

        void performCallerLookup(incomingNumber)
    }, [
        incomingNumber,
        performCallerLookup,
        callStore,
    ])

    const callerNumber =
        callStore.callerInfo?.phone ||
        callStore.incomingNumber ||
        callStore.caller ||
        'Unknown number'

    const callerName =
        callStore.callerInfo?.name ||
        callStore.contactName ||
        'Unknown caller'

    const callerType =
        callStore.callerInfo?.type || ''

    const isLead =
        callerType === 'lead'

    const isDeal =
        callerType === 'deal'

    /*
     * Minimized incoming-call bar.
     * The call continues ringing and can still be answered/rejected.
     */
    if (isMinimized) {
        return (
            <div
                className="
                    fixed z-[10000]
                    flex w-[min(92vw,380px)]
                    items-center gap-3
                    rounded-xl border border-blue-200
                    bg-gradient-to-r from-blue-50 to-blue-100
                    p-3 h-14
                    text-foreground"

                style={{
                    top: '8px',
                    right: '23%',
                }}
                role="dialog"
                aria-label="Minimized incoming call"
            >
                <button
                    type="button"
                    onClick={() => setIsMinimized(false)}
                    className="
                        flex min-w-0 flex-1
                        items-center gap-3 text-left
                    "
                    aria-label="Restore incoming call popup"
                >
                    <span
                        className="
                            relative flex size-10
                            shrink-0 items-center
                            justify-center rounded-full
                            bg-primary/10 text-primary
                        "
                    >
                        <span
                            className="
                                absolute inset-0
                                animate-ping rounded-full
                                bg-primary/10
                            "
                        />

                        <PhoneCall className="relative size-5" />
                    </span>

                    <span className="min-w-0">
                        <span
                            className="
                                block truncate text-sm
                                font-semibold
                            "
                        >
                            {callerName}
                        </span>

                        <span
                            className="
                                block truncate text-xs
                                text-muted-foreground
                            "
                        >
                            {callerNumber}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    onClick={onReject}
                    className="
                        inline-flex size-9
                        shrink-0 items-center
                        justify-center rounded-full
                        bg-destructive
                        text-destructive-foreground
                        transition-colors
                        hover:bg-destructive/90
                    "
                    aria-label="Reject call"
                >
                    <PhoneIcon className="size-4 rotate-[135deg]" />
                </button>

                <button
                    type="button"
                    onClick={onAnswer}
                    className="
                        inline-flex size-9
                        shrink-0 items-center
                        justify-center rounded-full
                        bg-primary
                        text-primary-foreground
                        transition-colors
                        hover:bg-primary/90
                    "
                    aria-label="Answer call"
                >
                    <PhoneIcon className="size-4" />
                </button>
            </div>
        )
    }

    return (
        <div
            className="
                fixed inset-0 z-[10000]
                flex items-center justify-center
                bg-black/50 p-4
                backdrop-blur-[2px]
            "
            role="dialog"
            aria-modal="true"
            aria-labelledby="incoming-call-title"
        >
            <div
                className="
                    relative max-h-[calc(100vh-2rem)]
                    w-full max-w-md
                    overflow-y-auto rounded-2xl
                    border border-border
                    bg-background
                    text-foreground
                    shadow-2xl
                "
            >
                {/* Mac-style window controls */}
                <div
                    className="
                        absolute right-4 top-4 z-10
                        flex items-center gap-2
                    "
                >
                    <button
                        type="button"
                        onClick={() => setIsMinimized(true)}
                        className="
                            inline-flex size-7
                            items-center justify-center
                            rounded-full bg-amber-400
                            text-amber-950
                            transition-transform
                            hover:scale-105
                            focus-visible:outline-none
                            focus-visible:ring-2
                            focus-visible:ring-ring
                        "
                        aria-label="Minimize incoming call"
                        title="Minimize"
                    >
                        <Minus className="size-4" />
                    </button>

                    <button
                        type="button"
                        onClick={() => setIsMinimized(true)}
                        className="
                            inline-flex size-7
                            items-center justify-center
                            rounded-full bg-red-500
                            text-white
                            transition-transform
                            hover:scale-105
                            focus-visible:outline-none
                            focus-visible:ring-2
                            focus-visible:ring-red-500
                        "
                        aria-label="Hide incoming call popup"
                        title="Hide popup"
                    >
                        <X className="size-4" />
                    </button>
                </div>

                <div className="px-6 pb-5 pt-7 text-center">
                    <div
                        className="
                            relative mx-auto flex size-16
                            items-center justify-center
                            rounded-full bg-primary/10
                            text-primary
                        "
                    >
                        <span
                            className="
                                absolute inset-0
                                animate-ping rounded-full
                                bg-primary/10
                            "
                        />

                        <PhoneCall className="relative size-7" />
                    </div>

                    <p
                        className="
                            mt-4 text-xs font-semibold
                            uppercase tracking-[0.18em]
                            text-primary
                        "
                    >
                        Incoming call
                    </p>

                    <h2
                        id="incoming-call-title"
                        className="
                            mt-2 truncate text-xl
                            font-semibold
                        "
                    >
                        {callerName}
                    </h2>

                    <p className="mt-1 text-sm text-muted-foreground">
                        {callerNumber}
                    </p>

                    {(isLead || isDeal) && (
                        <div className="mt-3 flex justify-center">
                            <span
                                className="
                                    inline-flex items-center
                                    rounded-full
                                    bg-primary/10 px-3 py-1
                                    text-xs font-semibold
                                    uppercase tracking-wide
                                    text-primary
                                "
                            >
                                {isLead ? 'Lead' : 'Deal'}
                            </span>
                        </div>
                    )}
                </div>

                <div className="px-6">
                    <IncomingCallerDetails
                        callStore={callStore}
                    />
                </div>

                <div className="grid grid-cols-2 gap-3 p-5">
                    <button
                        type="button"
                        onClick={onReject}
                        className="
                            inline-flex h-11
                            items-center justify-center
                            gap-2 rounded-lg
                            bg-destructive px-4
                            text-sm font-medium
                            text-destructive-foreground
                            shadow-sm
                            transition-colors
                            hover:bg-destructive/90
                            focus-visible:outline-none
                            focus-visible:ring-2
                            focus-visible:ring-destructive
                            focus-visible:ring-offset-2
                        "
                    >
                        <PhoneIcon className="size-4 rotate-[135deg]" />
                        Reject
                    </button>

                    <button
                        type="button"
                        onClick={onAnswer}
                        className="
                            inline-flex h-11
                            items-center justify-center
                            gap-2 rounded-lg
                            bg-primary px-4
                            text-sm font-medium
                            text-primary-foreground
                            shadow-sm
                            transition-colors
                            hover:bg-primary/90
                            focus-visible:outline-none
                            focus-visible:ring-2
                            focus-visible:ring-ring
                            focus-visible:ring-offset-2
                        "
                    >
                        <PhoneIcon className="size-4" />
                        Answer
                    </button>
                </div>
            </div>
        </div>
    )
}