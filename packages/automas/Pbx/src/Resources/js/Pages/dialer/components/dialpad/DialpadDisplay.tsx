interface DialpadDisplayProps {
    number: string
    inputPlaceholder: string
    helperText: string
    connectionMessage?: string | null
    onNumberChange: (value: string) => void
    onSubmit: () => void
}

export default function DialpadDisplay({
    number,
    inputPlaceholder,
    helperText,
    connectionMessage,
    onNumberChange,
    onSubmit,
}: DialpadDisplayProps) {
    return (
        <div className="display">
            <input
                value={number}
                placeholder={
                    inputPlaceholder
                }
                inputMode="tel"
                autoComplete="off"
                aria-label="Phone number"
                onChange={(event) =>
                    onNumberChange(
                        event.target.value,
                    )
                }
                onKeyDown={(event) => {
                    if (
                        event.key !== 'Enter'
                    ) {
                        return
                    }

                    event.preventDefault()
                    onSubmit()
                }}
            />

            <div className="status-row">
                <span className="status-copy">
                    {helperText}
                </span>
            </div>

            {connectionMessage && (
                <div className="status-row">
                    <span className="status-copy text-danger">
                        {connectionMessage}
                    </span>
                </div>
            )}
        </div>
    )
}