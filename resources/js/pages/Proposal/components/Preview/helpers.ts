import DOMPurify from "dompurify";
import { getCompanySetting } from "@/utils/helpers";

export const DEFAULT_TEMPLATE_COLOR = "#E9591C";
export const FALLBACK_LOGO = "uploads/logo/logo_dark.png";
export const PROPOSAL_CONTENT_CLASSES = "html-preview-container";

export const A4_PAGE_WIDTH_MM = 210;
export const A4_PAGE_HEIGHT_MM = 297;
export const A4_HEADER_RESERVED_MM = 32;
export const A4_FOOTER_RESERVED_MM = 30;
export const A4_HORIZONTAL_PADDING_MM = 15;

export function isCustomHtmlContent(html?: string | null): boolean {
    if (!html) return false;
    return /<style|<link\s+rel|<!doctype|<html|<head|<svg|position:\s*absolute|297mm|210mm/i.test(html);
}

export function escapeHtmlAttribute(value: string): string {
    return value
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}

export function scopeCssRules(cssText: string, scopeSelector: string): string {
    if (!cssText) return "";

    let processed = cssText.replace(/@(media|supports|container)\b[^{]*\{([\s\S]*?\})\s*\}/gi, (atMatch) => {
        const firstBrace = atMatch.indexOf('{');
        const header = atMatch.slice(0, firstBrace + 1);
        const innerBlock = atMatch.slice(firstBrace + 1, -1);
        return `${header}\n${scopeCssRules(innerBlock, scopeSelector)}\n}`;
    });

    processed = processed.replace(/@(-webkit-|-moz-|-o-|-ms-)?(keyframes|page|font-face|import|charset)\b[\s\S]*?\{[\s\S]*?\}\s*\}?/gi, (match) => {
        return match;
    });

    processed = processed.replace(/([^{}@]+)\{([^}]+)\}/g, (ruleMatch, selectorGroup, declarationBlock) => {
        const trimmedSelector = selectorGroup.trim();

        if (trimmedSelector.startsWith("@") || trimmedSelector === "from" || trimmedSelector === "to" || /^\d+%/.test(trimmedSelector)) {
            return ruleMatch;
        }

        const selectors = trimmedSelector.split(/,(?![^(]*\))/);
        const scopedSelectors = selectors.map((sel: string) => {
            let s = sel.trim();
            if (!s) return "";

            if (/^(html|body|:root)$/i.test(s)) {
                return scopeSelector;
            }
            if (/^(html|body|:root)[\s>+~]/i.test(s)) {
                return s.replace(/^(html|body|:root)([\s>+~])/i, `${scopeSelector}$2`);
            }

            if (s.startsWith(scopeSelector)) {
                return s;
            }

            return `${scopeSelector} ${s}`;
        });

        return `${scopedSelectors.filter(Boolean).join(", ")} {${declarationBlock}}`;
    });

    return processed;
}

export function scopeAndSanitizeDocumentHtml(
    rawHtml?: string | null,
    scopeSelector = ".proposal-preview-sheet",
): string {
    if (!rawHtml) return "";

    let clean = DOMPurify.sanitize(rawHtml, {
        WHOLE_DOCUMENT: false,
        FORCE_BODY: false,
        ALLOWED_TAGS: [
            "a", "abbr", "address", "article", "aside", "b", "bdi", "bdo", "blockquote", "br",
            "caption", "cite", "code", "col", "colgroup", "data", "dd", "del", "details", "dfn",
            "div", "dl", "dt", "em", "figcaption", "figure", "footer", "h1", "h2", "h3", "h4",
            "h5", "h6", "header", "hgroup", "hr", "i", "img", "ins", "kbd", "li", "main", "mark",
            "nav", "ol", "p", "pre", "q", "rp", "rt", "ruby", "s", "samp", "section", "small",
            "span", "strong", "sub", "summary", "sup", "table", "tbody", "td",
            "tfoot", "th", "thead", "time", "tr", "u", "ul", "var", "wbr", "style",
            // SVG elements
            "svg", "circle", "ellipse", "rect", "line", "polygon", "polyline", "path", "g",
            "defs", "use", "linearGradient", "radialGradient", "stop", "mask", "clipPath",
            "lineargradient", "radialgradient", "clippath", "text", "tspan"
        ],
        ALLOWED_ATTR: [
            "class", "style", "id", "href", "target", "src", "alt", "title", "width", "height",
            "align", "valign", "colspan", "rowspan", "cellpadding", "cellspacing", "border",
            // SVG attributes
            "d", "fill", "fill-opacity", "fill-rule", "viewBox", "xmlns", "cx", "cy", "r", "rx", "ry",
            "x", "y", "x1", "y1", "x2", "y2", "points", "stroke", "stroke-width", "stroke-linecap",
            "stroke-linejoin", "stroke-miterlimit", "stroke-dasharray", "stroke-dashoffset",
            "stroke-opacity", "opacity", "transform", "preserveAspectRatio", "gradientUnits", "offset",
            "stop-color", "stop-opacity"
        ],
    });

    clean = clean.replace(/<body\b([^>]*)>/gi, '<div class="proposal-body-wrapper" $1>');
    clean = clean.replace(/<\/body>/gi, "</div>");
    clean = clean.replace(/<style\b([^>]*)>([\s\S]*?)<\/style>/gi, (match, attrs, cssText) => {
        const scopedCss = scopeCssRules(cssText, scopeSelector);
        return `<style${attrs}>${scopedCss}</style>`;
    });

    return clean;
}

export const scopeAndSanitizeProposalHtml = scopeAndSanitizeDocumentHtml;

export function formatAmountOnly(val: number | string, pageProps?: any): string {
    try {
        const num = Number(val) || 0;
        const decimalPlaces = parseInt(getCompanySetting("decimalFormat", pageProps) || "2");
        const decimalSeparator = getCompanySetting("decimalSeparator", pageProps) || ".";
        const thousandsSeparator = getCompanySetting("thousandsSeparator", pageProps) || ",";
        const floatNumber = getCompanySetting("floatNumber", pageProps) !== "0";

        let finalAmount = floatNumber ? num : Math.floor(num);
        const parts = Number(finalAmount).toFixed(decimalPlaces).split(".");

        if (thousandsSeparator !== "none") {
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator);
        }

        return parts.join(decimalSeparator);
    } catch {
        return Number(val || 0).toFixed(2);
    }
}

export function getAmountFontSize(amountStr: string, defaultSize = 10): string {
    const cleanStr = amountStr.replace(/<[^>]*>/g, "");
    const len = cleanStr.length;
    if (len > 18) return "7.5px";
    if (len > 15) return "8.5px";
    if (len > 12) return "9.5px";
    return `${defaultSize}px`;
}
