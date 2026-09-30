import { useEffect } from 'react'
import { PhoneIcon } from 'lucide-react'

import { useDialer } from '../hooks/useDialer'
import { useDialerTabOwner } from '../hooks/useDialerTabOwner'

import Dialer from './Dialer'
import IncomingCallPopup from './incoming/IncomingCallPopup'
import OngoingCallBar from './ongoing/OngoingCallBar'
import DialerTransferModal from './transfer/DialerTransferModal'

const BLOCKING_CALL_STATUSES = [
    'active',
    'calling',
    'ringing',
]

export default function WebPhone() {
    const dialer = useDialer()

    const {
        isOwnerTab,
        openDialerPopup,
    } = useDialerTabOwner()

    const {
        callStore,
        isIncomingCall,
        answerCurrentCall,
        rejectCurrentCall,
        endCurrentCall,
        toggleMuteCurrentCall,
        toggleHoldCurrentCall,
        activeCallTitle,
        callTransfer,
    } = dialer

    const showOngoingCallBar =
        !isIncomingCall &&
        BLOCKING_CALL_STATUSES.includes(
            callStore.callStatus,
        )

    useEffect(() => {
        const handleBeforeUnload = (
            event: BeforeUnloadEvent,
        ): string | void => {
            if (
                !isOwnerTab ||
                !BLOCKING_CALL_STATUSES.includes(
                    callStore.callStatus,
                )
            ) {
                return
            }

            const message =
                'A call is in progress. Please end or reject the call before leaving this page.'

            event.preventDefault()
            event.returnValue = message

            return message
        }

        window.addEventListener(
            'beforeunload',
            handleBeforeUnload,
        )

        return () => {
            window.removeEventListener(
                'beforeunload',
                handleBeforeUnload,
            )
        }
    }, [
        callStore.callStatus,
        isOwnerTab,
    ])

    if (!isOwnerTab) {
        return (
            <button
                type="button"
                className="phone-tab"
                onClick={() => {
                    void openDialerPopup()
                }}
                title="Dialer active in another tab"
                aria-label="Open active dialer tab"
            >
                <PhoneIcon className="phone-tab-icon" />

                <span className="call-alert-badge">
                    !
                </span>
            </button>
        )
    }

    return (
        <>
            <audio
                id="remoteAudio"
                autoPlay
                aria-hidden="true"
            />

            <Dialer dialer={dialer} />

            {isIncomingCall && (
                <IncomingCallPopup
                    callStore={callStore}
                    onAnswer={
                        answerCurrentCall
                    }
                    onReject={
                        rejectCurrentCall
                    }
                />
            )}

            {showOngoingCallBar && (
                <OngoingCallBar
                    callStore={callStore}
                    activeCallTitle={
                        activeCallTitle
                    }
                    onToggleMute={
                        toggleMuteCurrentCall
                    }
                    onToggleHold={
                        toggleHoldCurrentCall
                    }
                    onEndCall={
                        endCurrentCall
                    }
                    onTransferCall={
                        callTransfer.openTransferModal
                    }
                    isTransferDisabled={
                        callTransfer.transferStatus === 'transferring' ||
                        callTransfer.transferStatus === 'transferred' ||
                        callTransfer.hasTransferred
                    }
                />
            )}

            <DialerTransferModal
                isOpen={callTransfer.isTransferModalOpen}
                onClose={callTransfer.closeTransferModal}
                targetExtension={callTransfer.targetExtension}
                onTargetExtensionChange={callTransfer.setTargetExtension}
                searchQuery={callTransfer.searchQuery}
                onSearchQueryChange={callTransfer.setSearchQuery}
                directory={callTransfer.directory}
                loadingDirectory={callTransfer.loadingDirectory}
                transferStatus={callTransfer.transferStatus}
                transferError={callTransfer.transferError}
                onExecuteTransfer={callTransfer.executeTransfer}
            />
        </>
    )
}