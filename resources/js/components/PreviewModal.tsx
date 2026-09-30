import React, {
    useRef,
    useMemo,
    useCallback,
    useState,
    useEffect,
} from "react";
import { useTranslation } from "react-i18next";
import { usePage } from "@inertiajs/react";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Printer, FileText, Eye } from "lucide-react";
import { getImagePath, getCompanySetting, formatCurrency, getCurrencySymbol } from "@/utils/helpers";
import { replaceProposalShortcodes } from "@/pages/SalesProposals/utils/proposalShortcodes";
import { cn } from "@/lib/utils";

// =============================================================================
// TYPES
// =============================================================================

export interface ProposalPreviewSection {
    id?: string;
    title: string;
    content: string;
    page_type?: string;
    background_image?: string;
    order?: number;
}

export interface ProposalItem {
    id?: string | number;
    name?: string;
    product_id?: string | number;
    product_name?: string;
    description?: string;
    product_description?: string;
    quantity?: number;
    unit?: string;
    unit_name?: string;
    unit_price?: number;
    total_amount?: number;
    discount_amount?: number;
    discount_percentage?: number;
    discount_type?: string;
    tax_amount?: number;
    section?: string;
    product?: {
        name?: string;
        description?: string;
        unit?: string;
        unit_name?: string;
        unit_relation?: {
            id?: number;
            unit_name?: string;
        };
    };
}

export interface ProposalTotals {
    subtotal: number;
    tax_amount?: number;
    taxAmount?: number;
    discount_amount?: number;
    discountAmount?: number;
    total_amount?: number;
    total?: number;
}

export interface ProposalFormData {
    id?: string | number;
    proposal_id?: string | number;
    proposal_number?: string;
    invoice_date?: string;
    due_date?: string;
    customer_id?: string | number;
    warehouse_id?: string | number;
    type?: string;
    payment_terms?: string;
    notes?: string;
    subject?: string;
    other_details?: string;
    items?: ProposalItem[];
    creator_name?: string;
}

export interface ProposalSettingsConfig {
    logo_image?: string;
    company_logo?: string;
    show_logo?: boolean | string | number;
    background_image?: string;
    template_color?: string;
    company_name?: string;
    company_email?: string;
    company_phone?: string;
    company_telephone?: string;
    company_address?: string;
    company_website?: string;
    [key: string]: any;
}

export interface PreviewModalProps {
    isOpen?: boolean;
    open?: boolean;
    onClose?: () => void;
    onOpenChange?: (open: boolean) => void;

    // Full Proposal Mode Props
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

    // Single Page / Default Page Mode Props
    title?: string;
    pageTitle?: string;
    content?: string;
    backgroundImage?: string;
    settings?: ProposalSettingsConfig | null;
    isDefaultPageSetup?: boolean;
    showPrintButton?: boolean;
    customHtml?: boolean;

    // Direct Page / Inline Render Mode
    inline?: boolean;
    autoPrint?: boolean;
    hideHeaderBar?: boolean;
}

// =============================================================================
// CONSTANTS & STYLES
// =============================================================================

export const DEFAULT_TEMPLATE_COLOR = "#E9591C";
export const FALLBACK_LOGO = "uploads/logo/logo_dark.png";
export const PROPOSAL_CONTENT_CLASSES = "html-preview-container";

export function isCustomHtmlContent(html?: string | null): boolean {
    if (!html) return false;
    return /<style|<link\s+rel|<!doctype|<html|<head|<svg|position:\s*absolute|297mm|210mm/i.test(
        html,
    );
}

/**
 * Helper to scope all CSS rules (including inside @media queries)
 */
function scopeCssRules(cssText: string, scopeSelector: string): string {
    // 1. First handle @media or @supports rules by recursively scoping their inner rules
    let processed = cssText.replace(/@(media|supports)\b[^{]*\{([\s\S]*?\})\s*\}/gi, (atMatch, atType, innerBlock) => {
        const header = atMatch.slice(0, atMatch.indexOf('{') + 1);
        const scopedInner = scopeCssRules(innerBlock, scopeSelector);
        return `${header}\n${scopedInner}\n}`;
    });

    // 2. Scope regular rules (e.g. .selector { ... })
    processed = processed.replace(/([^{}@]+)\{([^}]+)\}/g, (ruleMatch, selectorGroup, declarationBlock) => {
        const trimmedSelector = selectorGroup.trim();

        // Skip other at-rules like @keyframes, @font-face, @page
        if (trimmedSelector.startsWith("@")) {
            return ruleMatch;
        }

        const selectors = trimmedSelector.split(",");
        const scopedSelectors = selectors.map((sel: string) => {
            let s = sel.trim();
            if (!s) return "";

            // Replace global root selectors with scopeSelector
            if (/^(html|body|:root)$/i.test(s)) {
                return scopeSelector;
            }
            if (/^(html|body|:root)[\s>+~]/i.test(s)) {
                return s.replace(/^(html|body|:root)([\s>+~])/i, `${scopeSelector}$2`);
            }

            // If selector already starts with scopeSelector, keep it
            if (s.startsWith(scopeSelector)) {
                return s;
            }

            // Universal selector * -> .proposal-preview-sheet *
            return `${scopeSelector} ${s}`;
        });

        return `${scopedSelectors.filter(Boolean).join(", ")} {${declarationBlock}}`;
    });

    return processed;
}

/**
 * Scopes user-supplied CSS rules inside <style> tags to only apply within the document/preview page container,
 * strips external stylesheet links, strips dangerous script tags & event handlers,
 * and cleans outer <!DOCTYPE>, <html>, <head>, <body> tags to prevent DOM pollution.
 */
export function scopeAndSanitizeDocumentHtml(
    rawHtml?: string | null,
    scopeSelector = ".proposal-preview-sheet",
): string {
    if (!rawHtml) return "";

    let clean = rawHtml;

    // 1. Remove dangerous script tags & javascript: protocols
    clean = clean
        .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, "")
        .replace(/on\w+\s*=\s*(["'][^"']*["']|[^\s>]+)/gi, "");

    // 2. Remove external <link rel="stylesheet"> or <link> tags that pollute global page (e.g. Bootstrap CDN)
    clean = clean.replace(/<link\b[^>]*>/gi, "");

    // 3. Remove outer meta tags, title tags, DOCTYPE
    clean = clean
        .replace(/<!doctype[^>]*>/gi, "")
        .replace(/<\/?(html|head|meta|title)\b[^>]*>/gi, "");

    // 4. Transform <body>...</body> to clean <div>
    clean = clean.replace(/<body\b([^>]*)>/gi, "<div class=\"proposal-body-wrapper\" $1>");
    clean = clean.replace(/<\/body>/gi, "</div>");

    // 5. Scope all <style>...</style> blocks (including nested @media)
    clean = clean.replace(/<style\b([^>]*)>([\s\S]*?)<\/style>/gi, (match, attrs, cssText) => {
        const scopedCss = scopeCssRules(cssText, scopeSelector);
        return `<style${attrs}>${scopedCss}</style>`;
    });

    return clean;
}

// Backward-compatible alias
export const scopeAndSanitizeProposalHtml = scopeAndSanitizeDocumentHtml;

export const A4_PAGE_WIDTH_MM = 210;
export const A4_PAGE_HEIGHT_MM = 297;
export const A4_HEADER_RESERVED_MM = 32;
export const A4_FOOTER_RESERVED_MM = 30;
export const A4_HORIZONTAL_PADDING_MM = 15;

export const PRINT_STYLES = `
    @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; box-sizing: border-box !important; }

    .phone-tab{
        display:none;
    }
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

// =============================================================================
// DOM PAGINATOR
// =============================================================================

interface PaginationContext {
    maxHeight: number;
    sectionIndex?: string;
}

/**
 * Convert millimetres to browser CSS pixels using an actual DOM probe.
 * No DPI / screen-resolution assumptions.
 */
function mmToPx(mm: number): number {
    if (typeof document === "undefined") {
        return mm * 3.7795275591;
    }

    const probe = document.createElement("div");

    probe.style.cssText = `
        position: absolute;
        visibility: hidden;
        pointer-events: none;
        width: ${mm}mm;
        height: 0;
        padding: 0;
        margin: 0;
        border: 0;
        left: -10000px;
        top: -10000px;
    `;

    document.body.appendChild(probe);

    const px = probe.getBoundingClientRect().width;

    probe.remove();

    return px || mm * 3.7795275591;
}

/**
 * Measures the exact usable A4 content height.
 *
 * A4:
 * 297mm total
 * - 38.1mm top reserved area
 * - 25.4mm bottom reserved area
 *
 * = 233.5mm usable content area.
 *
 * The browser calculates the actual CSS pixel value.
 */
export function getA4ContentHeightPx(): number {
    if (typeof document === "undefined") {
        return (
            mmToPx(A4_PAGE_HEIGHT_MM) -
            mmToPx(A4_HEADER_RESERVED_MM) -
            mmToPx(A4_FOOTER_RESERVED_MM)
        );
    }

    const probe = document.createElement("div");

    probe.style.cssText = `
        position: absolute;
        visibility: hidden;
        pointer-events: none;
        left: -10000px;
        top: -10000px;

        width: ${A4_PAGE_WIDTH_MM}mm;
        height: ${A4_PAGE_HEIGHT_MM}mm;

        box-sizing: border-box;

        padding:
            ${A4_HEADER_RESERVED_MM}mm
            ${A4_HORIZONTAL_PADDING_MM}mm
            ${A4_FOOTER_RESERVED_MM}mm;

        display: flex;
        flex-direction: column;

        margin: 0;
        border: 0;
    `;

    const contentProbe = document.createElement("div");

    contentProbe.style.cssText = `
        width: 100%;
        flex: 1 1 auto;
        min-height: 0;
        box-sizing: border-box;
    `;

    probe.appendChild(contentProbe);
    document.body.appendChild(probe);

    const height = contentProbe.getBoundingClientRect().height;

    probe.remove();

    return (
        height ||
        mmToPx(A4_PAGE_HEIGHT_MM) -
        mmToPx(A4_HEADER_RESERVED_MM) -
        mmToPx(A4_FOOTER_RESERVED_MM)
    );
}

/**
 * Small tolerance only for browser fractional-pixel rounding.
 *
 * This is NOT a page-height limit.
 */
const PAGINATION_EPSILON_PX = 1;

/**
 * Gets vertical margin contribution.
 */
function getVerticalMargins(el: HTMLElement): number {
    if (typeof window === "undefined") {
        return 0;
    }

    const computedStyle = window.getComputedStyle(el);

    return (
        (parseFloat(computedStyle.marginTop) || 0) +
        (parseFloat(computedStyle.marginBottom) || 0)
    );
}

/**
 * Gets actual rendered height.
 */
function getRenderedHeight(el: HTMLElement): number {
    const rect = el.getBoundingClientRect();

    if (rect.height > 0) {
        return rect.height;
    }

    return el.offsetHeight || 0;
}

/**
 * Gets occupied height including margins.
 */
function getOccupiedHeight(el: HTMLElement): number {
    return getRenderedHeight(el) + getVerticalMargins(el);
}

/**
 * Adds section marker to generated fragments.
 */
function addSectionMarker(html: string, sectionIndex?: string): string {
    if (
        sectionIndex === undefined ||
        /data-proposal-section-index=/.test(html)
    ) {
        return html;
    }

    return html.replace(
        /^<(\w+)(\s|>)/,
        `<$1 data-proposal-section-index="${escapeHtmlAttribute(
            sectionIndex,
        )}"$2`,
    );
}

/**
 * Escape HTML attribute values.
 */
function escapeHtmlAttribute(value: string): string {
    return value
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}

/**
 * Checks explicit page-break instructions.
 */
function hasForcedPageBreak(el: HTMLElement): boolean {
    return (
        el.classList?.contains("page-break") ||
        el.style?.pageBreakAfter === "always" ||
        el.style?.pageBreakBefore === "always" ||
        el.style?.breakAfter === "page" ||
        el.style?.breakBefore === "page"
    );
}

/**
 * Only explicit full-page markers are treated as full-page units.
 *
 * IMPORTANT:
 * Do NOT use inherited sectionIndex here.
 */
function isFullPageElement(el: HTMLElement): boolean {
    return (
        el.hasAttribute("data-full-page") ||
        el.classList?.contains("cover-page-wrapper") ||
        el.style?.height?.includes("297mm") ||
        el.style?.minHeight?.includes("297mm")
    );
}

/**
 * Structural containers which can normally be traversed.
 */
function isTraversableContainer(el: HTMLElement): boolean {
    const tag = el.tagName.toLowerCase();

    return (
        (tag === "div" ||
            tag === "section" ||
            tag === "article" ||
            tag === "main") &&
        el.children.length > 0 &&
        !el.classList.contains("page-break")
    );
}

/**
 * Clone element shell while preserving all attributes.
 */
function cloneElementShell(el: HTMLElement, childrenHtml = ""): string {
    const clone = el.cloneNode(false) as HTMLElement;

    clone.innerHTML = childrenHtml;

    return clone.outerHTML;
}

/**
 * Measure generated HTML inside the actual
 * measurement container.
 *
 * This ensures:
 * - same width
 * - same fonts
 * - same table CSS
 * - same Tailwind-generated CSS
 * - same typography
 */
function measureHtmlHeight(measurementRoot: HTMLElement, html: string): number {
    const probe = document.createElement("div");

    probe.style.cssText = `
        position: absolute;
        visibility: hidden;
        pointer-events: none;

        left: 0;
        top: 0;

        width: 100%;

        margin: 0;
        padding: 0;
        border: 0;

        box-sizing: border-box;
    `;

    probe.innerHTML = html;

    measurementRoot.appendChild(probe);

    const rectHeight = probe.getBoundingClientRect().height;

    const scrollHeight = probe.scrollHeight;

    probe.remove();

    return Math.max(rectHeight, scrollHeight, 0);
}

/**
 * Builds a clone of an element containing selected child nodes.
 */
function buildChildFragment(parent: HTMLElement, children: Node[]): string {
    const wrapper = parent.cloneNode(false) as HTMLElement;

    children.forEach((child) => {
        wrapper.appendChild(child.cloneNode(true));
    });

    return wrapper.outerHTML;
}

/**
 * Finds a table which is a direct visual child of a wrapper.
 */
function findDirectTable(el: HTMLElement): HTMLTableElement | null {
    const directTable = Array.from(el.children).find(
        (child) => child.tagName.toLowerCase() === "table",
    );

    return directTable ? (directTable as HTMLTableElement) : null;
}

/**
 * Returns the content directly before a table
 * which visually behaves like its label.
 */
function findTableLabel(
    wrapper: HTMLElement,
    table: HTMLTableElement,
): HTMLElement | null {
    const children = Array.from(wrapper.children);

    const tableIndex = children.indexOf(table);

    if (tableIndex <= 0) {
        return null;
    }

    const previous = children[tableIndex - 1] as HTMLElement;

    if (!previous) {
        return null;
    }

    const previousTag = previous.tagName.toLowerCase();

    if (
        previousTag === "div" ||
        previousTag === "h1" ||
        previousTag === "h2" ||
        previousTag === "h3" ||
        previousTag === "h4" ||
        previousTag === "h5" ||
        previousTag === "h6" ||
        previousTag === "p"
    ) {
        return previous;
    }

    return null;
}

/**
 * Gets image/font resources inside the element.
 *
 * Pagination should ideally run after images have a
 * real browser layout.
 */
function waitForElementResources(root: HTMLElement): Promise<void> {
    const images = Array.from(root.querySelectorAll("img"));

    if (images.length === 0) {
        return Promise.resolve();
    }

    return Promise.all(
        images.map((img) => {
            if (img.complete) {
                return Promise.resolve();
            }

            return new Promise<void>((resolve) => {
                const done = () => {
                    img.removeEventListener("load", done);

                    img.removeEventListener("error", done);

                    resolve();
                };

                img.addEventListener("load", done);

                img.addEventListener("error", done);
            });
        }),
    ).then(() => undefined);
}

/**
 * Split oversized text-only elements by actual browser measurement.
 */
function splitSingleTextNodeElement(
    el: HTMLElement,
    measurementRoot: HTMLElement,
    availableHeight: number,
): string[] | null {
    const childNodes = Array.from(el.childNodes);

    if (childNodes.length !== 1) {
        return null;
    }

    const node = childNodes[0];

    if (node.nodeType !== Node.TEXT_NODE) {
        return null;
    }

    const text = node.textContent || "";

    if (!text.trim()) {
        return null;
    }

    const words = text.split(/(\s+)/);

    if (words.length <= 1) {
        return null;
    }

    const chunks: string[] = [];

    let current = "";

    const fits = (candidate: string): boolean => {
        const wrapper = el.cloneNode(false) as HTMLElement;

        wrapper.textContent = candidate;

        return (
            measureHtmlHeight(measurementRoot, wrapper.outerHTML) <=
            availableHeight + PAGINATION_EPSILON_PX
        );
    };

    for (const part of words) {
        const candidate = current + part;

        if (current.trim() && !fits(candidate)) {
            chunks.push(current.trim());

            current = part;
        } else {
            current = candidate;
        }
    }

    if (current.trim()) {
        chunks.push(current.trim());
    }

    if (chunks.length <= 1) {
        return null;
    }

    return chunks.map((textChunk) => {
        const wrapper = el.cloneNode(false) as HTMLElement;

        wrapper.textContent = textChunk;

        return wrapper.outerHTML;
    });
}

/**
 * Split an oversized element using its direct children.
 */
function splitOversizedElement(
    el: HTMLElement,
    measurementRoot: HTMLElement,
    availableHeight: number,
): string[] | null {
    const childNodes = Array.from(el.childNodes);

    if (childNodes.length <= 1) {
        return null;
    }

    const chunks: string[] = [];

    let currentNodes: Node[] = [];

    const flush = () => {
        if (currentNodes.length === 0) {
            return;
        }

        chunks.push(buildChildFragment(el, currentNodes));

        currentNodes = [];
    };

    for (const node of childNodes) {
        const candidateNodes = [...currentNodes, node];

        const candidateHtml = buildChildFragment(el, candidateNodes);

        const candidateHeight = measureHtmlHeight(
            measurementRoot,
            candidateHtml,
        );

        if (
            currentNodes.length > 0 &&
            candidateHeight > availableHeight + PAGINATION_EPSILON_PX
        ) {
            flush();

            currentNodes = [node];
        } else {
            currentNodes.push(node);
        }
    }

    flush();

    return chunks.length > 1 ? chunks : null;
}

/**
 * Handles an element which cannot fit onto a fresh page.
 */
function paginateOversizedElement(
    el: HTMLElement,
    measurementRoot: HTMLElement,
    availableHeight: number,
    sectionIndex?: string,
): string[] {
    const childChunks = splitOversizedElement(
        el,
        measurementRoot,
        availableHeight,
    );

    if (childChunks && childChunks.length > 1) {
        return childChunks.map((html) => addSectionMarker(html, sectionIndex));
    }

    const textChunks = splitSingleTextNodeElement(
        el,
        measurementRoot,
        availableHeight,
    );

    if (textChunks && textChunks.length > 1) {
        return textChunks.map((html) => addSectionMarker(html, sectionIndex));
    }

    /*
     * No safe semantic split exists.
     *
     * Keep original structure intact.
     */
    const fallback = el.cloneNode(true) as HTMLElement;

    fallback.classList.add("proposal-pagination-splittable");

    return [addSectionMarker(fallback.outerHTML, sectionIndex)];
}

/**
 * Builds a table chunk.
 *
 * Header repeats on every chunk.
 * Footer appears only on final chunk.
 */
function buildTableChunkHtml(
    table: HTMLTableElement,
    rows: string[],
    theadHtml: string,
    tfootHtml: string,
    includeFooter: boolean,
    sectionIndex?: string,
): string {
    const tableClasses = table.getAttribute("class") || "";

    const tableStyle = table.getAttribute("style") || "";

    const marker =
        sectionIndex !== undefined
            ? ` data-proposal-section-index="${escapeHtmlAttribute(
                sectionIndex,
            )}"`
            : "";

    return (
        `<table class="${escapeHtmlAttribute(
            tableClasses,
        )}"${marker} style="${escapeHtmlAttribute(tableStyle)}">` +
        theadHtml +
        `<tbody>${rows.join("")}</tbody>` +
        (includeFooter ? tfootHtml : "") +
        `</table>`
    );
}

/**
 * ============================================================================
 * TABLE PAGINATION
 * ============================================================================
 *
 * Important rules:
 *
 * 1. Label + first table chunk are measured together.
 * 2. Label can never be left alone at the bottom of a page.
 * 3. Header repeats on every table chunk.
 * 4. Footer appears only on the final chunk.
 * 5. Rows remain in exact original order.
 * 6. OTC/MRC table markup is not modified.
 */
function paginateTableWithLabel(
    wrapper: HTMLElement,
    table: HTMLTableElement,
    label: HTMLElement | null,
    measurementRoot: HTMLElement,
    effectiveMaxHeight: number,
    currentPageAccumulatedHeight: number,
    sectionIndex?: string,
): {
    pages: string[];
    remainingHeight: number;
} {
    void wrapper;

    const thead = table.querySelector("thead");

    const tfoot = table.querySelector("tfoot");

    const bodyRows = Array.from(
        table.querySelectorAll("tbody > tr"),
    ) as HTMLElement[];

    const theadHtml = thead ? thead.outerHTML : "";

    const tfootHtml = tfoot ? tfoot.outerHTML : "";

    const labelHtml = label
        ? addSectionMarker(label.outerHTML, sectionIndex)
        : "";

    const pages: string[] = [];

    /*
     * Empty table.
     */
    if (bodyRows.length === 0) {
        const tableHtml = buildTableChunkHtml(
            table,
            [],
            theadHtml,
            tfootHtml,
            true,
            sectionIndex,
        );

        const combinedHtml = labelHtml + tableHtml;

        const combinedHeight = measureHtmlHeight(measurementRoot, combinedHtml);

        if (
            currentPageAccumulatedHeight > 0 &&
            currentPageAccumulatedHeight + combinedHeight >
            effectiveMaxHeight + PAGINATION_EPSILON_PX
        ) {
            pages.push(combinedHtml);

            return {
                pages,
                remainingHeight: combinedHeight,
            };
        }

        return {
            pages: [combinedHtml],
            remainingHeight: currentPageAccumulatedHeight + combinedHeight,
        };
    }

    let rowIndex = 0;
    let firstChunk = true;
    let pageHeight = currentPageAccumulatedHeight;

    while (rowIndex < bodyRows.length) {
        const chunkRows: string[] = [];

        while (rowIndex < bodyRows.length) {
            const row = bodyRows[rowIndex];

            const candidateRows = [...chunkRows, row.outerHTML];

            const isLastRow = rowIndex === bodyRows.length - 1;

            const candidateTableHtml = buildTableChunkHtml(
                table,
                candidateRows,
                theadHtml,
                tfootHtml,
                isLastRow,
                sectionIndex,
            );

            const candidateHtml = firstChunk
                ? labelHtml + candidateTableHtml
                : candidateTableHtml;

            const candidateHeight = measureHtmlHeight(
                measurementRoot,
                candidateHtml,
            );

            /*
             * Normal fit.
             */
            if (
                pageHeight + candidateHeight <=
                effectiveMaxHeight + PAGINATION_EPSILON_PX
            ) {
                chunkRows.push(row.outerHTML);

                rowIndex++;

                continue;
            }

            /*
             * Existing chunk already has rows.
             * Finish it and move remaining rows
             * to the next page.
             */
            if (chunkRows.length > 0) {
                break;
            }

            /*
             * Nothing fits on current page.
             *
             * Move the COMPLETE label + first row/table
             * to a fresh page.
             */
            if (pageHeight > 0) {
                pages.push("__PAGINATION_PUSH_CURRENT_PAGE__");

                pageHeight = 0;

                continue;
            }

            /*
             * Fresh page, but row itself is larger
             * than the usable page.
             *
             * Preserve row structure.
             */
            const oversizedRow = row.cloneNode(true) as HTMLElement;

            oversizedRow.classList.add("proposal-oversized-row");

            chunkRows.push(oversizedRow.outerHTML);

            rowIndex++;

            break;
        }

        if (chunkRows.length === 0) {
            break;
        }

        const isFinalChunk = rowIndex >= bodyRows.length;

        const tableHtml = buildTableChunkHtml(
            table,
            chunkRows,
            theadHtml,
            tfootHtml,
            isFinalChunk,
            sectionIndex,
        );

        const chunkHtml = firstChunk ? labelHtml + tableHtml : tableHtml;

        const chunkHeight = measureHtmlHeight(measurementRoot, chunkHtml);

        /*
         * If first chunk doesn't fit current page,
         * label + table move together.
         */
        if (
            pageHeight > 0 &&
            pageHeight + chunkHeight >
            effectiveMaxHeight + PAGINATION_EPSILON_PX
        ) {
            pages.push("__PAGINATION_PUSH_CURRENT_PAGE__");

            pageHeight = 0;
        }

        pageHeight += chunkHeight;

        firstChunk = false;

        /*
         * Every non-final table chunk gets its own page.
         */
        if (!isFinalChunk) {
            pages.push(chunkHtml);

            pageHeight = 0;
        }
    }

    return {
        pages,
        remainingHeight: pageHeight,
    };
}

/**
 * ============================================================================
 * MAIN DOM PAGINATOR
 * ============================================================================
 */
export function paginateDomContainer(
    container: HTMLElement,
    maxPageHeight: number = getA4ContentHeightPx(),
): string[] {
    const styleTags = Array.from(container.querySelectorAll("style"))
        .map((s) => s.outerHTML)
        .join("\n");

    const pages: string[] = [];

    let currentPageHtml: string[] = [];

    let currentPageAccumulatedHeight = 0;

    const effectiveMaxHeight = Math.max(
        1,
        maxPageHeight - PAGINATION_EPSILON_PX,
    );

    const pushCurrentPage = () => {
        if (currentPageHtml.length === 0) {
            return;
        }

        pages.push(
            (styleTags ? styleTags + "\n" : "") + currentPageHtml.join(""),
        );

        currentPageHtml = [];

        currentPageAccumulatedHeight = 0;
    };

    const addHtmlToCurrentPage = (html: string, height: number) => {
        currentPageHtml.push(html);

        currentPageAccumulatedHeight += height;
    };

    /**
     * Process table and keep label + table together.
     */
    const processTable = (
        wrapper: HTMLElement,
        table: HTMLTableElement,
        label: HTMLElement | null,
        sectionIndex?: string,
    ) => {
        const thead = table.querySelector("thead");

        const tfoot = table.querySelector("tfoot");

        const bodyRows = Array.from(
            table.querySelectorAll("tbody > tr"),
        ) as HTMLElement[];

        const theadHtml = thead ? thead.outerHTML : "";

        const tfootHtml = tfoot ? tfoot.outerHTML : "";

        const labelHtml = label
            ? addSectionMarker(label.outerHTML, sectionIndex)
            : "";

        /*
         * ------------------------------------------------------------
         * EMPTY TABLE
         * ------------------------------------------------------------
         */
        if (bodyRows.length === 0) {
            const tableHtml = buildTableChunkHtml(
                table,
                [],
                theadHtml,
                tfootHtml,
                true,
                sectionIndex,
            );

            const combinedHtml = labelHtml + tableHtml;

            const combinedHeight = measureHtmlHeight(container, combinedHtml);

            if (
                currentPageAccumulatedHeight > 0 &&
                currentPageAccumulatedHeight + combinedHeight >
                effectiveMaxHeight + PAGINATION_EPSILON_PX
            ) {
                pushCurrentPage();
            }

            addHtmlToCurrentPage(combinedHtml, combinedHeight);

            return;
        }

        let rowIndex = 0;
        let firstChunk = true;

        while (rowIndex < bodyRows.length) {
            const chunkRows: string[] = [];

            /*
             * --------------------------------------------------------
             * Find largest fitting row group.
             * --------------------------------------------------------
             */
            while (rowIndex < bodyRows.length) {
                const row = bodyRows[rowIndex];

                const candidateRows = [...chunkRows, row.outerHTML];

                const isLastRow = rowIndex === bodyRows.length - 1;

                const candidateTableHtml = buildTableChunkHtml(
                    table,
                    candidateRows,
                    theadHtml,
                    tfootHtml,
                    isLastRow,
                    sectionIndex,
                );

                const candidateHtml = firstChunk
                    ? labelHtml + candidateTableHtml
                    : candidateTableHtml;

                const candidateHeight = measureHtmlHeight(
                    container,
                    candidateHtml,
                );

                /*
                 * Fits current page.
                 */
                if (
                    currentPageAccumulatedHeight + candidateHeight <=
                    effectiveMaxHeight + PAGINATION_EPSILON_PX
                ) {
                    chunkRows.push(row.outerHTML);

                    rowIndex++;

                    continue;
                }

                /*
                 * Existing chunk has rows.
                 */
                if (chunkRows.length > 0) {
                    break;
                }

                /*
                 * No row fits current page.
                 *
                 * Most important rule:
                 *
                 * label + table must move together.
                 */
                if (
                    currentPageHtml.length > 0 &&
                    currentPageAccumulatedHeight > 0
                ) {
                    pushCurrentPage();

                    continue;
                }

                /*
                 * Fresh page but the row itself is
                 * larger than usable page.
                 */
                const oversizedRow = row.cloneNode(true) as HTMLElement;

                oversizedRow.classList.add("proposal-oversized-row");

                chunkRows.push(oversizedRow.outerHTML);

                rowIndex++;

                break;
            }

            if (chunkRows.length === 0) {
                break;
            }

            const isFinalChunk = rowIndex >= bodyRows.length;

            const tableHtml = buildTableChunkHtml(
                table,
                chunkRows,
                theadHtml,
                tfootHtml,
                isFinalChunk,
                sectionIndex,
            );

            /*
             * Label ONLY appears on first table chunk.
             */
            const chunkHtml = firstChunk ? labelHtml + tableHtml : tableHtml;

            const chunkHeight = measureHtmlHeight(container, chunkHtml);

            /*
             * First chunk safety.
             *
             * If label + table doesn't fit,
             * move both to next page.
             */
            if (
                currentPageAccumulatedHeight > 0 &&
                currentPageAccumulatedHeight + chunkHeight >
                effectiveMaxHeight + PAGINATION_EPSILON_PX
            ) {
                pushCurrentPage();
            }

            addHtmlToCurrentPage(chunkHtml, chunkHeight);

            firstChunk = false;

            /*
             * More rows remain:
             * table continuation starts on new page.
             */
            if (!isFinalChunk) {
                pushCurrentPage();
            }
        }

        /*
         * Keep wrapper referenced so TypeScript does not
         * consider the argument accidental.
         */
        void wrapper;
    };

    /**
     * Main recursive processor.
     */
    const processElement = (
        el: HTMLElement,
        inheritedSectionIndex?: string,
    ) => {
        const ownSectionIndex = el.getAttribute("data-proposal-section-index");

        const sectionIndex = ownSectionIndex ?? inheritedSectionIndex;

        const tag = el.tagName.toLowerCase();

        if (tag === "style" || tag === "script") {
            return;
        }

        /*
         * ------------------------------------------------------------
         * Explicit page break
         * ------------------------------------------------------------
         */
        if (hasForcedPageBreak(el)) {
            pushCurrentPage();

            const directTable = findDirectTable(el);

            if (directTable) {
                const label = findTableLabel(el, directTable);

                processTable(el, directTable, label, sectionIndex);

                /*
                 * Preserve siblings after table.
                 */
                const children = Array.from(el.children) as HTMLElement[];

                const tableIndex = children.indexOf(directTable);

                for (let i = tableIndex + 1; i < children.length; i++) {
                    processElement(children[i], sectionIndex);
                }

                return;
            }

            const html = addSectionMarker(el.outerHTML, sectionIndex);

            const height = getOccupiedHeight(el);

            addHtmlToCurrentPage(html, height);

            pushCurrentPage();

            return;
        }

        /*
         * ------------------------------------------------------------
         * Explicit full-page unit
         * ------------------------------------------------------------
         */
        if (isFullPageElement(el)) {
            if (currentPageHtml.length > 0) {
                pushCurrentPage();
            }

            const html = addSectionMarker(el.outerHTML, sectionIndex);

            currentPageHtml.push(html);

            currentPageAccumulatedHeight = getOccupiedHeight(el);

            pushCurrentPage();

            return;
        }

        /*
         * ------------------------------------------------------------
         * DIRECT TABLE
         * ------------------------------------------------------------
         */
        if (tag === "table") {
            processTable(el, el as HTMLTableElement, null, sectionIndex);

            return;
        }

        /*
         * ------------------------------------------------------------
         * TABLE WRAPPER
         * ------------------------------------------------------------
         */
        const directTable = findDirectTable(el);

        if (directTable) {
            const children = Array.from(el.children) as HTMLElement[];

            const tableIndex = children.indexOf(directTable);

            const label = findTableLabel(el, directTable);

            /*
             * Preserve content BEFORE label/table.
             *
             * Normally OTC/MRC only contains:
             *
             * label
             * table
             *
             * but this protects future templates.
             */
            const labelIndex = label ? children.indexOf(label) : tableIndex;

            for (let i = 0; i < Math.max(0, labelIndex); i++) {
                processElement(children[i], sectionIndex);
            }

            processTable(el, directTable, label, sectionIndex);

            /*
             * Preserve siblings AFTER table.
             */
            for (let i = tableIndex + 1; i < children.length; i++) {
                processElement(children[i], sectionIndex);
            }

            return;
        }

        /*
         * ------------------------------------------------------------
         * LISTS
         * ------------------------------------------------------------
         */
        if ((tag === "ul" || tag === "ol") && el.children.length > 0) {
            const listClasses = el.getAttribute("class") || "";

            const listStyle = el.getAttribute("style") || "";

            const items = Array.from(el.children) as HTMLElement[];

            let currentItems: string[] = [];

            const flushList = () => {
                if (currentItems.length === 0) {
                    return;
                }

                const listHtml =
                    `<${tag} class="${escapeHtmlAttribute(
                        listClasses,
                    )}" style="${escapeHtmlAttribute(listStyle)}">` +
                    currentItems.join("") +
                    `</${tag}>`;

                const height = measureHtmlHeight(container, listHtml);

                addHtmlToCurrentPage(
                    addSectionMarker(listHtml, sectionIndex),
                    height,
                );

                currentItems = [];
            };

            for (let i = 0; i < items.length; i++) {
                const item = items[i];

                const candidateItems = [
                    ...currentItems,
                    addSectionMarker(item.outerHTML, sectionIndex),
                ];

                const candidateHtml = `<${tag}>${candidateItems.join(
                    "",
                )}</${tag}>`;

                const candidateHeight = measureHtmlHeight(
                    container,
                    candidateHtml,
                );

                /*
                 * Current item doesn't fit.
                 */
                if (
                    currentPageAccumulatedHeight + candidateHeight >
                    effectiveMaxHeight + PAGINATION_EPSILON_PX
                ) {
                    /*
                     * Existing list items:
                     * finish current list first.
                     */
                    if (currentItems.length > 0) {
                        flushList();

                        pushCurrentPage();
                    }

                    /*
                     * Re-measure item alone on fresh page.
                     */
                    const itemHeight = measureHtmlHeight(
                        container,
                        `<${tag}>${addSectionMarker(
                            item.outerHTML,
                            sectionIndex,
                        )}</${tag}>`,
                    );

                    /*
                     * Normal item fits fresh page.
                     */
                    if (
                        itemHeight <=
                        effectiveMaxHeight + PAGINATION_EPSILON_PX
                    ) {
                        currentItems.push(
                            addSectionMarker(item.outerHTML, sectionIndex),
                        );

                        continue;
                    }

                    /*
                     * Oversized LI.
                     */
                    const chunks = paginateOversizedElement(
                        item,
                        container,
                        effectiveMaxHeight,
                        sectionIndex,
                    );

                    chunks.forEach((chunk, chunkIndex) => {
                        if (
                            currentPageHtml.length > 0 &&
                            currentPageAccumulatedHeight > 0
                        ) {
                            pushCurrentPage();
                        }

                        const height = measureHtmlHeight(container, chunk);

                        addHtmlToCurrentPage(chunk, height);

                        if (chunkIndex < chunks.length - 1) {
                            pushCurrentPage();
                        }
                    });

                    continue;
                }

                currentItems.push(
                    addSectionMarker(item.outerHTML, sectionIndex),
                );
            }

            flushList();

            return;
        }

        /*
         * ------------------------------------------------------------
         * GENERIC STRUCTURAL CONTAINER
         * ------------------------------------------------------------
         */
        if (isTraversableContainer(el)) {
            const elHeight = getOccupiedHeight(el);

            /*
             * Keep complete block when possible.
             *
             * This is important for existing design.
             */
            if (
                currentPageAccumulatedHeight + elHeight <=
                effectiveMaxHeight + PAGINATION_EPSILON_PX
            ) {
                addHtmlToCurrentPage(
                    addSectionMarker(el.outerHTML, sectionIndex),
                    elHeight,
                );

                return;
            }

            /*
             * Block doesn't fit.
             *
             * Traverse children instead of forcing
             * the entire block onto the page.
             */
            const children = Array.from(el.children) as HTMLElement[];

            if (children.length > 0) {
                children.forEach((child) =>
                    processElement(child, sectionIndex),
                );

                return;
            }
        }

        /*
         * ------------------------------------------------------------
         * GENERIC LEAF
         * ------------------------------------------------------------
         */
        const elHeight = getOccupiedHeight(el);

        /*
         * Fits current page.
         */
        if (
            currentPageAccumulatedHeight + elHeight <=
            effectiveMaxHeight + PAGINATION_EPSILON_PX
        ) {
            addHtmlToCurrentPage(
                addSectionMarker(el.outerHTML, sectionIndex),
                elHeight,
            );

            return;
        }

        /*
         * Doesn't fit current page.
         */
        if (currentPageHtml.length > 0) {
            pushCurrentPage();
        }

        /*
         * Fits fresh page.
         */
        if (elHeight <= effectiveMaxHeight + PAGINATION_EPSILON_PX) {
            addHtmlToCurrentPage(
                addSectionMarker(el.outerHTML, sectionIndex),
                elHeight,
            );

            return;
        }

        /*
         * Oversized rich-text/media/block fallback.
         */
        const chunks = paginateOversizedElement(
            el,
            container,
            effectiveMaxHeight,
            sectionIndex,
        );

        chunks.forEach((chunk, chunkIndex) => {
            if (
                currentPageHtml.length > 0 &&
                currentPageAccumulatedHeight > 0
            ) {
                pushCurrentPage();
            }

            const height = measureHtmlHeight(container, chunk);

            addHtmlToCurrentPage(chunk, height);

            if (chunkIndex < chunks.length - 1) {
                pushCurrentPage();
            }
        });
    };

    /*
     * Process top-level content in exact DOM order.
     *
     * Existing Page Order remains unchanged.
     */
    Array.from(container.children).forEach((child) => {
        processElement(child as HTMLElement);
    });

    if (currentPageHtml.length > 0) {
        pushCurrentPage();
    }

    return pages.length > 0 ? pages : [container.innerHTML];
}

const formatAmountOnly = (val: number | string, pageProps?: any): string => {
    try {
        const num = Number(val) || 0;
        const decimalPlaces = parseInt(getCompanySetting('decimalFormat', pageProps) || '2');
        const decimalSeparator = getCompanySetting('decimalSeparator', pageProps) || '.';
        const thousandsSeparator = getCompanySetting('thousandsSeparator', pageProps) || ',';
        const floatNumber = getCompanySetting('floatNumber', pageProps) !== '0';

        let finalAmount = floatNumber ? num : Math.floor(num);
        const parts = Number(finalAmount).toFixed(decimalPlaces).split('.');

        if (thousandsSeparator !== 'none') {
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator);
        }

        return parts.join(decimalSeparator);
    } catch {
        return Number(val || 0).toFixed(2);
    }
};

const getAmountFontSize = (amountStr: string, defaultSize = 10): string => {
    const cleanStr = amountStr.replace(/<[^>]*>/g, "");
    const len = cleanStr.length;
    if (len > 18) {
        return "7.5px";
    }
    if (len > 15) {
        return "8.5px";
    }
    if (len > 12) {
        return "9.5px";
    }
    return `${defaultSize}px`;
};

// =============================================================================
// REUSABLE PAGE COMPONENTS
// =============================================================================

export interface ProposalPreviewSheetProps {
    children?: React.ReactNode;
    content?: string;
    backgroundImage?: string;
    defaultBg?: string;
    templateColor?: string;
    headerLogo?: string;
    headerLogoAlign?: "left" | "center" | "right" | string;
    pageKey?: string;
    className?: string;
    customHtml?: boolean;
}

export const ProposalPreviewSheet = React.memo<ProposalPreviewSheetProps>(
    ({
        children,
        content,
        backgroundImage,
        defaultBg,
        templateColor = DEFAULT_TEMPLATE_COLOR,
        headerLogo,
        headerLogoAlign = "right",
        pageKey,
        className = "",
        customHtml = false,
    }) => {
        const rawBg =
            backgroundImage && String(backgroundImage).trim() !== ""
                ? backgroundImage
                : defaultBg;
        const bgUrl = rawBg ? getImagePath(rawBg) : "";
        const logoUrl = headerLogo ? getImagePath(headerLogo) : "";

        const getLogoContainerStyle = (): React.CSSProperties => {
            const align = headerLogoAlign || "right";
            if (align === "left") {
                return {
                    top: "8mm",
                    left: "15mm",
                    right: "auto",
                    justifyContent: "flex-start",
                    maxHeight: "20mm",
                    maxWidth: "60mm",
                };
            }
            if (align === "center" || align === "middle") {
                return {
                    top: "8mm",
                    left: "50%",
                    right: "auto",
                    transform: "translateX(-50%)",
                    justifyContent: "center",
                    maxHeight: "20mm",
                    maxWidth: "60mm",
                };
            }
            return {
                top: "8mm",
                right: "15mm",
                left: "auto",
                justifyContent: "flex-end",
                maxHeight: "20mm",
                maxWidth: "60mm",
            };
        };

        return (
            <div
                key={pageKey}
                style={
                    {
                        width: "210mm",
                        ...(customHtml
                            ? {
                                minHeight: "297mm",
                                boxSizing: "border-box",
                            }
                            : {
                                height: "297mm",
                                minHeight: "297mm",
                                maxHeight: "297mm",
                                boxSizing: "border-box",
                                overflow: "hidden",
                            }),
                        pageBreakAfter: "always",
                        breakAfter: "page",
                        pageBreakInside: "avoid",
                        breakInside: "avoid-page",
                        fontFamily: '"Open Sans", sans-serif',
                        "--template-color": templateColor,
                    } as unknown as React.CSSProperties
                }
                className={cn(
                    "proposal-preview-sheet proposal-cover__sheet bg-white text-slate-900 w-[210mm] max-w-full shadow-2xl rounded-sm text-sm border border-slate-300 dark:border-slate-800 shrink-0 relative",
                    !customHtml && "h-[297mm] overflow-hidden",
                    className,
                )}
            >
                {bgUrl && (
                    <div className="absolute inset-0 w-full h-full z-0 pointer-events-none overflow-hidden">
                        <img
                            src={bgUrl}
                            alt="Page Background"
                            className="w-full h-full object-fill block"
                            onError={(e) => {
                                const target = e.currentTarget;
                                const raw = String(backgroundImage || defaultBg || '').split('/').pop();
                                if (raw && !target.src.includes('/storage/media/')) {
                                    target.src = `/storage/media/${raw}`;
                                }
                            }}
                        />
                    </div>
                )}


                {logoUrl && (
                    <div
                        className="absolute z-20 pointer-events-none flex items-center"
                        style={getLogoContainerStyle()}
                    >
                        <img
                            src={logoUrl}
                            alt="Header Logo"
                            className="max-h-[16mm] max-w-[55mm] object-contain"
                        />
                    </div>
                )}

                <div
                    className={cn(
                        !customHtml && "proposal-page__body",
                        customHtml && "w-full h-full p-0 m-0",
                    )}
                    style={{
                        position: "relative",
                        zIndex: 1,
                        ...(customHtml
                            ? {
                                padding: 0,
                                margin: 0,
                                width: "100%",
                                minHeight: "297mm",
                                boxSizing: "border-box",
                                display: "block",
                            }
                            : {
                                padding: "32mm 15mm 20mm",
                                height: "calc(297mm - 52mm)",
                                minHeight: "calc(297mm - 52mm)",
                                maxHeight: "calc(297mm - 52mm)",
                                boxSizing: "border-box",
                                display: "flex",
                                flexDirection: "column",
                                justifyContent: "flex-start",
                            }),
                    }}
                >
                    {children ? (
                        children
                    ) : content ? (
                        <div
                            className={cn(
                                !customHtml &&
                                cn(
                                    "html-preview-container flex-1 flex flex-col",
                                    PROPOSAL_CONTENT_CLASSES,
                                ),
                                customHtml && "w-full h-full",
                            )}
                            style={
                                !customHtml
                                    ? {
                                        display: "flex",
                                        flexDirection: "column",
                                        flex: 1,
                                        width: "100%",
                                    }
                                    : {
                                        width: "100%",
                                        height: "100%",
                                    }
                            }
                            dangerouslySetInnerHTML={{
                                __html: scopeAndSanitizeDocumentHtml(content),
                            }}
                        />
                    ) : null}
                </div>
            </div>
        );
    },
);
ProposalPreviewSheet.displayName = "ProposalPreviewSheet";

// =============================================================================
// MAIN UNIFIED PREVIEW MODAL COMPONENT
// =============================================================================

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
    isDefaultPageSetup,
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
        const subject = formData?.subject || title || pageTitle || '';
        const customerName = (formData as any)?.customer_name || (customers && customers.length > 0 ? customers[0]?.name : '') || '';
        const parts = [subject, customerName].filter(Boolean);
        const printTitle = parts.length > 0 ? parts.join('_') : (formData?.proposal_number ? String(formData.proposal_number) : (title || ''));

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

    const previewContainerRef = useRef<HTMLDivElement>(null);
    const measureContainerRef = useRef<HTMLDivElement>(null);

    const activeSettings = useMemo(() => {
        return (
            settings ||
            proposalSetting ||
            (pageProps as any)?.proposalSetting ||
            (pageProps as any)?.quotationSetting ||
            {}
        );
    }, [settings, proposalSetting, pageProps]);

    const templateColor =
        activeSettings?.template_color || DEFAULT_TEMPLATE_COLOR;
    const isLogoEnabled =
        activeSettings?.show_logo !== undefined
            ? activeSettings.show_logo === "1" ||
            activeSettings.show_logo === true ||
            activeSettings.show_logo === 1 ||
            activeSettings.show_logo === "true"
            : true;
    const rawLogo =
        activeSettings?.logo_image || activeSettings?.company_logo || "";
    const headerLogo = isLogoEnabled && rawLogo ? rawLogo : "";
    const headerLogoAlign = activeSettings?.header_logo_align || "right";
    const defaultBgImage = activeSettings?.background_image || "";
    const isSinglePageMode = Boolean(
        !formData &&
        (content !== undefined ||
            title !== undefined ||
            pageTitle !== undefined),
    );

    const isCustomHtml = Boolean(
        customHtml ||
        (isSinglePageMode &&
            content &&
            isCustomHtmlContent(content)),
    );

    const singleProcessedContent = useMemo(() => {
        if (!isSinglePageMode) return "";
        const rawContent = (content || "").trim();
        if (!rawContent && (backgroundImage || defaultBgImage)) {
            return "&nbsp;";
        }
        if (!rawContent) return "";
        const processed = replaceProposalShortcodes(content, {
            settings: activeSettings,
            pageProps,
            isDefaultPageSetup: isDefaultPageSetup ?? true,
        });
        return scopeAndSanitizeDocumentHtml(processed);
    }, [isSinglePageMode, content, backgroundImage, defaultBgImage, activeSettings, isDefaultPageSetup]);

    const [paginatedSinglePages, setPaginatedSinglePages] = useState<string[]>(
        [],
    );
    const [paginatedFullProposalPages, setPaginatedFullProposalPages] =
        useState<string[]>([]);
    const [
        paginatedFullProposalBackgrounds,
        setPaginatedFullProposalBackgrounds,
    ] = useState<string[]>([]);
    const [
        paginatedFullProposalCustomHtml,
        setPaginatedFullProposalCustomHtml,
    ] = useState<boolean[]>([]);

    useEffect(() => {
        if (!isSinglePageMode) return;
        if (!singleProcessedContent) {
            setPaginatedSinglePages([]);
            return;
        }

        const runPagination = () => {
            if (isCustomHtml) {
                const hasExplicitBreak =
                    /class=["'][^"']*page-break[^"']*["']|style=["'][^"']*(?:page-break|break-after|break-before)[^"']*["']/i.test(
                        singleProcessedContent,
                    );
                if (hasExplicitBreak && measureContainerRef.current) {
                    const chunks = paginateDomContainer(
                        measureContainerRef.current,
                        getA4ContentHeightPx(),
                    );
                    setPaginatedSinglePages(chunks);
                } else {
                    setPaginatedSinglePages([singleProcessedContent]);
                }
                return;
            }

            if (measureContainerRef.current) {
                const chunks = paginateDomContainer(
                    measureContainerRef.current,
                    getA4ContentHeightPx(),
                );
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
                const found = availableProducts.find(
                    (p) => String(p.id) === String(item.product_id),
                );
                if (found?.name) return found.name;
            }
            return (
                item.product_description ||
                item.description ||
                t("Item / Service")
            );
        },
        [availableProducts, t],
    );

    const getItemDesc = useCallback(
        (item: ProposalItem): string => {
            if (item.description) return item.description;
            if (item.product_description) return item.product_description;
            if (item.product?.description) return item.product.description;
            if (item.product_id && availableProducts.length > 0) {
                const found = availableProducts.find(
                    (p) => String(p.id) === String(item.product_id),
                );
                if (found?.description) return found.description;
            }
            return "";
        },
        [availableProducts],
    );

    const getItemUnit = useCallback(
        (item: ProposalItem): string => {
            if (item.unit_name) return item.unit_name;
            if (item.unit && isNaN(Number(item.unit))) return item.unit;
            if (item.product?.unit_relation?.unit_name)
                return item.product.unit_relation.unit_name;
            if (item.product?.unit_name) return item.product.unit_name;
            if (item.product?.unit && isNaN(Number(item.product.unit)))
                return item.product.unit;
            if (item.product_id && availableProducts.length > 0) {
                const found: any = availableProducts.find(
                    (p) => String(p.id) === String(item.product_id),
                );
                if (found?.unit_name) return found.unit_name;
                if (found?.unit && isNaN(Number(found.unit))) return found.unit;
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
            customers.find(
                (c) => String(c.id) === String(formData?.customer_id),
            ) ||
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
    }, [
        customers,
        formData?.customer_id,
        (formData as any)?.customer_mode,
        (formData as any)?.customer_type,
        (formData as any)?.customer_name,
        (formData as any)?.customer_email,
        (formData as any)?.customer_phone,
        (formData as any)?.customer_address,
    ]);

    const fullProposalHtml = useMemo(() => {
        if (isSinglePageMode || !formData) return "";

        const pageProps = (typeof window !== 'undefined' ? (window as any)?.__INITIAL_PAGE__?.props : null) || {};
        const currencyCode = getCompanySetting('defaultCurrency', pageProps) || 'BDT';

        const items = formData.items || [];
        const otcItems = items.filter(
            (i) =>
                (i.section === "otc" ||
                    i.section === "general" ||
                    !i.section) &&
                (Number(i.product_id) > 0 ||
                    Number(i.unit_price) > 0 ||
                    Boolean(i.product_description) ||
                    Boolean(i.description)),
        );
        const mrcItems = items.filter(
            (i) =>
                i.section === "mrc" &&
                (Number(i.product_id) > 0 ||
                    Number(i.unit_price) > 0 ||
                    Boolean(i.product_description) ||
                    Boolean(i.description)),
        );

        const secSubtotalOtc = otcItems.reduce(
            (sum, item) =>
                sum + Number(item.quantity ?? 1) * Number(item.unit_price || 0),
            0,
        );
        const otcItemDiscSum = otcItems.reduce(
            (sum, item) => sum + Number(item.discount_amount || 0),
            0,
        );
        let secDiscountOtc = otcItemDiscSum;
        if (otcItemDiscSum === 0 && Number((formData as any).otc_discount_value) > 0) {
            const discVal = Number((formData as any).otc_discount_value) || 0;
            if ((formData as any).otc_discount_type === "percentage") {
                secDiscountOtc =
                    (secSubtotalOtc * Math.min(Math.max(discVal, 0), 100)) /
                    100;
            } else {
                secDiscountOtc = Math.min(Math.max(discVal, 0), secSubtotalOtc);
            }
        }
        const secTaxOtc = otcItems.reduce(
            (sum, item) => sum + Number(item.tax_amount || 0),
            0,
        );
        const secTotalOtc = Math.max(
            0,
            secSubtotalOtc - secDiscountOtc + secTaxOtc,
        );

        const secSubtotalMrc = mrcItems.reduce(
            (sum, item) =>
                sum + Number(item.quantity ?? 1) * Number(item.unit_price || 0),
            0,
        );
        const mrcItemDiscSum = mrcItems.reduce(
            (sum, item) => sum + Number(item.discount_amount || 0),
            0,
        );
        let secDiscountMrc = mrcItemDiscSum;
        if (mrcItemDiscSum === 0 && Number((formData as any).mrc_discount_value) > 0) {
            const discVal = Number((formData as any).mrc_discount_value) || 0;
            if ((formData as any).mrc_discount_type === "percentage") {
                secDiscountMrc =
                    (secSubtotalMrc * Math.min(Math.max(discVal, 0), 100)) /
                    100;
            } else {
                secDiscountMrc = Math.min(Math.max(discVal, 0), secSubtotalMrc);
            }
        }
        const secTaxMrc = mrcItems.reduce(
            (sum, item) => sum + Number(item.tax_amount || 0),
            0,
        );
        const secTotalMrc = Math.max(
            0,
            secSubtotalMrc - secDiscountMrc + secTaxMrc,
        );

        const htmlParts: string[] = [];

        sections.forEach((sec, sectionIndex) => {
            const rawContent = (sec.content || "").trim();
            const pageType = (sec.page_type || "").toLowerCase();
            const isOtc =
                pageType === "otc" ||
                rawContent === "[OTC_CHARGES_TABLE]" ||
                (sec.title &&
                    sec.title.toLowerCase().includes("one-time charges"));
            const isMrc =
                pageType === "mrc" ||
                rawContent === "[MRC_CHARGES_TABLE]" ||
                (sec.title &&
                    sec.title
                        .toLowerCase()
                        .includes("monthly recurring charges"));
            const isOther =
                pageType === "other-details" ||
                rawContent === "[OTHER_DETAILS_CONTENT]" ||
                (sec.title &&
                    sec.title.toLowerCase().includes("other details"));

            if (isOtc) {
                if (otcItems.length === 0) return;
                const title = sec.title || t("ONE-TIME CHARGES (OTC)");
                let rowsHtml = "";
                otcItems.forEach((item, idx) => {
                    const qty = Number(item.quantity ?? 1);
                    const unit = getItemUnit(item);
                    const price = Number(item.unit_price) || 0;
                    const lineTotal =
                        item.total_amount !== undefined
                            ? Number(item.total_amount)
                            : qty * price;
                    const desc = getItemDesc(item);
                    const taxAmt = Number(item.tax_amount) || 0;
                    const discPct = Number(item.discount_percentage) || 0;
                    const discAmt = Number(item.discount_amount) || 0;
                    const effectiveDiscType = item.discount_type || 'percentage';
                    let discCellHtml = "-";
                    if (effectiveDiscType === 'percentage' && discPct > 0) {
                        discCellHtml = `<div>${discPct}%</div>${discAmt > 0 ? `<div style="font-size: 9px; color: #64748b;">(${formatCurrency(discAmt, pageProps)})</div>` : ''}`;
                    } else if (effectiveDiscType === 'fixed' && discAmt > 0) {
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

                htmlParts.push(`
                    <div class="proposal-section-block otc-charges-block" data-proposal-section-index="${sectionIndex}" style="margin-top: 1.5rem; margin-bottom: 1.25rem;">
                        <div class="font-bold mb-2 text-[#293240] text-sm">${title}</div>
                        <table class="charges-table w-full text-xs mb-2 border-collapse border border-slate-300" style="font-size: 11px; width: 100%; table-layout: fixed;">
                            <thead>
                                <tr class="text-center font-semibold" style="background-color: ${templateColor}; color: #ffffff;">
                                    <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 5%; white-space: nowrap; padding: 7.5px 4px !important;">${t("S/N")}</th>
                                    <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 15%; padding: 7.5px 8px !important;">${t("Item / Service")}</th>
                                    <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 27%; padding: 7.5px 8px !important;">${t("Description")}</th>
                                    <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 6%; white-space: nowrap; padding: 7.5px 4px !important;">${t("Qty.")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Price")} (${currencyCode})</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 10%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Discount")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 10%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Tax / VAT")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 13%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Total")} (${currencyCode})</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rowsHtml}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Subtotal")}:</td>
                                    <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(formatAmountOnly(secSubtotalOtc, pageProps), 10)}; padding: 6px 8px !important;">${formatAmountOnly(secSubtotalOtc, pageProps)}</td>
                                </tr>
                                ${secDiscountOtc > 0
                        ? `
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Discount")}:</td>
                                    <td class="text-right text-rose-600 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(`(-) ${formatAmountOnly(secDiscountOtc, pageProps)}`, 10)}; padding: 6px 8px !important;">(-) ${formatAmountOnly(secDiscountOtc, pageProps)}</td>
                                </tr>`
                        : ""
                    }
                                ${secTaxOtc > 0
                        ? `
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Tax / VAT")}:</td>
                                    <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(`(+) ${formatAmountOnly(secTaxOtc, pageProps)}`, 10)}; padding: 6px 8px !important;">(+) ${formatAmountOnly(secTaxOtc, pageProps)}</td>
                                </tr>`
                        : ""
                    }
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-bold text-slate-900 border border-slate-200 text-right" style="font-size: 10px; padding: 7px 8px !important;">${t("Total")}:</td>
                                    <td class="text-right font-bold text-slate-900 border border-slate-200" style="font-size: ${getAmountFontSize(`${formatAmountOnly(secTotalOtc, pageProps)} ${currencyCode}`, 10)}; padding: 7px 8px !important;">${formatAmountOnly(secTotalOtc, pageProps)} ${currencyCode}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `);
                return;
            }

            if (isMrc) {
                if (mrcItems.length === 0) return;
                const title = sec.title || t("MONTHLY RECURRING CHARGES (MRC)");
                let rowsHtml = "";
                mrcItems.forEach((item, idx) => {
                    const qty = Number(item.quantity ?? 1);
                    const unit = getItemUnit(item);
                    const price = Number(item.unit_price) || 0;
                    const lineTotal =
                        item.total_amount !== undefined
                            ? Number(item.total_amount)
                            : qty * price;
                    const desc = getItemDesc(item);
                    const taxAmt = Number(item.tax_amount) || 0;
                    const discPct = Number(item.discount_percentage) || 0;
                    const discAmt = Number(item.discount_amount) || 0;
                    const effectiveDiscType = item.discount_type || 'percentage';
                    let discCellHtml = "-";
                    if (effectiveDiscType === 'percentage' && discPct > 0) {
                        discCellHtml = `<div>${discPct}%</div>${discAmt > 0 ? `<div style="font-size: 9px; color: #64748b;">(${formatCurrency(discAmt, pageProps)})</div>` : ''}`;
                    } else if (effectiveDiscType === 'fixed' && discAmt > 0) {
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
                                <div class="leading-normal break-words [&_ul]:list-disc [&_ul]:pl-4 [&_ul]:my-0.5 [&_ol]:list-decimal [&_ol]:pl-4 [&_ol]:my-0.5 [&_li]:my-0.5 [&_li]:list-item [&_li_p]:inline [&_li_p]:m-0 [&_p]:my-0 [&_p:first-child]:mt-0 [&_p:last-child]:mb-0">
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

                const titleMarginTop = sectionIndex === 0 ? "" : "margin-top: 2rem;";
                htmlParts.push(`
                    <div class="proposal-section-block mrc-charges-block" data-proposal-section-index="${sectionIndex}" style="margin-bottom: 1.25rem;">
                        <div class="font-bold mb-2 text-[#293240] text-sm" style="${titleMarginTop}">${title}</div>
                        <table class="charges-table w-full text-xs mb-2 border-collapse border border-slate-300" style="font-size: 11px; width: 100%; table-layout: fixed;">
                            <thead>
                                <tr class="text-center font-semibold" style="background-color: ${templateColor}; color: #ffffff;">
                                    <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 5%; white-space: nowrap; padding: 7.5px 4px !important;">${t("S/N")}</th>
                                    <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 15%; padding: 7.5px 8px !important;">${t("Item / Service")}</th>
                                    <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 25%; padding: 7.5px 8px !important;">${t("Description")}</th>
                                    <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 6%; white-space: nowrap; padding: 7.5px 4px !important;">${t("Qty.")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Price")} (${currencyCode})</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Discount")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Tax / VAT")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 13%; white-space: nowrap; padding: 7.5px 8px !important;">${t("Total")} (${currencyCode})</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rowsHtml}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Subtotal")}:</td>
                                    <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(formatAmountOnly(secSubtotalMrc, pageProps), 10)}; padding: 6px 8px !important;">${formatAmountOnly(secSubtotalMrc, pageProps)}</td>
                                </tr>
                                ${secDiscountMrc > 0
                        ? `
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Discount")}:</td>
                                    <td class="text-right text-rose-600 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(`(-) ${formatAmountOnly(secDiscountMrc, pageProps)}`, 10)}; padding: 6px 8px !important;">(-) ${formatAmountOnly(secDiscountMrc, pageProps)}</td>
                                </tr>`
                        : ""
                    }
                                ${secTaxMrc > 0
                        ? `
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${t("Tax / VAT")}:</td>
                                    <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${getAmountFontSize(`(+) ${formatAmountOnly(secTaxMrc, pageProps)}`, 10)}; padding: 6px 8px !important;">(+) ${formatAmountOnly(secTaxMrc, pageProps)}</td>
                                </tr>`
                        : ""
                    }
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-bold text-slate-900 border border-slate-200 text-right" style="font-size: 10px; padding: 7px 8px !important;">${t("Total")}:</td>
                                    <td class="text-right font-bold text-slate-900 border border-slate-200" style="font-size: ${getAmountFontSize(`${formatAmountOnly(secTotalMrc, pageProps)} ${currencyCode}`, 10)}; padding: 7px 8px !important;">${formatAmountOnly(secTotalMrc, pageProps)} ${currencyCode}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `);
                return;
            }

            if (isOther) {
                const detailsText =
                    formData.other_details || other_details || "";
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
            isDefaultPageSetup: false,
        });
        return scopeAndSanitizeDocumentHtml(processed);
    }, [
        isSinglePageMode,
        formData,
        sections,
        customers,
        getItemName,
        getItemDesc,
        getItemUnit,
        t,
        templateColor,
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
                const chunks = paginateDomContainer(
                    measureContainerRef.current,
                    getA4ContentHeightPx(),
                );
                setPaginatedFullProposalPages((prev) =>
                    prev.length === chunks.length && prev.every((val, idx) => val === chunks[idx])
                        ? prev
                        : chunks,
                );

                const bgs: string[] = [];
                const customHtmlFlags: boolean[] = [];
                chunks.forEach((chunkHtml) => {
                    const match = chunkHtml.match(
                        /data-proposal-section-index=["'](\d+)["']/,
                    );
                    if (match && match[1] !== undefined) {
                        const secIdx = parseInt(match[1], 10);
                        const matchedSec = sections[secIdx];
                        if (
                            matchedSec?.background_image &&
                            matchedSec.background_image.trim() !== ""
                        ) {
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
                    prev.length === bgs.length && prev.every((val, idx) => val === bgs[idx])
                        ? prev
                        : bgs,
                );
                setPaginatedFullProposalCustomHtml((prev) =>
                    prev.length === customHtmlFlags.length && prev.every((val, idx) => val === customHtmlFlags[idx])
                        ? prev
                        : customHtmlFlags,
                );
            } else {
                setPaginatedFullProposalPages((prev) =>
                    prev.length === 1 && prev[0] === fullProposalHtml
                        ? prev
                        : [fullProposalHtml],
                );
                setPaginatedFullProposalBackgrounds((prev) =>
                    prev.length === 1 && prev[0] === defaultBgImage
                        ? prev
                        : [defaultBgImage],
                );
                setPaginatedFullProposalCustomHtml((prev) =>
                    prev.length === 1 && prev[0] === false
                        ? prev
                        : [false],
                );
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
    }, [
        isSinglePageMode,
        fullProposalHtml,
        sections,
        defaultBgImage,
        isModalOpen,
        inline,
    ]);

    const handlePrint = useCallback(() => {
        const subject = formData?.subject || title || pageTitle || '';
        const customerName = (customer as any)?.name || (formData as any)?.customer_name || '';
        const parts = [subject, customerName].filter(Boolean);
        const printTitle = parts.length > 0 ? parts.join('_') : (formData?.proposal_number ? String(formData.proposal_number) : document.title);

        const originalTitle = document.title;
        if (printTitle) {
            document.title = printTitle;
        }
        window.print();
        setTimeout(() => {
            document.title = originalTitle;
        }, 1000);
    }, [formData, customer, title, pageTitle]);

    const modalTitleText =
        title || pageTitle || formData?.subject || t("Preview");

    const renderSheetsContent = () => (
        <div className="flex flex-col gap-6 items-center w-full print:gap-0 print:block">
            {isSinglePageMode ? (
                paginatedSinglePages.length > 0 ? (
                    paginatedSinglePages.map((pageHtml, pIdx) => (
                        <ProposalPreviewSheet
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
                    <ProposalPreviewSheet
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
                    <ProposalPreviewSheet
                        key={`proposal-page-${pIdx}`}
                        pageKey={`proposal-page-${pIdx}`}
                        backgroundImage={
                            paginatedFullProposalBackgrounds[pIdx] ||
                            defaultBgImage
                        }
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
                className={cn(
                    "html-preview-container",
                    PROPOSAL_CONTENT_CLASSES,
                )}
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
                    __html: isSinglePageMode
                        ? singleProcessedContent
                        : fullProposalHtml,
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
                                        {formData?.proposal_number ||
                                            modalTitleText}
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
                        <style
                            dangerouslySetInnerHTML={{ __html: PRINT_STYLES }}
                        />
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
                            <style
                                dangerouslySetInnerHTML={{
                                    __html: PRINT_STYLES,
                                }}
                            />
                            {renderSheetsContent()}
                        </div>
                    </DialogContent>
                </Dialog>
            )}
        </>
    );
}
