import { j as m } from "./ui-Bjt2ZI42.js";
import { X as S, S as b } from "./app-ByTFysFa.js";
import { F as A, H as M } from "./Footer-CxtFDgAH.js";
import { C as w } from "./CTA-93C1Fg93.js";
import { C as H } from "./cookie-consent-DWWv6PEE.js";
import y from "./Hero-BQd512Wf.js";
import D from "./Modules-CR1x76vW.js";
import E from "./Dedication-BSmZDUNX.js";
import F from "./Screenshots-Mmi56pxI.js";
import R from "./WhyChoose-BWg2v456.js";
import { d as I, b as J } from "./helpers-Cchd7re_.js";
import "./language-switcher-CQJ0OXT4.js";
import "./select-BfC2WbKO.js";
import "./index-D17hSi50.js";
import "./index-CcDlNl2K.js";
import "./input-BhkK921K.js";
import "./utils-DqweA7RH.js";
import "./utils-BMVe_KuB.js";
import "./chevron-down-DSojvcO8.js";
import "./check-C5DAqmsE.js";
import "./chevron-up-CDZx4iGM.js";
import "./dialog-DEfnO-F7.js";
import "./button-BwagC4xJ.js";
import "./index-DnPxjgVG.js";
import "./label-DBCG1vnl.js";
import "./useTranslation-Dg4p5P8I.js";
import "./refresh-cw-gf1D_gQi.js";
import "./language-BbjLdHTk.js";
import "./globe-F2pSGvzV.js";
import "./plus-CHr6Ebgc.js";
import "./settings-Bl4suzgS.js";
import "./layers-DGEQb0z2.js";
import "./shield-check-BiVWBtN6.js";
import "./arrow-right-HsxUQEcU.js";
import "./arrow-up-D4NA7aVs.js";
import "./user-cog-CUsDCR0V.js";
import "./switch-7LWM7G0-.js";
import "./cookie-DEqwbdBd.js";
import "./AddonCard-YU-6A_GG.js";
import "./package-BGHnV2Zb.js";
import "./circle-check-big-x_lv4KRT.js";
import "./lucide-react-KDOAYied.js";
import "./activity-k9j2Lflh.js";
import "./undo-DTedaugm.js";
import "./arrow-down-D9dUouRN.js";
import "./arrow-left-Dc3hP62h.js";
import "./chevrons-right-BhdHpdN8.js";
import "./user-round-LgPy0BCx.js";
import "./book-open-203Q56Ge.js";
import "./message-square-Cy4beLrM.js";
import "./building-2-DD3ZgokS.js";
import "./building-DUetGz1w.js";
import "./calendar-ouWmHBTr.js";
import "./chevron-right-BvXJbubv.js";
import "./circle-x-uuKTS_10.js";
import "./circle-zIUPQzS7.js";
import "./clock-D2SMvqry.js";
import "./credit-card-D0fBEZbA.js";
import "./database-DSgrl4gN.js";
import "./dollar-sign-Q9-4k88N.js";
import "./download-Ct5wqB-D.js";
import "./eye-off-sOvv-e8l.js";
import "./eye-KGP6CZCc.js";
import "./truck-CHRsJAlX.js";
import "./file-text-Z8m5wj_r.js";
import "./file-pof4cdPu.js";
import "./filter-EooBZUpE.js";
import "./folder-CpmNZ42E.js";
import "./git-branch-7v9qAWrW.js";
import "./pencil-Cp5YYMoW.js";
import "./hard-drive-BZpBPxVM.js";
import "./image-C17vLl5J.js";
import "./info---MSbXY2.js";
import "./list-DSI0cAaV.js";
import "./lock-IOyyq6ve.js";
import "./mail-JKoSqkQw.js";
import "./map-pin-CddvINvu.js";
import "./panel-left-DbyzdsL_.js";
import "./video-Blhx4u1u.js";
import "./ruler-D1hVkK6X.js";
import "./printer-DXSn9qkb.js";
import "./radio-7RbyHE_Z.js";
import "./save-CAmGE9bt.js";
import "./send-DTgM7o0y.js";
import "./shopping-cart-f74zu0z-.js";
import "./sparkles-BCzQf0ge.js";
import "./square-check-big-DkTVmBxi.js";
import "./square-pen-qVMB5b5O.js";
import "./store-BJl2kaHF.js";
import "./tag-DxzvJZ2S.js";
import "./target-DxvZMtre.js";
import "./upload-WCHSOHkK.js";
import "./user-check-DrpU9D9d.js";
import "./user-plus-DRAzxyOw.js";
import "./users-DDGnCMu-.js";
import "./zap-CFn9yzja.js";
import "./icon-picker-oUM4uLUr.js";
import "./popover-IM7TCWxo.js";
function wo({
    packages: h = [],
    matchedPackage: x,
    settings: r,
    landingPageSettings: e,
}) {
    var l, f, u, d;
    const j = (o) => {
            var t, p;
            return (
                ((p =
                    (t = r == null ? void 0 : r.config_sections) == null
                        ? void 0
                        : t.sections) == null
                    ? void 0
                    : p[o]) || {}
            );
        },
        s = I("favicon"),
        n = s ? J(s) : null,
        { adminAllSetting: _, auth: i } = S().props,
        c = {
            ...e,
            is_authenticated:
                ((l = i == null ? void 0 : i.user) == null ? void 0 : l.id) !==
                    void 0 &&
                ((f = i == null ? void 0 : i.user) == null ? void 0 : f.id) !==
                    null,
        },
        k = (o) => {
            var t, p;
            return (
                ((p =
                    (t = r == null ? void 0 : r.config_sections) == null
                        ? void 0
                        : t.section_visibility) == null
                    ? void 0
                    : p[o]) !== !1
            );
        },
        v = ((u = r == null ? void 0 : r.config_sections) == null
            ? void 0
            : u.section_order) || [
            "header",
            "hero",
            "modules",
            "dedication",
            "screenshots",
            "why_choose",
            "cta",
            "footer",
        ],
        C = (o) => {
            if (!k(o)) return null;
            switch ((j(o), o)) {
                case "header":
                    return m.jsx(M, { settings: c }, o);
                case "hero":
                    return m.jsx(y, { settings: r, matchedPackage: x }, o);
                case "modules":
                    return m.jsx(D, { packages: h, settings: r }, o);
                case "dedication":
                    return m.jsx(E, { settings: r }, o);
                case "screenshots":
                    return m.jsx(F, { settings: r }, o);
                case "why_choose":
                    return m.jsx(R, { settings: r }, o);
                case "cta":
                    return m.jsx(w, { settings: c }, o);
                case "footer":
                    return m.jsx(A, { settings: c }, o);
                default:
                    return null;
            }
        },
        a = ((d = e == null ? void 0 : e.config_sections) == null
            ? void 0
            : d.colors) || {
            primary: "#10b981",
            secondary: "#059669",
            accent: "#065f46",
        };
    return m.jsxs("div", {
        className:
            "min-h-screen bg-slate-50/70 font-['Plus_Jakarta_Sans',sans-serif]",
        style: {
            "--color-primary": a.primary,
            "--color-secondary": a.secondary,
            "--color-accent": a.accent,
        },
        children: [
            m.jsx(b, {
                title: `${(r == null ? void 0 : r.title) || "Automas CRM Marketplace"} - Premium Packages`,
                children:
                    n &&
                    m.jsx("link", {
                        rel: "icon",
                        type: "image/x-icon",
                        href: n,
                    }),
            }),
            v.map((o) => C(o)),
            m.jsx(H, { settings: _ || {} }),
        ],
    });
}
export { wo as default };
