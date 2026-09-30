import { PhoneIcon } from 'lucide-react'

interface DialerLauncherProps {
    missedUnreadCount: number
    onOpen: () => void
}

export default function DialerLauncher({
    missedUnreadCount,
    onOpen,
}: DialerLauncherProps) {
    return (
        <button
            type="button"
            className="phone-tab"
            onClick={onOpen}
            title="Open Dialer"
            aria-label="Open Dialer"
        >
            <PhoneIcon className="phone-tab-icon" />

            {missedUnreadCount > 0 && (
                <span className="call-alert-badge">
                    {missedUnreadCount > 9
                        ? '9+'
                        : missedUnreadCount}
                </span>
            )}
        </button>
    )
}