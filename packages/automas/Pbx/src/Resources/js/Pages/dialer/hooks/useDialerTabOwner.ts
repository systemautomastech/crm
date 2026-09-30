import { useCallback, useEffect, useState } from 'react'

import {
    getIsOwnerTab,
    openDialerPopup as openDialerPopupService,
    startDialerTabOwnerService,
    stopDialerTabOwnerService,
    subscribeToDialerOwnership,
} from '../services/dialerTabOwner'

export function useDialerTabOwner() {
    const [isOwnerTab, setIsOwnerTab] = useState(
        getIsOwnerTab,
    )

    useEffect(() => {
        startDialerTabOwnerService()

        const unsubscribe =
            subscribeToDialerOwnership(setIsOwnerTab)

        return () => {
            unsubscribe()
            stopDialerTabOwnerService()
        }
    }, [])

    const openDialerPopup = useCallback(
        async (): Promise<void> => {
            await openDialerPopupService()
        },
        [],
    )

    return {
        isOwnerTab,
        openDialerPopup,
    }
}