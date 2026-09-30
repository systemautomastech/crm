import {
    useEffect,
    useState,
} from 'react'

import { PhoneIcon } from 'lucide-react'

import type { CallStore } from '../../store/callStore'

import OngoingCallControls from './OngoingCallControls'

interface OngoingCallBarProps {
    callStore: CallStore
    activeCallTitle: string
    onToggleMute: () => void
    onToggleHold: () => void
    onEndCall: () => void
    onTransferCall?: () => void
    isTransferDisabled?: boolean
}

function getStatusText(
    callStore: CallStore,
): string {
    switch (callStore.callStatus) {
        case 'calling':
            return 'Calling...'

        case 'ringing':
            return 'Ringing...'

        case 'active':
            return (
                callStore.callDuration ||
                'Connected'
            )

        default:
            return 'Call in progress'
    }
}

export default function OngoingCallBar({
    callStore,
    activeCallTitle,
    onToggleMute,
    onToggleHold,
    onEndCall,
    onTransferCall,
    isTransferDisabled = false,
}: OngoingCallBarProps) {
    const canUseCallControls =
        callStore.callStatus === 'active'

    const [duration, setDuration] =
        useState(
            callStore.callDuration ||
            '00:00',
        )

    const callerName =
        callStore.callerInfo?.name ||
        callStore.contactName ||
        'Unknown Number'

    const callerNumber =
        callStore.callerInfo?.phone ||
        callStore.callerInfo?.number ||
        callStore.currentNumber ||
        callStore.caller ||
        callStore.incomingNumber ||
        activeCallTitle ||
        'Phone call'

    useEffect(() => {
        if (callStore.callStatus !== 'active') {
            setDuration('00:00')
            return
        }

        const startedAt =
            callStore.callStartedAt ?? Date.now()

        if (!callStore.callStartedAt) {
            callStore.callStartedAt = startedAt
        }

        const updateDuration = (): void => {
            const totalSeconds = Math.max(
                0,
                Math.floor(
                    (Date.now() - startedAt) / 1000,
                ),
            )

            const minutes = String(
                Math.floor(totalSeconds / 60),
            ).padStart(2, '0')

            const seconds = String(
                totalSeconds % 60,
            ).padStart(2, '0')

            const formattedDuration =
                `${minutes}:${seconds}`

            setDuration(formattedDuration)
            callStore.callDuration =
                formattedDuration
        }

        updateDuration()

        const intervalId = window.setInterval(
            updateDuration,
            1000,
        )

        return () => {
            window.clearInterval(intervalId)
        }
    }, [
        callStore,
        callStore.callStatus,
    ])

    return (
        <div
            className="h-14
                pointer-events-none fixed
                z-[9999]
                w-[min(97vw,430px)]
                max-w-xl -translate-x-1/2"
            style={{
                top: '8px',
                right: '10%',
            }}
            role="status"
            aria-live="polite"
        >
            <div
                className=" h-14
                    pointer-events-auto
                    flex items-center gap-3
                    rounded-xl border
                    border-border
                    bg-background/95
                    px-4 py-2
                    bg-gradient-to-r
                    from-blue-50 to-blue-100 shadow-lg"
            >


                <div className="min-w-0 flex-1">
                    <div className="flex min-w-0 items-center gap-2">
                        <span
                            className={[
                                'size-2 shrink-0 rounded-full',
                                callStore.callStatus ===
                                    'active'
                                    ? 'bg-emerald-500'
                                    : 'animate-pulse bg-amber-500',
                            ].join(' ')}
                        />

                        <p
                            className="
                                truncate text-sm
                                font-semibold
                                text-foreground
                            "
                        >
                            {callerName}
                        </p>
                    </div>

                    <div
                        className="
                            mt-0.5 flex
                            items-center gap-2
                            text-xs
                            text-muted-foreground
                        "
                    >   <span>
                            {callerNumber}
                        </span>

                        <span aria-hidden="true">
                            •
                        </span>
                        <span>
                            {callStore.callStatus ===
                                'active'
                                ? duration
                                : getStatusText(
                                    callStore,
                                )}
                        </span>

                        {callStore.callStatus === 'active' &&
                            callStore.latency !== null &&
                            callStore.latency !== undefined && (
                                <>
                                    <span aria-hidden="true">
                                        •
                                    </span>

                                    <span
                                        className={[
                                            'font-medium',
                                            callStore.latency <= 50
                                                ? 'text-blue-600'
                                                : callStore.latency <= 100
                                                    ? 'text-green-600'
                                                    : callStore.latency <= 180
                                                        ? 'text-yellow-600'
                                                        : callStore.latency <= 300
                                                            ? 'text-orange-600'
                                                            : 'text-red-600',
                                        ].join(' ')}
                                    >
                                        {callStore.latency} ms
                                    </span>
                                </>
                            )}
                    </div>
                </div>

                <OngoingCallControls
                    muted={callStore.muted}
                    onHold={callStore.onHold}
                    canUseCallControls={
                        canUseCallControls
                    }
                    isTransferDisabled={
                        isTransferDisabled
                    }
                    onToggleMute={
                        onToggleMute
                    }
                    onToggleHold={
                        onToggleHold
                    }
                    onEndCall={
                        onEndCall
                    }
                    onTransferCall={
                        onTransferCall
                    }
                />
            </div>
        </div>
    )
}