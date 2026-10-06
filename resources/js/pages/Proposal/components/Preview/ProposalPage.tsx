import React from "react";
import { getImagePath } from "@/utils/helpers";
import { cn } from "@/lib/utils";
import {
    DEFAULT_TEMPLATE_COLOR,
    PROPOSAL_CONTENT_CLASSES,
    scopeAndSanitizeDocumentHtml,
} from "./helpers";

export interface ProposalPageProps {
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

export const ProposalPage = React.memo<ProposalPageProps>(
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
        const rawBg = backgroundImage && String(backgroundImage).trim() !== "" ? backgroundImage : defaultBg;
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
                            ? { minHeight: "297mm", boxSizing: "border-box" }
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
                                const raw = String(backgroundImage || defaultBg || "").split("/").pop();
                                if (raw && !target.src.includes("/storage/media/")) {
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
                                !customHtml && cn("html-preview-container flex-1 flex flex-col", PROPOSAL_CONTENT_CLASSES),
                                customHtml && "w-full h-full",
                            )}
                            style={
                                !customHtml
                                    ? { display: "flex", flexDirection: "column", flex: 1, width: "100%" }
                                    : { width: "100%", height: "100%" }
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

ProposalPage.displayName = "ProposalPage";
export default ProposalPage;
