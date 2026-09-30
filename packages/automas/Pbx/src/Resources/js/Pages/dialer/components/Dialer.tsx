import {
    History,
    Minimize2,
    Undo2,
    X,
} from 'lucide-react'

import '../styles/dialer/variables.css'
import '../styles/dialer/base.css'
import '../styles/dialer/topbar.css'
import '../styles/dialer/buttons.css'
import '../styles/dialer/dialpad.css'
import '../styles/dialer/incoming.css'
import '../styles/dialer/history.css'
import '../styles/dialer/panels.css'
import '../styles/dialer/active.css'

import { useDialer } from '../hooks/useDialer'

import DialerLauncher from './DialerLauncher'
import DialpadPanel from './dialpad/DialpadPanel'
import DialerHistory from './history/DialerHistory'

type DialerController = ReturnType<
    typeof useDialer
>

interface DialerProps {
    dialer: DialerController
}

export default function Dialer({
    dialer,
}: DialerProps) {
    const {
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

        keys,
        callStore,

        pressKey,
        backspaceNumber,
        clearNumber,

        handlePrimaryAction,
        callFromHistory,

        endCurrentCall,
        manualRegister,

        redialLastCall,
        markMissedRead,
        confirmClearMissed,

        inputPlaceholder,
        helperText,
        hasDigits,
        canPlaceCall,
        statusLabel,
    } = dialer

    const closeDialer = (): void => {
        setIsOpen(false)
    }

    const openHistoryPanel = (): void => {
        setActivePanel('calls')
        markMissedRead()
    }

    if (!isOpen) {
        return (
            <DialerLauncher
                missedUnreadCount={
                    missedUnreadCount
                }
                onOpen={() =>
                    setIsOpen(true)
                }
            />
        )
    }

    return (
        <div
            ref={panelRef}
            className="webphone"
            style={panelStyle}
        >
            <div className="phone-screen">
                <div
                    ref={headerRef}
                    className="phone-topbar"
                    onPointerDown={
                        startDragging
                    }
                >
                    <div className="phone-topbar-left">
                        {currentTime}
                    </div>

                    <div className="dynamic-island">
                        <span
                            className={`status-pill ${callStore.agentStatus}`}
                        >
                            {callStore.agentStatus}
                        </span>

                        <span className="dynamic-status">
                            {statusLabel}
                        </span>
                    </div>

                    <div className="phone-topbar-right">
                        {callStore.registered ? (
                            <span className="signal-dots">
                                <span />
                                <span />
                                <span />
                                <span />
                            </span>
                        ) : (
                            <X className="connection-cross" />
                        )}

                        <span className="battery">
                            80%
                        </span>
                    </div>
                </div>

                <div className="phone-content">
                    <div className="dialer-window-actions">
                        <button
                            type="button"
                            className="phone-close"
                            onClick={
                                closeDialer
                            }
                            title="Minimize dialer"
                            aria-label="Minimize dialer"
                        >
                            <Minimize2 />
                        </button>

                        {activePanel !==
                            'dialpad' && (
                                <button
                                    type="button"
                                    className="phone-close"
                                    onClick={() =>
                                        setActivePanel(
                                            'dialpad',
                                        )
                                    }
                                    title="Back to dial pad"
                                    aria-label="Back to dial pad"
                                >
                                    <Undo2 />
                                </button>
                            )}

                        <button
                            type="button"
                            className={[
                                'close-btn',
                                'phone-close',
                                'position-relative',
                                missedUnreadCount >
                                    0
                                    ? 'history-alert-badge'
                                    : '',
                            ]
                                .filter(
                                    Boolean,
                                )
                                .join(' ')}
                            onClick={
                                openHistoryPanel
                            }
                            title="Call history"
                            aria-label="Call history"
                        >
                            <History />
                        </button>
                    </div>

                    <div
                        key={activePanel}
                        className={
                            activePanel ===
                                'dialpad'
                                ? 'idle-panel'
                                : undefined
                        }
                    >
                        {activePanel ===
                            'dialpad' && (
                                <DialpadPanel
                                    number={number}
                                    inputPlaceholder={
                                        inputPlaceholder
                                    }
                                    helperText={
                                        helperText
                                    }
                                    connectionMessage={
                                        callStore.connectionMessage
                                    }
                                    callStatus={
                                        callStore.callStatus
                                    }
                                    registered={
                                        callStore.registered
                                    }
                                    keys={keys}
                                    hasDigits={
                                        hasDigits
                                    }
                                    canPlaceCall={
                                        canPlaceCall
                                    }
                                    onNumberChange={
                                        setNumber
                                    }
                                    onPressKey={
                                        pressKey
                                    }
                                    onClear={
                                        clearNumber
                                    }
                                    onBackspace={
                                        backspaceNumber
                                    }
                                    onPrimaryAction={
                                        handlePrimaryAction
                                    }
                                    onEndCall={
                                        endCurrentCall
                                    }
                                    onRegister={
                                        manualRegister
                                    }
                                />
                            )}

                        {activePanel ===
                            'calls' && (
                                <DialerHistory
                                    recentSearch={
                                        recentSearch
                                    }
                                    filteredRecentCalls={
                                        filteredRecentCalls
                                    }
                                    missedUnreadCount={
                                        missedUnreadCount
                                    }
                                    redialLastCall={
                                        redialLastCall
                                    }
                                    confirmClearMissed={
                                        confirmClearMissed
                                    }
                                    callFromHistory={
                                        callFromHistory
                                    }
                                    onRecentSearchChange={
                                        setRecentSearch
                                    }
                                />
                            )}
                    </div>
                </div>

                <button
                    type="button"
                    className="home-indicator"
                    onClick={closeDialer}
                    aria-label="Minimize dialer"
                />
            </div>
        </div>
    )
}