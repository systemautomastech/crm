import { useState, useEffect, useRef, useMemo, useCallback } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Bot, Send, Loader2, MessageSquare, Plus, Trash2, User, Sparkles, ChevronDown, ChevronUp, Search, X, FileText, Receipt, FolderKanban, Users } from 'lucide-react';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';

interface ChatMessage {
    id?: number;
    role: 'user' | 'assistant';
    content: string;
    created_at?: string;
}

interface ChatSession {
    id: number;
    title: string;
    updated_at: string;
    last_message?: { content: string; role: string };
}

interface Props {
    sessions: ChatSession[];
}

const toggleMessageExpansion = (setExpandedMessages: React.Dispatch<React.SetStateAction<Set<number>>>, index: number) => {
    setExpandedMessages(prev => {
        const newSet = new Set(prev);
        if (newSet.has(index)) {
            newSet.delete(index);
        } else {
            newSet.add(index);
        }
        return newSet;
    });
};

const isMessageLong = (content: string) => content.length > 500;
const getTruncatedContent = (content: string) => content.substring(0, 500) + '...';

const groupSessionsByDate = (sessions: ChatSession[]) => {
    const today     = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    const weekAgo   = new Date(today);
    weekAgo.setDate(weekAgo.getDate() - 7);

    const groups: Record<string, ChatSession[]> = {
        Today: [],
        Yesterday: [],
        'This Week': [],
        Older: [],
    };

    sessions.forEach(s => {
        const d = new Date(s.updated_at);
        if (d.toDateString() === today.toDateString()) {
            groups['Today'].push(s);
        } else if (d.toDateString() === yesterday.toDateString()) {
            groups['Yesterday'].push(s);
        } else if (d >= weekAgo) {
            groups['This Week'].push(s);
        } else {
            groups['Older'].push(s);
        }
    });

    return groups;
};

const MessageInput = ({ onSend, loading, inputRef }: { onSend: (msg: string) => void; loading: boolean; inputRef: React.RefObject<HTMLTextAreaElement> }) => {
    const [value, setValue] = useState('');

    const handleSend = () => {
        if (value.trim() && !loading) {
            onSend(value.trim());
            setValue('');
            if (inputRef.current) {
                inputRef.current.style.height = 'auto';
            }
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
        setValue(e.target.value);
        e.target.style.height = 'auto';
        e.target.style.height = Math.min(e.target.scrollHeight, 200) + 'px';
    };

    return (
        <div className="border-t bg-background/80 backdrop-blur-sm">
            <div className="max-w-5xl mx-auto px-4 py-4">
                <div className="flex gap-3 items-end bg-background border rounded-2xl shadow-sm p-2 focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                    <textarea
                        ref={inputRef}
                        rows={1}
                        value={value}
                        onChange={handleChange}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter' && !e.shiftKey) {
                                e.preventDefault();
                                handleSend();
                            }
                        }}
                        placeholder="Message AI Agent..."
                        disabled={loading}
                        className="flex-1 resize-none bg-transparent px-2 py-2 text-[15px] focus:outline-none disabled:opacity-50 max-h-[200px] overflow-y-auto"
                        style={{ minHeight: '36px' }}
                    />
                    <Button
                        size="icon"
                        onClick={handleSend}
                        disabled={loading || !value.trim()}
                        className="h-9 w-9 shrink-0 rounded-xl"
                    >
                        <Send className="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    );
};

export default function AIAgentChatPage({ sessions: initialSessions }: Props) {
    const { t } = useTranslation();

    useFlashMessages();

    const [sessions, setSessions]           = useState<ChatSession[]>(initialSessions);
    const [activeSession, setActiveSession] = useState<ChatSession | null>(null);
    const [messages, setMessages]           = useState<ChatMessage[]>([]);
    const [searchQuery, setSearchQuery]     = useState('');
    const [loading, setLoading]             = useState(false);
    const [loadingMessages, setLoadingMessages] = useState(false);
    const [deleteTarget, setDeleteTarget]   = useState<ChatSession | null>(null);
    const [expandedMessages, setExpandedMessages] = useState<Set<number>>(new Set());
    const bottomRef                         = useRef<HTMLDivElement>(null);
    const inputRef                          = useRef<HTMLTextAreaElement>(null);

    const filteredSessions = useMemo(() => 
        sessions.filter(session => 
            session.title.toLowerCase().includes(searchQuery.toLowerCase())
        ),
        [sessions, searchQuery]
    );

    const grouped = useMemo(() => groupSessionsByDate(filteredSessions), [filteredSessions]);

    const suggestedPrompts = useMemo(() => [
        { text: t('Show this month accepted proposals'), icon: FileText },
        { text: t('Show all draft sales invoices'), icon: Receipt },
        { text: t('List all ongoing projects'), icon: FolderKanban },
        { text: t('Show first 10 active leads'), icon: Users },
    ], [t]);

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages, loading]);

    const loadSession = async (session: ChatSession) => {
        setActiveSession(session);
        setLoadingMessages(true);
        setMessages([]);

        try {
            const res = await fetch(route('ai-agent.sessions.messages', session.id), {
                headers: { 'Accept': 'application/json' },
            });
            
            const data = await res.json();
            
            if (!res.ok) {
                // Error will be shown by useFlashMessages if it's a permission error
                setMessages([]);
                return;
            }
            
            setMessages(data);
        } catch {
            setMessages([]);
        } finally {
            setLoadingMessages(false);
            setTimeout(() => inputRef.current?.focus(), 100);
        }
    };

    const createNewChat = () => {
        setActiveSession(null);
        setMessages([]);
        setTimeout(() => inputRef.current?.focus(), 100);
    };

    const deleteSession = (session: ChatSession) => {
        router.delete(route('ai-agent.sessions.destroy', session.id), {
            preserveState: true,
            onSuccess: () => {
                const remaining = sessions.filter(s => s.id !== session.id);
                setSessions(remaining);
                setDeleteTarget(null);

                if (activeSession?.id === session.id) {
                    setActiveSession(null);
                    setMessages([]);
                }
            },
            onFinish: () => {
                setDeleteTarget(null);
            }
        });
    };

    const sendMessage = async (message: string) => {
        if (!message || loading) return;

        // Auto-create session if none exists
        let currentSession = activeSession;
        let isNewSession = false;
        
        if (!currentSession) {
            setLoading(true);
            
            try {
                const res = await fetch(route('ai-agent.sessions.store'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                });
                
                if (!res.ok) {
                    setLoading(false);
                    return;
                }
                
                const newSession = await res.json();
                setSessions(prev => [newSession, ...prev]);
                setActiveSession(newSession);
                currentSession = newSession;
                isNewSession = true;
                
                // Now proceed with sending the message
                await proceedWithMessage(newSession, message, isNewSession);
            } catch (error) {
                setLoading(false);
                return;
            }
            return;
        }

        proceedWithMessage(currentSession, message, isNewSession);
    };

    const proceedWithMessage = async (currentSession: ChatSession, message: string, isNewSession: boolean) => {
        const userMsg: ChatMessage = { role: 'user', content: message };
        setMessages(prev => [...prev, userMsg]);
        setLoading(true);

        const history = [...messages, userMsg]
            .slice(-6)
            .map(m => ({ role: m.role, content: m.content }));

        try {
            const res = await fetch(route('ai-agent.chat'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    message,
                    session_id: currentSession.id,
                    history,
                }),
            });

            const data = await res.json();
            
            if (!res.ok) {
                // Permission errors handled by backend flash messages
                setMessages(prev => prev.slice(0, -1)); // Remove user message on error
                return;
            }

            const reply = data.reply ?? t('Something went wrong. Please try again.');

            setMessages(prev => [...prev, { role: 'assistant', content: reply }]);

            // Update session title and timestamp
            if (currentSession.title === 'New Chat') {
                const newTitle = message.substring(0, 60);
                setActiveSession(prev => prev ? { ...prev, title: newTitle } : prev);
                setSessions(prev => prev.map(s =>
                    s.id === currentSession.id
                        ? { ...s, title: newTitle, updated_at: new Date().toISOString() }
                        : s
                ));
            } else {
                setSessions(prev => {
                    const updated = prev.map(s =>
                        s.id === currentSession.id ? { ...s, updated_at: new Date().toISOString() } : s
                    );
                    return [...updated].sort((a, b) =>
                        new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime()
                    );
                });
            }

            // Reload messages from database for newly created sessions to sync state
            if (isNewSession) {
                const msgRes = await fetch(route('ai-agent.sessions.messages', currentSession.id), {
                    headers: { 'Accept': 'application/json' },
                });
                const savedMessages = await msgRes.json();
                setMessages(savedMessages);
            }
        } catch {
            setMessages(prev => [...prev, { role: 'assistant', content: t('Network error. Please try again.') }]);
        } finally {
            setLoading(false);
        }
    };



    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('AI Agent') }]}
            pageTitle={t('AI Agent')}
        >
            <Head title={t('AI Agent')} />

            <div className="flex h-[calc(100vh-120px)] gap-0 border rounded-xl overflow-hidden bg-background">
                <div className="w-64 border-r flex flex-col bg-muted/20 shrink-0">
                    <div className="p-3 border-b bg-background/50 space-y-3">
                        <Button 
                            onClick={createNewChat} 
                            variant="outline"
                            className="w-full gap-2 h-9 font-medium hover:bg-primary hover:text-primary-foreground transition-colors" 
                            size="sm"
                        >
                            <Plus className="h-4 w-4" />
                            {t('New Chat')}
                        </Button>
                        <div className="relative">
                            <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                            <input
                                type="text"
                                placeholder={t('Search chats...')}
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full h-8 pl-8 pr-8 text-xs rounded-md border bg-background focus:outline-none focus:ring-1 focus:ring-primary"
                            />
                            {searchQuery && (
                                <button
                                    onClick={() => setSearchQuery('')}
                                    className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                >
                                    <X className="h-3.5 w-3.5" />
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="flex-1 overflow-y-auto p-2 space-y-4">
                        {filteredSessions.length === 0 && searchQuery && (
                            <div className="flex flex-col items-center justify-center py-12 px-4 text-center">
                                <Search className="h-8 w-8 text-muted-foreground/40 mb-2" />
                                <p className="text-xs text-muted-foreground font-medium">
                                    {t('No chats found')}
                                </p>
                                <p className="text-[11px] text-muted-foreground/60 mt-1">
                                    {t('Try a different search term')}
                                </p>
                            </div>
                        )}
                        {sessions.length === 0 && (
                            <div className="flex flex-col items-center justify-center py-12 px-4 text-center">
                                <div className="bg-muted/50 rounded-full p-3 mb-3">
                                    <MessageSquare className="h-5 w-5 text-muted-foreground/60" />
                                </div>
                                <p className="text-xs text-muted-foreground font-medium">
                                    {t('No conversations yet')}
                                </p>
                                <p className="text-[11px] text-muted-foreground/60 mt-1">
                                    {t('Start a new chat')}
                                </p>
                            </div>
                        )}

                        {Object.entries(grouped).map(([group, items]) =>
                            items.length === 0 ? null : (
                                <div key={group}>
                                    <p className="text-xs font-semibold text-muted-foreground/70 px-3 mb-1.5 uppercase tracking-wider">{t(group)}</p>
                                    {items.map(session => (
                                        <div
                                            key={session.id}
                                            onClick={() => loadSession(session)}
                                            className={`group flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg cursor-pointer transition-all ${
                                                activeSession?.id === session.id
                                                    ? 'bg-accent text-accent-foreground font-medium'
                                                    : 'hover:bg-muted/50 text-foreground'
                                            }`}
                                        >
                                            <span className="truncate flex-1 text-sm">{session.title}</span>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="h-6 w-6 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity text-destructive hover:text-destructive hover:bg-destructive/10"
                                                onClick={e => { e.stopPropagation(); setDeleteTarget(session); }}
                                            >
                                                <Trash2 className="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    ))}
                                </div>
                            )
                        )}
                    </div>
                </div>

                <div className="flex-1 flex flex-col bg-gradient-to-b from-background to-muted/10">
                    <div className="flex-1 overflow-y-auto">
                        {!activeSession && (
                            <div className="flex flex-col items-center justify-center h-full text-center px-4 max-w-2xl mx-auto">
                                <div className="bg-primary/10 rounded-full p-6 mb-6">
                                    <Sparkles className="h-12 w-12 text-primary" />
                                </div>
                                <h2 className="text-2xl font-semibold mb-2">{t('Welcome to AI Agent')}</h2>
                                <p className="text-muted-foreground mb-8">{t('Your intelligent assistant is ready to help')}</p>
                                <div className="grid grid-cols-2 gap-3 w-full">
                                    {suggestedPrompts.map((prompt, i) => {
                                        const Icon = prompt.icon;
                                        return (
                                            <button
                                                key={i}
                                                onClick={() => sendMessage(prompt.text)}
                                                className="p-4 text-left text-sm border rounded-xl hover:bg-muted/50 hover:border-primary/30 transition-all group"
                                            >
                                                <Icon className="h-4 w-4 text-primary mb-2 group-hover:scale-110 transition-transform" />
                                                <p className="text-foreground/80">{prompt.text}</p>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        )}

                        {activeSession && loadingMessages && (
                            <div className="flex justify-center py-12">
                                <Loader2 className="h-6 w-6 animate-spin text-primary" />
                            </div>
                        )}

                        {activeSession && !loadingMessages && messages.length === 0 && (
                            <div className="flex flex-col items-center justify-center h-full text-center px-4">
                                <div className="bg-primary/10 rounded-full p-5 mb-4">
                                    <MessageSquare className="h-10 w-10 text-primary" />
                                </div>
                                <p className="text-muted-foreground">{t('Start a conversation with AI Agent')}</p>
                            </div>
                        )}

                        {activeSession && !loadingMessages && messages.length > 0 && (
                            <div className="max-w-5xl mx-auto px-4 py-6 space-y-6">
                                {messages.map((msg, i) => (
                                    <div key={i} className="space-y-2">
                                        {msg.role === 'assistant' && (
                                            <div className="flex gap-3">
                                                <div className="flex-1">
                                                    <div className="text-[15px] leading-relaxed whitespace-pre-wrap text-foreground/90">
                                                        {isMessageLong(msg.content) && !expandedMessages.has(i)
                                                            ? getTruncatedContent(msg.content)
                                                            : msg.content
                                                        }
                                                        {isMessageLong(msg.content) && (
                                                            <button
                                                                onClick={() => toggleMessageExpansion(setExpandedMessages, i)}
                                                                className="flex items-center gap-1 mt-2 text-xs font-medium text-primary hover:text-primary/80 transition-colors"
                                                            >
                                                                {expandedMessages.has(i) ? (
                                                                    <>
                                                                        <ChevronUp className="h-3 w-3" />
                                                                        {t('Show less')}
                                                                    </>
                                                                ) : (
                                                                    <>
                                                                        <ChevronDown className="h-3 w-3" />
                                                                        {t('Show more')}
                                                                    </>
                                                                )}
                                                            </button>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        )}
                                        {msg.role === 'user' && (
                                            <div className="flex justify-end">
                                                <div className="max-w-[80%] rounded-2xl px-4 py-3 text-[15px] leading-relaxed whitespace-pre-wrap bg-accent text-accent-foreground shadow-sm">
                                                    {isMessageLong(msg.content) && !expandedMessages.has(i)
                                                        ? getTruncatedContent(msg.content)
                                                        : msg.content
                                                    }
                                                    {isMessageLong(msg.content) && (
                                                        <button
                                                            onClick={() => toggleMessageExpansion(setExpandedMessages, i)}
                                                            className="flex items-center gap-1 mt-2 text-xs font-medium opacity-80 hover:opacity-100 transition-opacity"
                                                        >
                                                            {expandedMessages.has(i) ? (
                                                                <>
                                                                    <ChevronUp className="h-3 w-3" />
                                                                    {t('Show less')}
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <ChevronDown className="h-3 w-3" />
                                                                    {t('Show more')}
                                                                </>
                                                            )}
                                                        </button>
                                                    )}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                ))}

                                {loading && (
                                    <div className="flex gap-3">
                                        <div className="flex-1">
                                            <div className="inline-flex">
                                                <Loader2 className="h-4 w-4 animate-spin text-primary" />
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}

                        <div ref={bottomRef} />
                    </div>

                    <MessageInput onSend={sendMessage} loading={loading} inputRef={inputRef} />
                </div>
            </div>

            <ConfirmationDialog
                open={!!deleteTarget}
                onOpenChange={open => { if (!open) setDeleteTarget(null); }}
                onConfirm={() => deleteTarget && deleteSession(deleteTarget)}
                title={t('Delete Conversation')}
                message={t('Are you sure you want to delete this conversation? All messages will be permanently removed.')}
                confirmText={t('Delete')}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}
