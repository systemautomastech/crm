import {
    Delete,
    PhoneIcon,
    Radio,
    X,
} from 'lucide-react'

import Dialpad from './Dialpad'
import DialpadDisplay from './DialpadDisplay'

interface DialpadKey {
    value: string
    label: string
}

interface DialpadPanelProps {
    number: string
    inputPlaceholder: string
    helperText: string
    connectionMessage?: string | null

    callStatus: string
    registered: boolean

    keys: DialpadKey[]

    hasDigits: boolean
    canPlaceCall: boolean

    onNumberChange: (value: string) => void
    onPressKey: (value: string) => void

    onClear: () => void
    onBackspace: () => void

    onPrimaryAction: () => void
    onEndCall: () => void
    onRegister: () => void
}

export default function DialpadPanel({
    number,
    inputPlaceholder,
    helperText,
    connectionMessage,

    callStatus,
    registered,

    keys,

    hasDigits,
    canPlaceCall,

    onNumberChange,
    onPressKey,

    onClear,
    onBackspace,

    onPrimaryAction,
    onEndCall,
    onRegister,
}: DialpadPanelProps) {
    const isOutgoingCallPending = [
        'calling',
        'ringing',
    ].includes(callStatus)

    return (
        <div className="idle-panel">
            <DialpadDisplay
                number={number}
                inputPlaceholder={
                    inputPlaceholder
                }
                helperText={helperText}
                connectionMessage={
                    connectionMessage
                }
                onNumberChange={
                    onNumberChange
                }
                onSubmit={
                    onPrimaryAction
                }
            />

            <Dialpad
                keys={keys}
                onPressKey={onPressKey}
            />

            <div
                className={`utility-actions dialer-actions ${callStatus}`}
            >
                <button
                    type="button"
                    className="utility-btn icon-only"
                    disabled={!hasDigits}
                    onClick={onClear}
                    title="Clear number"
                    aria-label="Clear number"
                >
                    <X />
                </button>

                {isOutgoingCallPending ? (
                    <button
                        type="button"
                        className="dialer-action-btn hangup full icon-only"
                        onClick={onEndCall}
                        title="Cancel call"
                        aria-label="Cancel call"
                    >
                        <PhoneIcon className="action-icon rotate-135" />
                    </button>
                ) : (
                    <button
                        type="button"
                        className="dialer-action-btn call full icon-only"
                        disabled={
                            !canPlaceCall
                        }
                        onClick={
                            onPrimaryAction
                        }
                        title="Start call"
                        aria-label="Start call"
                    >
                        <PhoneIcon className="action-icon" />
                    </button>
                )}

                <button
                    type="button"
                    className="utility-btn icon-only"
                    disabled={!hasDigits}
                    onClick={onBackspace}
                    title="Backspace"
                    aria-label="Backspace"
                >
                    <Delete />
                </button>
            </div>

            {/* {!registered && (
                <button
                    type="button"
                    className="register-btn"
                    onClick={onRegister}
                >
                    <Radio />
                    <span>
                        Register Dialer
                    </span>
                </button>
            )} */}
        </div>
    )
}