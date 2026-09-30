import type { CallStore } from '../store/callStore'

let timerInterval: number | null = null

export function useCallTimer(callStore: CallStore) {
  const startTimer = (): void => {
    stopTimer()

    callStore.callStartedAt = Date.now()
    callStore.callDuration = '00:00'

    timerInterval = window.setInterval(() => {
      if (!callStore.callStartedAt) return

      const diff = Math.floor((Date.now() - callStore.callStartedAt) / 1000)

      const min = String(Math.floor(diff / 60)).padStart(2, '0')
      const sec = String(diff % 60).padStart(2, '0')

      callStore.callDuration = `${min}:${sec}`
    }, 1000)
  }

  const stopTimer = (): void => {
    if (timerInterval !== null) {
      window.clearInterval(timerInterval)
      timerInterval = null
    }

    callStore.callStartedAt = null
    callStore.callDuration = '00:00'
  }

  return {
    startTimer,
    stopTimer,
  }
}