import {
    ArrowDownLeft,
    ArrowUpRight,
    PhoneIcon,
    PhoneMissed,
    X,
} from 'lucide-react'

import type {
    RecentCall,
} from '../../store/callStore'

interface HistoryItemProps {
    item: RecentCall
    copied: boolean
    onCopy: (number: string) => void
    onCall: (item: RecentCall) => void
}

function getCallLabel(
    item: RecentCall,
): string {
    if (item.status === 'missed') {
        return 'Missed'
    }

    if (item.status === 'failed') {
        return 'Failed'
    }

    if (item.status === 'rejected') {
        return 'Rejected'
    }

    if (item.direction === 'inbound') {
        return 'Incoming'
    }

    if (item.direction === 'outbound') {
        return 'Outgoing'
    }

    return 'Call'
}

function getHistoryTextClass(
    item: RecentCall,
): string {
    if (
        item.status === 'missed' ||
        item.status === 'failed' ||
        item.status === 'rejected'
    ) {
        return 'text-destructive'
    }

    if (
        item.direction === 'outbound' &&
        item.status === 'answered'
    ) {
        return 'text-primary'
    }

    if (item.status === 'answered') {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    return 'text-foreground'
}

function DirectionIcon({
    item,
}: {
    item: RecentCall
}) {
    const iconClassName =
        'size-3.5 shrink-0'

    if (item.status === 'missed') {
        return (
            <PhoneMissed
                className={`${iconClassName} text-destructive`}
            />
        )
    }

    if (
        item.status === 'failed' ||
        item.status === 'rejected'
    ) {
        return (
            <X
                className={`${iconClassName} text-destructive`}
            />
        )
    }

    if (item.direction === 'outbound') {
        return (
            <ArrowDownLeft
                className={`${iconClassName} text-emerald-600 dark:text-emerald-400`}
            />
        )
    }

    if (item.direction === 'inbound') {
        return (
            <ArrowUpRight
                className={`${iconClassName} text-emerald-600 dark:text-emerald-400`}
            />
        )
    }

    return null
}

export default function HistoryItem({
    item,
    copied,
    onCopy,
    onCall,
}: HistoryItemProps) {
    return (
        <div
            className="
                group flex items-center gap-3
                border-b border-border
                px-4 py-3
                transition-colors
                hover:bg-muted/50
            "
        >
            <button
                type="button"
                className="
                    min-w-0 flex-1
                    text-left outline-none
                    focus-visible:rounded-md
                    focus-visible:ring-2
                    focus-visible:ring-ring
                "
                onClick={() =>
                    onCopy(item.number)
                }
                aria-label={`Copy ${item.number}`}
            >
                <span
                    className={`
                        flex min-w-0 items-center
                        gap-2 text-sm font-semibold
                        ${getHistoryTextClass(
                            item,
                        )}
                    `}
                >
                    <span className="truncate">
                        {item.number}
                    </span>

                    {copied && (
                        <span
                            className="
                                shrink-0 text-xs
                                font-medium
                                text-emerald-600
                                dark:text-emerald-400
                            "
                        >
                            Copied
                        </span>
                    )}
                </span>

                <span
                    className="
                        mt-1 flex min-w-0
                        items-center gap-2
                        text-xs
                        text-muted-foreground
                    "
                >
                    <DirectionIcon item={item} />

                    <span className="shrink-0">
                        {getCallLabel(item)}
                    </span>

                    {item.time && (
                        <>
                            <span
                                aria-hidden="true"
                                className="
                                    size-1 shrink-0
                                    rounded-full
                                    bg-muted-foreground/50
                                "
                            />

                            <span className="truncate">
                                {item.time}
                            </span>
                        </>
                    )}
                </span>
            </button>

            <button
                type="button"
                className="
                    inline-flex size-9
                    shrink-0 items-center
                    justify-center rounded-full
                    border border-border
                    bg-background text-primary
                    shadow-sm
                    transition-colors
                    hover:border-primary/30
                    hover:bg-primary
                    hover:text-primary-foreground
                    focus-visible:outline-none
                    focus-visible:ring-2
                    focus-visible:ring-ring
                "
                onClick={() =>
                    onCall(item)
                }
                aria-label={`Call ${item.number}`}
            >
                <PhoneIcon className="size-4" />
            </button>
        </div>
    )
}