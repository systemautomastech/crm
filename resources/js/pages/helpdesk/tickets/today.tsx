import { useState, useMemo } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Edit as EditIcon, Trash2, Eye, MessageSquare, Clock, CheckCircle, PlayCircle, User, Filter } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import Edit from './edit';
import { TodayTicketsProps, HelpdeskTicketModalState } from './types';

export default function Today() {
    const { t } = useTranslation();
    const { tickets, stats, auth } = usePage<TodayTicketsProps>().props;

    const [searchQuery, setSearchQuery] = useState('');
    const [activePriorityFilter, setActivePriorityFilter] = useState<string>('all');
    const [modalState, setModalState] = useState<HelpdeskTicketModalState>({
        isOpen: false,
        mode: '',
        data: null
    });

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'helpdesk-tickets.destroy',
        defaultMessage: t('Are you sure you want to delete this ticket?')
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('helpdesk-tickets.today'), { search: searchQuery }, {
            preserveState: true,
            replace: true
        });
    };

    const clearSearch = () => {
        setSearchQuery('');
        router.get(route('helpdesk-tickets.today'));
    };

    const openModal = (mode: 'edit', data: any = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const handleQuickStatusChange = (ticket: any, newStatus: string) => {
        router.put(route('helpdesk-tickets.update', ticket.id), {
            title: ticket.title,
            description: ticket.description,
            status: newStatus,
            priority: ticket.priority,
            category_id: ticket.category_id,
        }, {
            preserveState: true,
            onSuccess: () => {
                router.reload({ only: ['tickets', 'stats'] });
            }
        });
    };

    const getTimeAgo = (dateString: string) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMs = now.getTime() - date.getTime();
        const diffInMinutes = Math.floor(diffInMs / 60000);
        const diffInHours = Math.floor(diffInMinutes / 60);
        const diffInDays = Math.floor(diffInHours / 24);

        if (diffInMinutes < 1) return t('Just now');
        if (diffInMinutes < 60) return `${diffInMinutes}m ${t('ago')}`;
        if (diffInHours < 24) return `${diffInHours}h ${t('ago')}`;
        return `${diffInDays}d ${t('ago')}`;
    };

    const isOld = (dateString: string) => {
        const diffInHours = Math.floor((new Date().getTime() - new Date(dateString).getTime()) / 3600000);
        return diffInHours > 24;
    };

    const getAgeColor = (dateString: string) => {
        const diffInHours = Math.floor((new Date().getTime() - new Date(dateString).getTime()) / 3600000);
        if (diffInHours < 2) return 'bg-green-500';
        if (diffInHours < 24) return 'bg-yellow-500';
        return 'bg-red-500';
    };

    // Filter tickets based on search and priority filter
    const filteredTickets = useMemo(() => {
        let result = tickets;
        
        // Apply search filter
        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            result = result.filter(t => 
                t.ticket_id.toLowerCase().includes(query) ||
                t.title.toLowerCase().includes(query) ||
                t.description?.toLowerCase().includes(query)
            );
        }
        
        // Apply priority filter
        if (activePriorityFilter !== 'all') {
            result = result.filter(t => t.priority === activePriorityFilter);
        }
        
        return result;
    }, [tickets, searchQuery, activePriorityFilter]);

    // Group tickets by priority
    const groupedTickets = useMemo(() => ({
        urgent: filteredTickets.filter(t => t.priority === 'urgent'),
        high: filteredTickets.filter(t => t.priority === 'high'),
        medium: filteredTickets.filter(t => t.priority === 'medium'),
        low: filteredTickets.filter(t => t.priority === 'low'),
    }), [filteredTickets]);

    const priorityConfig = {
        urgent: { label: 'Urgent', textColor: 'text-red-800', bgColor: 'bg-red-100' },
        high: { label: 'High', textColor: 'text-orange-800', bgColor: 'bg-orange-100' },
        medium: { label: 'Medium', textColor: 'text-yellow-800', bgColor: 'bg-yellow-100' },
        low: { label: 'Low', textColor: 'text-green-800', bgColor: 'bg-green-100' },
    };

    const renderTicketCard = (ticket: any) => {
        const hasReplies = ticket.replies && ticket.replies.length > 0;
        const replyCount = ticket.replies?.length || 0;
        const old = isOld(ticket.created_at);
        const config = priorityConfig[ticket.priority as keyof typeof priorityConfig];
        
        // Get last reply info
        const lastReply = hasReplies ? ticket.replies[ticket.replies.length - 1] : null;
        const lastReplyTime = lastReply ? getTimeAgo(lastReply.created_at) : null;
        // Check if last reply is from admin (superadmin type)
        const lastReplyByAdmin = lastReply?.creator?.type === 'superadmin';
        // Check if last reply is from current user
        const lastReplyByCurrentUser = lastReply?.created_by === auth.user?.id;

        return (
            <Card 
                key={ticket.id} 
                className="relative hover:shadow-md transition-shadow bg-white border border-gray-200"
            >
                <CardContent className="p-4">
                    {/* Header Row */}
                    <div className="flex items-start justify-between mb-3">
                        <div className="flex items-center gap-2 flex-1">
                            {/* Ticket ID */}
                            {auth.user?.permissions?.includes('view-helpdesk-tickets') ? (
                                <button
                                    onClick={() => router.get(route('helpdesk-tickets.show', ticket.id))}
                                    className="text-base font-bold text-blue-600 hover:text-blue-700 hover:underline"
                                >
                                    #{ticket.ticket_id}
                                </button>
                            ) : (
                                <span className="text-base font-bold text-gray-900">#{ticket.ticket_id}</span>
                            )}

                            {/* Old Indicator */}
                            {old && (
                                <span className="px-1.5 py-0.5 text-xs font-semibold text-red-600 bg-red-100 rounded">
                                    !
                                </span>
                            )}

                            {/* Reply Count */}
                            {hasReplies && (
                                <span className="flex items-center gap-1 text-xs text-gray-500">
                                    <MessageSquare className="h-3.5 w-3.5" />
                                    {replyCount}
                                </span>
                            )}
                        </div>

                        {/* Status Badge */}
                        <span className={`px-2 py-1 rounded-full text-xs font-semibold ${
                            ticket.status === 'open' 
                                ? 'bg-blue-100 text-blue-800' 
                                : 'bg-yellow-100 text-yellow-800'
                        }`}>
                            {ticket.status === 'open' ? t('Open') : t('In Progress')}
                        </span>
                    </div>

                    {/* Title */}
                    <h3 className="text-sm font-medium text-gray-900 mb-3 line-clamp-2" title={ticket.title}>
                        {ticket.title}
                    </h3>

                    {/* Meta Info Row */}
                    <div className="space-y-2 text-xs text-gray-600 mb-3 pb-3 border-b">
                        {/* First Row: Category, Creator */}
                        <div className="flex items-center gap-2 flex-wrap">
                            {/* Category */}
                            {ticket.category && (
                                <div className="flex items-center gap-1.5">
                                    {ticket.category.color && (
                                        <span 
                                            className="w-2 h-2 rounded-full" 
                                            style={{ backgroundColor: ticket.category.color }}
                                        />
                                    )}
                                    <span>{ticket.category.name}</span>
                                </div>
                            )}

                            {ticket.category && <span className="text-gray-300">•</span>}

                            {/* Creator */}
                            {ticket.creator && (
                                <div className="flex items-center gap-1">
                                    <User className="h-3 w-3" />
                                    <span className="truncate max-w-[100px]" title={ticket.creator.name}>
                                        {ticket.creator.name}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Second Row: Created & Last Reply */}
                        <div className="flex items-center gap-2 flex-wrap">
                            {/* Created Time */}
                            <div className="flex items-center gap-1">
                                <Clock className="h-3 w-3" />
                                <span className={old ? 'text-red-600 font-semibold' : ''}>
                                    {t('Created')}: {getTimeAgo(ticket.created_at)}
                                </span>
                            </div>

                            {/* Last Reply */}
                            {lastReply && (
                                <>
                                    <span className="text-gray-300">•</span>
                                    <div className="flex items-center gap-1">
                                        <MessageSquare className="h-3 w-3" />
                                        <span className={lastReplyByAdmin ? 'text-blue-600 font-medium' : 'text-gray-600'}>
                                            {lastReply.creator?.name}: {lastReplyTime}
                                        </span>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>

                    {/* Actions Row */}
                    <div className="flex items-center gap-2">
                        {/* Reply Button */}
                        {auth.user?.permissions?.includes('view-helpdesk-tickets') && (
                            <Button 
                                variant="outline" 
                                size="sm" 
                                onClick={() => router.get(route('helpdesk-tickets.show', ticket.id))} 
                                className="flex-1 h-8 text-xs"
                            >
                                <MessageSquare className="h-3.5 w-3.5 mr-1" />
                                {t('Reply')}
                            </Button>
                        )}

                        {/* Quick Status Change */}
                        {auth.user?.permissions?.includes('edit-helpdesk-tickets') && (
                            <>
                                {ticket.status === 'open' && (
                                    <Button 
                                        variant="outline" 
                                        size="sm" 
                                        onClick={() => handleQuickStatusChange(ticket, 'in_progress')}
                                        className="flex-1 h-8 text-xs border-yellow-300 text-yellow-700 hover:bg-yellow-50"
                                    >
                                        <PlayCircle className="h-3.5 w-3.5 mr-1" />
                                        {t('Start')}
                                    </Button>
                                )}
                                {ticket.status === 'in_progress' && (
                                    <Button 
                                        variant="outline" 
                                        size="sm" 
                                        onClick={() => handleQuickStatusChange(ticket, 'resolved')}
                                        className="flex-1 h-8 text-xs border-green-300 text-green-700 hover:bg-green-50"
                                    >
                                        <CheckCircle className="h-3.5 w-3.5 mr-1" />
                                        {t('Resolve')}
                                    </Button>
                                )}
                            </>
                        )}

                        {/* Edit & Delete */}
                        <TooltipProvider>
                            {auth.user?.permissions?.includes('edit-helpdesk-tickets') && (
                                <Tooltip delayDuration={300}>
                                    <TooltipTrigger asChild>
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            onClick={() => openModal('edit', ticket)} 
                                            className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                        >
                                            <EditIcon className="h-4 w-4" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent><p>{t('Edit')}</p></TooltipContent>
                                </Tooltip>
                            )}

                            {auth.user?.permissions?.includes('delete-helpdesk-tickets') && (
                                <Tooltip delayDuration={300}>
                                    <TooltipTrigger asChild>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => openDeleteDialog(ticket.id)}
                                            className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                                </Tooltip>
                            )}
                        </TooltipProvider>
                    </div>
                </CardContent>
            </Card>
        );
    };

    const renderPrioritySection = (priority: string, ticketsList: any[]) => {
        if (ticketsList.length === 0) return null;
        const config = priorityConfig[priority as keyof typeof priorityConfig];

        return (
            <div key={priority} className="mb-6">
                <div className="flex items-center gap-2 mb-3">
                    <h2 className={`text-sm font-bold uppercase ${config.textColor}`}>
                        {t(config.label)}
                    </h2>
                    <span className="text-xs text-gray-500 font-medium">
                        ({ticketsList.length})
                    </span>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    {ticketsList.map(renderTicketCard)}
                </div>
            </div>
        );
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{label: t('Helpdesk')} , {label: t('Today\'s Tickets')}]}
            pageTitle={t('Manage Today\'s Tickets')}
        >
            <Head title={t('Today\'s Tickets')} />

            {/* Compact Statistics + Search Bar */}
            <Card className="mb-6">
                <CardContent className="p-4">
                    <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        {/* Statistics */}
                        <div className="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6 text-sm">
                            <div className="flex items-center gap-2">
                                <span className="text-gray-500 font-medium">{t('Total')}:</span>
                                <span className="text-xl font-bold text-gray-900">{stats.total}</span>
                            </div>
                            
                            <div className="hidden sm:block h-6 w-px bg-gray-300"></div>

                            <div className="flex flex-wrap items-center gap-3 sm:gap-4">
                                <div className="flex items-center gap-1.5">
                                    <div className="px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-semibold">{t('Urgent')}</div>
                                    <span className="font-semibold text-gray-900">{stats.urgent}</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <div className="px-2 py-0.5 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">{t('High')}</div>
                                    <span className="font-semibold text-gray-900">{stats.high}</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <div className="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">{t('Medium')}</div>
                                    <span className="font-semibold text-gray-900">{stats.medium}</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <div className="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{t('Low')}</div>
                                    <span className="font-semibold text-gray-900">{stats.low}</span>
                                </div>
                            </div>

                            <div className="hidden sm:block h-6 w-px bg-gray-300"></div>

                            <div className="flex flex-wrap items-center gap-3 sm:gap-4">
                                <div className="flex items-center gap-1.5">
                                    <span className="text-gray-600">{t('Open')}:</span>
                                    <span className="font-semibold text-blue-600">{stats.open}</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <span className="text-gray-600">{t('In Progress')}:</span>
                                    <span className="font-semibold text-yellow-600">{stats.in_progress}</span>
                                </div>
                            </div>
                        </div>

                        {/* Search with Filter Dropdown */}
                        <form onSubmit={handleSearch} className="flex items-center gap-2 w-full lg:w-auto">
                            {/* Filter Dropdown */}
                            <Select value={activePriorityFilter} onValueChange={(value) => setActivePriorityFilter(value)}>
                                <SelectTrigger className="w-[140px]">
                                    <div className="flex items-center">
                                        <Filter className="h-4 w-4 mr-2" />
                                        <span>
                                            {activePriorityFilter === 'all' && t('All')}
                                            {activePriorityFilter === 'urgent' && t('Urgent')}
                                            {activePriorityFilter === 'high' && t('High')}
                                            {activePriorityFilter === 'medium' && t('Medium')}
                                            {activePriorityFilter === 'low' && t('Low')}
                                        </span>
                                    </div>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All')} ({stats.total})</SelectItem>
                                    <SelectItem value="urgent">
                                        <div className="flex items-center gap-2">
                                            <span className="px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-semibold">{t('Urgent')}</span>
                                            <span>({stats.urgent})</span>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="high">
                                        <div className="flex items-center gap-2">
                                            <span className="px-2 py-0.5 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">{t('High')}</span>
                                            <span>({stats.high})</span>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="medium">
                                        <div className="flex items-center gap-2">
                                            <span className="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">{t('Medium')}</span>
                                            <span>({stats.medium})</span>
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="low">
                                        <div className="flex items-center gap-2">
                                            <span className="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{t('Low')}</span>
                                            <span>({stats.low})</span>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            {/* Search Input */}
                            <Input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder={t('Search tickets...')}
                                className="w-full lg:w-64"
                            />
                            {searchQuery && (
                                <Button type="button" variant="ghost" size="sm" onClick={clearSearch}>
                                    {t('Clear')}
                                </Button>
                            )}
                        </form>
                    </div>
                </CardContent>
            </Card>

            {/* Tickets Grid */}
            {filteredTickets.length > 0 ? (
                <div>
                    {renderPrioritySection('urgent', groupedTickets.urgent)}
                    {renderPrioritySection('high', groupedTickets.high)}
                    {renderPrioritySection('medium', groupedTickets.medium)}
                    {renderPrioritySection('low', groupedTickets.low)}
                </div>
            ) : (
                <Card>
                    <CardContent className="text-center py-12">
                        <Clock className="h-12 w-12 text-gray-400 mx-auto mb-3" />
                        <h3 className="text-lg font-semibold text-gray-900 mb-1">
                            {searchQuery ? t('No tickets found') : t('No pending tickets')}
                        </h3>
                        <p className="text-sm text-gray-500">
                            {searchQuery ? t('Try adjusting your search query') : t('All tickets are resolved or closed. Great job!')}
                        </p>
                        {searchQuery && (
                            <Button variant="outline" size="sm" onClick={clearSearch} className="mt-4">
                                {t('Clear Search')}
                            </Button>
                        )}
                    </CardContent>
                </Card>
            )}

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'edit' && modalState.data && (
                    <Edit ticket={modalState.data} onSuccess={closeModal} />
                )}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Ticket')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}
