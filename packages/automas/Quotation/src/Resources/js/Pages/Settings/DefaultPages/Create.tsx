import React from "react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import DefaultPageForm, { VariableGroup } from "@/components/DefaultPageForm";
import { replaceQuotationShortcodes } from "../../Quotations/utils/quotationShortcodes";

interface Props {
    settings?: {
        template_color?: string;
        background_image?: string;
        [key: string]: any;
    } | null;
    nextSortOrder?: number;
    variables?: Record<string, string>;
}

export const defaultQuotationVariableGroups: VariableGroup[] = [
    {
        title: "Quotation",
        items: [
            { label: "Subject", key: "quotation_subject" },
            { label: "Quotation Number", key: "quotation_number" },
            { label: "Quotation Date", key: "quotation_date" },
            { label: "Quotation Due Date", key: "due_date" },
        ],
    },
    {
        title: "Company",
        items: [
            { label: "Company Name", key: "company_name" },
            { label: "Company Logo", key: "company_logo" },
            { label: "Quotation Logo", key: "quotation_logo" },
            { label: "Company Email", key: "company_email" },
            { label: "Company Phone", key: "company_phone" },
            { label: "Company Address", key: "company_address" },
            { label: "Company Website", key: "company_website" },
        ],
    },
    {
        title: "User",
        items: [
            { label: "User Name", key: "user_name" },
            { label: "User Email", key: "user_email" },
            { label: "User Phone", key: "user_phone" },
        ],
    },
    {
        title: "Customer",
        items: [
            { label: "Customer Name", key: "customer_name" },
            { label: "Customer Email", key: "customer_email" },
            { label: "Customer Phone", key: "customer_phone" },
            { label: "Customer Address", key: "customer_address" },
        ],
    },
];

export default function Create({ settings, nextSortOrder }: Props) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t("Create Default Page")} />
            <DefaultPageForm
                mode="create"
                moduleType="quotation"
                settings={settings}
                nextSortOrder={nextSortOrder}
                variableGroups={defaultQuotationVariableGroups}
                replaceShortcodes={replaceQuotationShortcodes}
                storeRoute={route("quotation-setup.default-pages.store")}
                backRoute={route("quotation-setup.index")}
                breadcrumbs={[
                    {
                        label: t("Quotations"),
                        url: route("quotations.index"),
                    },
                    {
                        label: t("Quotation Setup"),
                        url: route("quotation-setup.index"),
                    },
                    {
                        label: t("Create Default Page"),
                    },
                ]}
                pageTitle={t("Create Default Page")}
            />
        </>
    );
}
