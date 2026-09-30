import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { Switch } from "@/components/ui/switch";
import { Users as UsersIcon, User as UserIcon, Mail, Phone, ArrowLeft } from "lucide-react";
import { getImagePath } from '@/utils/helpers';
import { SearchInput } from '@/components/ui/search-input';
import NoRecordsFound from '@/components/no-records-found';
import { User, AdminHubProps, UserFilters } from './types';

export default function AdminHub() {
    const { t } = useTranslation();
    const { company, companyUsers, totalActiveUsers, totalInactiveUsers, auth } = usePage<AdminHubProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState<UserFilters>({
        name: urlParams.get('name') || '',
        email: '',
        role: '',
        is_enable_login: ''
    });

    const [togglingUserId, setTogglingUserId] = useState<number | null>(null);

    useFlashMessages();

    const handleToggleStatus = (userId: number) => {
        setTogglingUserId(userId);
        router.patch(route('users.toggle-status', userId), {}, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => setTogglingUserId(null),
        });
    };

    const hasActiveFilters = !!filters.name;

    const handleSearch = () => {
        router.get(route('users.admin-hub', company.id), { name: filters.name }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ name: '', email: '', role: '', is_enable_login: '' });
        router.get(route('users.admin-hub', company.id), {});
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('Users'), url: route('users.index')},
                {label: t('Admin Hub')}
            ]}
            pageTitle={t('Admin Hub')}
            pageActions={
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => router.visit(route('users.index'))}
                >
                    <ArrowLeft className="h-4 w-4" />
                    {t('Back')}
                </Button>
            }
        >
            <Head title={t('Admin Hub')} />

            {/* Simple Header Card */}
            <Card className="mb-6 border">
                <CardContent className="p-6">
                    <div className="flex items-center gap-6">
                        {/* Company Avatar */}
                        <div className="w-20 h-20 rounded-lg overflow-hidden bg-gray-200 border flex items-center justify-center flex-shrink-0">
                            {company.avatar ? (
                                <img src={getImagePath(company.avatar)} alt={company.name} className="w-full h-full object-cover" />
                            ) : (
                                <UsersIcon className="w-10 h-10 text-gray-400" />
                            )}
                        </div>

                        {/* Company Info */}
                        <div className="flex-1">
                            <h2 className="text-xl font-bold text-gray-900">{company.name}</h2>
                            <div className="mt-2 space-y-1 text-sm text-gray-600">
                                <div className="flex items-center gap-2">
                                    <Mail className="w-4 h-4" />
                                    {company.email}
                                </div>
                                {company.mobile_no && (
                                    <div className="flex items-center gap-2">
                                        <Phone className="w-4 h-4" />
                                        {company.mobile_no}
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Statistics */}
                        <div className="grid grid-cols-3 gap-4 ml-auto">
                            <div className="text-center">
                                <p className="text-2xl font-bold text-gray-900">{companyUsers?.length || 0}</p>
                                <p className="text-xs text-gray-500 mt-1">{t('Total Users')}</p>
                            </div>
                            <div className="text-center">
                                <p className="text-2xl font-bold text-green-600">{totalActiveUsers}</p>
                                <p className="text-xs text-gray-500 mt-1">{t('Active')}</p>
                            </div>
                            <div className="text-center">
                                <p className="text-2xl font-bold text-red-600">{totalInactiveUsers}</p>
                                <p className="text-xs text-gray-500 mt-1">{t('Inactive')}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Users Grid */}
            <Card className="border">
                {/* Search */}
                <CardContent className="p-6 border-b">
                    <div className="flex items-center gap-2">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.name}
                                onChange={(value) => setFilters({...filters, name: value})}
                                onSearch={handleSearch}
                                placeholder={t('Search by name, email...')}
                            />
                        </div>
                    </div>
                </CardContent>

                {/* Users Grid - 3 per row */}
                <CardContent className="p-6">
                    {companyUsers && companyUsers.length > 0 ? (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {companyUsers.map((user: User) => (
                                <div key={user.id} className="border rounded-lg p-3 hover:shadow-md transition-shadow flex items-center gap-3 justify-between">
                                    {/* Left: Avatar + Info */}
                                    <div className="flex items-center gap-3 flex-1 min-w-0">
                                        {/* Avatar */}
                                        <div className="w-10 h-10 rounded-lg overflow-hidden bg-gray-200 border flex items-center justify-center flex-shrink-0">
                                            {user.avatar ? (
                                                <img src={getImagePath(user.avatar)} alt={user.name} className="w-full h-full object-cover" />
                                            ) : (
                                                <UserIcon className="w-5 h-5 text-gray-400" />
                                            )}
                                        </div>

                                        {/* Name and Email */}
                                        <div className="flex-1 min-w-0">
                                            <h3 className="text-xs font-semibold text-gray-900 truncate">{user.name}</h3>
                                            <p className="text-xs text-gray-500 truncate leading-tight">{user.email}</p>
                                        </div>
                                    </div>

                                    {/* Right: Toggle */}
                                    <Switch
                                        checked={user.is_enable_login}
                                        disabled={togglingUserId === user.id}
                                        onCheckedChange={() => handleToggleStatus(user.id)}
                                    />
                                </div>
                            ))}
                        </div>
                    ) : (
                        <NoRecordsFound
                            icon={UsersIcon}
                            title={t('No users found')}
                            description={hasActiveFilters ? t('No users match your search.') : t('This company has no sub-users yet.')}
                            hasFilters={hasActiveFilters}
                            onClearFilters={clearFilters}
                            className="h-auto py-12"
                        />
                    )}
                </CardContent>
            </Card>
        </AuthenticatedLayout>
    );
}
