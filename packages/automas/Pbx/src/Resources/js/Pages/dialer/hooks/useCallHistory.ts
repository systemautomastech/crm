import { useCallback, useMemo, useState } from 'react'

import type { CallStore, RecentCall } from '../store/callStore'

const RECENT_CALLS_KEY = 'pbx_recent_calls'
const MISSED_CALLS_KEY = 'pbx_missed_calls'

const MAX_RECENT_CALLS = 30
const MAX_MISSED_CALLS = 30

function uniqueByNumber(
    items: RecentCall[],
    limit = 30,
): RecentCall[] {
    const seen = new Set<string>()
    const unique: RecentCall[] = []

    for (const item of items ?? []) {
        const key = String(item?.number ?? '').trim()

        if (!key || seen.has(key)) {
            continue
        }

        seen.add(key)
        unique.push(item)

        if (unique.length >= limit) {
            break
        }
    }

    return unique
}

function getCachedCalls(key: string): RecentCall[] {
    try {
        const cachedValue = localStorage.getItem(key)

        if (!cachedValue) {
            return []
        }

        const parsedValue: unknown = JSON.parse(cachedValue)

        return Array.isArray(parsedValue)
            ? (parsedValue as RecentCall[])
            : []
    } catch {
        localStorage.removeItem(key)
        return []
    }
}

export function useCallHistory(callStore: CallStore) {
    const [recentSearch, setRecentSearch] = useState('')
    const [missedUnreadCount, setMissedUnreadCount] = useState(0)

    /*
     * callStore is currently a mutable compatibility store.
     * Incrementing this version forces this hook to recalculate whenever
     * recentCalls or missedCalls are changed.
     */
    const [historyVersion, setHistoryVersion] = useState(0)

    const refreshHistory = useCallback((): void => {
        setHistoryVersion((currentVersion) => currentVersion + 1)
    }, [])

    const filteredRecentCalls = useMemo((): RecentCall[] => {
        const search = recentSearch.trim().toLowerCase()

        if (!search) {
            return callStore.recentCalls
        }

        return callStore.recentCalls.filter((call) => {
            const number = String(call.number ?? '').toLowerCase()
            const direction = String(call.direction ?? '').toLowerCase()
            const status = String(call.status ?? '').toLowerCase()

            return (
                number.includes(search) ||
                direction.includes(search) ||
                status.includes(search)
            )
        })
    }, [callStore, recentSearch, historyVersion])

    const updateMissedUnreadCount = useCallback((): void => {
        const unreadCount = callStore.missedCalls.filter(
            (call) => !call.read,
        ).length

        setMissedUnreadCount(unreadCount)
    }, [callStore])

    const loadCallCache = useCallback((): void => {
        callStore.recentCalls = uniqueByNumber(
            getCachedCalls(RECENT_CALLS_KEY),
            MAX_RECENT_CALLS,
        )

        callStore.missedCalls = uniqueByNumber(
            getCachedCalls(MISSED_CALLS_KEY),
            MAX_MISSED_CALLS,
        )

        updateMissedUnreadCount()
        refreshHistory()
    }, [callStore, refreshHistory, updateMissedUnreadCount])

    const saveCallCache = useCallback((): void => {
        callStore.recentCalls = uniqueByNumber(
            callStore.recentCalls,
            MAX_RECENT_CALLS,
        )

        callStore.missedCalls = uniqueByNumber(
            callStore.missedCalls,
            MAX_MISSED_CALLS,
        )

        localStorage.setItem(
            RECENT_CALLS_KEY,
            JSON.stringify(callStore.recentCalls),
        )

        localStorage.setItem(
            MISSED_CALLS_KEY,
            JSON.stringify(callStore.missedCalls),
        )

        updateMissedUnreadCount()
        refreshHistory()
    }, [callStore, refreshHistory, updateMissedUnreadCount])

    const addRecentCall = useCallback(
        (
            number: string,
            direction: RecentCall['direction'] = 'outbound',
            status = 'answered',
        ): void => {
            const normalizedNumber = String(number ?? '').trim()

            if (!normalizedNumber) {
                return
            }

            const item: RecentCall = {
                id: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
                number: normalizedNumber,
                direction,
                status,
                read: status !== 'missed',
                time: new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                }),
                created_at: new Date().toISOString(),
            }

            callStore.recentCalls = [
                item,
                ...callStore.recentCalls.filter(
                    (call) => call?.number !== normalizedNumber,
                ),
            ]

            if (status === 'missed') {
                callStore.missedCalls = [
                    item,
                    ...callStore.missedCalls.filter(
                        (call) => call?.number !== normalizedNumber,
                    ),
                ]
            }

            saveCallCache()
        },
        [callStore, saveCallCache],
    )

    const markMissedRead = useCallback((): void => {
        callStore.missedCalls = callStore.missedCalls.map((call) => ({
            ...call,
            read: true,
        }))

        saveCallCache()
    }, [callStore, saveCallCache])

    const clearMissedCalls = useCallback((): void => {
        callStore.missedCalls = []
        saveCallCache()
    }, [callStore, saveCallCache])

    const confirmClearMissed = useCallback((): void => {
        if (!callStore.missedCalls.length) {
            return
        }

        const confirmed = window.confirm(
            'Clear all missed calls? This cannot be undone.',
        )

        if (confirmed) {
            clearMissedCalls()
        }
    }, [callStore, clearMissedCalls])

    return {
        recentSearch,
        setRecentSearch,

        missedUnreadCount,
        filteredRecentCalls,

        loadCallCache,
        saveCallCache,
        addRecentCall,
        markMissedRead,
        clearMissedCalls,
        confirmClearMissed,
    }
}