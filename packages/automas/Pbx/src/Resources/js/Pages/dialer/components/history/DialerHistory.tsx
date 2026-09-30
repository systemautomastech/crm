import {
    useMemo,
    useState,
} from 'react'

import {
    History,
    PhoneIcon,
    RotateCcw,
    Trash2,
} from 'lucide-react'

import type {
    RecentCall,
} from '../../store/callStore'

import HistoryItem from './HistoryItem'

interface DialerHistoryProps {
    recentSearch: string
    missedUnreadCount: number
    filteredRecentCalls: RecentCall[]

    redialLastCall: () => void
    confirmClearMissed: () => void
    callFromHistory: (
        item: RecentCall,
    ) => void

    onRecentSearchChange: (
        value: string,
    ) => void
}

export default function DialerHistory({
    recentSearch,
    missedUnreadCount,
    filteredRecentCalls,

    redialLastCall,
    confirmClearMissed,
    callFromHistory,

    onRecentSearchChange,
}: DialerHistoryProps) {
    const [
        copiedNumber,
        setCopiedNumber,
    ] = useState<string | null>(null)

    const showNoResultCallPopover =
        useMemo(
            () =>
                recentSearch.trim()
                    .length >= 3 &&
                filteredRecentCalls
                    .length === 0,
            [
                recentSearch,
                filteredRecentCalls.length,
            ],
        )

    const copyNumber = async (
        number: string,
    ): Promise<void> => {
        try {
            await navigator.clipboard.writeText(
                number,
            )

            setCopiedNumber(number)

            window.setTimeout(() => {
                setCopiedNumber(
                    (current) =>
                        current === number
                            ? null
                            : current,
                )
            }, 1500)
        } catch (error) {
            console.error(
                'Failed to copy number:',
                error,
            )
        }
    }

    const callSearchNumber =
        (): void => {
            const number =
                recentSearch.trim()

            if (!number) {
                return
            }

            callFromHistory({
                number,
                direction: 'outbound',
                status: 'answered',
            })
        }

    return (
        <div className="flex h-full min-h-0 flex-col">
            <div
                className="
                    shrink-0 border-b
                    border-border px-4 py-3
                "
            >
                <div className="flex items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                        <History className="size-4 text-primary" />

                        <h2 className="text-base font-semibold text-foreground">
                            Recent calls
                        </h2>

                        {missedUnreadCount >
                            0 && (
                            <span
                                className="
                                    inline-flex min-w-5
                                    items-center
                                    justify-center
                                    rounded-full
                                    bg-destructive
                                    px-1.5 py-0.5
                                    text-[10px]
                                    font-semibold
                                    text-destructive-foreground
                                "
                            >
                                {missedUnreadCount >
                                9
                                    ? '9+'
                                    : missedUnreadCount}
                            </span>
                        )}
                    </div>

                    <div className="flex items-center gap-1">
                        <button
                            type="button"
                            onClick={
                                redialLastCall
                            }
                            className="
                                inline-flex size-8
                                items-center
                                justify-center
                                rounded-md
                                text-muted-foreground
                                transition-colors
                                hover:bg-muted
                                hover:text-foreground
                            "
                            title="Redial last call"
                            aria-label="Redial last call"
                        >
                            <RotateCcw className="size-4" />
                        </button>

                        <button
                            type="button"
                            onClick={
                                confirmClearMissed
                            }
                            className="
                                inline-flex size-8
                                items-center
                                justify-center
                                rounded-md
                                text-muted-foreground
                                transition-colors
                                hover:bg-destructive/10
                                hover:text-destructive
                            "
                            title="Clear missed calls"
                            aria-label="Clear missed calls"
                        >
                            <Trash2 className="size-4" />
                        </button>
                    </div>
                </div>
            </div>

            <div className="relative shrink-0 px-4 py-3">
                <input
                    value={recentSearch}
                    onChange={(event) =>
                        onRecentSearchChange(
                            event.target.value,
                        )
                    }
                    type="text"
                    inputMode="tel"
                    placeholder="Search recent calls"
                    autoComplete="off"
                    className="
                        h-10 w-full rounded-md
                        border border-input
                        bg-background px-3 py-2
                        text-sm text-foreground
                        shadow-sm outline-none
                        transition
                        placeholder:text-muted-foreground
                        focus:border-ring
                        focus:ring-2
                        focus:ring-ring/20
                    "
                />

                {showNoResultCallPopover && (
                    <div
                        className="
                            absolute left-4 right-4
                            top-[calc(100%-4px)]
                            z-30 rounded-lg
                            border border-border
                            bg-popover p-3
                            text-popover-foreground
                            shadow-lg
                        "
                    >
                        <p className="text-xs text-muted-foreground">
                            No result found
                        </p>

                        <p className="mt-1 truncate text-sm font-semibold">
                            {recentSearch}
                        </p>

                        <button
                            type="button"
                            className="
                                mt-3 inline-flex
                                h-9 w-full
                                items-center
                                justify-center gap-2
                                rounded-md bg-primary
                                px-3 text-sm
                                font-medium
                                text-primary-foreground
                                transition-colors
                                hover:bg-primary/90
                                focus-visible:outline-none
                                focus-visible:ring-2
                                focus-visible:ring-ring
                            "
                            onClick={
                                callSearchNumber
                            }
                        >
                            <PhoneIcon className="size-4" />
                            Call
                        </button>
                    </div>
                )}
            </div>

            {filteredRecentCalls.length >
            0 ? (
                <div className="min-h-0 flex-1 overflow-y-auto">
                    {filteredRecentCalls.map(
                        (item) => {
                            const itemKey =
                                item.id ??
                                `${item.number}-${item.created_at ?? item.time ?? ''}`

                            return (
                                <HistoryItem
                                    key={itemKey}
                                    item={item}
                                    copied={
                                        copiedNumber ===
                                        item.number
                                    }
                                    onCopy={
                                        copyNumber
                                    }
                                    onCall={
                                        callFromHistory
                                    }
                                />
                            )
                        },
                    )}
                </div>
            ) : (
                <div
                    className="
                        flex min-h-32 flex-1
                        items-center
                        justify-center
                        px-4 py-8
                        text-center text-sm
                        text-muted-foreground
                    "
                >
                    No recent calls found
                </div>
            )}
        </div>
    )
}