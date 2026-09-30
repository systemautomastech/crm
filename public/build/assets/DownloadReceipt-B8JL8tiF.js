import{h as x}from"./html2pdf-BOhZUO8c.js";import{a as u,o as w,f as i}from"./helpers-DP60IMUj.js";import"./ui-BSZZ9r9D.js";import"./jspdf.es.min-BfCHS_BX.js";import"./app-BaZ18SVS.js";import"./html2canvas-AJFj9Zk9.js";const C=async(o,a)=>{var d;const v=`
        <div class="receipt">
            <div class="header">
                <div class="company-name">${(a==null?void 0:a.company_name)||"COMPANY NAME"}</div>
                <div class="company-info">
                    ${(a==null?void 0:a.company_address)||"Company Address"}<br>
                    ${(a==null?void 0:a.company_city)||"City"}, ${(a==null?void 0:a.company_state)||"State"}<br>
                    ${(a==null?void 0:a.company_country)||"Country"} - ${(a==null?void 0:a.company_zipcode)||"Zipcode"}
                </div>
            </div>
            
            <div class="separator"></div>
            
            <div class="receipt-info">
                <div class="info-row">
                    <span>Receipt No:</span>
                    <span>${o.pos_number}</span>
                </div>
                <div class="info-row">
                    <span>Date:</span>
                    <span>${u(new Date,{companyAllSetting:a})}</span>
                </div>
                <div class="info-row">
                    <span>Time:</span>
                    <span>${w(new Date().toLocaleTimeString(),{companyAllSetting:a})}</span>
                </div>
                <div class="info-row">
                    <span>Customer:</span>
                    <span>${((d=o.customer)==null?void 0:d.name)||"Walk-in Customer"}</span>
                </div>
            </div>
            
            <div class="separator"></div>
            
            <div class="items-section">
                ${o.items.map(n=>{const c=n.price*n.quantity,s=n.item_discount_amount||0,m=c-s;let p=0,e="";n.taxes&&n.taxes.length>0?e=n.taxes.map(r=>(p+=m*r.rate/100,`${r.name} (${r.rate}%)`)).join(", "):e="No Tax";const f=s>0?`<div class="total-row" style="color: #16a34a;">
                                <span>Discount:</span>
                                <span>-${i(s,{companyAllSetting:a})}</span>
                            </div>`:"";return`
                        <div class="item">
                            <div class="item-name">${n.name}</div>
                            <div class="item-details">
                                <div class="total-row">
                                    <span>Qty: ${n.quantity}</span>
                                    <span>Price: ${i(n.price,{companyAllSetting:a})}</span>
                                </div>
                                <div class="total-row">
                                    <span>Subtotal:</span>
                                    <span>${i(c,{companyAllSetting:a})}</span>
                                </div>
                                ${f}
                                <div class="total-row">
                                    <span>Tax: ${e}</span>
                                    <span>Tax Amount: ${i(p,{companyAllSetting:a})}</span>
                                </div>
                                <div class="total-row" style="font-weight: bold;">
                                    <span>Total:</span>
                                    <span>${i(m+p,{companyAllSetting:a})}</span>
                                </div>
                            </div>
                        </div>
                    `}).join("")}
            </div>
            
            <div class="totals">
                <div class="final-total">
                    <span>TOTAL:</span>
                    <span>${i(o.total,{companyAllSetting:a})}</span>
                </div>
            </div>
            
            <div class="separator"></div>
            
            <div class="footer">
                <div style="font-weight: bold;">*** THANK YOU ***</div>
                <div>Visit Again!</div>
            </div>
        </div>
        
        <style>
            .receipt { max-width: 400px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 20px; }
            .company-name { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
            .company-info { font-size: 12px; line-height: 1.4; }
            .separator { border-top: 1px dashed #000; margin: 15px 0; }
            .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
            .item { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dotted #ccc; }
            .item-name { font-weight: bold; margin-bottom: 8px; }
            .item-details { font-size: 12px; }
            .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
            .final-total { display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; border-top: 2px solid #000; padding-top: 10px; margin-top: 10px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        </style>
    `,t=document.createElement("div");t.innerHTML=v,document.body.appendChild(t);const y={margin:.1,filename:`receipt-${o.pos_number}.pdf`,image:{type:"jpeg",quality:.98},html2canvas:{scale:2},jsPDF:{unit:"mm",format:[80,297],orientation:"portrait"}};try{await x().set(y).from(t).save()}catch(n){console.error("PDF generation failed:",n)}finally{document.body.removeChild(t)}};export{C as downloadReceiptPDF};
