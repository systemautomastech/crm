import React from 'react'
import { PhoneForwarded, Search, X, AlertCircle, CheckCircle2, User, Loader2 } from 'lucide-react'
import type { ExtensionDirectoryItem, TransferStatus } from '../../hooks/useCallTransfer'

interface DialerTransferModalProps {
    isOpen: boolean
    onClose: () => void
    targetExtension: string
    onTargetExtensionChange: (value: string) => void
    searchQuery: string
    onSearchQueryChange: (value: string) => void
    directory: ExtensionDirectoryItem[]
    loadingDirectory: boolean
    transferStatus: TransferStatus
    transferError: string | null
    onExecuteTransfer: (targetOverride?: string) => Promise<boolean>
}

export default function DialerTransferModal({
    isOpen,
    onClose,
    targetExtension,
    onTargetExtensionChange,
    searchQuery,
    onSearchQueryChange,
    directory,
    loadingDirectory,
    transferStatus,
    transferError,
    onExecuteTransfer,
}: DialerTransferModalProps) {
    if (!isOpen) return null

    const isTransferring = transferStatus === 'transferring'
    const isTransferred = transferStatus === 'transferred' || transferStatus === 'success'
    const cleanTarget = targetExtension.trim()
    const canTransfer = cleanTarget.length > 0 && !isTransferring && !isTransferred

    const handleTransferClick = async (overrideExt?: string) => {
        const destination = (overrideExt ?? cleanTarget).trim()
        if (!destination || !canTransfer) return
        await onExecuteTransfer(destination)
    }

    return (
        <div className="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm animate-in fade-in duration-150">
            <div className="w-full max-w-md rounded-xl border border-border bg-background p-5 shadow-2xl space-y-4">
                {/* Header */}
                <div className="flex items-center justify-between border-b border-border pb-3">
                    <div className="flex items-center gap-2">
                        <div className="flex size-8 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <PhoneForwarded className="size-4" />
                        </div>
                        <div>
                            <h3 className="text-base font-semibold text-foreground">Transfer Call</h3>
                            <p className="text-xs text-muted-foreground">Select an extension or enter manually</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        disabled={isTransferring || isTransferred}
                        className="rounded-lg p-1 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors disabled:opacity-50"
                        title="Close transfer dialog"
                    >
                        <X className="size-5" />
                    </button>
                </div>

                {/* Error Banner */}
                {transferError && (
                    <div className="flex items-start gap-2.5 rounded-lg border border-destructive/30 bg-destructive/10 p-3 text-xs text-destructive">
                        <AlertCircle className="size-4 shrink-0 mt-0.5" />
                        <div className="flex-1 font-medium">{transferError}</div>
                    </div>
                )}

                {/* Success Banner */}
                {isTransferred && (
                    <div className="flex items-center gap-2.5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-3 text-xs text-emerald-600 font-medium">
                        <CheckCircle2 className="size-4 shrink-0" />
                        <span>Transfer accepted by Asterisk. Disconnecting original session...</span>
                    </div>
                )}

                {/* Search user or extension */}
                <div className="space-y-1.5">
                    <label className="text-xs font-semibold text-foreground">
                        Search user or extension
                    </label>
                    <div className="relative">
                        <Search className="absolute left-3 top-2.5 size-4 text-muted-foreground" />
                        <input
                            type="text"
                            placeholder="Type name or extension..."
                            value={searchQuery}
                            onChange={(e) => onSearchQueryChange(e.target.value)}
                            disabled={isTransferring || isTransferred}
                            className="h-9 w-full rounded-md border border-input bg-background pl-9 pr-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring/20 disabled:opacity-50"
                        />
                    </div>
                </div>

                {/* Users / Extensions Directory List */}
                <div className="space-y-1.5">
                    <span className="text-xs font-semibold text-muted-foreground">
                        Available Destinations
                    </span>
                    <div className="max-h-44 overflow-y-auto rounded-lg border border-border bg-muted/20 p-1 divide-y divide-border/40">
                        {loadingDirectory ? (
                            <div className="flex items-center justify-center p-4 text-xs text-muted-foreground gap-2">
                                <Loader2 className="size-4 animate-spin text-primary" />
                                <span>Loading extensions...</span>
                            </div>
                        ) : directory.length === 0 ? (
                            <div className="p-3 text-center text-xs text-muted-foreground">
                                No matching active extensions found.
                            </div>
                        ) : (
                            directory.map((item) => (
                                <div
                                    key={item.id}
                                    className="flex items-center justify-between p-2 hover:bg-accent/60 rounded-md transition-colors group cursor-pointer"
                                    onClick={() => {
                                        if (!isTransferring && !isTransferred) {
                                            onTargetExtensionChange(item.extension)
                                        }
                                    }}
                                >
                                    <div className="flex items-center gap-2.5 min-w-0 pr-2">
                                        <div className="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                            <User className="size-3.5" />
                                        </div>
                                        <div className="min-w-0">
                                            <p className="truncate text-xs font-medium text-foreground group-hover:text-primary transition-colors">
                                                {item.user_name || item.caller_id || 'Extension'}
                                            </p>
                                            <p className="text-[11px] text-muted-foreground">
                                                Extension {item.extension}
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        disabled={isTransferring || isTransferred}
                                        onClick={(e) => {
                                            e.stopPropagation()
                                            onTargetExtensionChange(item.extension)
                                            void handleTransferClick(item.extension)
                                        }}
                                        className="shrink-0 h-7 rounded bg-primary/10 px-2.5 text-xs font-semibold text-primary hover:bg-primary hover:text-primary-foreground disabled:opacity-50 transition-colors"
                                    >
                                        Transfer
                                    </button>
                                </div>
                            ))
                        )}
                    </div>
                </div>

                {/* Manual Extension Input */}
                <div className="space-y-1.5 pt-1">
                    <label htmlFor="manualExtInput" className="text-xs font-semibold text-foreground">
                        Manual extension:
                    </label>
                    <input
                        id="manualExtInput"
                        type="text"
                        inputMode="numeric"
                        placeholder="e.g. 105"
                        value={targetExtension}
                        onChange={(e) => onTargetExtensionChange(e.target.value)}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter' && canTransfer) {
                                e.preventDefault()
                                void handleTransferClick()
                            }
                        }}
                        disabled={isTransferring || isTransferred}
                        className="h-9 w-full rounded-md border border-input bg-background px-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring/20 disabled:opacity-50"
                    />
                </div>

                {/* Modal Footer Actions */}
                <div className="flex items-center justify-end gap-2 border-t border-border pt-3">
                    <button
                        type="button"
                        onClick={onClose}
                        disabled={isTransferring || isTransferred}
                        className="h-9 rounded-md border border-input bg-background px-4 text-xs font-medium text-foreground hover:bg-muted transition-colors disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        disabled={!canTransfer}
                        onClick={() => void handleTransferClick()}
                        className="inline-flex h-9 items-center justify-center gap-1.5 rounded-md bg-primary px-4 text-xs font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50 transition-colors"
                    >
                        {isTransferring ? (
                            <>
                                <Loader2 className="size-3.5 animate-spin" />
                                <span>Transferring...</span>
                            </>
                        ) : isTransferred ? (
                            <>
                                <CheckCircle2 className="size-3.5" />
                                <span>Transferred</span>
                            </>
                        ) : (
                            <>
                                <PhoneForwarded className="size-3.5" />
                                <span>Transfer</span>
                            </>
                        )}
                    </button>
                </div>
            </div>
        </div>
    )
}
