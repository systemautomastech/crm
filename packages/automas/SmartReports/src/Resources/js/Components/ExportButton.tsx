import React from 'react';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { FileSpreadsheet, FileText } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import { formatDate, formatCurrency } from '@/utils/helpers';

export interface ExportColumn {
    key: string;
    header: string;
    type?: 'date' | 'currency' | 'status' | 'text' | 'number';
    render?: (value: any, row: any) => string;
}

// ── Grouped Export Types ──────────────────────────────────────────────────────

/**
 * Generic grouped Excel export config.
 *
 * summaryColumns  – columns shown on the GROUP HEADER row (bold, colored bg)
 * detailColumns   – columns shown on each DETAIL row under the group
 * groupKey        – function that returns the group key for a raw data row
 * summaryBuilder  – function that takes all rows of a group and returns
 *                   the summary object used by summaryColumns[].key
 */
export interface GroupedExportConfig {
    summaryColumns: ExportColumn[];
    detailColumns: ExportColumn[];
    groupKey: (row: any) => string;
    summaryBuilder: (rows: any[]) => Record<string, any>;
    sheetName?: string;
}

interface ExportButtonProps {
    data: any[];
    columns: ExportColumn[];
    filename: string;
    title: string;
    /** Pass this to use grouped Excel export instead of flat export */
    groupedConfig?: GroupedExportConfig;
}

// ── PDF status colors (kept for PDF only) ────────────────────────────────────

const STATUS_PDF_COLORS: Record<string, [number, number, number]> = {
    approved:[212,237,218], pending:[255,243,205], rejected:[248,215,218],
    present:[212,237,218], absent:[248,215,218], 'on leave':[209,236,241], 'half day':[255,243,205],
    paid:[212,237,218], partial:[255,235,156], overdue:[248,215,218], draft:[220,220,220], posted:[209,236,241],
    won:[212,237,218], loss:[248,215,218], active:[209,236,241],
    'on track':[212,237,218], 'at risk':[255,243,205], delayed:[248,215,218],
    cleared:[212,237,218], cancelled:[248,215,218],
};

// ── Helpers ───────────────────────────────────────────────────────────────────

function escapeXml(str: string): string {
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&apos;');
}

function getStatusKey(value: string): string {
    return String(value).toLowerCase().trim();
}

function resolveCellValue(row: any, col: ExportColumn, pageProps: any): string {
    const raw = row[col.key];
    if (col.render) return String(col.render(raw, row) ?? '').replace(/<[^>]*>/g, '');
    if (raw === null || raw === undefined || raw === '') return '';
    if (col.type === 'date') return formatDate(raw, pageProps);
    if (col.type === 'currency') return formatCurrency(raw, pageProps);
    if (col.type === 'status') return String(raw).charAt(0).toUpperCase() + String(raw).slice(1);
    if (typeof raw === 'boolean') return raw ? 'Yes' : 'No';
    if (typeof raw === 'object') { try { return JSON.stringify(raw); } catch { return String(raw); } }
    return String(raw);
}

// PDF-safe version: replaces non-Latin currency symbols with ASCII equivalents
function sanitizeForPdf(text: string): string {
    return text
        .replace(/₹/g, 'Rs.')
        .replace(/₩/g, 'KRW')
        .replace(/₪/g, 'ILS')
        .replace(/₦/g, 'NGN')
        .replace(/₫/g, 'VND')
        .replace(/₨/g, 'Rs.')
        .replace(/฿/g, 'THB')
        .replace(/₴/g, 'UAH')
        .replace(/₲/g, 'PYG')
        .replace(/₡/g, 'CRC');
}

function resolvePdfCellValue(row: any, col: ExportColumn, pageProps: any): string {
    return sanitizeForPdf(resolveCellValue(row, col, pageProps));
}

// resolveCellValue for Excel — currency returns formatted string (no raw number)
function resolveExcelValue(row: any, col: ExportColumn, pageProps: any): string {
    return resolveCellValue(row, col, pageProps);
}

// ── Column width calculator ──────────────────────────────────────────────────

function calcColWidths(headers: string[], rows: string[][]): number[] {
    return headers.map((h, ci) => {
        let maxLen = h.length;
        rows.forEach(row => {
            const len = (row[ci] ?? '').length;
            if (len > maxLen) maxLen = len;
        });
        return Math.min(Math.max(maxLen * 7.5, 60), 300);
    });
}

function colWidthTags(widths: number[]): string {
    return widths.map(w => `   <Column ss:AutoFitWidth="0" ss:Width="${w}"/>\n`).join('');
}

// ── XLS blob writer ───────────────────────────────────────────────────────────

function downloadXls(xml: string, filename: string) {
    const bytes: number[] = [0xEF, 0xBB, 0xBF];
    for (let i = 0; i < xml.length; i++) {
        const code = xml.charCodeAt(i);
        if (code < 0x80) bytes.push(code);
        else if (code < 0x800) { bytes.push(0xC0 | (code >> 6)); bytes.push(0x80 | (code & 0x3F)); }
        else { bytes.push(0xE0 | (code >> 12)); bytes.push(0x80 | ((code >> 6) & 0x3F)); bytes.push(0x80 | (code & 0x3F)); }
    }
    const blob = new Blob([new Uint8Array(bytes)], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}

// ── Plain XLS styles (no colors) ─────────────────────────────────────────────

function buildBaseStyles(): string {
    let s = ' <Styles>\n';
    s += '  <Style ss:ID="Title"><Font ss:Bold="1" ss:Size="13"/></Style>\n';
    s += '  <Style ss:ID="Header"><Font ss:Bold="1"/><Alignment ss:Horizontal="Center"/></Style>\n';
    s += '  <Style ss:ID="GroupRow"><Font ss:Bold="1"/></Style>\n';
    s += '  <Style ss:ID="SubHeader"><Font ss:Bold="1" ss:Italic="1"/><Alignment ss:Horizontal="Center"/></Style>\n';
    s += '  <Style ss:ID="Indent"><Alignment ss:Indent="2"/></Style>\n';
    s += ' </Styles>\n';
    return s;
}

function xlsCell(val: string, styleId?: string): string {
    return `    <Cell${styleId ? ` ss:StyleID="${styleId}"` : ''}><Data ss:Type="String">${escapeXml(val)}</Data></Cell>\n`;
}

// ── FLAT Excel export ─────────────────────────────────────────────────────────

function exportToExcel(data: any[], columns: ExportColumn[], filename: string, pageProps: any) {
    if (!data?.length) return;

    const headers = columns.map(c => c.header);
    const allRows = data.map(row => columns.map(col => resolveExcelValue(row, col, pageProps).trim()));
    const widths = calcColWidths(headers, allRows);

    let xml = '<?xml version="1.0" encoding="UTF-8"?>\n<?mso-application progid="Excel.Sheet"?>\n';
    xml += '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">\n';
    xml += buildBaseStyles();
    xml += ' <Worksheet ss:Name="Sheet1">\n  <Table>\n';
    xml += colWidthTags(widths);
    xml += `   <Row><Cell ss:MergeAcross="${columns.length - 1}" ss:StyleID="Title"><Data ss:Type="String">${escapeXml(filename)}</Data></Cell></Row>\n`;
    xml += '   <Row/>\n';
    xml += '   <Row>\n';
    columns.forEach(col => { xml += `    <Cell ss:StyleID="Header"><Data ss:Type="String">${escapeXml(col.header)}</Data></Cell>\n`; });
    xml += '   </Row>\n';

    allRows.forEach(rowCells => {
        xml += '   <Row>\n';
        rowCells.forEach(val => { xml += xlsCell(val); });
        xml += '   </Row>\n';
    });

    xml += '  </Table>\n </Worksheet>\n</Workbook>';
    downloadXls(xml, filename);
}

export function exportGroupedExcel(
    data: any[],
    filename: string,
    config: GroupedExportConfig,
    pageProps: any = {}
) {
    if (!data?.length) return;

    const { groupKey, summaryBuilder, summaryColumns, detailColumns, sheetName = 'Report' } = config;

    // Build ordered groups
    const orderedKeys: string[] = [];
    const groupMap: Record<string, any[]> = {};
    data.forEach(row => {
        const key = groupKey(row);
        if (!groupMap[key]) { groupMap[key] = []; orderedKeys.push(key); }
        groupMap[key].push(row);
    });

    const totalCols = Math.max(summaryColumns.length, detailColumns.length);
    const xml_header = '<?xml version="1.0" encoding="UTF-8"?>\n<?mso-application progid="Excel.Sheet"?>\n'
        + '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">\n';

    // Pre-compute all cell values for width calculation
    // Width = max of: summary header, detail header, summary values, detail values
    const sumHeaders = summaryColumns.map(c => c.header);
    const detHeaders = detailColumns.map(c => c.header);
    const allHeaders = Array.from({ length: totalCols }, (_, i) =>
        sumHeaders[i] ?? detHeaders[i] ?? ''
    );

    const allValueRows: string[][] = [];
    orderedKeys.forEach(key => {
        const rows = groupMap[key];
        const summary = summaryBuilder(rows);
        allValueRows.push(Array.from({ length: totalCols }, (_, i) => {
            const col = summaryColumns[i];
            if (!col) return '';
            const rawVal = summary[col.key];
            if (col.type === 'date') return rawVal ? formatDate(String(rawVal).split('T')[0], pageProps) : '';
            if (col.type === 'currency') return formatCurrency(Number(rawVal) || 0, pageProps);
            return String(rawVal ?? '');
        }));
        rows.forEach(row => {
            allValueRows.push(Array.from({ length: totalCols }, (_, i) => {
                const col = detailColumns[i];
                return col ? resolveExcelValue(row, col, pageProps) : '';
            }));
        });
    });
    const widths = calcColWidths(allHeaders, allValueRows);

    let xml = xml_header + buildBaseStyles() + ` <Worksheet ss:Name="${escapeXml(sheetName)}">\n  <Table>\n`;
    xml += colWidthTags(widths);

    // Title
    xml += `   <Row><Cell ss:MergeAcross="${totalCols - 1}" ss:StyleID="Title"><Data ss:Type="String">${escapeXml(filename)}</Data></Cell></Row>\n`;
    xml += '   <Row/>\n';

    // Global column header
    xml += '   <Row>\n';
    summaryColumns.forEach(col => { xml += `    <Cell ss:StyleID="Header"><Data ss:Type="String">${escapeXml(col.header)}</Data></Cell>\n`; });
    for (let i = summaryColumns.length; i < totalCols; i++) xml += '    <Cell ss:StyleID="Header"><Data ss:Type="String"></Data></Cell>\n';
    xml += '   </Row>\n';

    orderedKeys.forEach(key => {
        const rows = groupMap[key];
        const summary = summaryBuilder(rows);

        // ── Group summary row (bold) ──
        xml += '   <Row>\n';
        summaryColumns.forEach(col => {
            const rawVal = summary[col.key];
            let val: string;
            if (col.type === 'date') val = rawVal ? formatDate(String(rawVal).split('T')[0], pageProps) : '';
            else if (col.type === 'currency') val = formatCurrency(Number(rawVal) || 0, pageProps);
            else val = String(rawVal ?? '');
            xml += xlsCell(val, 'GroupRow');
        });
        for (let i = summaryColumns.length; i < totalCols; i++) xml += xlsCell('', 'GroupRow');
        xml += '   </Row>\n';

        // ── Detail sub-header + rows ──
        if (detailColumns.length > 0) {
            xml += '   <Row>\n';
            detailColumns.forEach(col => { xml += `    <Cell ss:StyleID="SubHeader"><Data ss:Type="String">${escapeXml(col.header)}</Data></Cell>\n`; });
            for (let i = detailColumns.length; i < totalCols; i++) xml += '    <Cell ss:StyleID="SubHeader"><Data ss:Type="String"></Data></Cell>\n';
            xml += '   </Row>\n';

            rows.forEach(row => {
                xml += '   <Row>\n';
                detailColumns.forEach((col, ci) => {
                    const val = resolveExcelValue(row, col, pageProps);
                    xml += xlsCell(val, ci === 0 ? 'Indent' : undefined);
                });
                for (let i = detailColumns.length; i < totalCols; i++) xml += xlsCell('');
                xml += '   </Row>\n';
            });
        }

        xml += '   <Row/>\n';
    });

    xml += '  </Table>\n </Worksheet>\n</Workbook>';
    downloadXls(xml, filename);
}

// ── PDF export ────────────────────────────────────────────────────────────────

function exportToPDF(data: any[], columns: ExportColumn[], filename: string, title: string, pageProps: any) {
    if (!data?.length) return;
    const doc = new jsPDF('landscape');
    doc.setFontSize(16);
    doc.text(title, 14, 20);
    doc.setFontSize(10);
    doc.text('Generated: ' + new Date().toLocaleDateString(), 14, 28);

    const statusCols = columns.map((c, i) => c.type === 'status' ? i : -1).filter(i => i >= 0);

    autoTable(doc, {
        head: [columns.map(c => c.header)],
        body: data.map(row => columns.map(col => resolvePdfCellValue(row, col, pageProps))),
        startY: 34,
        styles: { fontSize: 7, cellPadding: 2 },
        headStyles: { fillColor: [59, 130, 246], textColor: 255, fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [245, 247, 250] },
        didParseCell: (hookData) => {
            if (hookData.section === 'body' && statusCols.includes(hookData.column.index)) {
                const col = columns[hookData.column.index];
                const row = data[hookData.row.index];
                const raw = col.render ? col.render(row[col.key], row).replace(/<[^>]*>/g, '') : row[col.key];
                const color = STATUS_PDF_COLORS[getStatusKey(String(raw ?? ''))];
                if (color) { hookData.cell.styles.fillColor = color; hookData.cell.styles.textColor = [30, 30, 30]; hookData.cell.styles.halign = 'center'; }
            }
        },
    });
    doc.save(filename + '.pdf');
}

function exportGroupedPDF(
    data: any[],
    filename: string,
    title: string,
    config: GroupedExportConfig,
    pageProps: any
) {
    if (!data?.length) return;

    const { groupKey, summaryBuilder, summaryColumns, detailColumns } = config;

    // Build ordered groups
    const orderedKeys: string[] = [];
    const groupMap: Record<string, any[]> = {};
    data.forEach(row => {
        const key = groupKey(row);
        if (!groupMap[key]) { groupMap[key] = []; orderedKeys.push(key); }
        groupMap[key].push(row);
    });

    const doc = new jsPDF('landscape');
    doc.setFontSize(16);
    doc.text(title, 14, 20);
    doc.setFontSize(10);
    doc.text('Generated: ' + new Date().toLocaleDateString(), 14, 28);

    let startY = 34;

   // Flat mode: no detail columns — render one single table with all summary rows
    if (detailColumns.length === 0) {
        const statusCols = summaryColumns.map((c, i) => c.type === 'status' ? i : -1).filter(i => i >= 0);
        const allRows = orderedKeys.map(key => {
            const summary = summaryBuilder(groupMap[key]);
            return summaryColumns.map(col => {
                const rawVal = summary[col.key];
                if (col.type === 'currency') return sanitizeForPdf(formatCurrency(Number(rawVal) || 0, pageProps));
                if (col.type === 'date') return rawVal ? formatDate(String(rawVal).split('T')[0], pageProps) : '';
                return String(rawVal ?? '');
            });
        });

        autoTable(doc, {
            head: [summaryColumns.map(c => c.header)],
            body: allRows,
            startY,
            styles: { fontSize: 7, cellPadding: 2 },
            headStyles: { fillColor: [59, 130, 246], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            margin: { left: 14, right: 14 },
            didParseCell: (hookData) => {
                if (hookData.section === 'body' && statusCols.includes(hookData.column.index)) {
                    const col = summaryColumns[hookData.column.index];
                    const rawVal = orderedKeys[hookData.row.index]
                        ? summaryBuilder(groupMap[orderedKeys[hookData.row.index]])[col.key]
                        : '';
                    const color = STATUS_PDF_COLORS[getStatusKey(String(rawVal ?? ''))];
                    if (color) { hookData.cell.styles.fillColor = color; hookData.cell.styles.textColor = [30, 30, 30]; hookData.cell.styles.halign = 'center'; }
                }
            },
        });
        doc.save(filename + '.pdf');
        return;
    }

    orderedKeys.forEach((key) => {
        const rows = groupMap[key];
        const summary = summaryBuilder(rows);

        // Group header row (summary columns)
        const summaryBody = [summaryColumns.map(col => {
            const rawVal = summary[col.key];
            if (col.type === 'currency') return sanitizeForPdf(formatCurrency(Number(rawVal) || 0, pageProps));
            if (col.type === 'date') return rawVal ? formatDate(String(rawVal).split('T')[0], pageProps) : '';
            return String(rawVal ?? '');
        })];

        autoTable(doc, {
            head: [summaryColumns.map(c => c.header)],
            body: summaryBody,
            startY,
            styles: { fontSize: 8, cellPadding: 2, fontStyle: 'bold' },
            headStyles: { fillColor: [59, 130, 246], textColor: 255, fontStyle: 'bold' },
            bodyStyles: { fillColor: [239, 246, 255], textColor: [30, 30, 100], fontStyle: 'bold' },
            margin: { left: 14, right: 14 },
            tableLineColor: [200, 200, 200],
            tableLineWidth: 0.1,
        });

        startY = (doc as any).lastAutoTable.finalY;

        const statusCols = detailColumns.map((c, i) => c.type === 'status' ? i : -1).filter(i => i >= 0);

        autoTable(doc, {
            head: [detailColumns.map(c => c.header)],
            body: rows.map(row => detailColumns.map(col => resolvePdfCellValue(row, col, pageProps))),
            startY,
            styles: { fontSize: 7, cellPadding: 2 },
            headStyles: { fillColor: [100, 116, 139], textColor: 255, fontStyle: 'bold', fontSize: 7 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            margin: { left: 20, right: 14 },
            didParseCell: (hookData) => {
                if (hookData.section === 'body' && statusCols.includes(hookData.column.index)) {
                    const col = detailColumns[hookData.column.index];
                    const row = rows[hookData.row.index];
                    const raw = col.render ? col.render(row[col.key], row).replace(/<[^>]*>/g, '') : row[col.key];
                    const color = STATUS_PDF_COLORS[getStatusKey(String(raw ?? ''))];
                    if (color) { hookData.cell.styles.fillColor = color; hookData.cell.styles.textColor = [30, 30, 30]; hookData.cell.styles.halign = 'center'; }
                }
            },
        });

        startY = (doc as any).lastAutoTable.finalY + 4;
    });

    doc.save(filename + '.pdf');
}

// ── ExportButton component ────────────────────────────────────────────────────

export default function ExportButton({ data, columns, filename, title, groupedConfig }: ExportButtonProps) {
    const { t } = useTranslation();
    const pageProps = usePage().props;

    const handleExcel = () => {
        if (groupedConfig) {
            exportGroupedExcel(data, filename, groupedConfig, pageProps);
        } else {
            exportToExcel(data, columns, filename, pageProps);
        }
    };
    const handlePDF = () => {
        if (groupedConfig) {
            exportGroupedPDF(data, filename, title, groupedConfig, pageProps);
        } else {
            exportToPDF(data, columns, filename, title, pageProps);
        }
    };

    return (
        <TooltipProvider>
            <div className="flex items-center gap-2">
                <Tooltip delayDuration={0}>
                    <TooltipTrigger asChild>
                        <Button variant="outline" size="sm" onClick={handleExcel}>
                            <FileSpreadsheet className="h-4 w-4 text-green-600" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent><p>{t('Export Excel')}</p></TooltipContent>
                </Tooltip>
                <Tooltip delayDuration={0}>
                    <TooltipTrigger asChild>
                        <Button variant="outline" size="sm" onClick={handlePDF}>
                            <FileText className="h-4 w-4 text-red-600" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent><p>{t('Export PDF')}</p></TooltipContent>
                </Tooltip>
            </div>
        </TooltipProvider>
    );
}
