import React from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import DefaultPageForm, { VariableGroup } from '@/components/DefaultPageForm';
import { replaceProposalShortcodes } from '@/pages/SalesProposals/utils/proposalShortcodes';

interface Props {
    settings?: {
        template_color?: string;
        background_image?: string;
        [key: string]: any;
    } | null;
    nextSortOrder?: number;
    variables?: Record<string, string>;
}

export const defaultProposalVariableGroups: VariableGroup[] = [
    {
        title: 'Proposal',
        items: [
            { label: 'Proposal Subject', key: 'proposal_subject' },
            { label: 'Proposal Number', key: 'proposal_number' },
            { label: 'Proposal Date', key: 'proposal_date' },
            { label: 'Proposal Due Date', key: 'proposal_due_date' },
        ],
    },
    {
        title: 'Company',
        items: [
            { label: 'Company Name', key: 'company_name' },
            { label: 'Company Logo', key: 'company_logo' },
            { label: 'Proposal Logo', key: 'proposal_logo' },
            { label: 'Company Email', key: 'company_email' },
            { label: 'Company Phone', key: 'company_phone' },
            { label: 'Company Address', key: 'company_address' },
            { label: 'Company Website', key: 'company_website' },
        ],
    },
    {
        title: 'User',
        items: [
            { label: 'User Name', key: 'user_name' },
            { label: 'User Email', key: 'user_email' },
            { label: 'User Phone', key: 'user_phone' },
        ],
    },
    {
        title: 'Customer',
        items: [
            { label: 'Customer Name', key: 'customer_name' },
            { label: 'Customer Email', key: 'customer_email' },
            { label: 'Customer Phone', key: 'customer_phone' },
            { label: 'Customer Address', key: 'customer_address' },
        ],
    },
];

export default function Create({ settings, nextSortOrder }: Props) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Create Default Page')} />
            <DefaultPageForm
                mode="create"
                moduleType="proposal"
                settings={settings}
                nextSortOrder={nextSortOrder}
                variableGroups={defaultProposalVariableGroups}
                replaceShortcodes={replaceProposalShortcodes}
                storeRoute={route('proposal-setup.default-pages.store')}
                backRoute={route('proposal-setup.index')}
                breadcrumbs={[
                    {
                        label: t('Sales Proposals'),
                        url: route('sales-proposals.index'),
                    },
                    {
                        label: t('Proposal Setup'),
                        url: route('proposal-setup.index'),
                    },
                    {
                        label: t('Create Default Page'),
                    },
                ]}
                pageTitle={t('Create Default Page')}
            />
        </>
    );
}
