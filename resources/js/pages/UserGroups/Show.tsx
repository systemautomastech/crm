import React from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { ArrowLeft, Edit, Users, CheckCircle2, XCircle, Calendar, UserCheck } from 'lucide-react';
import { formatDate } from '@/utils/helpers';

interface GroupUser {
    id: number;
    name: string;
    email: string;
    type?: string;
}

interface UserGroup {
    id: number;
    name: string;
    description?: string | null;
    is_active: boolean;
    created_at: string;
    creator?: { id: number; name: string } | null;
    users?: GroupUser[];
}

interface PageProps {
    group: UserGroup;
    auth: any;
    [key: string]: any;
}

export default function Show() {
    const { t } = useTranslation();
    const { group, auth } = usePage<PageProps>().props;

    const canEdit = auth?.user?.permissions?.includes('edit-user-groups') || auth?.user?.type === 'company';

    const getInitials = (name: string) => {
        return name
            ? name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
            : 'U';
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('User Management'), url: route('users.index') },
                { label: t('User Groups'), url: route('user-groups.index') },
                { label: group.name },
            ]}
            pageTitle={group.name}
            pageActions={
                <div className="flex items-center gap-2">
                    <Button variant="outline" size="sm" onClick={() => router.visit(route('user-groups.index'))}>
                        <ArrowLeft className="w-4 h-4 mr-1" />
                        {t('Back to Groups')}
                    </Button>
                    {canEdit && (
                        <Button size="sm" onClick={() => router.visit(route('user-groups.edit', group.id))}>
                            <Edit className="w-4 h-4 mr-1" />
                            {t('Edit Group')}
                        </Button>
                    )}
                </div>
            }
        >
            <Head title={`${t('User Group')} - ${group.name}`} />

            <div className="space-y-6">
                {/* Overview Header Card */}
                <Card>
                    <CardHeader className="pb-3">
                        <div className="flex items-start justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold">
                                    <Users className="w-6 h-6" />
                                </div>
                                <div>
                                    <CardTitle className="text-xl font-bold">{group.name}</CardTitle>
                                    <p className="text-sm text-muted-foreground mt-0.5">
                                        {group.description || t('No description provided.')}
                                    </p>
                                </div>
                            </div>
                            <div>
                                {group.is_active ? (
                                    <Badge className="bg-green-100 text-green-800 border-green-200 text-xs px-3 py-1">
                                        <CheckCircle2 className="w-3.5 h-3.5 mr-1" />
                                        {t('Active')}
                                    </Badge>
                                ) : (
                                    <Badge variant="secondary" className="bg-red-100 text-red-800 border-red-200 text-xs px-3 py-1">
                                        <XCircle className="w-3.5 h-3.5 mr-1" />
                                        {t('Inactive')}
                                    </Badge>
                                )}
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="border-t pt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div className="flex items-center gap-2 text-muted-foreground">
                            <Users className="w-4 h-4 text-primary" />
                            <span>{t('Total Members')}: <strong className="text-foreground">{group.users?.length || 0}</strong></span>
                        </div>
                        <div className="flex items-center gap-2 text-muted-foreground">
                            <UserCheck className="w-4 h-4 text-primary" />
                            <span>{t('Created By')}: <strong className="text-foreground">{group.creator?.name || 'System'}</strong></span>
                        </div>
                        <div className="flex items-center gap-2 text-muted-foreground">
                            <Calendar className="w-4 h-4 text-primary" />
                            <span>{t('Created Date')}: <strong className="text-foreground">{formatDate(group.created_at)}</strong></span>
                        </div>
                    </CardContent>
                </Card>

                {/* Group Members List Card */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg font-semibold flex items-center gap-2">
                            <Users className="w-5 h-5 text-primary" />
                            {t('Group Members')} ({group.users?.length || 0})
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {!group.users || group.users.length === 0 ? (
                            <div className="p-8 text-center text-muted-foreground text-sm">
                                {t('No users assigned to this group yet.')}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b bg-muted/40 text-muted-foreground">
                                            <th className="px-4 py-3 text-left font-medium">{t('User')}</th>
                                            <th className="px-4 py-3 text-left font-medium">{t('Email')}</th>
                                            <th className="px-4 py-3 text-right font-medium">{t('Role / Type')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {group.users.map((user) => (
                                            <tr key={user.id} className="hover:bg-muted/20 transition-colors">
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-3">
                                                        <Avatar className="h-8 w-8">
                                                            <AvatarFallback className="bg-primary/10 text-primary font-medium text-xs">
                                                                {getInitials(user.name)}
                                                            </AvatarFallback>
                                                        </Avatar>
                                                        <span className="font-medium">{user.name}</span>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">{user.email}</td>
                                                <td className="px-4 py-3 text-right">
                                                    <Badge variant="outline" className="capitalize text-xs">
                                                        {user.type || t('Employee')}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
