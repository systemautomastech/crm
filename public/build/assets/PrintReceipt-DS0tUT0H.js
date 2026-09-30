import{a as h,o as $,f as i}from"./helpers-DP60IMUj.js";import"./app-BaZ18SVS.js";import"./ui-BSZZ9r9D.js";const z=(o,n)=>{var m,r,c,v;const y=`
    <!DOCTYPE html>
    <html>
    <head>
        <title>Receipt - ${o.pos_number}</title>
        <style>
            @page {
                size: 80mm auto;
                margin: 0;
            }
            @media print {
                body { 
                    width: 80mm;
                    margin: 0;
                    padding: 0;
                }
            }
            body { 
                font-family: 'Courier New', monospace; 
                width: 80mm;
                margin: 0; 
                padding: 0;
                font-size: 12px;
                line-height: 1.3;
                color: #000;
            }
            .receipt { 
                width: 100%;
                text-align: center;
                padding: 5mm;
                margin: 0;
                box-sizing: border-box;
            }
            .header {
                margin-bottom: 8px;
            }
            .company-name {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 3px;
                letter-spacing: 0.5px;
            }
            .company-info {
                font-size: 11px;
                line-height: 1.4;
                margin-bottom: 5px;
            }
            .separator {
                border-top: 2px dashed #000;
                margin: 8px 0;
            }
            .receipt-info {
                text-align: left;
                margin-bottom: 6px;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
                font-size: 12px;
            }
            .items-section {
                text-align: left;
                margin-bottom: 6px;
            }
            .item {
                margin-bottom: 10px;
                border-bottom: 1px dotted #000;
                padding-bottom: 5px;
            }
            .item-name {
                font-weight: bold;
                font-size: 13px;
                margin-bottom: 3px;
            }
            .item-details {
                font-size: 11px;
            }
            .item-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
            }
            .totals {
                text-align: left;
                margin-bottom: 6px;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
                font-size: 12px;
            }
            .final-total {
                display: flex;
                justify-content: space-between;
                font-weight: bold;
                font-size: 16px;
                border-top: 2px solid #000;
                padding-top: 5px;
                margin-top: 5px;
            }
            .footer {
                text-align: center;
                margin-top: 10px;
                font-size: 11px;
            }
            .thank-you {
                font-weight: bold;
                margin-bottom: 3px;
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="header">
                <div class="company-name">${(n==null?void 0:n.company_name)||"COMPANY NAME"}</div>
                <div class="company-info">
                    ${(n==null?void 0:n.company_address)||"Company Address"}<br>
                    ${(n==null?void 0:n.company_city)||"City"}, ${(n==null?void 0:n.company_state)||"State"}<br>
                    ${(n==null?void 0:n.company_country)||"Country"} - ${(n==null?void 0:n.company_zipcode)||"Zipcode"}
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
                    <span>${h(new Date,{companyAllSetting:n})}</span>
                </div>
                <div class="info-row">
                    <span>Time:</span>
                    <span>${$(new Date().toLocaleTimeString(),{companyAllSetting:n})}</span>
                </div>
                <div class="info-row">
                    <span>Customer:</span>
                    <span>${((m=o.customer)==null?void 0:m.name)||"Walk-in Customer"}</span>
                </div>
            </div>
            
            <div class="separator"></div>
            
            <div class="items-section">
                ${o.items.map(t=>{const f=t.price*t.quantity,p=t.item_discount_amount||0,x=f-p;let s=0;const u=t.taxes&&t.taxes.length>0?t.taxes.reduce((d,w)=>d+w.rate,0):0;return t.taxes&&t.taxes.length>0&&t.taxes.forEach(d=>{s+=x*d.rate/100}),`
                        <div class="item">
                            <div class="item-name">${t.name}</div>
                            <div class="item-details">
                                <div class="item-row">
                                    <span>Qty:</span>
                                    <span>${t.quantity}</span>
                                </div>
                                <div class="item-row">
                                    <span>Price:</span>
                                    <span>${i(t.price,{companyAllSetting:n})}</span>
                                </div>
                                <div class="item-row">
                                    <span>Subtotal:</span>
                                    <span>${i(f,{companyAllSetting:n})}</span>
                                </div>
                                ${p>0?`
                                <div class="item-row" style="color: #16a34a;">
                                    <span>Discount:</span>
                                    <span>-${i(p,{companyAllSetting:n})}</span>
                                </div>
                                `:""}
                                <div class="item-row">
                                    <span>Tax (${u}%):</span>
                                    <span>${i(s,{companyAllSetting:n})}</span>
                                </div>
                                <div class="item-row" style="font-weight: bold;">
                                    <span>Total:</span>
                                    <span>${i(x+s,{companyAllSetting:n})}</span>
                                </div>
                            </div>
                        </div>
                    `}).join("")}
            </div>
            
            <div class="totals">
                <div class="final-total">
                    <span>TOTAL:</span>
                    <span>${i(o.total,{companyAllSetting:n})}</span>
                </div>
            </div>
            
            <div class="footer">
                <div class="thank-you">*** THANK YOU ***</div>
                <div>Visit Again!</div>
            </div>
        </div>
    </body>
    </html>
    `,a=document.createElement("iframe");a.style.display="none",document.body.appendChild(a);const e=a.contentDocument||((r=a.contentWindow)==null?void 0:r.document);e&&(e.write(y),e.close(),(c=a.contentWindow)==null||c.focus(),(v=a.contentWindow)==null||v.print(),setTimeout(()=>{document.body.removeChild(a)},1e3))};export{z as printReceipt};
