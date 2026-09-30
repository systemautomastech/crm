import {
    Mic,
    MicOff,
    Pause,
    PhoneForwarded,
    PhoneIcon,
    Play,
} from 'lucide-react'

interface OngoingCallControlsProps {
    muted: boolean
    onHold: boolean
    canUseCallControls: boolean
    isTransferDisabled?: boolean
    onToggleMute: () => void
    onToggleHold: () => void
    onEndCall: () => void
    onTransferCall?: () => void
}

interface ControlButtonProps {
    title: string
    active?: boolean
    disabled?: boolean
    onClick?: () => void
    children: React.ReactNode
}

function ControlButton({
    title,
    active = false,
    disabled = false,
    onClick,
    children,
}: ControlButtonProps) {
    return (
        <button
            type="button"
            title={title}
            aria-label={title}
            disabled={disabled}
            onClick={onClick}
            className={[
                'inline-flex size-9 items-center',
                'justify-center rounded-full border',
                'shadow-sm transition-colors',
                'focus-visible:outline-none',
                'focus-visible:ring-2',
                'focus-visible:ring-ring',
                'disabled:pointer-events-none',
                'disabled:opacity-40',
                active
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-border bg-background text-foreground hover:bg-muted',
            ].join(' ')}
        >
            {children}
        </button>
    )
}

export default function OngoingCallControls({
    muted,
    onHold,
    canUseCallControls,
    isTransferDisabled = false,
    onToggleMute,
    onToggleHold,
    onEndCall,
    onTransferCall,
}: OngoingCallControlsProps) {
    return (
        <div className="flex shrink-0 items-center gap-2">
            <ControlButton
                title={
                    muted
                        ? 'Unmute'
                        : 'Mute'
                }
                active={muted}
                disabled={
                    !canUseCallControls
                }
                onClick={onToggleMute}
            >
                {muted ? (
                    <MicOff className="size-4" />
                ) : (
                    <Mic className="size-4" />
                )}
            </ControlButton>

            <ControlButton
                title={
                    onHold
                        ? 'Resume call'
                        : 'Hold call'
                }
                active={onHold}
                disabled={
                    !canUseCallControls
                }
                onClick={onToggleHold}
            >
                {onHold ? (
                    <Play className="size-4" />
                ) : (
                    <Pause className="size-4" />
                )}
            </ControlButton>

            {onTransferCall && (
                <ControlButton
                    title={isTransferDisabled ? 'Transfer in progress' : 'Transfer call'}
                    disabled={!canUseCallControls || isTransferDisabled}
                    onClick={onTransferCall}
                >
                    <PhoneForwarded className="size-4" />
                </ControlButton>
            )}

            <button
                type="button"
                title="End call"
                aria-label="End call"
                onClick={onEndCall}
                className="
                    inline-flex size-9
                    items-center justify-center
                    rounded-full bg-destructive
                    text-destructive-foreground
                    shadow-sm transition-colors
                    hover:bg-destructive/90
                    focus-visible:outline-none
                    focus-visible:ring-2
                    focus-visible:ring-destructive
                "
            >
                <PhoneIcon className="size-4 rotate-[135deg]" />
            </button>
        </div>
    )
}