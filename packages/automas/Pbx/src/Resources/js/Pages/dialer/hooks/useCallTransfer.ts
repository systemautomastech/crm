import { useState, useCallback, useEffect } from 'react'
import axios from 'axios'
import type { CallStore } from '../store/callStore'

export type TransferStatus = 'idle' | 'transferring' | 'transferred' | 'success' | 'failed'

export interface ExtensionDirectoryItem {
    id: number
    extension: string
    caller_id?: string | null
    user_name?: string | null
    user_email?: string | null
    label: string
}

export function useCallTransfer(callStore: CallStore) {
    const [isTransferModalOpen, setIsTransferModalOpen] = useState(false)
    const [targetExtension, setTargetExtension] = useState('')
    const [searchQuery, setSearchQuery] = useState('')
    const [transferStatus, setTransferStatus] = useState<TransferStatus>('idle')
    const [hasTransferred, setHasTransferred] = useState(false)
    const [transferError, setTransferError] = useState<string | null>(null)
    const [directory, setDirectory] = useState<ExtensionDirectoryItem[]>([])
    const [loadingDirectory, setLoadingDirectory] = useState(false)

    const fetchDirectory = useCallback(async () => {
        setLoadingDirectory(true)
        try {
            const endpoint = (window as any).Dialer?.api?.extensionsDirectory || '/pbx/extensions/directory'
            const { data } = await axios.get(endpoint)
            const itemsMap = new Map<string, ExtensionDirectoryItem>()

            const extList = Array.isArray(data)
                ? data
                : (Array.isArray(data?.extensions) ? data.extensions : [])

            extList.forEach((ext: any) => {
                if (ext && ext.extension) {
                    const extStr = String(ext.extension)
                    itemsMap.set(extStr, {
                        id: ext.id || extStr,
                        extension: extStr,
                        caller_id: ext.caller_id || null,
                        user_name: ext.user_name || ext.user?.name || null,
                        user_email: ext.user_email || ext.user?.email || null,
                        label: ext.label || (ext.user_name ? `${ext.user_name} (${extStr})` : `Ext ${extStr}`),
                    })
                }
            })

            setDirectory(Array.from(itemsMap.values()))
        } catch (err) {
            console.warn('Failed to load extension directory for transfer:', err)
        } finally {
            setLoadingDirectory(false)
        }
    }, [])

    useEffect(() => {
        if (isTransferModalOpen) {
            void fetchDirectory()
        }
    }, [isTransferModalOpen, fetchDirectory])

    const openTransferModal = useCallback(() => {
        if (hasTransferred || transferStatus === 'transferred' || transferStatus === 'transferring') {
            return
        }
        setTransferStatus('idle')
        setTransferError(null)
        setTargetExtension('')
        setSearchQuery('')
        setIsTransferModalOpen(true)
    }, [hasTransferred, transferStatus])

    const closeTransferModal = useCallback(() => {
        if (transferStatus !== 'transferring') {
            setIsTransferModalOpen(false)
            if (transferStatus !== 'transferred') {
                setTransferStatus('idle')
                setTransferError(null)
            }
        }
    }, [transferStatus])

    const executeTransfer = useCallback(async (destination?: string): Promise<boolean> => {
        if (hasTransferred || transferStatus === 'transferred' || transferStatus === 'transferring') {
            setTransferError('Call has already been transferred.')
            return false
        }

        const target = (destination ?? targetExtension).trim()

        if (!target) {
            setTransferError('Invalid destination extension.')
            setTransferStatus('failed')
            return false
        }

        if (callStore.callStatus !== 'active') {
            setTransferError('No active call to transfer.')
            setTransferStatus('failed')
            return false
        }

        // Self transfer protection
        const ownExt = window.CTI_PHONE?.getExtension?.()
        if (ownExt && String(target) === String(ownExt)) {
            setTransferError('You cannot transfer a call to your own extension.')
            setTransferStatus('failed')
            return false
        }

        if (!window.CTI_PHONE?.transfer) {
            setTransferError('Transfer functionality not available.')
            setTransferStatus('failed')
            return false
        }

        setTransferStatus('transferring')
        setTransferError(null)

        try {
            await window.CTI_PHONE.transfer(target)
            setTransferStatus('transferred')
            setHasTransferred(true)
            return true
        } catch (err: any) {
            console.error('Transfer attempt failed:', err)
            setTransferStatus('failed')
            setHasTransferred(false)
            setTransferError(err.message || 'Unable to transfer the call.')
            return false
        }
    }, [targetExtension, callStore.callStatus, hasTransferred, transferStatus])

    useEffect(() => {
        const handleTransferSuccess = () => {
            setTransferStatus('transferred')
            setHasTransferred(true)
        }

        const handleTransferFailed = (e: Event) => {
            const detail = (e as CustomEvent).detail
            setTransferStatus('failed')
            setHasTransferred(false)
            setTransferError(detail?.reason || 'Unable to transfer the call.')
        }

        const handleCallEnded = () => {
            setIsTransferModalOpen(false)
            setTransferStatus('idle')
            setHasTransferred(false)
            setTransferError(null)
        }

        window.addEventListener('cti:transfer-sent', handleTransferSuccess)
        window.addEventListener('cti:transfer-success', handleTransferSuccess)
        window.addEventListener('cti:transfer-failed', handleTransferFailed)
        window.addEventListener('cti:call-ended', handleCallEnded)

        return () => {
            window.removeEventListener('cti:transfer-sent', handleTransferSuccess)
            window.removeEventListener('cti:transfer-success', handleTransferSuccess)
            window.removeEventListener('cti:transfer-failed', handleTransferFailed)
            window.removeEventListener('cti:call-ended', handleCallEnded)
        }
    }, [])

    const ownExt = window.CTI_PHONE?.getExtension?.()

    const filteredDirectory = directory.filter((item) => {
        // Exclude current user's own extension
        if (ownExt && String(item.extension) === String(ownExt)) {
            return false
        }

        const query = searchQuery.toLowerCase().trim()
        if (!query) return true
        return (
            item.extension.toLowerCase().includes(query) ||
            (item.user_name && item.user_name.toLowerCase().includes(query)) ||
            (item.user_email && item.user_email.toLowerCase().includes(query))
        )
    })

    return {
        isTransferModalOpen,
        openTransferModal,
        closeTransferModal,
        targetExtension,
        setTargetExtension,
        searchQuery,
        setSearchQuery,
        transferStatus,
        hasTransferred,
        transferError,
        directory: filteredDirectory,
        loadingDirectory,
        executeTransfer,
    }
}
