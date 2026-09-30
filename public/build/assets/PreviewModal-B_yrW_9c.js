import{b as A,j as m,R as we}from"./ui-BSZZ9r9D.js";import{X as _e}from"./app-BaZ18SVS.js";import{D as ve,a as $e,b as Ne,c as Te}from"./dialog-CCdZNezA.js";import{B as ne}from"./button-CNAj7G3n.js";import{c as _t,f as pt,b as ie}from"./helpers-DP60IMUj.js";import{r as oe}from"./proposalShortcodes-9vrSW96k.js";import{c as mt}from"./utils-DqweA7RH.js";import{u as ke}from"./useTranslation-DHrMWs-f.js";import{F as He}from"./file-text-Bv-btBOu.js";import{P as re}from"./printer-BR8Z5BlF.js";import{E as ze}from"./eye-BS4w1PoR.js";const me="#E9591C",ue="html-preview-container";function se(e){return e?/<style|<link\s+rel|<!doctype|<html|<head|<svg|position:\s*absolute|297mm|210mm/i.test(e):!1}function he(e,i){let o=e.replace(/@(media|supports)\b[^{]*\{([\s\S]*?\})\s*\}/gi,(d,t,r)=>{const u=d.slice(0,d.indexOf("{")+1),p=he(r,i);return`${u}
${p}
}`});return o=o.replace(/([^{}@]+)\{([^}]+)\}/g,(d,t,r)=>{const u=t.trim();return u.startsWith("@")?d:`${u.split(",").map(z=>{let w=z.trim();return w?/^(html|body|:root)$/i.test(w)?i:/^(html|body|:root)[\s>+~]/i.test(w)?w.replace(/^(html|body|:root)([\s>+~])/i,`${i}$2`):w.startsWith(i)?w:`${i} ${w}`:""}).filter(Boolean).join(", ")} {${r}}`}),o}function Vt(e,i=".proposal-preview-sheet"){if(!e)return"";let o=e;return o=o.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,"").replace(/on\w+\s*=\s*(["'][^"']*["']|[^\s>]+)/gi,""),o=o.replace(/<link\b[^>]*>/gi,""),o=o.replace(/<!doctype[^>]*>/gi,"").replace(/<\/?(html|head|meta|title)\b[^>]*>/gi,""),o=o.replace(/<body\b([^>]*)>/gi,'<div class="proposal-body-wrapper" $1>'),o=o.replace(/<\/body>/gi,"</div>"),o=o.replace(/<style\b([^>]*)>([\s\S]*?)<\/style>/gi,(d,t,r)=>{const u=he(r,i);return`<style${t}>${u}</style>`}),o}const Ce=210,Rt=297,It=32,Ft=30,Se=15,le=`
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
`;function ct(e){if(typeof document>"u")return e*3.7795275591;const i=document.createElement("div");i.style.cssText=`
        position: absolute;
        visibility: hidden;
        pointer-events: none;
        width: ${e}mm;
        height: 0;
        padding: 0;
        margin: 0;
        border: 0;
        left: -10000px;
        top: -10000px;
    `,document.body.appendChild(i);const o=i.getBoundingClientRect().width;return i.remove(),o||e*3.7795275591}function St(){if(typeof document>"u")return ct(Rt)-ct(It)-ct(Ft);const e=document.createElement("div");e.style.cssText=`
        position: absolute;
        visibility: hidden;
        pointer-events: none;
        left: -10000px;
        top: -10000px;

        width: ${Ce}mm;
        height: ${Rt}mm;

        box-sizing: border-box;

        padding:
            ${It}mm
            ${Se}mm
            ${Ft}mm;

        display: flex;
        flex-direction: column;

        margin: 0;
        border: 0;
    `;const i=document.createElement("div");i.style.cssText=`
        width: 100%;
        flex: 1 1 auto;
        min-height: 0;
        box-sizing: border-box;
    `,e.appendChild(i),document.body.appendChild(e);const o=i.getBoundingClientRect().height;return e.remove(),o||ct(Rt)-ct(It)-ct(Ft)}const Q=1;function je(e){if(typeof window>"u")return 0;const i=window.getComputedStyle(e);return(parseFloat(i.marginTop)||0)+(parseFloat(i.marginBottom)||0)}function Me(e){const i=e.getBoundingClientRect();return i.height>0?i.height:e.offsetHeight||0}function Ct(e){return Me(e)+je(e)}function B(e,i){return i===void 0||/data-proposal-section-index=/.test(e)?e:e.replace(/^<(\w+)(\s|>)/,`<$1 data-proposal-section-index="${ut(i)}"$2`)}function ut(e){return e.replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/</g,"&lt;").replace(/>/g,"&gt;")}function Ee(e){var i,o,d,t,r;return((i=e.classList)==null?void 0:i.contains("page-break"))||((o=e.style)==null?void 0:o.pageBreakAfter)==="always"||((d=e.style)==null?void 0:d.pageBreakBefore)==="always"||((t=e.style)==null?void 0:t.breakAfter)==="page"||((r=e.style)==null?void 0:r.breakBefore)==="page"}function Ae(e){var i,o,d,t,r;return e.hasAttribute("data-full-page")||((i=e.classList)==null?void 0:i.contains("cover-page-wrapper"))||((d=(o=e.style)==null?void 0:o.height)==null?void 0:d.includes("297mm"))||((r=(t=e.style)==null?void 0:t.minHeight)==null?void 0:r.includes("297mm"))}function Le(e){const i=e.tagName.toLowerCase();return(i==="div"||i==="section"||i==="article"||i==="main")&&e.children.length>0&&!e.classList.contains("page-break")}function Y(e,i){const o=document.createElement("div");o.style.cssText=`
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
    `,o.innerHTML=i,e.appendChild(o);const d=o.getBoundingClientRect().height,t=o.scrollHeight;return o.remove(),Math.max(d,t,0)}function ae(e,i){const o=e.cloneNode(!1);return i.forEach(d=>{o.appendChild(d.cloneNode(!0))}),o.outerHTML}function de(e){const i=Array.from(e.children).find(o=>o.tagName.toLowerCase()==="table");return i||null}function pe(e,i){const o=Array.from(e.children),d=o.indexOf(i);if(d<=0)return null;const t=o[d-1];if(!t)return null;const r=t.tagName.toLowerCase();return r==="div"||r==="h1"||r==="h2"||r==="h3"||r==="h4"||r==="h5"||r==="h6"||r==="p"?t:null}function Pe(e,i,o){const d=Array.from(e.childNodes);if(d.length!==1)return null;const t=d[0];if(t.nodeType!==Node.TEXT_NODE)return null;const r=t.textContent||"";if(!r.trim())return null;const u=r.split(/(\s+)/);if(u.length<=1)return null;const p=[];let b="";const z=w=>{const s=e.cloneNode(!1);return s.textContent=w,Y(i,s.outerHTML)<=o+Q};for(const w of u){const s=b+w;b.trim()&&!z(s)?(p.push(b.trim()),b=w):b=s}return b.trim()&&p.push(b.trim()),p.length<=1?null:p.map(w=>{const s=e.cloneNode(!1);return s.textContent=w,s.outerHTML})}function Oe(e,i,o){const d=Array.from(e.childNodes);if(d.length<=1)return null;const t=[];let r=[];const u=()=>{r.length!==0&&(t.push(ae(e,r)),r=[])};for(const p of d){const b=[...r,p],z=ae(e,b),w=Y(i,z);r.length>0&&w>o+Q?(u(),r=[p]):r.push(p)}return u(),t.length>1?t:null}function ce(e,i,o,d){const t=Oe(e,i,o);if(t&&t.length>1)return t.map(p=>B(p,d));const r=Pe(e,i,o);if(r&&r.length>1)return r.map(p=>B(p,d));const u=e.cloneNode(!0);return u.classList.add("proposal-pagination-splittable"),[B(u.outerHTML,d)]}function qt(e,i,o,d,t,r){const u=e.getAttribute("class")||"",p=e.getAttribute("style")||"",b=r!==void 0?` data-proposal-section-index="${ut(r)}"`:"";return`<table class="${ut(u)}"${b} style="${ut(p)}">`+o+`<tbody>${i.join("")}</tbody>`+(t?d:"")+"</table>"}function Gt(e,i=St()){const o=Array.from(e.querySelectorAll("style")).map(s=>s.outerHTML).join(`
`),d=[];let t=[],r=0;const u=Math.max(1,i-Q),p=()=>{t.length!==0&&(d.push((o?o+`
`:"")+t.join("")),t=[],r=0)},b=(s,k)=>{t.push(s),r+=k},z=(s,k,G,g)=>{const H=k.querySelector("thead"),R=k.querySelector("tfoot"),I=Array.from(k.querySelectorAll("tbody > tr")),D=H?H.outerHTML:"",f=R?R.outerHTML:"",j=G?B(G.outerHTML,g):"";if(I.length===0){const x=qt(k,[],D,f,!0,g),M=j+x,C=Y(e,M);r>0&&r+C>u+Q&&p(),b(M,C);return}let _=0,a=!0;for(;_<I.length;){const x=[];for(;_<I.length;){const W=I[_],vt=[...x,W.outerHTML],it=_===I.length-1,Z=qt(k,vt,D,f,it,g),ot=a?j+Z:Z,P=Y(e,ot);if(r+P<=u+Q){x.push(W.outerHTML),_++;continue}if(x.length>0)break;if(t.length>0&&r>0){p();continue}const O=W.cloneNode(!0);O.classList.add("proposal-oversized-row"),x.push(O.outerHTML),_++;break}if(x.length===0)break;const M=_>=I.length,C=qt(k,x,D,f,M,g),V=a?j+C:C,y=Y(e,V);r>0&&r+y>u+Q&&p(),b(V,y),a=!1,M||p()}},w=(s,k)=>{const g=s.getAttribute("data-proposal-section-index")??k,H=s.tagName.toLowerCase();if(H==="style"||H==="script")return;if(Ee(s)){p();const f=de(s);if(f){const a=pe(s,f);z(s,f,a,g);const x=Array.from(s.children),M=x.indexOf(f);for(let C=M+1;C<x.length;C++)w(x[C],g);return}const j=B(s.outerHTML,g),_=Ct(s);b(j,_),p();return}if(Ae(s)){t.length>0&&p();const f=B(s.outerHTML,g);t.push(f),r=Ct(s),p();return}if(H==="table"){z(s,s,null,g);return}const R=de(s);if(R){const f=Array.from(s.children),j=f.indexOf(R),_=pe(s,R),a=_?f.indexOf(_):j;for(let x=0;x<Math.max(0,a);x++)w(f[x],g);z(s,R,_,g);for(let x=j+1;x<f.length;x++)w(f[x],g);return}if((H==="ul"||H==="ol")&&s.children.length>0){const f=s.getAttribute("class")||"",j=s.getAttribute("style")||"",_=Array.from(s.children);let a=[];const x=()=>{if(a.length===0)return;const M=`<${H} class="${ut(f)}" style="${ut(j)}">`+a.join("")+`</${H}>`,C=Y(e,M);b(B(M,g),C),a=[]};for(let M=0;M<_.length;M++){const C=_[M],V=[...a,B(C.outerHTML,g)],y=`<${H}>${V.join("")}</${H}>`,W=Y(e,y);if(r+W>u+Q){if(a.length>0&&(x(),p()),Y(e,`<${H}>${B(C.outerHTML,g)}</${H}>`)<=u+Q){a.push(B(C.outerHTML,g));continue}const it=ce(C,e,u,g);it.forEach((Z,ot)=>{t.length>0&&r>0&&p();const P=Y(e,Z);b(Z,P),ot<it.length-1&&p()});continue}a.push(B(C.outerHTML,g))}x();return}if(Le(s)){const f=Ct(s);if(r+f<=u+Q){b(B(s.outerHTML,g),f);return}const j=Array.from(s.children);if(j.length>0){j.forEach(_=>w(_,g));return}}const I=Ct(s);if(r+I<=u+Q){b(B(s.outerHTML,g),I);return}if(t.length>0&&p(),I<=u+Q){b(B(s.outerHTML,g),I);return}const D=ce(s,e,u,g);D.forEach((f,j)=>{t.length>0&&r>0&&p();const _=Y(e,f);b(f,_),j<D.length-1&&p()})};return Array.from(e.children).forEach(s=>{w(s)}),t.length>0&&p(),d.length>0?d:[e.innerHTML]}const T=(e,i)=>{try{const o=Number(e)||0,d=parseInt(_t("decimalFormat",i)||"2"),t=_t("decimalSeparator",i)||".",r=_t("thousandsSeparator",i)||",";let p=_t("floatNumber",i)!=="0"?o:Math.floor(o);const b=Number(p).toFixed(d).split(".");return r!=="none"&&(b[0]=b[0].replace(/\B(?=(\d{3})+(?!\d))/g,r)),b.join(t)}catch{return Number(e||0).toFixed(2)}},nt=(e,i=10)=>{const d=e.replace(/<[^>]*>/g,"").length;return d>18?"7.5px":d>15?"8.5px":d>12?"9.5px":`${i}px`},jt=we.memo(({children:e,content:i,backgroundImage:o,defaultBg:d,templateColor:t=me,headerLogo:r,headerLogoAlign:u="right",pageKey:p,className:b="",customHtml:z=!1})=>{const w=o&&String(o).trim()!==""?o:d,s=w?ie(w):"",k=r?ie(r):"",G=()=>{const g=u||"right";return g==="left"?{top:"8mm",left:"15mm",right:"auto",justifyContent:"flex-start",maxHeight:"20mm",maxWidth:"60mm"}:g==="center"||g==="middle"?{top:"8mm",left:"50%",right:"auto",transform:"translateX(-50%)",justifyContent:"center",maxHeight:"20mm",maxWidth:"60mm"}:{top:"8mm",right:"15mm",left:"auto",justifyContent:"flex-end",maxHeight:"20mm",maxWidth:"60mm"}};return m.jsxs("div",{style:{width:"210mm",...z?{minHeight:"297mm",boxSizing:"border-box"}:{height:"297mm",minHeight:"297mm",maxHeight:"297mm",boxSizing:"border-box",overflow:"hidden"},pageBreakAfter:"always",breakAfter:"page",pageBreakInside:"avoid",breakInside:"avoid-page",fontFamily:'"Open Sans", sans-serif',"--template-color":t},className:mt("proposal-preview-sheet proposal-cover__sheet bg-white text-slate-900 w-[210mm] max-w-full shadow-2xl rounded-sm text-sm border border-slate-300 dark:border-slate-800 shrink-0 relative",!z&&"h-[297mm] overflow-hidden",b),children:[s&&m.jsx("div",{className:"absolute inset-0 w-full h-full z-0 pointer-events-none overflow-hidden",children:m.jsx("img",{src:s,alt:"Page Background",className:"w-full h-full object-fill block",onError:g=>{const H=g.currentTarget,R=String(o||d||"").split("/").pop();R&&!H.src.includes("/storage/media/")&&(H.src=`/storage/media/${R}`)}})}),k&&m.jsx("div",{className:"absolute z-20 pointer-events-none flex items-center",style:G(),children:m.jsx("img",{src:k,alt:"Header Logo",className:"max-h-[16mm] max-w-[55mm] object-contain"})}),m.jsx("div",{className:mt(!z&&"proposal-page__body",z&&"w-full h-full p-0 m-0"),style:{position:"relative",zIndex:1,...z?{padding:0,margin:0,width:"100%",minHeight:"297mm",boxSizing:"border-box",display:"block"}:{padding:"32mm 15mm 20mm",height:"calc(297mm - 52mm)",minHeight:"calc(297mm - 52mm)",maxHeight:"calc(297mm - 52mm)",boxSizing:"border-box",display:"flex",flexDirection:"column",justifyContent:"flex-start"}},children:e||(i?m.jsx("div",{className:mt(!z&&mt("html-preview-container flex-1 flex flex-col",ue),z&&"w-full h-full"),style:z?{width:"100%",height:"100%"}:{display:"flex",flexDirection:"column",flex:1,width:"100%"},dangerouslySetInnerHTML:{__html:Vt(i)}}):null)})]},p)});jt.displayName="ProposalPreviewSheet";function Ye({isOpen:e,open:i,onClose:o,onOpenChange:d,formData:t,sections:r=[],customers:u=[],availableProducts:p=[],proposalSetting:b,totals:z,other_details:w,title:s,pageTitle:k,content:G,backgroundImage:g,settings:H,isDefaultPageSetup:R,showPrintButton:I=!0,customHtml:D=!1,inline:f=!1,autoPrint:j=!1,hideHeaderBar:_=!1}){var Yt;const{t:a}=ke(),x=((Yt=_e())==null?void 0:Yt.props)||{},M=!!(e??i);A.useEffect(()=>{var N;const n=(t==null?void 0:t.subject)||s||k||"",h=(t==null?void 0:t.customer_name)||(u&&u.length>0?(N=u[0])==null?void 0:N.name:"")||"",v=[n,h].filter(Boolean),l=v.length>0?v.join("_"):t!=null&&t.proposal_number?String(t.proposal_number):s||"";if(f&&l&&(document.title=l),f&&j){const $=setTimeout(()=>{l&&(document.title=l),window.onafterprint=()=>{window.close()},window.print()},600);return()=>clearTimeout($)}},[f,j,t,u,s,k]);const C=A.useCallback(()=>{o&&o(),d&&d(!1)},[o,d]);A.useRef(null);const V=A.useRef(null),y=A.useMemo(()=>H||b||(x==null?void 0:x.proposalSetting)||(x==null?void 0:x.quotationSetting)||{},[H,b,x]),W=(y==null?void 0:y.template_color)||me,vt=(y==null?void 0:y.show_logo)!==void 0?y.show_logo==="1"||y.show_logo===!0||y.show_logo===1||y.show_logo==="true":!0,it=(y==null?void 0:y.logo_image)||(y==null?void 0:y.company_logo)||"",Z=vt&&it?it:"",ot=(y==null?void 0:y.header_logo_align)||"right",P=(y==null?void 0:y.background_image)||"",O=!t&&(G!==void 0||s!==void 0||k!==void 0),$t=!!(D||O&&G&&se(G)),rt=A.useMemo(()=>{if(!O)return"";const n=(G||"").trim();if(!n&&(g||P))return"&nbsp;";if(!n)return"";const h=oe(G,{settings:y,pageProps:x,isDefaultPageSetup:R??!0});return Vt(h)},[O,G,g,P,y,R]),[Wt,ht]=A.useState([]),[Ut,Mt]=A.useState([]),[ge,Et]=A.useState([]),[xe,Xt]=A.useState([]);A.useEffect(()=>{if(!O)return;if(!rt){ht([]);return}const n=()=>{if($t){if(/class=["'][^"']*page-break[^"']*["']|style=["'][^"']*(?:page-break|break-after|break-before)[^"']*["']/i.test(rt)&&V.current){const N=Gt(V.current,St());ht(N)}else ht([rt]);return}if(V.current){const l=Gt(V.current,St());ht(l)}else ht([rt])};let h=!1;return(async()=>{var l;(l=document.fonts)!=null&&l.ready&&await document.fonts.ready,h||n()})(),()=>{h=!0}},[O,rt,M,f,$t]);const At=A.useCallback(n=>{var h;if(n.product_name)return n.product_name;if(n.name)return n.name;if((h=n.product)!=null&&h.name)return n.product.name;if(n.product_id&&p.length>0){const v=p.find(l=>String(l.id)===String(n.product_id));if(v!=null&&v.name)return v.name}return n.product_description||n.description||a("Item / Service")},[p,a]),Lt=A.useCallback(n=>{var h;if(n.description)return n.description;if(n.product_description)return n.product_description;if((h=n.product)!=null&&h.description)return n.product.description;if(n.product_id&&p.length>0){const v=p.find(l=>String(l.id)===String(n.product_id));if(v!=null&&v.description)return v.description}return""},[p]),Pt=A.useCallback(n=>{var h,v,l,N;if(n.unit_name)return n.unit_name;if(n.unit&&isNaN(Number(n.unit)))return n.unit;if((v=(h=n.product)==null?void 0:h.unit_relation)!=null&&v.unit_name)return n.product.unit_relation.unit_name;if((l=n.product)!=null&&l.unit_name)return n.product.unit_name;if((N=n.product)!=null&&N.unit&&isNaN(Number(n.product.unit)))return n.product.unit;if(n.product_id&&p.length>0){const $=p.find(E=>String(E.id)===String(n.product_id));if($!=null&&$.unit_name)return $.unit_name;if($!=null&&$.unit&&isNaN(Number($.unit)))return $.unit}return""},[p]),gt=A.useMemo(()=>(t==null?void 0:t.customer_mode)==="new"||(t==null?void 0:t.customer_type)==="new"||!(t!=null&&t.customer_id)&&!!(t!=null&&t.customer_name||t!=null&&t.customer_email)?{id:0,name:(t==null?void 0:t.customer_name)||"",email:(t==null?void 0:t.customer_email)||"",mobile_no:(t==null?void 0:t.customer_phone)||"",phone:(t==null?void 0:t.customer_phone)||"",address:(t==null?void 0:t.customer_address)||"",type:(t==null?void 0:t.customer_type)||"Individual"}:u.find(h=>String(h.id)===String(t==null?void 0:t.customer_id))||(t!=null&&t.customer_name?{id:Number(t==null?void 0:t.customer_id)||0,name:(t==null?void 0:t.customer_name)||"",email:(t==null?void 0:t.customer_email)||"",mobile_no:(t==null?void 0:t.customer_phone)||"",phone:(t==null?void 0:t.customer_phone)||"",address:(t==null?void 0:t.customer_address)||"",type:(t==null?void 0:t.customer_type)||"Individual"}:void 0),[u,t==null?void 0:t.customer_id,t==null?void 0:t.customer_mode,t==null?void 0:t.customer_type,t==null?void 0:t.customer_name,t==null?void 0:t.customer_email,t==null?void 0:t.customer_phone,t==null?void 0:t.customer_address]),xt=A.useMemo(()=>{var Jt;if(O||!t)return"";const n=(typeof window<"u"?(Jt=window==null?void 0:window.__INITIAL_PAGE__)==null?void 0:Jt.props:null)||{},h=_t("defaultCurrency",n)||"BDT",v=t.items||[],l=v.filter(c=>(c.section==="otc"||c.section==="general"||!c.section)&&(Number(c.product_id)>0||Number(c.unit_price)>0||!!c.product_description||!!c.description)),N=v.filter(c=>c.section==="mrc"&&(Number(c.product_id)>0||Number(c.unit_price)>0||!!c.product_description||!!c.description)),$=l.reduce((c,S)=>c+Number(S.quantity??1)*Number(S.unit_price||0),0),E=l.reduce((c,S)=>c+Number(S.discount_amount||0),0);let L=E;if(E===0&&Number(t.otc_discount_value)>0){const c=Number(t.otc_discount_value)||0;t.otc_discount_type==="percentage"?L=$*Math.min(Math.max(c,0),100)/100:L=Math.min(Math.max(c,0),$)}const U=l.reduce((c,S)=>c+Number(S.tax_amount||0),0),X=Math.max(0,$-L+U),st=N.reduce((c,S)=>c+Number(S.quantity??1)*Number(S.unit_price||0),0),Nt=N.reduce((c,S)=>c+Number(S.discount_amount||0),0);let dt=Nt;if(Nt===0&&Number(t.mrc_discount_value)>0){const c=Number(t.mrc_discount_value)||0;t.mrc_discount_type==="percentage"?dt=st*Math.min(Math.max(c,0),100)/100:dt=Math.min(Math.max(c,0),st)}const Tt=N.reduce((c,S)=>c+Number(S.tax_amount||0),0),Zt=Math.max(0,st-dt+Tt),bt=[];r.forEach((c,S)=>{const ft=(c.content||"").trim(),Ot=(c.page_type||"").toLowerCase(),Dt=Ot==="otc"||ft==="[OTC_CHARGES_TABLE]"||c.title&&c.title.toLowerCase().includes("one-time charges"),te=Ot==="mrc"||ft==="[MRC_CHARGES_TABLE]"||c.title&&c.title.toLowerCase().includes("monthly recurring charges"),ee=Ot==="other-details"||ft==="[OTHER_DETAILS_CONTENT]"||c.title&&c.title.toLowerCase().includes("other details");if(Dt){if(l.length===0)return;const tt=c.title||a("ONE-TIME CHARGES (OTC)");let lt="";l.forEach((F,q)=>{const kt=Number(F.quantity??1);Pt(F);const yt=Number(F.unit_price)||0,Ht=F.total_amount!==void 0?Number(F.total_amount):kt*yt,Bt=Lt(F),zt=Number(F.tax_amount)||0,at=Number(F.discount_percentage)||0,K=Number(F.discount_amount)||0,J=F.discount_type||"percentage";let et="-";J==="percentage"&&at>0?et=`<div>${at}%</div>${K>0?`<div style="font-size: 9px; color: #64748b;">(${pt(K,n)})</div>`:""}`:J==="fixed"&&K>0?et=pt(K,n):at>0?et=`<div>${at}%</div>`:K>0&&(et=pt(K,n)),lt+=`
                        <tr class="border-b border-slate-200 hover:bg-slate-50/50">
                            <td class="text-center font-medium border border-slate-200" style="font-size: 10px; padding: 6.5px 4px !important;">${q+1}</td>
                            <td class="font-semibold text-slate-900 border border-slate-200 align-top" style="font-size: 11px; padding: 6.5px 8px !important; line-height: 1.35;">${At(F)}</td>
                            <td class="text-slate-600 border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">
                                <div class="leading-normal break-words [&_ul]:list-disc [&_ul]:pl-4 [&_ul]:my-0.5 [&_ol]:list-decimal [&_ol]:pl-4 [&_ol]:my-0.5 [&_li]:my-0.5 [&_li]:list-item [&_li_p]:inline [&_li_p]:m-0 [&_p]:my-0.5 [&_p:first-child]:mt-0 [&_p:last-child]:mb-0">
                                    ${Bt||"-"}
                                </div>
                            </td>
                            <td class="text-center border border-slate-200 align-top whitespace-nowrap" style="font-size: 10px; padding: 6.5px 4px !important;">${kt}</td>
                            <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${T(yt,n)}</td>
                            <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${et}</td>
                            <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${zt>0?T(zt,n):"-"}</td>
                            <td class="text-right font-medium text-slate-900 border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${T(Ht,n)}</td>
                        </tr>
                    `}),bt.push(`
                    <div class="proposal-section-block otc-charges-block" data-proposal-section-index="${S}" style="margin-top: 1.5rem; margin-bottom: 1.25rem;">
                        <div class="font-bold mb-2 text-[#293240] text-sm">${tt}</div>
                        <table class="charges-table w-full text-xs mb-2 border-collapse border border-slate-300" style="font-size: 11px; width: 100%; table-layout: fixed;">
                            <thead>
                                <tr class="text-center font-semibold" style="background-color: ${W}; color: #ffffff;">
                                    <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 5%; white-space: nowrap; padding: 7.5px 4px !important;">${a("S/N")}</th>
                                    <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 15%; padding: 7.5px 8px !important;">${a("Item / Service")}</th>
                                    <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 27%; padding: 7.5px 8px !important;">${a("Description")}</th>
                                    <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 6%; white-space: nowrap; padding: 7.5px 4px !important;">${a("Qty.")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${a("Price")} (${h})</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 10%; white-space: nowrap; padding: 7.5px 8px !important;">${a("Discount")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 10%; white-space: nowrap; padding: 7.5px 8px !important;">${a("Tax / VAT")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 13%; white-space: nowrap; padding: 7.5px 8px !important;">${a("Total")} (${h})</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${lt}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${a("Subtotal")}:</td>
                                    <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${nt(T($,n),10)}; padding: 6px 8px !important;">${T($,n)}</td>
                                </tr>
                                ${L>0?`
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${a("Discount")}:</td>
                                    <td class="text-right text-rose-600 font-semibold border border-slate-200" style="font-size: ${nt(`(-) ${T(L,n)}`,10)}; padding: 6px 8px !important;">(-) ${T(L,n)}</td>
                                </tr>`:""}
                                ${U>0?`
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${a("Tax / VAT")}:</td>
                                    <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${nt(`(+) ${T(U,n)}`,10)}; padding: 6px 8px !important;">(+) ${T(U,n)}</td>
                                </tr>`:""}
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-bold text-slate-900 border border-slate-200 text-right" style="font-size: 10px; padding: 7px 8px !important;">${a("Total")}:</td>
                                    <td class="text-right font-bold text-slate-900 border border-slate-200" style="font-size: ${nt(`${T(X,n)} ${h}`,10)}; padding: 7px 8px !important;">${T(X,n)} ${h}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `);return}if(te){if(N.length===0)return;const tt=c.title||a("MONTHLY RECURRING CHARGES (MRC)");let lt="";N.forEach((q,kt)=>{const yt=Number(q.quantity??1);Pt(q);const Ht=Number(q.unit_price)||0,Bt=q.total_amount!==void 0?Number(q.total_amount):yt*Ht,zt=Lt(q),at=Number(q.tax_amount)||0,K=Number(q.discount_percentage)||0,J=Number(q.discount_amount)||0,et=q.discount_type||"percentage";let wt="-";et==="percentage"&&K>0?wt=`<div>${K}%</div>${J>0?`<div style="font-size: 9px; color: #64748b;">(${pt(J,n)})</div>`:""}`:et==="fixed"&&J>0?wt=pt(J,n):K>0?wt=`<div>${K}%</div>`:J>0&&(wt=pt(J,n)),lt+=`
                        <tr class="border-b border-slate-200 hover:bg-slate-50/50">
                            <td class="text-center font-medium border border-slate-200" style="font-size: 10px; padding: 6.5px 4px !important;">${kt+1}</td>
                            <td class="font-semibold text-slate-900 border border-slate-200 align-top" style="font-size: 11px; padding: 6.5px 8px !important; line-height: 1.35;">${At(q)}</td>
                            <td class="text-slate-600 border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">
                                <div class="leading-normal break-words [&_ul]:list-disc [&_ul]:pl-4 [&_ul]:my-0.5 [&_ol]:list-decimal [&_ol]:pl-4 [&_ol]:my-0.5 [&_li]:my-0.5 [&_li]:list-item [&_li_p]:inline [&_li_p]:m-0 [&_p]:my-0 [&_p:first-child]:mt-0 [&_p:last-child]:mb-0">
                                    ${zt||"-"}
                                </div>
                            </td>
                            <td class="text-center border border-slate-200 align-top whitespace-nowrap" style="font-size: 10px; padding: 6.5px 4px !important;">${yt}</td>
                            <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${T(Ht,n)}</td>
                            <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${wt}</td>
                            <td class="text-right border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${at>0?T(at,n):"-"}</td>
                            <td class="text-right font-medium text-slate-900 border border-slate-200 align-top" style="font-size: 10px; padding: 6.5px 8px !important;">${T(Bt,n)}</td>
                        </tr>
                    `});const F=S===0?"":"margin-top: 2rem;";bt.push(`
                    <div class="proposal-section-block mrc-charges-block" data-proposal-section-index="${S}" style="margin-bottom: 1.25rem;">
                        <div class="font-bold mb-2 text-[#293240] text-sm" style="${F}">${tt}</div>
                        <table class="charges-table w-full text-xs mb-2 border-collapse border border-slate-300" style="font-size: 11px; width: 100%; table-layout: fixed;">
                            <thead>
                                <tr class="text-center font-semibold" style="background-color: ${W}; color: #ffffff;">
                                    <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 5%; white-space: nowrap; padding: 7.5px 4px !important;">${a("S/N")}</th>
                                    <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 15%; padding: 7.5px 8px !important;">${a("Item / Service")}</th>
                                    <th class="border border-slate-300 text-white text-left" style="font-size: 10px; width: 25%; padding: 7.5px 8px !important;">${a("Description")}</th>
                                    <th class="border border-slate-300 text-white text-center" style="font-size: 10px; width: 6%; white-space: nowrap; padding: 7.5px 4px !important;">${a("Qty.")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${a("Price")} (${h})</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${a("Discount")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 12%; white-space: nowrap; padding: 7.5px 8px !important;">${a("Tax / VAT")}</th>
                                    <th class="border border-slate-300 text-white text-right" style="font-size: 10px; width: 13%; white-space: nowrap; padding: 7.5px 8px !important;">${a("Total")} (${h})</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${lt}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${a("Subtotal")}:</td>
                                    <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${nt(T(st,n),10)}; padding: 6px 8px !important;">${T(st,n)}</td>
                                </tr>
                                ${dt>0?`
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${a("Discount")}:</td>
                                    <td class="text-right text-rose-600 font-semibold border border-slate-200" style="font-size: ${nt(`(-) ${T(dt,n)}`,10)}; padding: 6px 8px !important;">(-) ${T(dt,n)}</td>
                                </tr>`:""}
                                ${Tt>0?`
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-medium text-slate-700 bg-slate-50 border border-slate-200 text-right" style="font-size: 10px; padding: 6px 8px !important;">${a("Tax / VAT")}:</td>
                                    <td class="text-right text-slate-900 font-semibold border border-slate-200" style="font-size: ${nt(`(+) ${T(Tt,n)}`,10)}; padding: 6px 8px !important;">(+) ${T(Tt,n)}</td>
                                </tr>`:""}
                                <tr>
                                    <td colspan="5" class="border border-slate-200"></td>
                                    <td colspan="2" class="font-bold text-slate-900 border border-slate-200 text-right" style="font-size: 10px; padding: 7px 8px !important;">${a("Total")}:</td>
                                    <td class="text-right font-bold text-slate-900 border border-slate-200" style="font-size: ${nt(`${T(Zt,n)} ${h}`,10)}; padding: 7px 8px !important;">${T(Zt,n)} ${h}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `);return}if(ee){const tt=t.other_details||w||"";if(!tt)return;const lt=c.title||a("OTHER DETAILS");bt.push(`
                    <div class="proposal-section-block other-details-block" data-proposal-section-index="${S}" style="margin-top: 1.5rem; margin-bottom: 1.25rem;">
                        <div class="font-bold mb-2 text-[#293240] text-sm">${lt}</div>
                        <div class="prose max-w-none text-xs leading-relaxed text-slate-700">
                            ${tt}
                        </div>
                    </div>
                `);return}if(c.page_type==="custom"||!Dt&&!te&&!ee){const tt=c.background_image||"";(ft||tt)&&bt.push(`
                        <div class="proposal-section-block custom-section-block page-break" data-full-page="true" data-proposal-section-index="${S}" style="min-height: 297mm; height: 100%;">
                            ${ft||"&nbsp;"}
                        </div>
                    `)}});const fe=bt.join(`

`),ye=oe(fe,{proposal:t,customer:gt,settings:y,pageProps:n||{},isDefaultPageSetup:!1});return Vt(ye)},[O,t,r,u,At,Lt,Pt,a,W,y,w]);A.useEffect(()=>{if(O||!xt){Mt(l=>l.length===0?l:[]),Et(l=>l.length===0?l:[]);return}const n=()=>{if(V.current){const l=Gt(V.current,St());Mt(E=>E.length===l.length&&E.every((L,U)=>L===l[U])?E:l);const N=[],$=[];l.forEach(E=>{const L=E.match(/data-proposal-section-index=["'](\d+)["']/);if(L&&L[1]!==void 0){const U=parseInt(L[1],10),X=r[U];X!=null&&X.background_image&&X.background_image.trim()!==""?N.push(X.background_image):N.push(P);const st=(X==null?void 0:X.content)||"",Nt=se(st);$.push(Nt);return}N.push(P),$.push(!1)}),Et(E=>E.length===N.length&&E.every((L,U)=>L===N[U])?E:N),Xt(E=>E.length===$.length&&E.every((L,U)=>L===$[U])?E:$)}else Mt(l=>l.length===1&&l[0]===xt?l:[xt]),Et(l=>l.length===1&&l[0]===P?l:[P]),Xt(l=>l.length===1&&l[0]===!1?l:[!1])};let h=!1;return(async()=>{var l;(l=document.fonts)!=null&&l.ready&&await document.fonts.ready,h||n()})(),()=>{h=!0}},[O,xt,r,P,M,f]);const be=A.useCallback(()=>{const n=(t==null?void 0:t.subject)||s||k||"",h=(gt==null?void 0:gt.name)||(t==null?void 0:t.customer_name)||"",v=[n,h].filter(Boolean),l=v.length>0?v.join("_"):t!=null&&t.proposal_number?String(t.proposal_number):document.title,N=document.title;l&&(document.title=l),window.print(),setTimeout(()=>{document.title=N},1e3)},[t,gt,s,k]),Kt=s||k||(t==null?void 0:t.subject)||a("Preview"),Qt=()=>m.jsx("div",{className:"flex flex-col gap-6 items-center w-full print:gap-0 print:block",children:O?Wt.length>0?Wt.map((n,h)=>m.jsx(jt,{pageKey:`single-page-${h}`,backgroundImage:g,defaultBg:P,templateColor:W,headerLogo:Z,headerLogoAlign:ot,content:n,customHtml:$t},`single-page-${h}`)):m.jsx(jt,{pageKey:"single-page-0",backgroundImage:g,defaultBg:P,templateColor:W,headerLogo:Z,headerLogoAlign:ot,content:rt,customHtml:$t},"single-page-0"):Ut.length>0?Ut.map((n,h)=>m.jsx(jt,{pageKey:`proposal-page-${h}`,backgroundImage:ge[h]||P,defaultBg:P,templateColor:W,headerLogo:Z,headerLogoAlign:ot,content:n,customHtml:!!xe[h]},`proposal-page-${h}`)):m.jsx("div",{className:"p-8 text-center text-slate-500",children:a("No pages configured in Page Order.")})});return m.jsxs(m.Fragment,{children:[m.jsx("div",{ref:V,className:mt("html-preview-container",ue),style:{position:"fixed",left:"-9999px",top:0,width:"180mm",visibility:"hidden",pointerEvents:"none",zIndex:-1},dangerouslySetInnerHTML:{__html:O?rt:xt}}),f?m.jsxs("div",{className:mt("min-h-screen bg-slate-100 dark:bg-slate-950 px-4 print:p-0 print:bg-white flex flex-col items-center",_?"py-0":"py-8"),children:[!_&&m.jsxs("div",{className:"w-full max-w-[210mm] mb-6 flex items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 print:hidden",children:[m.jsxs("div",{className:"flex items-center gap-3",children:[m.jsx("div",{className:"p-2 rounded-lg bg-primary/10 text-primary",children:m.jsx(He,{className:"h-5 w-5"})}),m.jsxs("div",{children:[m.jsx("h1",{className:"font-bold text-slate-900 dark:text-slate-100 text-base",children:(t==null?void 0:t.proposal_number)||Kt}),(t==null?void 0:t.subject)&&m.jsx("p",{className:"text-xs text-slate-500",children:t.subject})]})]}),m.jsx("div",{className:"flex items-center gap-2",children:m.jsxs(ne,{variant:"default",size:"sm",onClick:()=>window.print(),className:"gap-2",children:[m.jsx(re,{className:"h-4 w-4"}),a("Print / Save PDF")]})})]}),m.jsxs("div",{className:"w-full flex justify-center",children:[m.jsx("style",{dangerouslySetInnerHTML:{__html:le}}),Qt()]})]}):m.jsx(ve,{open:M,onOpenChange:n=>!n&&C(),children:m.jsxs($e,{className:"max-w-4xl max-h-[92vh] flex flex-col p-0 gap-0 overflow-hidden bg-background border-border shadow-xl !rounded-md [&>div]:p-0 [&>div]:max-h-[92vh] [&>div]:flex [&>div]:flex-col [&>button]:top-2.5 [&>button]:right-3",children:[m.jsxs(Ne,{className:"!py-3 !px-5 bg-background border-b border-border flex flex-row items-center justify-between space-y-0 shrink-0",children:[m.jsxs("div",{className:"flex items-center gap-2.5 pr-8",children:[m.jsx("div",{className:"p-1.5 rounded-md bg-primary/10 text-primary",children:m.jsx(ze,{className:"h-4 w-4"})}),m.jsx(Te,{className:"text-sm font-semibold",children:Kt})]}),I&&m.jsx("div",{className:"flex items-center gap-2 pr-6",children:m.jsxs(ne,{variant:"default",size:"sm",onClick:be,className:"gap-2 text-xs h-8",children:[m.jsx(re,{className:"h-3.5 w-3.5"}),a("Print")]})})]}),m.jsxs("div",{className:"flex-1 overflow-y-auto p-3 sm:p-5 bg-slate-100/70 dark:bg-slate-900 flex justify-center scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-700 scrollbar-track-transparent",children:[m.jsx("style",{dangerouslySetInnerHTML:{__html:le}}),Qt()]})]})})]})}export{Ye as P,Vt as s};
