let titleBlinkInterval: number | null = null
let faviconBlinkInterval: number | null = null

const originalTitle =
    typeof document !== 'undefined' ? document.title : ''

export function blinkTitle(duration = 6000): void {
    if (typeof document === 'undefined') return

    const alternativeTitle = 'Dialer — Attention'
    const startedAt = Date.now()
    let showingAlternative = false

    stopTitleBlink()

    titleBlinkInterval = window.setInterval(() => {
        if (Date.now() - startedAt > duration) {
            stopTitleBlink()
            return
        }

        document.title = showingAlternative
            ? originalTitle
            : alternativeTitle

        showingAlternative = !showingAlternative
    }, 500)
}

export function stopTitleBlink(): void {
    if (titleBlinkInterval !== null) {
        window.clearInterval(titleBlinkInterval)
        titleBlinkInterval = null
    }

    if (typeof document !== 'undefined') {
        document.title = originalTitle
    }
}

export function setFavicon(icon: string): void {
    if (typeof document === 'undefined') return

    let link = document.querySelector<HTMLLinkElement>(
        "link[rel*='icon']",
    )

    if (!link) {
        link = document.createElement('link')
        link.rel = 'icon'
        document.head.appendChild(link)
    }

    link.href = icon
}

export function blinkFavicon(): void {
    stopFaviconBlink()

    let showRedIcon = false

    faviconBlinkInterval = window.setInterval(() => {
        showRedIcon = !showRedIcon

        setFavicon(
            showRedIcon
                ? '/phone-red.ico'
                : '/phone-green.ico',
        )
    }, 500)
}

export function stopFaviconBlink(): void {
    if (faviconBlinkInterval !== null) {
        window.clearInterval(faviconBlinkInterval)
        faviconBlinkInterval = null
    }

    setFavicon('/favicon.ico')
}

export function playBeep(): void {
    try {
        const AudioContextConstructor =
            window.AudioContext ||
            (
                window as typeof window & {
                    webkitAudioContext?: typeof AudioContext
                }
            ).webkitAudioContext

        if (!AudioContextConstructor) return

        const audioContext = new AudioContextConstructor()
        const oscillator = audioContext.createOscillator()
        const gainNode = audioContext.createGain()

        oscillator.type = 'sine'
        oscillator.frequency.value = 880

        gainNode.gain.value = 0.05

        oscillator.connect(gainNode)
        gainNode.connect(audioContext.destination)

        oscillator.start()

        window.setTimeout(() => {
            try {
                oscillator.stop()
                void audioContext.close()
            } catch {
                // Ignore audio cleanup errors.
            }
        }, 200)
    } catch {
        // Browser may block audio until user interaction.
    }
}

export function stopDialerAttention(): void {
    stopTitleBlink()
    stopFaviconBlink()
}