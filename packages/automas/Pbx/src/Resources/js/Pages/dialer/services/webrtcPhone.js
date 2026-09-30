import JsSIP from 'jssip'
import axios from 'axios'

let ua = null
let session = null
let lastCalledNumber = null
let lastCallRetried = false
let phoneConfig = null

let incomingRingtone = null
let sessionStartedAt = null

let transferInProgress = false
let transferCompleted = false

let reconnectTimer = null
let reconnectAttempts = 0
let ownerPollTimer = null

let statsInterval = null

const MAX_RECONNECT_ATTEMPTS = 10
const OWNER_KEY = 'cti_dialer_owner'
const HEARTBEAT_KEY = 'cti_dialer_heartbeat'
const TIMEOUT = 5000
const TAB_ID = `${Date.now()}-${Math.random().toString(36).slice(2)}`


function scheduleReconnect() {
    // If there's already a reconnect timer scheduled, do nothing.
    if (reconnectTimer) return

    // If another tab claims ownership and is alive, do NOT attempt reconnect here.
    try {
        const owner = localStorage.getItem(OWNER_KEY)
        const heartbeat = Number(localStorage.getItem(HEARTBEAT_KEY) || 0)
        const now = Date.now()
        const ownerAlive = owner && now - heartbeat < TIMEOUT

        if (ownerAlive && owner !== TAB_ID) {
            // Poll until owner disappears/heartbeat expires, then start reconnect.
            if (ownerPollTimer) return

            ownerPollTimer = setInterval(() => {
                const hb = Number(localStorage.getItem(HEARTBEAT_KEY) || 0)
                const ow = localStorage.getItem(OWNER_KEY)
                if (!ow || Date.now() - hb >= TIMEOUT) {
                    clearInterval(ownerPollTimer)
                    ownerPollTimer = null
                    // attempt reconnect now
                    scheduleReconnect()
                }
            }, Math.max(1000, TIMEOUT))

            return
        }
    } catch (e) {
        // ignore and continue to reconnect
    }

    const delay = Math.min(3000 * (reconnectAttempts + 1), 30000)

    reconnectTimer = setTimeout(() => {
        reconnectTimer = null
        reconnectAttempts++

        console.log(`Trying WebRTC reconnect... attempt ${reconnectAttempts}`)

        try {
            if (!navigator.onLine) {
                console.log('Browser still offline, waiting...')
                scheduleReconnect()
                return
            }

            if (ua && !ua.isConnected()) {
                ua.start()
            } else if (ua && ua.isConnected() && !ua.isRegistered()) {
                ua.register()
            }
        } catch (err) {
            console.error('Reconnect failed:', err)
        }

        if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
            scheduleReconnect()
        }
    }, delay)
}

function startCallStats() {
    stopCallStats()

    if (!session?.connection) return

    statsInterval = setInterval(async () => {
        try {
            const stats = await session.connection.getStats()

            stats.forEach(report => {
                if (report.type === 'candidate-pair' && report.state === 'succeeded') {

                    window.dispatchEvent(new CustomEvent('cti:network-stats', {
                        detail: {
                            latency: report.currentRoundTripTime
                                ? Math.round(report.currentRoundTripTime * 1000)
                                : null,
                        }
                    }))
                }
            })

        } catch (e) {
            console.warn(e)
        }
    }, 3000)
}

function stopCallStats() {
    if (statsInterval) {
        clearInterval(statsInterval)
        statsInterval = null
    }
}

function clearReconnect() {
    if (reconnectTimer) {
        clearTimeout(reconnectTimer)
        reconnectTimer = null
    }

    reconnectAttempts = 0
    if (ownerPollTimer) {
        clearInterval(ownerPollTimer)
        ownerPollTimer = null
    }
}

function logSessionEvent(payload) {
    if (!window.Dialer?.api?.callEvents) return

    return axios.post(window.Dialer.api.callEvents, payload).catch(err => {
        console.warn('PBX session log failed:', err)
    })
}

function playIncomingRingtone() {
    console.log(window.Dialer.assets.ringtone)
    if (!incomingRingtone) {
        incomingRingtone = new Audio(window.Dialer.assets.ringtone)
        incomingRingtone.loop = true
        incomingRingtone.volume = 1
    }

    incomingRingtone.play().catch(err => {
        console.warn('Ringtone blocked:', err)
    })
}

function stopIncomingRingtone() {
    if (!incomingRingtone) return

    incomingRingtone.pause()
    incomingRingtone.currentTime = 0
}

async function checkMicrophonePermission() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            audio: true,
            video: false,
        })

        stream.getTracks().forEach(track => track.stop())
        return true
    } catch (err) {
        window.dispatchEvent(new CustomEvent('cti:mic-error', {
            detail: {
                reason: err.message || 'Microphone permission denied',
            },
        }))

        return false
    }
}

export function initWebRTCPhone(config) {
    phoneConfig = config

    console.log('WebSocket URL:', config.websocket_url)
    const socket = new JsSIP.WebSocketInterface(config.websocket_url)

    ua = new JsSIP.UA({
        sockets: [socket],
        uri: `sip:${config.extension}@${config.sip_domain}`,
        password: config.sip_secret,
        display_name: config.caller_id || config.extension,
        register: true,
        session_timers: false,
    })

    ua.on('connecting', () => {
        console.log('WebRTC connecting...')
    })

    ua.on('connected', () => {
        console.log('WebRTC socket connected')
        clearReconnect()
        window.dispatchEvent(new CustomEvent('cti:socket-connected'))
    })

    ua.on('disconnected', () => {
        console.log('WebRTC socket disconnected')
        window.dispatchEvent(new CustomEvent('cti:socket-disconnected'))
        scheduleReconnect()
    })

    ua.on('registered', () => {
        console.log('WebRTC registered')
        clearReconnect()
        window.dispatchEvent(new CustomEvent('cti:registered'))
    })

    ua.on('registrationFailed', (e) => {
        console.error('WebRTC registration failed:', e.cause, e)
        window.dispatchEvent(new CustomEvent('cti:registration-failed', {
            detail: {
                reason: e.cause || 'Registration failed',
            },
        }))
        scheduleReconnect()
    })

    function attachRemoteAudio(session) {
        const pc = session.connection
        if (!pc) return

        pc.addEventListener('track', (event) => {
            console.log('Remote track received:', event.track.kind, event.streams)

            let audio = document.getElementById('remoteAudio')

            if (!audio) {
                audio = document.createElement('audio')
                audio.id = 'remoteAudio'
                audio.autoplay = true
                audio.playsInline = true
                document.body.appendChild(audio)
            }

            if (event.streams && event.streams[0]) {
                audio.srcObject = event.streams[0]
            } else {
                const stream = new MediaStream([event.track])
                audio.srcObject = stream
            }

            audio.muted = false
            audio.volume = 1

            audio.play().catch(err => {
                console.warn('Remote audio play blocked:', err)
            })
        })
    }

    ua.on('newRTCSession', (e) => {
        session = e.session
        window.CTI_PHONE.currentSession = session
        sessionStartedAt = Date.now()
        transferInProgress = false
        transferCompleted = false

        console.log('New RTC session:', e.originator, session)

        attachRemoteAudio(session)

        session.on('peerconnection', () => {
            console.log('PeerConnection created')
            attachRemoteAudio(session)
        })

        const getSessionNumber = () => {
            return e.originator === 'remote'
                ? session.remote_identity?.uri?.user || 'Unknown'
                : lastCalledNumber || session.remote_identity?.uri?.user || 'Unknown'
        }

        const getDirection = () => {
            return e.originator === 'remote' ? 'inbound' : 'outbound'
        }

        if (e.originator === 'local') {
            window.dispatchEvent(new CustomEvent('cti:call-calling'))
        }

        session.on('progress', () => {
            console.log('Call progress/ringing')
            window.dispatchEvent(new CustomEvent('cti:call-ringing'))
        })

        if (e.originator === 'remote') {
            playIncomingRingtone()

            logSessionEvent({
                number: getSessionNumber(),
                direction: 'inbound',
                status: 'initiated',
                uniqueid: session.id,
                linkedid: session.id,
                call_started_at: new Date().toISOString(),
            })

            window.dispatchEvent(new CustomEvent('cti:sip-incoming', {
                detail: {
                    caller: getSessionNumber(),
                },
            }))
        }

        session.on('accepted', () => {
            stopIncomingRingtone()
            console.log('[PBX SESSION] accepted')
            console.log('Call accepted/active')

            attachRemoteAudio(session)

            window.dispatchEvent(new CustomEvent('cti:call-active'))

            logSessionEvent({
                number: getSessionNumber(),
                direction: getDirection(),
                status: 'answered',
                uniqueid: session.id,
                linkedid: session.id,
                call_started_at: sessionStartedAt
                    ? new Date(sessionStartedAt).toISOString()
                    : new Date().toISOString(),
            })

            startCallStats()
        })

        session.on('confirmed', () => {
            console.log('[PBX SESSION] confirmed')
            console.log('Call confirmed')

            const senders = session.connection?.getSenders() || []
            console.log('Senders:', senders.map(s => ({
                kind: s.track?.kind,
                enabled: s.track?.enabled,
                readyState: s.track?.readyState,
            })))

            const receivers = session.connection?.getReceivers() || []
            console.log('Receivers:', receivers.map(r => ({
                kind: r.track?.kind,
                enabled: r.track?.enabled,
                readyState: r.track?.readyState,
            })))

            window.dispatchEvent(new CustomEvent('cti:call-active'))
        })

        session.on('ended', (event) => {
            console.log('[PBX SESSION] ended', event.cause)
            console.log('Call ended:', event.cause)

            stopCallStats()

            const duration = sessionStartedAt
                ? Math.max(0, Math.floor((Date.now() - sessionStartedAt) / 1000))
                : 0

            logSessionEvent({
                number: getSessionNumber(),
                direction: getDirection(),
                status: 'ended',
                uniqueid: session.id,
                linkedid: session.id,
                call_started_at: sessionStartedAt
                    ? new Date(sessionStartedAt).toISOString()
                    : new Date().toISOString(),
                call_ended_at: new Date().toISOString(),
                duration,
            })

            resetSession(true, {
                reason: event.cause || 'Call ended',
                status: 'completed',
                direction: getDirection(),
                number: getSessionNumber(),
            })
        })

        session.on('failed', (event) => {
            console.error('[PBX SESSION] failed', event.cause)
            console.error('Call failed:', event.cause, event)

            stopCallStats()

            try {
                const cause = event?.cause || ''

                if (
                    !lastCallRetried &&
                    lastCalledNumber &&
                    typeof cause === 'string' &&
                    cause.toLowerCase().includes('not found')
                ) {
                    const stripped = lastCalledNumber.replace(/^\+/, '')

                    if (stripped && stripped !== lastCalledNumber) {
                        console.log('Retrying call without leading +:', stripped)

                        lastCallRetried = true
                        resetSession()

                        setTimeout(() => {
                            call(stripped)
                        }, 300)

                        return
                    }
                }
            } catch (err) {
                console.warn('Retry logic error:', err)
            }

            const duration = sessionStartedAt
                ? Math.max(0, Math.floor((Date.now() - sessionStartedAt) / 1000))
                : 0

            logSessionEvent({
                number: getSessionNumber(),
                direction: getDirection(),
                status: mapFailedCauseToStatus(event.cause),
                uniqueid: session.id,
                linkedid: session.id,
                call_started_at: sessionStartedAt
                    ? new Date(sessionStartedAt).toISOString()
                    : new Date().toISOString(),
                call_ended_at: new Date().toISOString(),
                duration,
            })

            window.dispatchEvent(new CustomEvent('cti:call-failed', {
                detail: {
                    reason: event.cause || 'Call failed',
                    status: mapFailedCauseToStatus(event.cause),
                    direction: getDirection(),
                    number: getSessionNumber(),
                },
            }))

            resetSession(false)
        })
    })
    function mapFailedCauseToStatus(cause) {
        const value = String(cause || '').toLowerCase()

        if (value.includes('rejected')) return 'rejected'
        if (value.includes('busy')) return 'busy'
        if (value.includes('unavailable')) return 'unavailable'
        if (value.includes('not found')) return 'not_found'
        if (value.includes('canceled')) return 'cancelled'
        if (value.includes('no answer')) return 'no_answer'

        return 'failed'
    }

    ua.start()

    window.addEventListener('online', () => {
        console.log('Internet restored')

        if (ua) {
            try {
                if (!ua.isConnected()) {
                    ua.start()
                } else if (!ua.isRegistered()) {
                    ua.register()
                }
            } catch (err) {
                console.error('Online reconnect failed:', err)
            }
        }
    })

    window.addEventListener('offline', () => {
        console.log('Internet lost')
        window.dispatchEvent(new CustomEvent('cti:socket-disconnected'))
    })
}


function getCallOptions() {
    return {
        mediaConstraints: {
            audio: true,
            video: false,
        },
        rtcOfferConstraints: {
            offerToReceiveAudio: true,
            offerToReceiveVideo: false,
        },
        pcConfig: {
            iceServers: [],
            iceTransportPolicy: 'all',
        },
    }
}

// function getCallOptions() {
//     const iceServers = phoneConfig?.ice_servers?.length
//         ? phoneConfig.ice_servers
//         : [
//             { urls: 'stun:stun.l.google.com:19302' }
//         ]

//     return {
//         mediaConstraints: {
//             audio: true,
//             video: false,
//         },
//         rtcOfferConstraints: {
//             offerToReceiveAudio: true,
//             offerToReceiveVideo: false,
//         },
//         pcConfig: {
//             iceServers,
//             iceTransportPolicy: 'all',
//         },
//     }
// }

export async function call(number) {
    if (!ua || !ua.isRegistered()) return false

    const micOk = await checkMicrophonePermission()
    if (!micOk) return false

    lastCalledNumber = number
    lastCallRetried = false

    const target = number.includes('@')
        ? `sip:${number}`
        : `sip:${number}@${phoneConfig.sip_domain}`

    console.log('Calling number:', target)

    session = ua.call(target, getCallOptions())
    logSessionEvent({
        number,
        direction: 'outbound',
        status: 'initiated',
        uniqueid: session.id,
        linkedid: session.id,
        call_started_at: new Date().toISOString(),
    })
    window.CTI_PHONE.currentSession = session

    return true
}

function getActiveSession() {
    if (session) return session

    if (window.CTI_PHONE?.currentSession) return window.CTI_PHONE.currentSession

    try {
        if (ua && typeof ua.sessions?.values === 'function') {
            const first = ua.sessions.values().next().value
            if (first) return first
        }
    } catch (err) {
        console.warn('Unable to resolve active SIP session from UA:', err)
    }

    return null
}

export async function answer() {
    const activeSession = getActiveSession()

    if (!activeSession) {
        console.error('No session to answer')
        return false
    }

    session = activeSession
    window.CTI_PHONE.currentSession = activeSession

    const micOk = await checkMicrophonePermission()
    if (!micOk) return false

    console.log('Answering incoming call')
    activeSession.answer(getCallOptions())

    return true
}

function isSessionEnded(activeSession) {
    return !activeSession ||
        activeSession.status === 8 ||
        activeSession.isEnded?.() ||
        activeSession.isCanceled?.()
}

export function hangup() {
    const activeSession = getActiveSession()
    if (!activeSession || isSessionEnded(activeSession)) return false

    try {
        session = activeSession
        window.CTI_PHONE.currentSession = activeSession
        activeSession.terminate()
        return true
    } catch (err) {
        console.warn('Hangup ignored, session already ended:', err.message)
        return false
    }
}

export function reject() {
    stopIncomingRingtone()

    const activeSession = getActiveSession()

    if (!activeSession || isSessionEnded(activeSession)) {
        console.warn('No active session to reject')
        return false
    }

    session = activeSession
    window.CTI_PHONE.currentSession = activeSession

    try {
        console.log('Rejecting incoming call')

        activeSession.terminate({
            status_code: 486,
            reason_phrase: 'Busy Here',
        })

        return true
    } catch (err) {
        console.warn('Reject ignored, session already ended:', err.message)
        return false
    }
}

export function sendDTMF(key) {
    session?.sendDTMF(key)
}

export function mute() {
    session?.mute({ audio: true })
}

export function unmute() {
    session?.unmute({ audio: true })
}

export function hold() {
    session?.hold()
}

export function unhold() {
    session?.unhold()
}

export function register() {
    ua?.register()
}

export async function transfer(target) {
    console.log('[PBX TRANSFER] requested', target)

    if (transferInProgress || transferCompleted) {
        console.error('[PBX TRANSFER] failed', 'Call has already been transferred or transfer in progress')
        throw new Error('Call has already been transferred.')
    }

    const activeSession = getActiveSession()

    if (!activeSession) {
        console.error('[PBX TRANSFER] failed', 'No active call')
        throw new Error('No active call to transfer.')
    }

    if (typeof activeSession.isEstablished === 'function' && !activeSession.isEstablished()) {
        console.error('[PBX TRANSFER] failed', 'Call is not established')
        throw new Error('Call is not established.')
    }

    const cleanTarget = String(target || '').trim()
    if (!cleanTarget) {
        console.error('[PBX TRANSFER] failed', 'Empty target extension')
        throw new Error('Invalid destination extension.')
    }

    const ownExt = phoneConfig?.extension ? String(phoneConfig.extension).trim() : null
    if (ownExt && cleanTarget === ownExt) {
        console.error('[PBX TRANSFER] failed', 'Cannot transfer to own extension')
        throw new Error('You cannot transfer a call to your own extension.')
    }

    const sipDomain = phoneConfig?.sip_domain || 'localhost'
    const uri = cleanTarget.includes('@')
        ? (cleanTarget.startsWith('sip:') ? cleanTarget : `sip:${cleanTarget}`)
        : `sip:${cleanTarget}@${sipDomain}`

    console.log('[PBX TRANSFER] REFER destination', uri)

    transferInProgress = true

    return new Promise((resolve, reject) => {
        let isResolved = false

        try {
            const referSubscriber = activeSession.refer(uri, {
                eventHandlers: {
                    requestSucceeded: (event) => {
                        console.log('[PBX TRANSFER] REFER sent & accepted by Asterisk')
                        transferInProgress = false
                        transferCompleted = true

                        window.dispatchEvent(new CustomEvent('cti:transfer-success', {
                            detail: { target: cleanTarget, uri }
                        }))

                        if (!isResolved) {
                            isResolved = true
                            resolve({ status: 'transferred', event })
                        }

                        // Blind Transfer Completion:
                        // Asterisk has accepted the REFER request.
                        // Agent 101 must leave the call immediately while Asterisk rings 105 independently.
                        setTimeout(() => {
                            try {
                                if (activeSession && typeof activeSession.terminate === 'function') {
                                    console.log('[PBX TRANSFER] Terminating local agent session after blind transfer acceptance')
                                    activeSession.terminate()
                                }
                            } catch (termErr) {
                                console.warn('[PBX TRANSFER] Error terminating session after transfer:', termErr)
                            }
                        }, 50)
                    },
                    requestFailed: (event) => {
                        const errorCause = event?.cause || 'Unable to transfer the call.'
                        console.error('[PBX TRANSFER] failed', errorCause)
                        transferInProgress = false
                        transferCompleted = false

                        window.dispatchEvent(new CustomEvent('cti:transfer-failed', {
                            detail: { target: cleanTarget, reason: errorCause }
                        }))

                        if (!isResolved) {
                            isResolved = true
                            reject(new Error(errorCause))
                        }
                    },
                    accepted: (event) => {
                        console.log('[PBX TRANSFER] NOTIFY accepted (transfer complete)', event)
                    },
                    failed: (event) => {
                        console.error('[PBX TRANSFER] NOTIFY failed (target status)', event)
                    }
                }
            })

            if (referSubscriber === false) {
                transferInProgress = false
                transferCompleted = false
                const err = new Error('Unable to transfer the call.')
                console.error('[PBX TRANSFER] failed', err)
                window.dispatchEvent(new CustomEvent('cti:transfer-failed', {
                    detail: { target: cleanTarget, reason: err.message }
                }))
                if (!isResolved) {
                    isResolved = true
                    reject(err)
                }
            }
        } catch (err) {
            transferInProgress = false
            transferCompleted = false
            console.error('[PBX TRANSFER] failed', err)
            window.dispatchEvent(new CustomEvent('cti:transfer-failed', {
                detail: { target: cleanTarget, reason: err.message || 'Unable to transfer the call.' }
            }))
            if (!isResolved) {
                isResolved = true
                reject(err)
            }
        }
    })
}

function resetSession(dispatchEnded = false, detail = {}) {
    stopIncomingRingtone()
    session = null
    sessionStartedAt = null
    transferInProgress = false
    transferCompleted = false
    window.CTI_PHONE.currentSession = null

    if (dispatchEnded) {
        window.dispatchEvent(new CustomEvent('cti:call-ended', {
            detail,
        }))
    }
}

window.CTI_PHONE = {
    call,
    answer,
    hangup,
    reject,
    sendDTMF,
    mute,
    unmute,
    hold,
    unhold,
    register,
    transfer,
    getExtension: () => phoneConfig?.extension ? String(phoneConfig.extension) : null,
    isTransferInProgress: () => transferInProgress,
    isTransferCompleted: () => transferCompleted,
    currentSession: null,
}