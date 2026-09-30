import { toast } from 'sonner'
import {
    blinkTitle,
    playBeep,
    stopDialerAttention,
} from './dialerAttention'

const OWNER_KEY = 'cti_dialer_owner'
const HEARTBEAT_KEY = 'cti_dialer_heartbeat'
const CHANNEL_NAME = 'cti_dialer_channel'

const OWNER_ALIVE_CHECK = 'OWNER_ALIVE_CHECK'
const OWNER_ALIVE_ACK = 'OWNER_ALIVE_ACK'
const OWNER_RELEASED = 'OWNER_RELEASED'
const FOCUS_DIALER_OWNER = 'FOCUS_DIALER_OWNER'
const OWNER_FOCUSED = 'OWNER_FOCUSED'
const MAKE_CALL = 'MAKE_CALL'

const TAB_ID =
    typeof window !== 'undefined'
        ? `${Date.now()}-${Math.random().toString(36).slice(2)}`
        : 'server'

const TIMEOUT = 45_000
const CHECK_INTERVAL = 10_000
const HEARTBEAT_INTERVAL = 1_000
const CLAIM_CONFIRMATION_DELAY = 120
const ALIVE_RESPONSE_TIMEOUT = 1_500

type OwnershipListener = (isOwner: boolean) => void

interface DialerChannelMessage {
    type: string
    from: string
    requestId?: string
    number?: string
    focused?: boolean
}

let isOwner = false
let started = false

let channel: BroadcastChannel | null = null
let heartbeatTimer: number | null = null
let ownershipCheckTimer: number | null = null

const ownershipListeners = new Set<OwnershipListener>()

function now(): number {
    return Date.now()
}

function getStoredOwner(): string | null {
    if (typeof localStorage === 'undefined') return null

    return localStorage.getItem(OWNER_KEY)
}

function getStoredHeartbeat(): number {
    if (typeof localStorage === 'undefined') return 0

    return Number(localStorage.getItem(HEARTBEAT_KEY) || 0)
}

function setOwnerState(value: boolean): void {
    if (isOwner === value) return

    isOwner = value

    ownershipListeners.forEach((listener) => {
        listener(value)
    })
}

function getChannel(): BroadcastChannel | null {
    if (typeof BroadcastChannel === 'undefined') {
        return null
    }

    if (!channel) {
        channel = new BroadcastChannel(CHANNEL_NAME)
    }

    return channel
}

function postMessage(message: DialerChannelMessage): void {
    try {
        getChannel()?.postMessage(message)
    } catch (error) {
        console.warn('Unable to send dialer channel message:', error)
    }
}

export function getIsOwnerTab(): boolean {
    return isOwner
}

export function getDialerTabId(): string {
    return TAB_ID
}

export function subscribeToDialerOwnership(
    listener: OwnershipListener,
): () => void {
    ownershipListeners.add(listener)
    listener(isOwner)

    return () => {
        ownershipListeners.delete(listener)
    }
}

export function currentOwnerAlive(): boolean {
    const owner = getStoredOwner()
    const heartbeat = getStoredHeartbeat()

    return Boolean(
        owner &&
        now() - heartbeat < TIMEOUT,
    )
}

function refreshHeartbeat(): void {
    if (getStoredOwner() !== TAB_ID) return

    localStorage.setItem(
        HEARTBEAT_KEY,
        String(now()),
    )
}

function stopHeartbeat(): void {
    if (heartbeatTimer !== null) {
        window.clearInterval(heartbeatTimer)
        heartbeatTimer = null
    }
}

function startHeartbeat(): void {
    stopHeartbeat()
    refreshHeartbeat()

    heartbeatTimer = window.setInterval(() => {
        if (getStoredOwner() === TAB_ID) {
            refreshHeartbeat()
            return
        }

        stopHeartbeat()
        setOwnerState(false)
    }, HEARTBEAT_INTERVAL)
}

function ownerAliveCheck(): Promise<boolean> {
    const owner = getStoredOwner()
    const heartbeat = getStoredHeartbeat()

    if (!owner) {
        return Promise.resolve(false)
    }

    if (owner === TAB_ID) {
        return Promise.resolve(true)
    }

    if (now() - heartbeat < TIMEOUT) {
        return Promise.resolve(true)
    }

    const currentChannel = getChannel()

    if (!currentChannel) {
        return Promise.resolve(false)
    }

    return new Promise<boolean>((resolve) => {
        const requestId = `${TAB_ID}-${now()}`

        let timeoutId: number | null = null
        let settled = false

        const cleanup = (): void => {
            if (timeoutId !== null) {
                window.clearTimeout(timeoutId)
                timeoutId = null
            }

            currentChannel.removeEventListener(
                'message',
                handleAliveAcknowledgement,
            )
        }

        const finish = (alive: boolean): void => {
            if (settled) return

            settled = true
            cleanup()
            resolve(alive)
        }

        const handleAliveAcknowledgement = (
            event: MessageEvent<DialerChannelMessage>,
        ): void => {
            const data = event.data

            if (
                !data ||
                data.type !== OWNER_ALIVE_ACK ||
                data.requestId !== requestId ||
                data.from !== owner
            ) {
                return
            }

            finish(true)
        }

        currentChannel.addEventListener(
            'message',
            handleAliveAcknowledgement,
        )

        postMessage({
            type: OWNER_ALIVE_CHECK,
            from: TAB_ID,
            requestId,
        })

        timeoutId = window.setTimeout(() => {
            finish(false)
        }, ALIVE_RESPONSE_TIMEOUT)
    })
}

export async function claimDialerOwnership(): Promise<boolean> {
    const owner = getStoredOwner()
    const heartbeat = getStoredHeartbeat()

    if (
        owner &&
        owner !== TAB_ID &&
        now() - heartbeat < TIMEOUT
    ) {
        stopHeartbeat()
        setOwnerState(false)

        return false
    }

    if (owner && owner !== TAB_ID) {
        const alive = await ownerAliveCheck()

        if (alive) {
            stopHeartbeat()
            setOwnerState(false)

            return false
        }
    }

    localStorage.setItem(OWNER_KEY, TAB_ID)
    localStorage.setItem(
        HEARTBEAT_KEY,
        String(now()),
    )

    await new Promise<void>((resolve) => {
        window.setTimeout(
            resolve,
            CLAIM_CONFIRMATION_DELAY,
        )
    })

    const ownershipConfirmed =
        getStoredOwner() === TAB_ID

    if (ownershipConfirmed) {
        setOwnerState(true)
        startHeartbeat()

        return true
    }

    stopHeartbeat()
    setOwnerState(false)

    return false
}

export async function syncDialerOwnership(): Promise<void> {
    const owner = getStoredOwner()
    const heartbeat = getStoredHeartbeat()

    if (owner === TAB_ID) {
        setOwnerState(true)
        startHeartbeat()

        return
    }

    if (
        owner &&
        now() - heartbeat < TIMEOUT
    ) {
        stopHeartbeat()
        setOwnerState(false)

        return
    }

    await claimDialerOwnership()
}

export function releaseDialerOwnership(): void {
    if (getStoredOwner() === TAB_ID) {
        localStorage.removeItem(OWNER_KEY)
        localStorage.removeItem(HEARTBEAT_KEY)

        postMessage({
            type: OWNER_RELEASED,
            from: TAB_ID,
        })
    }

    stopHeartbeat()
    setOwnerState(false)
}

function sendMakeCallMessage(number: string): void {
    postMessage({
        type: MAKE_CALL,
        from: TAB_ID,
        number,
    })
}

export async function forwardMakeCallToOwner(
    number: string,
): Promise<boolean> {
    const target = number.trim()
    const owner = getStoredOwner()

    if (!target || !owner || owner === TAB_ID) {
        return false
    }

    if (currentOwnerAlive()) {
        sendMakeCallMessage(target)
        return true
    }

    const alive = await ownerAliveCheck()

    if (!alive) {
        return false
    }

    sendMakeCallMessage(target)

    return true
}

export async function openDialerPopup(): Promise<void> {
    const owner = getStoredOwner()

    if (!currentOwnerAlive()) {
        await claimDialerOwnership()
        return
    }

    if (owner === TAB_ID) {
        window.focus()

        window.dispatchEvent(
            new CustomEvent('cti:open-dialer-panel'),
        )

        return
    }

    const currentChannel = getChannel()

    if (!currentChannel) {
        showOwnerTabMessage()
        return
    }

    let responded = false

    const handleOwnerResponse = (
        event: MessageEvent<DialerChannelMessage>,
    ): void => {
        const data = event.data

        if (
            !data ||
            data.from === TAB_ID ||
            data.type !== OWNER_FOCUSED
        ) {
            return
        }

        responded = true

        currentChannel.removeEventListener(
            'message',
            handleOwnerResponse,
        )

        if (!data.focused) {
            showOwnerTabMessage()
        }
    }

    currentChannel.addEventListener(
        'message',
        handleOwnerResponse,
    )

    postMessage({
        type: FOCUS_DIALER_OWNER,
        from: TAB_ID,
    })

    window.setTimeout(() => {
        currentChannel.removeEventListener(
            'message',
            handleOwnerResponse,
        )

        if (!responded) {
            showOwnerTabMessage()
        }
    }, 800)
}

function showOwnerTabMessage(): void {
    toast.warning(
        'The dialer is already running in another browser tab. Please switch to the tab where the dialer is currently active.',
    )
}

function handleChannelMessage(
    event: MessageEvent<DialerChannelMessage>,
): void {
    const data = event.data

    if (!data || data.from === TAB_ID) return

    if (data.type === OWNER_ALIVE_CHECK) {
        if (!isOwner) return

        refreshHeartbeat()

        postMessage({
            type: OWNER_ALIVE_ACK,
            from: TAB_ID,
            requestId: data.requestId,
        })

        return
    }

    if (
        data.type === FOCUS_DIALER_OWNER &&
        isOwner
    ) {
        try {
            window.focus()
        } catch {
            // Browsers may block programmatic focus.
        }

        window.dispatchEvent(
            new CustomEvent('cti:open-dialer-panel'),
        )

        blinkTitle(2000)
        playBeep()

        postMessage({
            type: OWNER_FOCUSED,
            from: TAB_ID,
            focused: document.hasFocus(),
        })

        return
    }

    if (
        data.type === MAKE_CALL &&
        isOwner &&
        data.number
    ) {
        blinkTitle(2000)
        playBeep()

        window.dispatchEvent(
            new CustomEvent('cti:make-call', {
                detail: {
                    number: data.number,
                },
            }),
        )

        return
    }

    if (
        data.type === OWNER_RELEASED &&
        !isOwner
    ) {
        void claimDialerOwnership()
    }
}

function handleStorageChange(event: StorageEvent): void {
    if (
        event.key === OWNER_KEY ||
        event.key === HEARTBEAT_KEY
    ) {
        void syncDialerOwnership()
    }
}

function handleBeforeUnload(): void {
    releaseDialerOwnership()
}

export function startDialerTabOwnerService(): void {
    if (started || typeof window === 'undefined') {
        return
    }

    started = true

    getChannel()?.addEventListener(
        'message',
        handleChannelMessage,
    )

    window.addEventListener(
        'storage',
        handleStorageChange,
    )

    window.addEventListener(
        'beforeunload',
        handleBeforeUnload,
    )

    void claimDialerOwnership()

    ownershipCheckTimer = window.setInterval(() => {
        if (!isOwner) {
            void claimDialerOwnership()
        }
    }, CHECK_INTERVAL)
}

export function stopDialerTabOwnerService(): void {
    if (!started) return

    started = false

    if (ownershipCheckTimer !== null) {
        window.clearInterval(ownershipCheckTimer)
        ownershipCheckTimer = null
    }

    releaseDialerOwnership()

    channel?.removeEventListener(
        'message',
        handleChannelMessage,
    )

    channel?.close()
    channel = null

    window.removeEventListener(
        'storage',
        handleStorageChange,
    )

    window.removeEventListener(
        'beforeunload',
        handleBeforeUnload,
    )

    stopDialerAttention()
}