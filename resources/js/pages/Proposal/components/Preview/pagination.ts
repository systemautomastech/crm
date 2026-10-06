import {
    A4_PAGE_WIDTH_MM,
    A4_PAGE_HEIGHT_MM,
    A4_HEADER_RESERVED_MM,
    A4_FOOTER_RESERVED_MM,
    A4_HORIZONTAL_PADDING_MM,
    escapeHtmlAttribute,
} from "./helpers";

export interface PaginationContext {
    maxHeight: number;
    sectionIndex?: string;
}

export const PAGINATION_EPSILON_PX = 1;

export function mmToPx(mm: number): number {
    if (typeof document === "undefined") {
        return mm * 3.7795275591;
    }

    const probe = document.createElement("div");
    probe.style.cssText = `
        position: absolute; visibility: hidden; pointer-events: none;
        width: ${mm}mm; height: 0; padding: 0; margin: 0; border: 0;
        left: -10000px; top: -10000px;
    `;

    document.body.appendChild(probe);
    const px = probe.getBoundingClientRect().width;
    probe.remove();

    return px || mm * 3.7795275591;
}

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
        position: absolute; visibility: hidden; pointer-events: none;
        left: -10000px; top: -10000px; width: ${A4_PAGE_WIDTH_MM}mm; height: ${A4_PAGE_HEIGHT_MM}mm;
        box-sizing: border-box;
        padding: ${A4_HEADER_RESERVED_MM}mm ${A4_HORIZONTAL_PADDING_MM}mm ${A4_FOOTER_RESERVED_MM}mm;
        display: flex; flex-direction: column; margin: 0; border: 0;
    `;

    const contentProbe = document.createElement("div");
    contentProbe.style.cssText = `width: 100%; flex: 1 1 auto; min-height: 0; box-sizing: border-box;`;

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

export function getVerticalMargins(el: HTMLElement): number {
    if (typeof window === "undefined") return 0;
    const computedStyle = window.getComputedStyle(el);
    return (
        (parseFloat(computedStyle.marginTop) || 0) +
        (parseFloat(computedStyle.marginBottom) || 0)
    );
}

export function getRenderedHeight(el: HTMLElement): number {
    const rect = el.getBoundingClientRect();
    if (rect.height > 0) return rect.height;
    return el.offsetHeight || 0;
}

export function getOccupiedHeight(el: HTMLElement): number {
    return getRenderedHeight(el) + getVerticalMargins(el);
}

export function addSectionMarker(html: string, sectionIndex?: string): string {
    if (sectionIndex === undefined || /data-proposal-section-index=/.test(html)) {
        return html;
    }
    return html.replace(
        /^<(\w+)(\s|>)/,
        `<$1 data-proposal-section-index="${escapeHtmlAttribute(sectionIndex)}"$2`,
    );
}

export function hasForcedPageBreak(el: HTMLElement): boolean {
    return (
        el.classList?.contains("page-break") ||
        el.style?.pageBreakAfter === "always" ||
        el.style?.pageBreakBefore === "always" ||
        el.style?.breakAfter === "page" ||
        el.style?.breakBefore === "page"
    );
}

export function isFullPageElement(el: HTMLElement): boolean {
    return (
        el.hasAttribute("data-full-page") ||
        el.classList?.contains("cover-page-wrapper") ||
        el.style?.height?.includes("297mm") ||
        el.style?.minHeight?.includes("297mm")
    );
}

export function isTraversableContainer(el: HTMLElement): boolean {
    const tag = el.tagName.toLowerCase();
    return (
        (tag === "div" || tag === "section" || tag === "article" || tag === "main") &&
        el.children.length > 0 &&
        !el.classList.contains("page-break")
    );
}

export function cloneElementShell(el: HTMLElement, childrenHtml = ""): string {
    const clone = el.cloneNode(false) as HTMLElement;
    clone.innerHTML = childrenHtml;
    return clone.outerHTML;
}

export function measureHtmlHeight(measurementRoot: HTMLElement, html: string): number {
    const probe = document.createElement("div");
    probe.style.cssText = `
        position: absolute; visibility: hidden; pointer-events: none;
        left: 0; top: 0; width: 100%; margin: 0; padding: 0; border: 0; box-sizing: border-box;
    `;
    probe.innerHTML = html;
    measurementRoot.appendChild(probe);

    const rectHeight = probe.getBoundingClientRect().height;
    const scrollHeight = probe.scrollHeight;
    probe.remove();

    return Math.max(rectHeight, scrollHeight, 0);
}

export function buildChildFragment(parent: HTMLElement, children: Node[]): string {
    const wrapper = parent.cloneNode(false) as HTMLElement;
    children.forEach((child) => {
        wrapper.appendChild(child.cloneNode(true));
    });
    return wrapper.outerHTML;
}

export function findDirectTable(el: HTMLElement): HTMLTableElement | null {
    const directTable = Array.from(el.children).find(
        (child) => child.tagName.toLowerCase() === "table",
    );
    return directTable ? (directTable as HTMLTableElement) : null;
}

export function findTableLabel(wrapper: HTMLElement, table: HTMLTableElement): HTMLElement | null {
    const children = Array.from(wrapper.children);
    const tableIndex = children.indexOf(table);
    if (tableIndex <= 0) return null;

    const previous = children[tableIndex - 1] as HTMLElement;
    if (!previous) return null;

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

export function waitForElementResources(root: HTMLElement): Promise<void> {
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

export function splitSingleTextNodeElement(
    el: HTMLElement,
    measurementRoot: HTMLElement,
    availableHeight: number,
): string[] | null {
    const childNodes = Array.from(el.childNodes);
    if (childNodes.length !== 1) return null;

    const node = childNodes[0];
    if (node.nodeType !== Node.TEXT_NODE) return null;

    const text = node.textContent || "";
    if (!text.trim()) return null;

    const words = text.split(/(\s+)/);
    if (words.length <= 1) return null;

    const chunks: string[] = [];
    let current = "";

    const fits = (candidate: string): boolean => {
        const wrapper = el.cloneNode(false) as HTMLElement;
        wrapper.textContent = candidate;
        return measureHtmlHeight(measurementRoot, wrapper.outerHTML) <= availableHeight + PAGINATION_EPSILON_PX;
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

    if (chunks.length <= 1) return null;
    return chunks.map((textChunk) => {
        const wrapper = el.cloneNode(false) as HTMLElement;
        wrapper.textContent = textChunk;
        return wrapper.outerHTML;
    });
}

export function splitOversizedElement(
    el: HTMLElement,
    measurementRoot: HTMLElement,
    availableHeight: number,
): string[] | null {
    const childNodes = Array.from(el.childNodes);
    if (childNodes.length <= 1) return null;

    const chunks: string[] = [];
    let currentNodes: Node[] = [];

    const flush = () => {
        if (currentNodes.length === 0) return;
        chunks.push(buildChildFragment(el, currentNodes));
        currentNodes = [];
    };

    for (const node of childNodes) {
        const candidateNodes = [...currentNodes, node];
        const candidateHtml = buildChildFragment(el, candidateNodes);
        const candidateHeight = measureHtmlHeight(measurementRoot, candidateHtml);

        if (currentNodes.length > 0 && candidateHeight > availableHeight + PAGINATION_EPSILON_PX) {
            flush();
            currentNodes = [node];
        } else {
            currentNodes.push(node);
        }
    }

    flush();
    return chunks.length > 1 ? chunks : null;
}

export function paginateOversizedElement(
    el: HTMLElement,
    measurementRoot: HTMLElement,
    availableHeight: number,
    sectionIndex?: string,
): string[] {
    const childChunks = splitOversizedElement(el, measurementRoot, availableHeight);
    if (childChunks && childChunks.length > 1) {
        return childChunks.map((html) => addSectionMarker(html, sectionIndex));
    }

    const textChunks = splitSingleTextNodeElement(el, measurementRoot, availableHeight);
    if (textChunks && textChunks.length > 1) {
        return textChunks.map((html) => addSectionMarker(html, sectionIndex));
    }

    const fallback = el.cloneNode(true) as HTMLElement;
    fallback.classList.add("proposal-pagination-splittable");
    return [addSectionMarker(fallback.outerHTML, sectionIndex)];
}

export function buildTableChunkHtml(
    table: HTMLTableElement,
    rows: string[],
    theadHtml: string,
    tfootHtml: string,
    includeFooter: boolean,
    sectionIndex?: string,
): string {
    const tableClasses = table.getAttribute("class") || "";
    const tableStyle = table.getAttribute("style") || "";
    const marker = sectionIndex !== undefined ? ` data-proposal-section-index="${escapeHtmlAttribute(sectionIndex)}"` : "";

    return (
        `<table class="${escapeHtmlAttribute(tableClasses)}"${marker} style="${escapeHtmlAttribute(tableStyle)}">` +
        theadHtml +
        `<tbody>${rows.join("")}</tbody>` +
        (includeFooter ? tfootHtml : "") +
        `</table>`
    );
}

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
    const effectiveMaxHeight = Math.max(1, maxPageHeight - PAGINATION_EPSILON_PX);

    const pushCurrentPage = () => {
        if (currentPageHtml.length === 0) return;
        pages.push((styleTags ? styleTags + "\n" : "") + currentPageHtml.join(""));
        currentPageHtml = [];
        currentPageAccumulatedHeight = 0;
    };

    const addHtmlToCurrentPage = (html: string, height: number) => {
        currentPageHtml.push(html);
        currentPageAccumulatedHeight += height;
    };

    const processTable = (
        table: HTMLTableElement,
        label: HTMLElement | null,
        sectionIndex?: string,
    ) => {
        const thead = table.querySelector("thead");
        const tfoot = table.querySelector("tfoot");
        const bodyRows = Array.from(table.querySelectorAll("tbody > tr")) as HTMLElement[];

        const theadHtml = thead ? thead.outerHTML : "";
        const tfootHtml = tfoot ? tfoot.outerHTML : "";
        const labelHtml = label ? addSectionMarker(label.outerHTML, sectionIndex) : "";

        if (bodyRows.length === 0) {
            const tableHtml = buildTableChunkHtml(table, [], theadHtml, tfootHtml, true, sectionIndex);
            const combinedHtml = labelHtml + tableHtml;
            const combinedHeight = measureHtmlHeight(container, combinedHtml);

            if (
                currentPageAccumulatedHeight > 0 &&
                currentPageAccumulatedHeight + combinedHeight > effectiveMaxHeight + PAGINATION_EPSILON_PX
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

                const candidateHtml = firstChunk ? labelHtml + candidateTableHtml : candidateTableHtml;
                const candidateHeight = measureHtmlHeight(container, candidateHtml);

                if (currentPageAccumulatedHeight + candidateHeight <= effectiveMaxHeight + PAGINATION_EPSILON_PX) {
                    chunkRows.push(row.outerHTML);
                    rowIndex++;
                    continue;
                }

                if (chunkRows.length > 0) break;

                if (currentPageHtml.length > 0 && currentPageAccumulatedHeight > 0) {
                    pushCurrentPage();
                    continue;
                }

                const oversizedRow = row.cloneNode(true) as HTMLElement;
                oversizedRow.classList.add("proposal-oversized-row");
                chunkRows.push(oversizedRow.outerHTML);
                rowIndex++;
                break;
            }

            if (chunkRows.length === 0) break;

            const isFinalChunk = rowIndex >= bodyRows.length;
            const tableHtml = buildTableChunkHtml(table, chunkRows, theadHtml, tfootHtml, isFinalChunk, sectionIndex);
            const chunkHtml = firstChunk ? labelHtml + tableHtml : tableHtml;
            const chunkHeight = measureHtmlHeight(container, chunkHtml);

            if (
                currentPageAccumulatedHeight > 0 &&
                currentPageAccumulatedHeight + chunkHeight > effectiveMaxHeight + PAGINATION_EPSILON_PX
            ) {
                pushCurrentPage();
            }

            addHtmlToCurrentPage(chunkHtml, chunkHeight);
            firstChunk = false;

            if (!isFinalChunk) {
                pushCurrentPage();
            }
        }
    };

    const processElement = (el: HTMLElement, inheritedSectionIndex?: string) => {
        const ownSectionIndex = el.getAttribute("data-proposal-section-index");
        const sectionIndex = ownSectionIndex ?? inheritedSectionIndex;
        const tag = el.tagName.toLowerCase();

        if (tag === "style" || tag === "script") return;

        if (hasForcedPageBreak(el)) {
            pushCurrentPage();
            const directTable = findDirectTable(el);

            if (directTable) {
                const label = findTableLabel(el, directTable);
                processTable(directTable, label, sectionIndex);

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

        if (isFullPageElement(el)) {
            if (currentPageHtml.length > 0) pushCurrentPage();

            const html = addSectionMarker(el.outerHTML, sectionIndex);
            currentPageHtml.push(html);
            currentPageAccumulatedHeight = getOccupiedHeight(el);
            pushCurrentPage();
            return;
        }

        if (tag === "table") {
            processTable(el as HTMLTableElement, null, sectionIndex);
            return;
        }

        const directTable = findDirectTable(el);

        if (directTable) {
            const children = Array.from(el.children) as HTMLElement[];
            const tableIndex = children.indexOf(directTable);
            const label = findTableLabel(el, directTable);

            const labelIndex = label ? children.indexOf(label) : tableIndex;

            for (let i = 0; i < Math.max(0, labelIndex); i++) {
                processElement(children[i], sectionIndex);
            }

            processTable(directTable, label, sectionIndex);

            for (let i = tableIndex + 1; i < children.length; i++) {
                processElement(children[i], sectionIndex);
            }
            return;
        }

        if ((tag === "ul" || tag === "ol") && el.children.length > 0) {
            const listClasses = el.getAttribute("class") || "";
            const listStyle = el.getAttribute("style") || "";
            const items = Array.from(el.children) as HTMLElement[];

            let currentItems: string[] = [];

            const flushList = () => {
                if (currentItems.length === 0) return;

                const listHtml =
                    `<${tag} class="${escapeHtmlAttribute(listClasses)}" style="${escapeHtmlAttribute(listStyle)}">` +
                    currentItems.join("") +
                    `</${tag}>`;

                const height = measureHtmlHeight(container, listHtml);
                addHtmlToCurrentPage(addSectionMarker(listHtml, sectionIndex), height);
                currentItems = [];
            };

            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                const candidateItems = [...currentItems, addSectionMarker(item.outerHTML, sectionIndex)];
                const candidateHtml = `<${tag}>${candidateItems.join("")}</${tag}>`;
                const candidateHeight = measureHtmlHeight(container, candidateHtml);

                if (currentPageAccumulatedHeight + candidateHeight > effectiveMaxHeight + PAGINATION_EPSILON_PX) {
                    if (currentItems.length > 0) {
                        flushList();
                        pushCurrentPage();
                    }

                    const itemHeight = measureHtmlHeight(
                        container,
                        `<${tag}>${addSectionMarker(item.outerHTML, sectionIndex)}</${tag}>`,
                    );

                    if (itemHeight <= effectiveMaxHeight + PAGINATION_EPSILON_PX) {
                        currentItems.push(addSectionMarker(item.outerHTML, sectionIndex));
                        continue;
                    }

                    const chunks = paginateOversizedElement(item, container, effectiveMaxHeight, sectionIndex);

                    chunks.forEach((chunk, chunkIndex) => {
                        if (currentPageHtml.length > 0 && currentPageAccumulatedHeight > 0) {
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

                currentItems.push(addSectionMarker(item.outerHTML, sectionIndex));
            }

            flushList();
            return;
        }

        if (isTraversableContainer(el)) {
            const elHeight = getOccupiedHeight(el);

            if (currentPageAccumulatedHeight + elHeight <= effectiveMaxHeight + PAGINATION_EPSILON_PX) {
                addHtmlToCurrentPage(addSectionMarker(el.outerHTML, sectionIndex), elHeight);
                return;
            }

            const children = Array.from(el.children) as HTMLElement[];
            if (children.length > 0) {
                children.forEach((child) => processElement(child as HTMLElement, sectionIndex));
                return;
            }
        }

        const elHeight = getOccupiedHeight(el);

        if (currentPageAccumulatedHeight + elHeight <= effectiveMaxHeight + PAGINATION_EPSILON_PX) {
            addHtmlToCurrentPage(addSectionMarker(el.outerHTML, sectionIndex), elHeight);
            return;
        }

        if (currentPageHtml.length > 0) {
            pushCurrentPage();
        }

        if (elHeight <= effectiveMaxHeight + PAGINATION_EPSILON_PX) {
            addHtmlToCurrentPage(addSectionMarker(el.outerHTML, sectionIndex), elHeight);
            return;
        }

        const chunks = paginateOversizedElement(el, container, effectiveMaxHeight, sectionIndex);

        chunks.forEach((chunk, chunkIndex) => {
            if (currentPageHtml.length > 0 && currentPageAccumulatedHeight > 0) {
                pushCurrentPage();
            }
            const height = measureHtmlHeight(container, chunk);
            addHtmlToCurrentPage(chunk, height);
            if (chunkIndex < chunks.length - 1) {
                pushCurrentPage();
            }
        });
    };

    Array.from(container.children).forEach((child) => {
        processElement(child as HTMLElement);
    });

    if (currentPageHtml.length > 0) {
        pushCurrentPage();
    }

    return pages.length > 0 ? pages : [container.innerHTML];
}
