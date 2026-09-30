import{b as x,j as e}from"./ui-BSZZ9r9D.js";import{X as L,S as C}from"./app-BaZ18SVS.js";import{h as D}from"./html2pdf-BOhZUO8c.js";import{c as i,b as c,a as m,f as l}from"./helpers-DP60IMUj.js";import{u as O}from"./useTranslation-DHrMWs-f.js";import"./jspdf.es.min-BfCHS_BX.js";import"./html2canvas-AJFj9Zk9.js";function $(){var _,w,v,N,A,k;const{t:a}=O(),d=L().props,{payment:t,salesInvoiceSetting:o}=d,[h,b]=x.useState(!1),g=x.useRef(!1),p=((o==null?void 0:o.sales_invoice_show_logo)??"on")!=="off",E=(o==null?void 0:o.sales_invoice_logo)||"",S=i("company_logo")||i("logo_dark")||"",f=E||S;p&&f&&c(f,d);const y=((o==null?void 0:o.sales_invoice_enable_letterhead)??"off")==="on",j=(o==null?void 0:o.sales_invoice_bg_letterhead)||"",u=y&&j?c(j,d):"";x.useEffect(()=>{if(new URLSearchParams(window.location.search).get("download")==="pdf"&&!g.current)g.current=!0,setTimeout(()=>P(),500);else{const n=setTimeout(()=>{window.print()},500);return()=>clearTimeout(n)}},[]);const P=async()=>{if(h)return;b(!0);const r=document.querySelector(".payment-container");if(r){const n={margin:.25,filename:`customer-payment-${t.payment_number||t.id}.pdf`,image:{type:"jpeg",quality:.98},html2canvas:{scale:2},jsPDF:{unit:"in",format:"a4",orientation:"portrait"}};try{await D().set(n).from(r).save(),setTimeout(()=>window.close(),1e3)}catch(s){console.error("PDF generation failed:",s)}}b(!1)};return e.jsxs("div",{className:"min-h-screen bg-white",children:[e.jsx(C,{title:`${a("Customer Payment")} - ${t.payment_number||"#"+t.id}`}),h&&e.jsx("div",{className:"fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50",children:e.jsx("div",{className:"bg-white p-6 rounded-lg shadow-lg",children:e.jsxs("div",{className:"flex items-center space-x-3",children:[e.jsx("div",{className:"animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"}),e.jsx("p",{className:"text-lg font-semibold text-gray-700",children:a("Generating PDF...")})]})})}),e.jsxs("div",{className:"a4-page",children:[y&&u&&e.jsx("img",{src:u,alt:"Letterhead Background",className:"letterhead-bg-layer"}),e.jsxs("div",{className:"a4-content",children:[e.jsxs("div",{children:[e.jsxs("div",{className:"flex justify-between items-start mb-5",children:[e.jsxs("div",{className:"w-1/2",children:[p&&(o!=null&&o.sales_invoice_logo)?e.jsx("img",{src:c(o.sales_invoice_logo,d),alt:"Logo",className:"max-h-14 max-w-[200px] object-contain mb-3"}):p&&(i("company_logo")||i("logo_dark"))?e.jsx("img",{src:c(i("company_logo")||i("logo_dark"),d),alt:"Logo",className:"max-h-14 max-w-[200px] object-contain mb-3"}):i("company_name")?e.jsx("h1",{className:"text-2xl font-bold mb-2 text-gray-900",children:i("company_name")}):null,e.jsxs("div",{className:"text-xs space-y-0.5 text-gray-600",children:[i("company_address")&&e.jsx("p",{children:i("company_address")}),(i("company_city")||i("company_state")||i("company_zipcode"))&&e.jsxs("p",{children:[i("company_city"),i("company_state")&&`, ${i("company_state")}`," ",i("company_zipcode")]}),i("company_country")&&e.jsx("p",{children:i("company_country")}),i("company_telephone")&&e.jsxs("p",{children:[a("Phone"),": ",i("company_telephone")]}),i("company_email")&&e.jsxs("p",{children:[a("Email"),": ",i("company_email")]}),i("registration_number")&&e.jsxs("p",{children:[a("Registration"),": ",i("registration_number")]})]})]}),e.jsxs("div",{className:"text-right w-1/2",children:[e.jsx("h2",{className:"text-2xl font-bold mb-1 text-gray-900",children:a("PAYMENT RECEIPT")}),e.jsx("p",{className:"text-base font-semibold text-gray-800",children:t.payment_number||`#${t.id}`}),e.jsxs("div",{className:"text-xs mt-2 space-y-0.5 text-gray-600",children:[e.jsxs("p",{children:[a("Payment Date"),": ",m(t.payment_date)]}),t.reference_number&&e.jsxs("p",{children:[a("Reference"),": ",t.reference_number]})]})]})]}),e.jsxs("div",{className:"flex justify-between mb-5 pt-3 border-t border-gray-200",children:[e.jsxs("div",{className:"w-1/2",children:[e.jsx("h3",{className:"font-bold text-xs uppercase mb-1.5 text-gray-900 tracking-wider",children:a("RECEIVED FROM")}),e.jsxs("div",{className:"text-xs space-y-0.5 text-gray-700",children:[e.jsx("p",{className:"font-semibold text-gray-900",children:((_=t.customer)==null?void 0:_.name)||"-"}),((w=t.customer)==null?void 0:w.email)&&e.jsx("p",{children:t.customer.email}),((v=t.customer)==null?void 0:v.contact)&&e.jsx("p",{children:t.customer.contact})]})]}),e.jsxs("div",{className:"text-right w-1/2",children:[e.jsx("h3",{className:"font-bold text-xs uppercase mb-1.5 text-gray-900 tracking-wider",children:a("DEPOSITED TO")}),e.jsxs("div",{className:"text-xs space-y-0.5 text-gray-700",children:[e.jsx("p",{className:"font-semibold text-gray-900",children:((N=t.bank_account)==null?void 0:N.account_name)||"-"}),((A=t.bank_account)==null?void 0:A.account_number)&&e.jsxs("p",{children:[a("Account No"),": ",t.bank_account.account_number]}),((k=t.bank_account)==null?void 0:k.bank_name)&&e.jsx("p",{children:t.bank_account.bank_name})]})]})]}),t.allocations&&t.allocations.length>0&&e.jsx("div",{className:"mb-4",children:e.jsxs("table",{style:{width:"100%",fontSize:"10px",tableLayout:"fixed",borderCollapse:"collapse",border:"1px solid #94a3b8"},children:[e.jsx("thead",{children:e.jsxs("tr",{style:{backgroundColor:"#e2e8f0",color:"#0f172a",fontWeight:700},children:[e.jsx("th",{style:{padding:"6px 4px",border:"1px solid #94a3b8",textAlign:"center",fontSize:"9.5px",width:"6%"},children:a("SN")}),e.jsx("th",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"left",fontSize:"9.5px",width:"28%"},children:a("INVOICE NUMBER")}),e.jsx("th",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"left",fontSize:"9.5px",width:"22%"},children:a("INVOICE DATE")}),e.jsx("th",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"right",fontSize:"9.5px",width:"22%"},children:a("INVOICE TOTAL")}),e.jsx("th",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"right",fontSize:"9.5px",width:"22%"},children:a("ALLOCATED AMOUNT")})]})}),e.jsx("tbody",{children:t.allocations.map((r,n)=>{var s,T,z;return e.jsxs("tr",{className:"page-break-inside-avoid",children:[e.jsx("td",{style:{padding:"6px 4px",border:"1px solid #94a3b8",textAlign:"center",verticalAlign:"top",color:"#475569"},children:n+1}),e.jsx("td",{style:{padding:"6px 8px",border:"1px solid #94a3b8",verticalAlign:"top",color:"#0f172a",fontWeight:600},children:((s=r.invoice)==null?void 0:s.invoice_number)||"-"}),e.jsx("td",{style:{padding:"6px 8px",border:"1px solid #94a3b8",verticalAlign:"top",color:"#475569"},children:m((T=r.invoice)==null?void 0:T.invoice_date)}),e.jsx("td",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"right",verticalAlign:"top",color:"#1e293b"},children:l((z=r.invoice)==null?void 0:z.total_amount)}),e.jsx("td",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"right",verticalAlign:"top",fontWeight:600,color:"#0f172a"},children:l(r.allocated_amount)})]},r.id||n)})}),e.jsx("tfoot",{children:e.jsxs("tr",{className:"page-break-inside-avoid",style:{fontWeight:700},children:[e.jsx("td",{colSpan:3,style:{border:"1px solid #94a3b8"}}),e.jsxs("td",{style:{padding:"6px 8px",fontSize:"11px",color:"#0f172a",border:"1px solid #94a3b8",textAlign:"right"},children:[a("TOTAL"),":"]}),e.jsx("td",{style:{padding:"6px 8px",fontSize:"11px",textAlign:"right",color:"#0f172a",border:"1px solid #94a3b8"},children:l(t.payment_amount)})]})})]})}),t.credit_note_applications&&t.credit_note_applications.length>0&&e.jsxs("div",{className:"mb-4",children:[e.jsx("div",{style:{fontSize:"10px",fontWeight:700,color:"#0f172a",marginBottom:"4px",textTransform:"uppercase",letterSpacing:"0.05em"},children:a("Credit Note History")}),e.jsxs("table",{style:{width:"100%",fontSize:"10px",tableLayout:"fixed",borderCollapse:"collapse",border:"1px solid #94a3b8"},children:[e.jsx("thead",{children:e.jsxs("tr",{style:{backgroundColor:"#e2e8f0",color:"#0f172a",fontWeight:700},children:[e.jsx("th",{style:{padding:"6px 4px",border:"1px solid #94a3b8",textAlign:"center",fontSize:"9.5px",width:"6%"},children:a("SN")}),e.jsx("th",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"left",fontSize:"9.5px",width:"38%"},children:a("CREDIT NOTE NUMBER")}),e.jsx("th",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"left",fontSize:"9.5px",width:"28%"},children:a("APPLICATION DATE")}),e.jsx("th",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"right",fontSize:"9.5px",width:"28%"},children:a("APPLIED AMOUNT")})]})}),e.jsx("tbody",{children:t.credit_note_applications.map((r,n)=>{var s;return e.jsxs("tr",{className:"page-break-inside-avoid",children:[e.jsx("td",{style:{padding:"6px 4px",border:"1px solid #94a3b8",textAlign:"center",verticalAlign:"top",color:"#475569"},children:n+1}),e.jsx("td",{style:{padding:"6px 8px",border:"1px solid #94a3b8",verticalAlign:"top",color:"#0f172a",fontWeight:600},children:((s=r.credit_note)==null?void 0:s.credit_note_number)||"-"}),e.jsx("td",{style:{padding:"6px 8px",border:"1px solid #94a3b8",verticalAlign:"top",color:"#475569"},children:m(r.application_date)}),e.jsx("td",{style:{padding:"6px 8px",border:"1px solid #94a3b8",textAlign:"right",verticalAlign:"top",fontWeight:600,color:"#0f172a"},children:l(r.applied_amount)})]},r.id||n)})}),e.jsx("tfoot",{children:e.jsxs("tr",{className:"page-break-inside-avoid",style:{fontWeight:700},children:[e.jsx("td",{colSpan:2,style:{border:"1px solid #94a3b8"}}),e.jsxs("td",{style:{padding:"6px 8px",fontSize:"11px",color:"#0f172a",border:"1px solid #94a3b8",textAlign:"right"},children:[a("TOTAL APPLIED"),":"]}),e.jsx("td",{style:{padding:"6px 8px",fontSize:"11px",textAlign:"right",color:"#0f172a",border:"1px solid #94a3b8"},children:l(t.credit_note_applications.reduce((r,n)=>r+parseFloat(n.applied_amount||"0"),0))})]})})]})]})]}),e.jsxs("div",{children:[t.notes&&e.jsxs("div",{className:"pt-2 text-xs text-gray-600 page-break-inside-avoid",children:[e.jsxs("span",{className:"font-semibold text-gray-800",children:[a("NOTES"),":"]}),e.jsx("div",{className:"pt-1 mb-2 text-xs text-gray-600",children:t.notes})]}),e.jsx("div",{className:"border-t border-gray-300 pt-2 text-center text-xs text-gray-500 page-break-inside-avoid",children:e.jsx("span",{children:a("Thank you for your business!")})})]})]})]}),e.jsx("style",{children:`
                * {
                    box-sizing: border-box !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    font-family: 'Open Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
                }

                html, body {
                    margin: 0 !important;
                    padding: 0 !important;
                    background-color: #ffffff;
                    color: #1e293b;
                }

                @page {
                    size: A4 portrait;
                    margin: 0;
                }

                .a4-page {
                    position: relative;
                    width: 210mm;
                    height: 297mm;
                    min-height: 297mm;
                    max-height: 297mm;
                    padding: 30mm 14mm 30mm 14mm;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    overflow: hidden;
                    page-break-after: always;
                    break-after: page;
                    page-break-inside: avoid;
                    break-inside: avoid-page;
                }

                .letterhead-bg-layer {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    z-index: 0;
                    pointer-events: none;
                }

                .a4-content {
                    position: relative;
                    z-index: 1;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    height: 100%;
                }

                .page-break-inside-avoid {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }

                table {
                    table-layout: fixed !important;
                    word-wrap: break-word !important;
                    overflow-wrap: break-word !important;
                    word-break: break-word !important;
                }

                th, td {
                    word-wrap: break-word !important;
                    overflow-wrap: break-word !important;
                    word-break: break-word !important;
                    box-sizing: border-box !important;
                }

                @media print {
                    html, body {
                        background: #ffffff !important;
                    }

                    .a4-page {
                        width: 210mm !important;
                        height: 297mm !important;
                        margin: 0 !important;
                        padding: 30mm 14mm 30mm 14mm !important;
                        box-shadow: none !important;
                    }
                }
            `})]})}export{$ as default};
