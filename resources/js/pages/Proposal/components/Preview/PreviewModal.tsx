import React, { useRef, useMemo, useCallback, useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { usePage } from "@inertiajs/react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Printer, FileText, Eye } from "lucide-react";
import { getCompanySetting, formatCurrency } from "@/utils/helpers";
import { replaceProposalShortcodes } from "@/pages/Proposal/utils/proposalShortcodes";
import { cn } from "@/lib/utils";

import ProposalPage from "./ProposalPage";
import {
    DEFAULT_TEMPLATE_COLOR,
    PROPOSAL_CONTENT_CLASSES,
    isCustomHtmlContent,
    scopeAndSanitizeDocumentHtml,
    formatAmountOnly,
    getAmountFontSize,
} from "./helpers";
import {
    getA4ContentHeightPx,
    paginateDomContainer,
} from "./pagination";

import {
    ProposalItem,
    ProposalPreviewSection,
    ProposalTotals,
    ProposalFormData,
    ProposalSettingsConfig,
} from "../../types";

export interface PreviewModalProps {
    isOpen?: boolean;
    open?: boolean;
    onClose?: () => void;
    onOpenChange?: (open: boolean) => void;
    formData?: ProposalFormData;
    sections?: ProposalPreviewSection[];
    customers?: Array<{
        id: number;
        name: string;
        email: string;
        address?: string;
    }>;
    warehouses?: Array<{ id: number; name: string; address?: string }>;
    availableProducts?: Array<{
        id: number;
        name: string;
        sku?: string;
        sale_price?: number;
        description?: string;
    }>;
    proposalSetting?: ProposalSettingsConfig | null;
    totals?: ProposalTotals;
    other_details?: string;
    title?: string;
    pageTitle?: string;
    content?: string;
    backgroundImage?: string;
    settings?: ProposalSettingsConfig | null;
    isPageSetup?: boolean;
    showPrintButton?: boolean;
    customHtml?: boolean;
    inline?: boolean;
    autoPrint?: boolean;
    hideHeaderBar?: boolean;
}

export const PRINT_STYLES = `
    @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; box-sizing: border-box !important; }

    .phone-tab { display: none; }
    .proposal-cover__sheet, .proposal-preview-sheet {
        width: 210mm; min-height: 297mm; height: 297mm; max-height: 297mm; margin: 0 auto; background: #fff; position: relative !important; overflow: hidden !important; box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.08); page-break-after: always; font-family: "Open Sans", sans-serif !important;
    }
    .proposal-page__body {
        position: relative !important; z-index: 1; padding: 32mm 15mm 20mm; height: calc(297mm - 52mm); min-height: calc(297mm - 52mm); max-height: calc(297mm - 52mm); box-sizing: border-box; display: flex !important; flex-direction: column !important;
    }

    /* Table Styles */
    .proposal-preview-sheet table, .proposal-page__body table, .html-preview-container table, .prose table {
        width: 100% !important; border-collapse: collapse !important; border: 1px solid #cbd5e1 !important; font-size: 10px !important; font-family: "Open Sans", sans-serif !important; line-height: 1.35 !important; margin: 8px 0 !important; color: #293240 !important;
    }
    .proposal-preview-sheet table th, .proposal-page__body table th, .html-preview-container table th, .prose table th {
        padding: 7.5px 8px !important; font-size: 10px !important; font-weight: 600 !important; border: 1px solid #cbd5e1 !important; vertical-align: middle !important; background-color: var(--template-color, #E9591C) !important; color: #ffffff !important; line-height: 1.2 !important;
    }
    .proposal-preview-sheet table th *, .proposal-page__body table th *, .html-preview-container table th *, .prose table th * {
        color: #ffffff !important; font-size: 10px !important; font-weight: 600 !important; margin: 0 !important; padding: 0 !important; line-height: 1.2 !important;
    }
    .proposal-preview-sheet table td, .proposal-page__body table td, .html-preview-container table td, .prose table td {
        padding: 6.5px 8px !important; font-size: 10px !important; border: 1px solid #cbd5e1 !important; vertical-align: middle !important; color: #293240 !important; word-break: break-word !important; line-height: 1.35 !important; background-color: transparent;
    }
    .proposal-preview-sheet table td > p, .proposal-page__body table td > p, .html-preview-container table td > p, .prose table td > p {
        margin: 0 !important; padding: 0 !important; line-height: 1.35 !important; font-size: 10px !important;
    }
    .proposal-preview-sheet table td p + p, .proposal-page__body table td p + p, .html-preview-container table td p + p, .prose table td p + p { margin-top: 3px !important; }

    /* Content Typography */
    .html-preview-container { font-size: 14px; line-height: 1.5; color: #1e293b; width: 100%; font-family: "Open Sans", sans-serif; display: flex !important; flex-direction: column !important; flex: 1 !important; height: 100% !important; }
    .html-preview-container h1 { font-size: 24px; font-weight: 700; margin: 8px 0; color: #0f172a; }
    .html-preview-container h2 { font-size: 20px; font-weight: 700; margin: 8px 0; color: #0f172a; }
    .html-preview-container h3 { font-size: 18px; font-weight: 600; margin: 6px 0; color: #0f172a; }
    .html-preview-container h4 { font-size: 16px; font-weight: 600; margin: 4px 0; color: #0f172a; }
    .html-preview-container p { margin: 4px 0; }
    .html-preview-container p:empty::before { content: "\\00a0"; }
    .html-preview-container ul, .proposal-page__body ul, .prose ul { list-style-type: disc !important; list-style-position: outside !important; padding-left: 20px !important; margin: 6px 0 !important; }
    .html-preview-container ol, .proposal-page__body ol, .prose ol { list-style-type: decimal !important; list-style-position: outside !important; padding-left: 20px !important; margin: 6px 0 !important; }
    .html-preview-container li, .proposal-page__body li, .prose li { display: list-item !important; margin: 3px 0 !important; line-height: 1.45 !important; }
    .html-preview-container li p, .proposal-page__body li p, .prose li p { display: inline !important; margin: 0 !important; }

    /* Tables Inner Lists Formatting */
    table td ul, .html-preview-container table td ul { list-style-type: disc !important; list-style-position: outside !important; padding-left: 14px !important; margin: 3px 0 3px 2px !important; }
    table td ol, .html-preview-container table td ol { list-style-type: decimal !important; list-style-position: outside !important; padding-left: 14px !important; margin: 3px 0 3px 2px !important; }
    table td li, .html-preview-container table td li { display: list-item !important; margin: 2px 0 !important; font-size: 10px !important; line-height: 1.35 !important; color: #293240 !important; }
    table td li p, .html-preview-container table td li p { display: inline !important; margin: 0 !important; }
    .html-preview-container blockquote { border-left: 4px solid #cbd5e1; padding-left: 16px; font-style: italic; margin: 8px 0; }
    .html-preview-container img, .proposal-page__body img, .prose img, img.proposal-logo { display: inline-block !important; vertical-align: middle; }
    .html-preview-container a { color: #2563eb; text-decoration: underline; }

    @media print {
        @page { size: 210mm 297mm; margin: 0; }
        html, body { width: 210mm !important; margin: 0 !important; padding: 0 !important; background: white !important; font-family: "Open Sans", sans-serif !important; }
        .print-wrapper { width: 210mm !important; margin: 0 !important; padding: 0 !important; }
        .proposal-preview-sheet, .proposal-cover__sheet { width: 210mm !important; height: 297mm !important; min-height: 297mm !important; max-height: 297mm !important; padding: 0 !important; margin: 0 !important; box-sizing: border-box !important; page-break-after: always !important; break-after: page !important; page-break-inside: avoid !important; break-inside: avoid-page !important; overflow: hidden !important; }
        .proposal-page__body { position: relative !important; z-index: 1 !important; padding: 32mm 15mm 20mm !important; height: calc(297mm - 52mm) !important; min-height: calc(297mm - 52mm) !important; max-height: calc(297mm - 52mm) !important; box-sizing: border-box !important; display: flex !important; flex-direction: column !important; justify-content: flex-start !important; }
        .proposal-preview-sheet:last-child, .proposal-cover__sheet:last-child { page-break-after: auto !important; break-after: auto !important; }
    }
`;

export default function PreviewModal({
    isOpen,
    open,
    onClose,
    onOpenChange,
    formData,
    sections = [],
    customers = [],
    availableProducts = [],
    proposalSetting,
    totals,
    other_details,
    title,
    pageTitle,
    content,
    backgroundImage,
    settings,
    isPageSetup,
    showPrintButton = true,
    customHtml = false,
    inline = false,
    autoPrint = false,
    hideHeaderBar = false,
}: PreviewModalProps) {
    const { t } = useTranslation();
    const pageProps = usePage<any>()?.props || {};
    const isModalOpen = Boolean(isOpen ?? open);

    useEffect(() => {
        const subject = formData?.subject || title || pageTitle || "";
        const customerName = (formData as any)?.customer_name || (customers && customers.length > 0 ? customers[0]?.name : "") || "";
        const parts = [subject, customerName].filter(Boolean);
        const printTitle = parts.length > 0 ? parts.join("_") : (formData?.proposal_number ? String(formData.proposal_number) : (title || ""));

        if (inline && printTitle) {
            document.title = printTitle;
        }

        if (inline && autoPrint) {
            const timer = setTimeout(() => {
                if (printTitle) {
                    document.title = printTitle;
                }
                window.onafterprint = () => {
                    window.close();
                };
                window.print();
            }, 600);
            return () => clearTimeout(timer);
        }
    }, [inline, autoPrint, formData, customers, title, pageTitle]);

    const handleClose = useCallback(() => {
        if (onClose) onClose();
        if (onOpenChange) onOpenChange(false);
    }, [onClose, onOpenChange]);

    const measureContainerRef = useRef<HTMLDivElement>(null);

    const activeSettings = useMemo(() => {
        return (
            settings ||
            proposalSetting ||
            (pageProps as any)?.proposalSetting ||
            {}
        );
    }, [settings, proposalSetting, pageProps]);

    const templateColor = activeSettings?.template_color || DEFAULT_TEMPLATE_COLOR;
    const isLogoEnabled =
        activeSettings?.show_logo !== undefined
            ? activeSettings.show_logo === "1" ||
            activeSettings.show_logo === true ||
            activeSettings.show_logo === 1 ||
            activeSettings.show_logo === "true"
            : true;
    const rawLogo = activeSettings?.logo_image || activeSettings?.company_logo || "";
    const headerLogo = isLogoEnabled && rawLogo ? rawLogo : "";
    const headerLogoAlign = activeSettings?.header_logo_align || "right";
    const defaultBgImage = activeSettings?.background_image || "";
    const isSinglePageMode = Boolean(
        !formData && (content !== undefined || title !== undefined || pageTitle !== undefined),
    );

    const isCustomHtml = Boolean(
        customHtml || (isSinglePageMode && content && isCustomHtmlContent(content)),
    );

    const singleProcessedContent = useMemo(() => {
        if (!isSinglePageMode) return "";
        const rawContent = (content || "").trim();
        if (!rawContent && (backgroundImage || defaultBgImage)) {
            return scopeAndSanitizeDocumentHtml(
                `<div class="proposal-section-block custom-section-block page-break" data-full-page="true" style="min-height: 297mm; height: 100%;">&nbsp;</div>`
            );
        }
        if (!rawContent) return "";
        const processed = replaceProposalShortcodes(content, {
            settings: activeSettings,
            pageProps,
            isPageSetup: isPageSetup ?? true,
        });

        const wrappedContent = `<div class="proposal-section-block custom-section-block page-break" data-full-page="true" style="min-height: 297mm; height: 100%;">${processed}</div>`;
        return scopeAndSanitizeDocumentHtml(wrappedContent);
    }, [isSinglePageMode, content, backgroundImage, defaultBgImage, activeSettings, isPageSetup, pageProps]);

    const [paginatedSinglePages, setPaginatedSinglePages] = useState<string[]>([]);
    const [paginatedFullProposalPages, setPaginatedFullProposalPages] = useState<string[]>([]);
    const [paginatedFullProposalBackgrounds, setPaginatedFullProposalBackgrounds] = useState<string[]>([]);
    const [paginatedFullProposalCustomHtml, setPaginatedFullProposalCustomHtml] = useState<boolean[]>([]);

    useEffect(() => {
        if (!isSinglePageMode) return;
        if (!singleProcessedContent) {
            setPaginatedSinglePages([]);
            return;
        }

        const runPagination = () => {
            if (measureContainerRef.current) {
                const chunks = paginateDomContainer(measureContainerRef.current, getA4ContentHeightPx());
                setPaginatedSinglePages(chunks);
            } else {
                setPaginatedSinglePages([singleProcessedContent]);
            }
        };

        let cancelled = false;
        const start = async () => {
            if (document.fonts?.ready) await document.fonts.ready;
            if (!cancelled) runPagination();
        };

        start();
        return () => {
            cancelled = true;
        };
    }, [isSinglePageMode, singleProcessedContent, isModalOpen, inline, isCustomHtml]);

    const getItemName = useCallback(
        (item: ProposalItem): string => {
            if (item.product_name) return item.product_name;
            if (item.name) return item.name;
            if (item.product?.name) return item.product.name;
            if (item.product_id && availableProducts.length > 0) {
                const found = availableProducts.find((p) => String(p.id) === String(item.product_id));
                if (found?.name) return found.name;
            }
            return item.product_description || item.description || t("Item / Service");
        },
        [availableProducts, t],
    );

    const getItemDesc = useCallback(
        (item: ProposalItem): string => {
            if (item.description) return item.description;
            if (item.product_description) return item.product_description;
            if (item.product?.description) return item.product.description;
            if (item.product_id && availableProducts.length > 0) {
                const found = availableProducts.find((p) => String(p.id) === String(item.product_id));
                if (found?.description) return found.description;
            }
            return "";
        },
        [availableProducts],
    );

    const customer = useMemo(() => {
        const isNew =
            (formData as any)?.customer_mode === "new" ||
            (formData as any)?.customer_type === "new" ||
            (!formData?.customer_id && Boolean((formData as any)?.customer_name || (formData as any)?.customer_email));

        if (isNew) {
            return {
                id: 0,
                name: (formData as any)?.customer_name || "",
                email: (formData as any)?.customer_email || "",
                mobile_no: (formData as any)?.customer_phone || "",
                phone: (formData as any)?.customer_phone || "",
                address: (formData as any)?.customer_address || "",
                type: (formData as any)?.customer_type || "Individual",
            };
        }
        return (
            customers.find((c) => String(c.id) === String(formData?.customer_id)) ||
            ((formData as any)?.customer_name
                ? {
                    id: Number(formData?.customer_id) || 0,
                    name: (formData as any)?.customer_name || "",
                    email: (formData as any)?.customer_email || "",
                    mobile_no: (formData as any)?.customer_phone || "",
                    phone: (formData as any)?.customer_phone || "",
                    address: (formData as any)?.customer_address || "",
                    type: (formData as any)?.customer_type || "Individual",
                }
                : undefined)
        );
    }, [customers, formData]);

    const buildChargesTable = useCallback(
        ({
            title,
            items,
            sectionIndex,
            discountType,
            discountValue,
            blockClass,
            customMarginTop = "",
        }: {
            title: string;
            items: ProposalItem[];
            sectionIndex: number;
            discountType?: string;
            discountValue?: number;
            blockClass: string;
            customMarginTop?: string;
        }) => {
            if (items.length === 0) return "";

            const pageProps = (typeof window !== "undefined" ? (window as any)?.__INITIAL_PAGE__?.props : null) || {};
            const currencyCode = getCompanySetting("defaultCurrency", pageProps) || "BDT";

            const subtotal = items.reduce((sum, item) => sum + Number(item.quantity ?? 1) * Number(item.unit_price || 0), 0);
            const itemDiscSum = items.reduce((sum, item) => sum + Number(item.discount_amount || 0), 0);
            let discount = itemDiscSum;
            if (itemDiscSum === 0 && Number(discountValue) > 0) {
                const discVal = Number(discountValue) || 0;
                if (discountType === "percentage") {
                    discount = (subtotal * Math.min(Math.max(discVal, 0), 100)) / 100;
                } else {
                    discount = Math.min(Math.max(discVal, 0), subtotal);
                }
            }
            const tax = items.reduce((sum, item) => sum + Number(item.tax_amount || 0), 0);
            const total = Math.max(0, subtotal - discount + tax);

            let rowsHtml = "";
            items.forEach((item, idx) => {
                const qty = Number(item.quantity ?? 1);
                const price = Number(item.unit_price) || 0;
                const lineTotal = item.total_amount !== undefined ? Number(item.total_amount) : qty * price;
                const desc = getItemDesc(item);
                const taxAmt = Number(item.tax_amount) || 0;
                const discPct = Number(item.discount_percentage) || 0;
                const discAmt = Number(item.discount_amount) || 0;
                const effectiveDiscType = item.discount_type || "percentage";

                let discCellHtml = "-";
                if (effectiveDiscType === "percentage" && discPct > 0) {
                    discCellHtml = `<div>${discPct}%</div>${discAmt > 0 ? `<div style="font-size: 9px; color: #64748b;">(${formatCurrency(discAmt, pageProps)})</div>` : ""}`;
                } else if (effectiveDiscType === "fixed" && discAmt > 0) {
                    discCellHtml = formatCurrency(discAmt, pageProps);
                } else if (discPct > 0) {
                    discCellHtml = `<div>${discPct}%</div>`;
                } else if (discAmt > 0) {
                    discCellHtml = formatCurrency(discAmt, pageProps);
                }

                rowsHtml += `
                    <tr class="border-b border-slate-200 hover:bg-slate-50/50">
                        <td class="text-center font-medium border border-slate-200" style="font-size: 10px; padding: 6.5px 4px !important;">${idx + 1}</td>
                        <td class="font-semibold text-slate-900 border border-slate-200 align-top" style="font-size: 11px; padding: 6.5px 8px !important; line-height: 1.35;">${getItemName(item)}</td>
                        <td class="text-slate-600 border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">
                            <div class="leading-normal break-words [&_ul]:list-disc [&_ul]:pl-4 [&_ul]:my-0.5 [&_ol]:list-decimal [&_ol]:pl-4 [&_ol]:my-0.5 [&_li]:my-0.5 [&_li]:list-item [&_li_p]:inline [&_li_p]:m-0 [&_p]:my-0.5 [&_p:first-child]:mt-0 [&_p:last-child]:mb-0">
                                ${desc || "-"}
                            </div>
                        </td>
                        <td class="text-center border border-slate-200 align-top whitespace-nowrap" style="font-size: 10px; padding: 6.5px 4px !important;">${qty}</td>
                        <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${formatAmountOnly(price, pageProps)}</td>
                        <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${discCellHtml}</td>
                        <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${taxAmt > 0 ? formatAmountOnly(taxAmt, pageProps) : "-"}</td>
                        <td class="text-right font-medium text-slate-900 border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${formatAmountOnly(lineTotal, pageProps)}</td>
                    </tr>
                `;
            });

            return `
                <div class="proposal-section-block ${blockClass}" data-proposal-section-index="${sectionIndex}" style="margin-bottom: 1.25rem; ${customMarginTop}">
                    <div class="font-bold mb-2 text-[#293240] text-sm">${title}</div>
                    <table class="charges-table w-full text-xs mb-2 border-collapse border border-slate-300" style="font-size: 11px; width: 100%; table-layout: fixed;">
                        <thead>
                            <tr class="text-center font-semibold" style="background-color: ${templateColor}; color: #ffffff;">
                                <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 5%; white-space: nowrap; padding: 7.5px 4px !important;">${t("S/N")}</th>
                                <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 15%; padding: 7.5px 8px !important;">${t("Item / Service")}</th>
                                <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 26%; padding: 7.5px 8px !important;">${t("Description")}</th>
                                <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 6%; white-space: nowrap; padding: 7.5px 4px !important;">${t("Qty.")}</th>
                                <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Price")} (${currencyCode})</th>
                                <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 11%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Discount")}</th>
                                <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 11%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Tax / VAT")}</th>
                                <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 14%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Total")} (${currencyCode})</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="border border-slate-200"></td>
                                <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Subtotal")}:</td>
                                <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(formatAmountOnly(subtotal, pageProps), 10)}; padding: 6px 8px !important;">${formatAmountOnly(subtotal, pageProps)}</td>
                            </tr>
                            ${discount > 0
                    ? `
                            <tr>
                                <td colspan="5" class="border border-slate-200"></td>
                                <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Discount")}:</td>
                                <td class="text-right text-rose-600 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(`(-) ${formatAmountOnly(discount, pageProps)}`, 10)}; padding: 6px 8px !important;">(-) ${formatAmountOnly(discount, pageProps)}</td>
                            </tr>`
                    : ""
                }
                            ${tax > 0
                    ? `
                            <tr>
                                <td colspan="5" class="border border-slate-200"></td>
                                <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Tax / VAT")}:</td>
                                <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(`(+) ${formatAmountOnly(tax, pageProps)}`, 10)}; padding: 6px 8px !important;">(+) ${formatAmountOnly(tax, pageProps)}</td>
                            </tr>`
                    : ""
                }
                            <tr>
                                <td colspan="5" class="border border-slate-200"></td>
                                <td colspan="2" class="font-bold text-slate-900 border border-slate-200 text-right" style="font-size: 10px; padding: 7px 8px !important;">${t("Total")}:</td>
                                <td class="text-right font-bold text-slate-900 border border-slate-200" style="font-size: ${getAmountFontSize(`${formatAmountOnly(total, pageProps)} ${currencyCode}`, 10)}; padding: 7px 8px !important;">${formatAmountOnly(total, pageProps)} ${currencyCode}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
        },
        [getItemDesc, getItemName, t, templateColor],
    );

    const fullProposalHtml = useMemo(() => {
        if (isSinglePageMode || !formData) return "";

        const pageProps = (typeof window !== "undefined" ? (window as any)?.__INITIAL_PAGE__?.props : null) || {};
        const items = formData.items || [];
        const otcItems = items.filter(
            (i) =>
                (i.section === "otc" || i.section === "general" || !i.section) &&
                (Number(i.product_id) > 0 || Number(i.unit_price) > 0 || Boolean(i.product_description) || Boolean(i.description)),
        );
        const mrcItems = items.filter(
            (i) =>
                i.section === "mrc" &&
                (Number(i.product_id) > 0 || Number(i.unit_price) > 0 || Boolean(i.product_description) || Boolean(i.description)),
        );

        const htmlParts: string[] = [];

        sections.forEach((sec, sectionIndex) => {
            const rawContent = (sec.content || "").trim();
            const pageType = (sec.page_type || "").toLowerCase();
            const isOtc = pageType === "otc" || rawContent === "[OTC_CHARGES_TABLE]" || (sec.title && sec.title.toLowerCase().includes("one-time charges"));
            const isMrc = pageType === "mrc" || rawContent === "[MRC_CHARGES_TABLE]" || (sec.title && sec.title.toLowerCase().includes("monthly recurring charges"));
            const isOther = pageType === "other-details" || rawContent === "[OTHER_DETAILS_CONTENT]" || (sec.title && sec.title.toLowerCase().includes("other details"));

            if (isOtc) {
                const tableHtml = buildChargesTable({
                    title: sec.title || t("ONE-TIME CHARGES (OTC)"),
                    items: otcItems,
                    sectionIndex,
                    discountType: (formData as any).otc_discount_type,
                    discountValue: (formData as any).otc_discount_value,
                    blockClass: "otc-charges-block",
                    customMarginTop: "margin-top: 1.5rem;",
                });
                if (tableHtml) htmlParts.push(tableHtml);
                return;
            }

            if (isMrc) {
                const tableHtml = buildChargesTable({
                    title: sec.title || t("MONTHLY RECURRING CHARGES (MRC)"),
                    items: mrcItems,
                    sectionIndex,
                    discountType: (formData as any).mrc_discount_type,
                    discountValue: (formData as any).mrc_discount_value,
                    blockClass: "mrc-charges-block",
                    customMarginTop: sectionIndex === 0 ? "" : "margin-top: 2rem;",
                });
                if (tableHtml) htmlParts.push(tableHtml);
                return;
            }

            if (isOther) {
                const detailsText = formData.other_details || other_details || "";
                if (!detailsText) return;
                const title = sec.title || t("OTHER DETAILS");
                htmlParts.push(`
                    <div class="proposal-section-block other-details-block" data-proposal-section-index="${sectionIndex}" style="margin-top: 1.5rem; margin-bottom: 1.25rem;">
                        <div class="font-bold mb-2 text-[#293240] text-sm">${title}</div>
                        <div class="prose max-w-none text-xs leading-relaxed text-slate-700">
                            ${detailsText}
                        </div>
                    </div>
                `);
                return;
            }

            const isCustomPage = sec.page_type === "custom" || (!isOtc && !isMrc && !isOther);
            if (isCustomPage) {
                const bgImage = sec.background_image || "";
                if (rawContent || bgImage) {
                    htmlParts.push(`
                        <div class="proposal-section-block custom-section-block page-break" data-full-page="true" data-proposal-section-index="${sectionIndex}" style="min-height: 297mm; height: 100%;">
                            ${rawContent || "&nbsp;"}
                        </div>
                    `);
                }
            }
        });

        const combinedRaw = htmlParts.join("\n\n");
        const processed = replaceProposalShortcodes(combinedRaw, {
            proposal: formData,
            customer,
            settings: activeSettings,
            pageProps: pageProps || {},
            isPageSetup: false,
        });
        return scopeAndSanitizeDocumentHtml(processed);
    }, [
        isSinglePageMode,
        formData,
        sections,
        customer,
        buildChargesTable,
        t,
        activeSettings,
        other_details,
    ]);

    useEffect(() => {
        if (isSinglePageMode || !fullProposalHtml) {
            setPaginatedFullProposalPages((prev) => (prev.length === 0 ? prev : []));
            setPaginatedFullProposalBackgrounds((prev) => (prev.length === 0 ? prev : []));
            return;
        }

        const runPagination = () => {
            if (measureContainerRef.current) {
                const chunks = paginateDomContainer(measureContainerRef.current, getA4ContentHeightPx());
                setPaginatedFullProposalPages((prev) =>
                    prev.length === chunks.length && prev.every((val, idx) => val === chunks[idx]) ? prev : chunks,
                );

                const bgs: string[] = [];
                const customHtmlFlags: boolean[] = [];
                chunks.forEach((chunkHtml) => {
                    const match = chunkHtml.match(/data-proposal-section-index=["'](\d+)["']/);
                    if (match && match[1] !== undefined) {
                        const secIdx = parseInt(match[1], 10);
                        const matchedSec = sections[secIdx];
                        if (matchedSec?.background_image && matchedSec.background_image.trim() !== "") {
                            bgs.push(matchedSec.background_image);
                        } else {
                            bgs.push(defaultBgImage);
                        }

                        const secContent = matchedSec?.content || "";
                        const isSecCustomHtml = isCustomHtmlContent(secContent);
                        customHtmlFlags.push(isSecCustomHtml);
                        return;
                    }
                    bgs.push(defaultBgImage);
                    customHtmlFlags.push(false);
                });
                setPaginatedFullProposalBackgrounds((prev) =>
                    prev.length === bgs.length && prev.every((val, idx) => val === bgs[idx]) ? prev : bgs,
                );
                setPaginatedFullProposalCustomHtml((prev) =>
                    prev.length === customHtmlFlags.length && prev.every((val, idx) => val === customHtmlFlags[idx]) ? prev : customHtmlFlags,
                );
            } else {
                setPaginatedFullProposalPages((prev) => (prev.length === 1 && prev[0] === fullProposalHtml ? prev : [fullProposalHtml]));
                setPaginatedFullProposalBackgrounds((prev) => (prev.length === 1 && prev[0] === defaultBgImage ? prev : [defaultBgImage]));
                setPaginatedFullProposalCustomHtml((prev) => (prev.length === 1 && prev[0] === false ? prev : [false]));
            }
        };

        let cancelled = false;
        const start = async () => {
            if (document.fonts?.ready) await document.fonts.ready;
            if (!cancelled) runPagination();
        };

        start();
        return () => {
            cancelled = true;
        };
    }, [isSinglePageMode, fullProposalHtml, sections, defaultBgImage, isModalOpen, inline]);

    const handlePrint = useCallback(() => {
        const subject = formData?.subject || title || pageTitle || "";
        const customerName = (customer as any)?.name || (formData as any)?.customer_name || "";
        const parts = [subject, customerName].filter(Boolean);
        const printTitle = parts.length > 0 ? parts.join("_") : (formData?.proposal_number ? String(formData.proposal_number) : document.title);

        const originalTitle = document.title;
        if (printTitle) {
            document.title = printTitle;
        }
        window.print();
        setTimeout(() => {
            document.title = originalTitle;
        }, 1000);
    }, [formData, customer, title, pageTitle]);

    const modalTitleText = title || pageTitle || formData?.subject || t("Preview");

    const renderSheetsContent = () => (
        <div className="flex flex-col gap-6 items-center w-full print:gap-0 print:block">
            {isSinglePageMode ? (
                paginatedSinglePages.length > 0 ? (
                    paginatedSinglePages.map((pageHtml, pIdx) => (
                        <ProposalPage
                            key={`single-page-${pIdx}`}
                            pageKey={`single-page-${pIdx}`}
                            backgroundImage={backgroundImage}
                            defaultBg={defaultBgImage}
                            templateColor={templateColor}
                            headerLogo={headerLogo}
                            headerLogoAlign={headerLogoAlign}
                            content={pageHtml}
                            customHtml={isCustomHtml}
                        />
                    ))
                ) : (
                    <ProposalPage
                        key="single-page-0"
                        pageKey="single-page-0"
                        backgroundImage={backgroundImage}
                        defaultBg={defaultBgImage}
                        templateColor={templateColor}
                        headerLogo={headerLogo}
                        headerLogoAlign={headerLogoAlign}
                        content={singleProcessedContent}
                        customHtml={isCustomHtml}
                    />
                )
            ) : paginatedFullProposalPages.length > 0 ? (
                paginatedFullProposalPages.map((pageHtml, pIdx) => (
                    <ProposalPage
                        key={`proposal-page-${pIdx}`}
                        pageKey={`proposal-page-${pIdx}`}
                        backgroundImage={paginatedFullProposalBackgrounds[pIdx] || defaultBgImage}
                        defaultBg={defaultBgImage}
                        templateColor={templateColor}
                        headerLogo={headerLogo}
                        headerLogoAlign={headerLogoAlign}
                        content={pageHtml}
                        customHtml={Boolean(paginatedFullProposalCustomHtml[pIdx])}
                    />
                ))
            ) : (
                <div className="p-8 text-center text-slate-500">
                    {t("No pages configured in Page Order.")}
                </div>
            )}
        </div>
    );

    return (
        <>
            <div
                ref={measureContainerRef}
                className={cn("html-preview-container", PROPOSAL_CONTENT_CLASSES)}
                style={{
                    position: "fixed",
                    left: "-9999px",
                    top: 0,
                    width: "180mm",
                    visibility: "hidden",
                    pointerEvents: "none",
                    zIndex: -1,
                }}
                dangerouslySetInnerHTML={{
                    __html: isSinglePageMode ? singleProcessedContent : fullProposalHtml,
                }}
            />

            {inline ? (
                <div
                    className={cn(
                        "min-h-screen bg-slate-100 dark:bg-slate-950 px-4 print:p-0 print:bg-white flex flex-col items-center",
                        hideHeaderBar ? "py-0" : "py-8",
                    )}
                >
                    {!hideHeaderBar && (
                        <div className="w-full max-w-[210mm] mb-6 flex items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 print:hidden">
                            <div className="flex items-center gap-3">
                                <div className="p-2 rounded-lg bg-primary/10 text-primary">
                                    <FileText className="h-5 w-5" />
                                </div>
                                <div>
                                    <h1 className="font-bold text-slate-900 dark:text-slate-100 text-base">
                                        {formData?.proposal_number || modalTitleText}
                                    </h1>
                                    {formData?.subject && (
                                        <p className="text-xs text-slate-500">
                                            {formData.subject}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="flex items-center gap-2">
                                <Button
                                    variant="default"
                                    size="sm"
                                    onClick={() => window.print()}
                                    className="gap-2"
                                >
                                    <Printer className="h-4 w-4" />
                                    {t("Print / Save PDF")}
                                </Button>
                            </div>
                        </div>
                    )}

                    <div className="w-full flex justify-center">
                        <style dangerouslySetInnerHTML={{ __html: PRINT_STYLES }} />
                        {renderSheetsContent()}
                    </div>
                </div>
            ) : (
                <Dialog
                    open={isModalOpen}
                    onOpenChange={(openVal) => !openVal && handleClose()}
                >
                    <DialogContent className="max-w-4xl max-h-[92vh] flex flex-col p-0 gap-0 overflow-hidden bg-background border-border shadow-xl !rounded-md [&>div]:p-0 [&>div]:max-h-[92vh] [&>div]:flex [&>div]:flex-col [&>button]:top-2.5 [&>button]:right-3">
                        <DialogHeader className="!py-3 !px-5 bg-background border-b border-border flex flex-row items-center justify-between space-y-0 shrink-0">
                            <div className="flex items-center gap-2.5 pr-8">
                                <div className="p-1.5 rounded-md bg-primary/10 text-primary">
                                    <Eye className="h-4 w-4" />
                                </div>
                                <DialogTitle className="text-sm font-semibold">
                                    {modalTitleText}
                                </DialogTitle>
                            </div>

                            {showPrintButton && (
                                <div className="flex items-center gap-2 pr-6">
                                    <Button
                                        variant="default"
                                        size="sm"
                                        onClick={handlePrint}
                                        className="gap-2 text-xs h-8"
                                    >
                                        <Printer className="h-3.5 w-3.5" />
                                        {t("Print")}
                                    </Button>
                                </div>
                            )}
                        </DialogHeader>

                        <div className="flex-1 overflow-y-auto p-3 sm:p-5 bg-slate-100/70 dark:bg-slate-900 flex justify-center scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-700 scrollbar-track-transparent">
                            <style dangerouslySetInnerHTML={{ __html: PRINT_STYLES }} />
                            {renderSheetsContent()}
                        </div>
                    </DialogContent>
                </Dialog>
            )}
        </>
    );
}
