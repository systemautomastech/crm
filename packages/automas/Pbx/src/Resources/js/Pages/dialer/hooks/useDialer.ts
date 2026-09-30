import axios from 'axios'
import { toast } from 'sonner'
import {
  type CSSProperties,
  type PointerEvent as ReactPointerEvent,
  type RefObject,
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react'

import { initWebRTCPhone } from '../services/webrtcPhone'
import { forwardMakeCallToOwner } from '../services/dialerTabOwner'
import callStore, {
  type CallStore,
  type RecentCall,
} from '../store/callStore'

import { useCallHistory } from './useCallHistory'
import { useCallerLookup } from './useCallerLookup'
import { useCallTimer } from './useCallTimer'
import { useCallTransfer } from './useCallTransfer'

export type DialerPanel = 'dialpad' | 'calls'

export interface DialpadKey {
  value: string
  label: string
}

interface CallContext {
  module?: string | null
  record_id?: number | string | null
}

interface DialerApiConfiguration {
  callerLookup?: string
  clickToCall?: string
  webrtcConfig?: string
}


interface CTIPhone {
  call?: (
    number: string,
  ) => boolean | void | Promise<boolean | void>

  answer?: () => void | Promise<void>
  reject?: () => void | Promise<void>
  hangup?: () => void | Promise<void>

  transfer?: (
    target: string,
  ) => Promise<{ status: string; event: any }>
  getExtension?: () => string | null

  mute?: () => void | Promise<void>
  unmute?: () => void | Promise<void>

  hold?: () => void | Promise<void>
  unhold?: () => void | Promise<void>

  register?: () => void | Promise<void>
  sendDTMF?: (key: string) => void

  currentSession?: {
    terminate?: () => void
  }
}

interface CTIEventDetail {
  caller?: string
  number?: string
  direction?: string
  status?: string
  reason?: string
  latency?: number | null
}

interface MakeCallEventDetail {
  number?: string
}

interface Position {
  x: number
  y: number
}

const typedWindow = window

const DIALPAD_KEYS: DialpadKey[] = [
  { value: '1', label: '' },
  { value: '2', label: 'ABC' },
  { value: '3', label: 'DEF' },
  { value: '4', label: 'GHI' },
  { value: '5', label: 'JKL' },
  { value: '6', label: 'MNO' },
  { value: '7', label: 'PQRS' },
  { value: '8', label: 'TUV' },
  { value: '9', label: 'WXYZ' },
  { value: '*', label: '' },
  { value: '0', label: '+' },
  { value: '#', label: '' },
]

function getFormattedCurrentTime(): string {
  return new Date().toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  })
}

function getEventDetail(
  event: Event,
): CTIEventDetail {
  return (
    event as CustomEvent<CTIEventDetail>
  ).detail ?? {}
}

function terminateCurrentSession(): void {
  try {
    if (typedWindow.CTI_PHONE?.hangup) {
      void typedWindow.CTI_PHONE.hangup()
      return
    }

    typedWindow.CTI_PHONE?.currentSession?.terminate?.()
  } catch (error) {
    console.warn(
      'Error terminating the current CTI session:',
      error,
    )
  }
}

function useDialerDraggable(
  panelRef: RefObject<HTMLDivElement | null>,
  initialPosition: Position,
) {
  const [position, setPosition] =
    useState<Position>(initialPosition)

  const dragStateRef = useRef<{
    dragging: boolean
    offsetX: number
    offsetY: number
  }>({
    dragging: false,
    offsetX: 0,
    offsetY: 0,
  })

  const handlePointerMove = useCallback(
    (event: PointerEvent): void => {
      if (!dragStateRef.current.dragging) return

      const panel = panelRef.current

      const panelWidth =
        panel?.offsetWidth ?? 0

      const panelHeight =
        panel?.offsetHeight ?? 0

      const maximumX = Math.max(
        0,
        window.innerWidth - panelWidth,
      )

      const maximumY = Math.max(
        0,
        window.innerHeight - panelHeight,
      )

      const nextX =
        event.clientX -
        dragStateRef.current.offsetX

      const nextY =
        event.clientY -
        dragStateRef.current.offsetY

      setPosition({
        x: Math.min(
          Math.max(0, nextX),
          maximumX,
        ),
        y: Math.min(
          Math.max(0, nextY),
          maximumY,
        ),
      })
    },
    [panelRef],
  )

  const stopDragging = useCallback((): void => {
    if (!dragStateRef.current.dragging) return

    dragStateRef.current.dragging = false

    document.body.style.userSelect = ''

    window.removeEventListener(
      'pointermove',
      handlePointerMove,
    )

    window.removeEventListener(
      'pointerup',
      stopDragging,
    )
  }, [handlePointerMove])

  const startDragging = useCallback(
    (
      event: ReactPointerEvent<HTMLElement>,
    ): void => {
      if (event.button !== 0) return

      const target =
        event.target as HTMLElement

      if (
        target.closest(
          'button, input, textarea, select, a',
        )
      ) {
        return
      }

      const panel = panelRef.current

      if (!panel) return

      const bounds =
        panel.getBoundingClientRect()

      dragStateRef.current = {
        dragging: true,
        offsetX:
          event.clientX - bounds.left,
        offsetY:
          event.clientY - bounds.top,
      }

      document.body.style.userSelect = 'none'

      window.addEventListener(
        'pointermove',
        handlePointerMove,
      )

      window.addEventListener(
        'pointerup',
        stopDragging,
      )
    },
    [
      handlePointerMove,
      panelRef,
      stopDragging,
    ],
  )

  useEffect(() => {
    return () => {
      window.removeEventListener(
        'pointermove',
        handlePointerMove,
      )

      window.removeEventListener(
        'pointerup',
        stopDragging,
      )

      document.body.style.userSelect = ''
    }
  }, [handlePointerMove, stopDragging])

  const panelStyle = useMemo<CSSProperties>(
    () => ({
      position: 'fixed',
      left: position.x,
      top: position.y,
    }),
    [position],
  )

  return {
    panelStyle,
    startDragging,
    setPosition,
  }
}

export function useDialer() {
  const [isOpen, setIsOpen] = useState(false)
  const [number, setNumber] = useState('')
  const [currentTime, setCurrentTime] = useState(
    getFormattedCurrentTime,
  )

  const [activePanel, setActivePanel] =
    useState<DialerPanel>('dialpad')

  /*
   * callStore is temporarily a mutable compatibility store.
   * This revision forces React to render after direct store mutations.
   */
  const [, setStoreRevision] = useState(0)

  const panelRef =
    useRef<HTMLDivElement | null>(null)

  const headerRef =
    useRef<HTMLDivElement | null>(null)

  const forceStoreRender = useCallback((): void => {
    setStoreRevision(
      (currentRevision) =>
        currentRevision + 1,
    )
  }, [])

  const callTransfer = useCallTransfer(callStore)

  const {
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
  } = useCallHistory(callStore)

  const { performCallerLookup } =
    useCallerLookup(callStore)

  const {
    startTimer,
    stopTimer,
  } = useCallTimer(callStore)

  const {
    panelStyle,
    startDragging,
    setPosition: setPanelPosition,
  } = useDialerDraggable(
    panelRef,
    {
      x: 950,
      y: 15,
    },
  )

  const keys = DIALPAD_KEYS

  const isIncomingCall =
    callStore.callStatus === 'incoming'

  const isActiveCall =
    callStore.callStatus === 'active'

  const hasDigits =
    number.trim().length > 0

  const canPlaceCall =
    hasDigits &&
    callStore.registered &&
    callStore.callStatus !== 'ringing'

  const activeCallTitle =
    callStore.caller ||
    callStore.currentNumber ||
    number ||
    'Connected call'

  const statusLabel = useMemo((): string => {
    if (callStore.callStatus === 'incoming') {
      return 'Incoming'
    }

    if (callStore.callStatus === 'ringing') {
      return 'Ringing'
    }

    if (callStore.callStatus === 'active') {
      return 'Connected'
    }

    if (callStore.callStatus === 'calling') {
      return 'Calling'
    }

    return callStore.registered
      ? 'Ready'
      : 'Not registered'
  }, [
    callStore.callStatus,
    callStore.registered,
  ])

  const helperText = useMemo((): string => {
    if (callStore.callStatus === 'incoming') {
      return 'Use the answer or reject controls to respond.'
    }

    if (callStore.callStatus === 'ringing') {
      return 'The call is being placed.'
    }

    if (callStore.callStatus === 'active') {
      return 'Keypad presses send DTMF tones while the call is active.'
    }

    if (callStore.callStatus === 'calling') {
      return 'Starting the call...'
    }

    if (!callStore.registered) {
      return 'Register first to place or receive calls.'
    }

    return 'Type a number, then start the call.'
  }, [
    callStore.callStatus,
    callStore.registered,
  ])

  const inputPlaceholder =
    callStore.callStatus === 'incoming'
      ? 'Incoming call number'
      : 'Enter number or extension'

  const resetCallState = useCallback((): void => {
    stopTimer()

    callStore.callStatus = 'idle'

    callStore.agentStatus =
      callStore.registered
        ? 'available'
        : 'offline'

    callStore.direction = null
    callStore.caller = ''
    callStore.incomingNumber = ''
    callStore.currentNumber = ''
    callStore.contactName = ''
    callStore.module = null
    callStore.recordId = null
    callStore.callerInfo = null
    callStore.muted = false
    callStore.onHold = false

    forceStoreRender()
  }, [forceStoreRender, stopTimer])

  const clickToCallWithLogging = useCallback(
    async (
      phoneNumber: string,
      context: CallContext = {},
    ): Promise<void> => {
      const target = phoneNumber.trim()

      if (!target) return

      if (
        callStore.callStatus === 'active' ||
        callStore.callStatus === 'calling' ||
        callStore.callStatus === 'ringing' ||
        callStore.callStatus === 'incoming'
      ) {
        toast.warning(
          'A call is already in progress. Please end or complete the current call before placing a new call.',
        )
        return
      }

      if (!callStore.registered) {
        toast.warning(
          'Softphone is not registered. Please register your extension before making calls.',
        )
        return
      }

      setNumber(target)

      callStore.currentNumber = target
      callStore.caller = target

      if (context.module) {
        callStore.module = context.module

        callStore.recordId =
          context.record_id ?? null
      }

      setIsOpen(true)

      callStore.callStatus = 'calling'
      callStore.agentStatus = 'busy'

      forceStoreRender()

      try {
        addRecentCall(
          target,
          'outbound',
          'calling',
        )
      } catch (error) {
        console.warn(
          'Failed to add immediate recent call log:',
          error,
        )
      }

      try {
        const callImplementation =
          typeof typedWindow
            .CTI_PHONE?.call ===
            'function'
            ? typedWindow.CTI_PHONE.call.bind(
              typedWindow.CTI_PHONE,
            )
            : null

        if (callImplementation) {
          try {
            const result =
              callImplementation(target)

            if (
              result &&
              typeof (
                result as Promise<unknown>
              ).then === 'function'
            ) {
              Promise.resolve(result).catch(
                (error) => {
                  console.error(
                    'Error placing call through CTI_PHONE:',
                    error,
                  )
                },
              )
            }

            if (result === false) {
              console.warn(
                'CTI_PHONE.call returned false.',
              )

              resetCallState()
            }
          } catch (error) {
            console.error(
              'Error placing call through CTI_PHONE:',
              error,
            )
          }
        } else {
          console.warn(
            'No CTI_PHONE implementation is available.',
          )

          callStore.callStatus =
            'ringing'

          callStore.agentStatus =
            'busy'

          forceStoreRender()
        }
      } finally {
        const clickToCallUrl =
          typedWindow.Dialer?.api
            ?.clickToCall

        if (clickToCallUrl) {
          axios
            .post(clickToCallUrl, {
              number: target,
              module: context.module,
              record_id:
                context.record_id,
            })
            .catch((error) => {
              console.warn(
                'Failed to log click-to-call:',
                error,
              )
            })
        }
      }
    },
    [
      addRecentCall,
      forceStoreRender,
      resetCallState,
    ],
  )

  const callNumber = useCallback(
    async (
      requestedNumber?: string,
    ): Promise<void> => {
      const target = (
        requestedNumber ?? number
      ).trim()

      if (!target) return

      if (
        callStore.callStatus === 'active' ||
        callStore.callStatus === 'calling' ||
        callStore.callStatus === 'ringing' ||
        callStore.callStatus === 'incoming'
      ) {
        toast.warning(
          'A call is already in progress. Please end or complete the current call before placing a new call.',
        )
        return
      }

      const forwarded =
        await forwardMakeCallToOwner(
          target,
        )

      if (forwarded) {
        toast.info(
          'The dialer is already active in another tab. Your call request has been forwarded to the active dialer.',
        )
        return
      }

      await clickToCallWithLogging(
        target,
      )
    },
    [
      clickToCallWithLogging,
      number,
    ],
  )

  const endCurrentCall =
    useCallback((): void => {
      try {
        void typedWindow.CTI_PHONE?.hangup?.()
      } catch (error) {
        console.warn(
          'Error calling CTI_PHONE.hangup():',
          error,
        )
      }

      resetCallState()
    }, [resetCallState])

  const handlePrimaryAction =
    useCallback((): void => {
      if (
        callStore.callStatus ===
        'ringing' ||
        callStore.callStatus ===
        'calling'
      ) {
        endCurrentCall()
        return
      }

      if (!hasDigits || !canPlaceCall) {
        return
      }

      void callNumber()
    }, [canPlaceCall, callNumber, endCurrentCall, hasDigits])

  const pressKey = useCallback(
    (key: string): void => {
      if (
        callStore.callStatus ===
        'active'
      ) {
        typedWindow.CTI_PHONE?.sendDTMF?.(
          key,
        )
        return
      }

      setNumber(
        (currentNumber) =>
          currentNumber + key,
      )
    },
    [],
  )

  const backspaceNumber =
    useCallback((): void => {
      setNumber((currentNumber) =>
        currentNumber.slice(0, -1),
      )
    }, [])

  const clearNumber =
    useCallback((): void => {
      setNumber('')
    }, [])

  const callFromHistory = useCallback(
    (item: RecentCall): void => {
      const phoneNumber =
        item?.number?.trim()

      if (!phoneNumber) return

      setActivePanel('dialpad')

      void clickToCallWithLogging(
        phoneNumber,
        {
          module: item?.module,
          record_id:
            item?.recordId,
        },
      )
    },
    [
      clickToCallWithLogging,
    ],
  )

  const redialLastCall =
    useCallback((): void => {
      const lastCall =
        callStore.recentCalls?.[0]

      if (!lastCall?.number) return

      callFromHistory(lastCall)
    }, [callFromHistory])

  const answerCurrentCall =
    useCallback((): void => {
      console.log(
        'Answer button clicked',
      )

      void typedWindow.CTI_PHONE?.answer?.()
    }, [])

  const rejectCurrentCall =
    useCallback((): void => {
      console.log(
        'Reject button clicked',
      )

      void typedWindow.CTI_PHONE?.reject?.()
    }, [])

  const toggleMuteCurrentCall =
    useCallback((): void => {
      if (callStore.muted) {
        void typedWindow.CTI_PHONE?.unmute?.()
        callStore.muted = false
      } else {
        void typedWindow.CTI_PHONE?.mute?.()
        callStore.muted = true
      }

      forceStoreRender()
    }, [forceStoreRender])

  const toggleHoldCurrentCall =
    useCallback((): void => {
      if (callStore.onHold) {
        void typedWindow.CTI_PHONE?.unhold?.()
        callStore.onHold = false
      } else {
        void typedWindow.CTI_PHONE?.hold?.()
        callStore.onHold = true
      }

      forceStoreRender()
    }, [forceStoreRender])

  const manualRegister =
    useCallback((): void => {
      void typedWindow.CTI_PHONE?.register?.()
    }, [])

  useEffect(() => {
    const updateCurrentTime = (): void => {
      setCurrentTime(
        getFormattedCurrentTime(),
      )
    }

    updateCurrentTime()

    const clockInterval =
      window.setInterval(
        updateCurrentTime,
        1000,
      )

    return () => {
      window.clearInterval(
        clockInterval,
      )
    }
  }, [])

  useEffect(() => {
    const handleRegistered = (): void => {
      callStore.registered = true
      callStore.agentStatus =
        'available'

      callStore.connectionMessage = ''

      forceStoreRender()
    }

    const handleRegistrationFailed = (
      event: Event,
    ): void => {
      const detail =
        getEventDetail(event)

      callStore.registered = false
      callStore.agentStatus =
        'offline'

      callStore.connectionMessage =
        detail.reason ??
        'Registration failed'

      forceStoreRender()
    }

    const handleIncoming = (
      event: Event,
    ): void => {
      const detail =
        getEventDetail(event)

      const caller =
        detail.caller ??
        detail.number ??
        ''

      callStore.callStatus =
        'incoming'

      callStore.agentStatus =
        'busy'

      callStore.direction =
        'inbound'

      callStore.caller = caller
      callStore.incomingNumber =
        caller

      callStore.currentNumber =
        caller

      setActivePanel('dialpad')
      setIsOpen(false)

      forceStoreRender()

      if (caller) {
        void performCallerLookup(
          caller,
        ).finally(
          forceStoreRender,
        )
      }
    }

    const handleRinging = (): void => {
      if (
        callStore.callStatus ===
        'incoming'
      ) {
        return
      }

      callStore.callStatus =
        'ringing'

      callStore.agentStatus =
        'busy'

      callStore.direction =
        'outbound'

      setIsOpen(true)
      forceStoreRender()
    }

    const handleCallFailed = (
      event: Event,
    ): void => {
      const detail =
        getEventDetail(event)

      const phoneNumber =
        detail.number ||
        callStore.currentNumber ||
        callStore.incomingNumber ||
        callStore.caller

      const direction =
        detail.direction ||
        callStore.direction ||
        (callStore.incomingNumber
          ? 'inbound'
          : 'outbound')

      let status =
        detail.status ?? 'failed'

      const reason = String(
        detail.reason ?? '',
      ).toLowerCase()

      if (
        direction === 'inbound' &&
        (
          reason.includes(
            'canceled',
          ) ||
          reason.includes(
            'cancelled',
          ) ||
          reason.includes(
            'no answer',
          ) ||
          status === 'cancelled'
        )
      ) {
        status = 'missed'
      }

      addRecentCall(
        phoneNumber,
        direction,
        status,
      )

      terminateCurrentSession()

      resetCallState()

      callStore.connectionMessage =
        detail.reason ??
        'Call failed'

      forceStoreRender()
    }

    const handleActive = (): void => {
      callStore.callStatus =
        'active'

      callStore.agentStatus =
        'busy'

      setIsOpen(false)
      startTimer()
      forceStoreRender()
    }

    const handleEnded = (
      event: Event,
    ): void => {
      const detail =
        getEventDetail(event)

      const phoneNumber =
        detail.number ||
        callStore.currentNumber ||
        callStore.incomingNumber ||
        callStore.caller

      const direction =
        detail.direction ||
        callStore.direction ||
        (callStore.incomingNumber
          ? 'inbound'
          : 'outbound')

      addRecentCall(
        phoneNumber,
        direction,
        detail.status ??
        'completed',
      )

      terminateCurrentSession()
      resetCallState()
    }

    const handleSocketConnected =
      (): void => {
        callStore.socketConnected =
          true

        callStore.connectionMessage =
          ''

        forceStoreRender()
      }

    const handleSocketDisconnected =
      (): void => {
        callStore.socketConnected =
          false

        callStore.registered = false
        callStore.agentStatus =
          'offline'

        callStore.connectionMessage =
          'Connection lost. Reconnecting...'

        forceStoreRender()
      }

    const handleMicError = (
      event: Event,
    ): void => {
      const detail =
        getEventDetail(event)

      callStore.connectionMessage =
        detail.reason ??
        'Microphone permission denied'

      callStore.callStatus = 'idle'

      callStore.agentStatus =
        callStore.registered
          ? 'available'
          : 'offline'

      forceStoreRender()
    }

    const handleNetworkStats = (
      event: Event,
    ): void => {
      const detail =
        getEventDetail(event)

      callStore.latency =
        detail.latency ?? null

      if (
        callStore.latency == null
      ) {
        callStore.callQuality =
          'Unknown'
      } else if (
        callStore.latency < 100
      ) {
        callStore.callQuality =
          'Excellent'
      } else if (
        callStore.latency < 180
      ) {
        callStore.callQuality =
          'Good'
      } else if (
        callStore.latency < 300
      ) {
        callStore.callQuality =
          'Fair'
      } else {
        callStore.callQuality =
          'Poor'
      }

      forceStoreRender()
    }

    const handleBeforeUnload = (
      event: BeforeUnloadEvent,
    ): string | void => {
      if (
        [
          'active',
          'ringing',
          'calling',
          'incoming',
        ].includes(
          callStore.callStatus,
        )
      ) {
        event.preventDefault()
        event.returnValue =
          'A call is currently in progress. Refreshing or leaving the page will end the call.'
        return event.returnValue
      }
    }

    const handlePageHide = (): void => {
      if (
        [
          'active',
          'ringing',
          'calling',
          'incoming',
        ].includes(
          callStore.callStatus,
        )
      ) {
        terminateCurrentSession()
      }
    }

    const handleOpenDialerPanel =
      (): void => {
        setIsOpen(true)
        setActivePanel('dialpad')
      }

    const handleMakeCall = (
      event: Event,
    ): void => {
      const detail = (
        event as CustomEvent<MakeCallEventDetail>
      ).detail

      const target =
        detail?.number?.trim()

      if (!target) return

      if (
        callStore.callStatus === 'active' ||
        callStore.callStatus === 'calling' ||
        callStore.callStatus === 'ringing' ||
        callStore.callStatus === 'incoming'
      ) {
        toast.warning(
          'A call is already in progress. Please end or complete the current call before placing a new call.',
        )
        return
      }

      setNumber(target)
      setIsOpen(true)
      setActivePanel('dialpad')

      window.setTimeout(() => {
        void callNumber(target)
      }, 150)
    }

    window.addEventListener(
      'cti:registered',
      handleRegistered,
    )

    window.addEventListener(
      'cti:registration-failed',
      handleRegistrationFailed,
    )

    window.addEventListener(
      'cti:sip-incoming',
      handleIncoming,
    )

    window.addEventListener(
      'cti:incoming-call',
      handleIncoming,
    )

    window.addEventListener(
      'cti:call-ringing',
      handleRinging,
    )

    window.addEventListener(
      'cti:call-active',
      handleActive,
    )

    window.addEventListener(
      'cti:call-ended',
      handleEnded,
    )

    window.addEventListener(
      'cti:socket-connected',
      handleSocketConnected,
    )

    window.addEventListener(
      'cti:socket-disconnected',
      handleSocketDisconnected,
    )

    window.addEventListener(
      'cti:call-failed',
      handleCallFailed,
    )

    window.addEventListener(
      'cti:mic-error',
      handleMicError,
    )

    window.addEventListener(
      'cti:network-stats',
      handleNetworkStats,
    )

    window.addEventListener(
      'cti:open-dialer-panel',
      handleOpenDialerPanel,
    )

    window.addEventListener(
      'cti:make-call',
      handleMakeCall,
    )

    window.addEventListener(
      'beforeunload',
      handleBeforeUnload,
    )

    window.addEventListener(
      'pagehide',
      handlePageHide,
    )

    return () => {
      window.removeEventListener(
        'cti:registered',
        handleRegistered,
      )

      window.removeEventListener(
        'cti:registration-failed',
        handleRegistrationFailed,
      )

      window.removeEventListener(
        'cti:sip-incoming',
        handleIncoming,
      )

      window.removeEventListener(
        'cti:incoming-call',
        handleIncoming,
      )

      window.removeEventListener(
        'cti:call-ringing',
        handleRinging,
      )

      window.removeEventListener(
        'cti:call-active',
        handleActive,
      )

      window.removeEventListener(
        'cti:call-ended',
        handleEnded,
      )

      window.removeEventListener(
        'cti:socket-connected',
        handleSocketConnected,
      )

      window.removeEventListener(
        'cti:socket-disconnected',
        handleSocketDisconnected,
      )

      window.removeEventListener(
        'cti:call-failed',
        handleCallFailed,
      )

      window.removeEventListener(
        'cti:mic-error',
        handleMicError,
      )

      window.removeEventListener(
        'cti:network-stats',
        handleNetworkStats,
      )

      window.removeEventListener(
        'cti:open-dialer-panel',
        handleOpenDialerPanel,
      )

      window.removeEventListener(
        'cti:make-call',
        handleMakeCall,
      )

      window.removeEventListener(
        'beforeunload',
        handleBeforeUnload,
      )

      window.removeEventListener(
        'pagehide',
        handlePageHide,
      )
    }
  }, [
    addRecentCall,
    callNumber,
    forceStoreRender,
    performCallerLookup,
    resetCallState,
    startTimer,
  ])

  useEffect(() => {
    loadCallCache()

    const webRTCConfigUrl =
      typedWindow.Dialer?.api
        ?.webrtcConfig

    if (!webRTCConfigUrl) {
      console.warn(
        'Dialer WebRTC configuration URL is missing.',
      )

      callStore.registered = false
      callStore.agentStatus =
        'offline'

      forceStoreRender()

      return
    }

    let cancelled = false

    const bootWebRTC =
      async (): Promise<void> => {
        try {
          const { data } =
            await axios.get(
              webRTCConfigUrl,
            )

          if (!cancelled) {
            initWebRTCPhone(data)
          }
        } catch (error) {
          if (cancelled) return

          console.error(
            'WebRTC config failed:',
            error,
          )

          callStore.registered =
            false

          callStore.agentStatus =
            'offline'

          forceStoreRender()
        }
      }

    void bootWebRTC()

    return () => {
      cancelled = true
    }
  }, [
    forceStoreRender,
    loadCallCache,
  ])

  useEffect(() => {
    typedWindow.CTI_PHONE_CALL =
      clickToCallWithLogging

    return () => {
      if (
        typedWindow.CTI_PHONE_CALL ===
        clickToCallWithLogging
      ) {
        delete typedWindow.CTI_PHONE_CALL
      }
    }
  }, [clickToCallWithLogging])

  useEffect(() => {
    return () => {
      stopTimer()
    }
  }, [stopTimer])

  return {
    isOpen,
    setIsOpen,

    number,
    setNumber,

    currentTime,

    panelRef,
    headerRef,

    activePanel,
    setActivePanel,

    recentSearch,
    setRecentSearch,

    missedUnreadCount,
    filteredRecentCalls,

    panelStyle,
    startDragging,
    setPanelPosition,

    keys,
    callStore: callStore as CallStore,

    pressKey,
    backspaceNumber,
    clearNumber,

    handlePrimaryAction,
    callFromHistory,
    callNumber,
    clickToCallWithLogging,

    answerCurrentCall,
    rejectCurrentCall,
    endCurrentCall,

    toggleMuteCurrentCall,
    toggleHoldCurrentCall,
    manualRegister,

    redialLastCall,

    markMissedRead,
    clearMissedCalls,
    confirmClearMissed,

    loadCallCache,
    saveCallCache,
    addRecentCall,

    callTransfer,

    resetCallState,

    isIncomingCall,
    isActiveCall,

    inputPlaceholder,
    helperText,
    hasDigits,
    canPlaceCall,
    activeCallTitle,
    statusLabel,
  }
}