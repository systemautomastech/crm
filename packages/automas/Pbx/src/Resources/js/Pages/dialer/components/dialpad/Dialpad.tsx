interface DialpadKey {
    value: string
    label: string
}

interface DialpadProps {
    keys: DialpadKey[]
    onPressKey: (value: string) => void
}

export default function Dialpad({
    keys,
    onPressKey,
}: DialpadProps) {
    return (
        <div className="dialpad">
            {keys.map((key) => (
                <button
                    type="button"
                    key={key.value}
                    className="dialpad-key"
                    onClick={() =>
                        onPressKey(key.value)
                    }
                >
                    <span className="dialpad-value">
                        {key.value}
                    </span>

                    <span className="dialpad-label">
                        {key.label}
                    </span>
                </button>
            ))}
        </div>
    )
}