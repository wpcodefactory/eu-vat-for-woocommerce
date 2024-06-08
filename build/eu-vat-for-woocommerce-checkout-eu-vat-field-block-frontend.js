(()=>{"use strict";const e=window.wc.blocksCheckout,a=window.React,t=window.wp.element,o=window.wc.wcSettings,c=window.wp.data,l=window.wp.i18n,{optInDefaultText:i}=(0,o.getSetting)("eu-vat-for-woocommerce_data",""),{registerCheckoutBlock:s}=wc.blocksCheckout,{hasError:r}=!1,n=JSON.parse('{"apiVersion":2,"name":"eu-vat-for-woocommerce/checkout-eu-vat-field","version":"2.11.2","title":"EU VAT Field","category":"woocommerce","description":"Adds a EU VAT Field checkbox to the checkout.","supports":{"html":false,"align":false,"multiple":false,"reusable":false},"parent":["woocommerce/checkout-billing-address-block"],"attributes":{"lock":{"type":"object","default":{"remove":true,"move":true}},"text":{"type":"string","source":"html","selector":".wp-block-eu-vat-for-woocommerce-checkout-eu-vat-field","default":""}},"textdomain":"eu-vat-for-woocommerce","editorStyle":"file:../../../build/style-eu-vat-for-woocommerce-checkout-eu-vat-field-block.css"}');(0,e.registerCheckoutBlock)({metadata:n,component:({children:o,checkoutExtensionData:i})=>{const[s,r]=(0,t.useState)(!1),[n,d]=(0,t.useState)(""),{setExtensionData:v}=i,{setValidationErrors:u,clearValidationError:m}=(0,c.useDispatch)("wc/store/validation"),{CART_STORE_KEY:_}=window.wc.wcBlocksData,w=(0,c.select)(_).getCartData().billingAddress.country;(0,t.useEffect)((()=>{v("eu-vat-for-woocommerce-block-example","billing_eu_vat_number",n),m("billing_eu_vat_number")}),[m,u,s,v]);const g=(0,t.useCallback)((e=>{d(e),v("eu-vat-for-woocommerce-block-example","billing_eu_vat_number",e),""==e||((e,a)=>{const{CART_STORE_KEY:t}=window.wc.wcBlocksData,o=(0,c.select)(t).getCartData(),l=o.billingAddress.country,i=o.billingAddress.company;var s=document.getElementById("alg_wc_eu_vat_progress"),r=document.getElementById("alg_eu_vat_for_woocommerce_field"),n=document.getElementsByClassName("wc-block-components-checkout-place-order-button")[0];const d=document.getElementById("store_previous_country");n.disabled=!0;var v=new URLSearchParams({action:"alg_wc_eu_vat_validate_action",channel:"bloock_api",alg_wc_eu_vat_to_check:e,billing_country:l,billing_company:i});s.innerHTML=alg_wc_eu_vat_ajax_object.progress_text_validating,s.classList.remove("alg-wc-eu-vat-not-valid"),s.classList.remove("alg-wc-eu-vat-validating"),s.classList.remove("alg-wc-eu-vat-valid"),s.classList.add("alg-wc-eu-vat-validating"),r.classList.remove("woocommerce-invalid"),r.classList.remove("woocommerce-validated"),r.classList.remove("woocommerce-invalid-mismatch"),fetch(alg_wc_eu_vat_ajax_object.ajax_url,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:v}).then((e=>e.json())).then((e=>{"1"==e.status?(r.classList.add("woocommerce-validated"),s.innerHTML=alg_wc_eu_vat_ajax_object.progress_text_valid,s.classList.remove("alg-wc-eu-vat-not-valid"),s.classList.remove("alg-wc-eu-vat-validating"),s.classList.add("alg-wc-eu-vat-valid")):"0"==e.status?(r.classList.add("woocommerce-invalid"),s.innerHTML=alg_wc_eu_vat_ajax_object.progress_text_not_valid,s.classList.remove("alg-wc-eu-vat-valid"),s.classList.remove("alg-wc-eu-vat-validating"),s.classList.add("alg-wc-eu-vat-not-valid")):"4"==e.status?(r.classList.add("woocommerce-invalid"),s.innerHTML=alg_wc_eu_vat_ajax_object.text_shipping_billing_countries,s.classList.remove("alg-wc-eu-vat-valid"),s.classList.remove("alg-wc-eu-vat-validating"),s.classList.add("alg-wc-eu-vat-not-valid")):"5"==e.status?(r.classList.add("woocommerce-invalid"),r.classList.add("woocommerce-invalid-mismatch"),s.innerHTML=alg_wc_eu_vat_ajax_object.company_name_mismatch,s.classList.remove("alg-wc-eu-vat-valid"),s.classList.remove("alg-wc-eu-vat-validating"),s.classList.add("alg-wc-eu-vat-not-valid")):"6"==e.status?(r.classList.remove("woocommerce-invalid"),r.classList.remove("woocommerce-validated"),s.innerHTML=alg_wc_eu_vat_ajax_object.progress_text_validation_failed,s.classList.remove("alg-wc-eu-vat-valid"),s.classList.remove("alg-wc-eu-vat-validating"),s.classList.remove("alg-wc-eu-vat-not-valid")):(r.classList.add("woocommerce-invalid"),s.innerHTML=alg_wc_eu_vat_ajax_object.progress_text_validation_failed,s.classList.remove("alg-wc-eu-vat-valid"),s.classList.remove("alg-wc-eu-vat-validating"),s.classList.add("alg-wc-eu-vat-not-valid")),d.value=l,wp.data.dispatch("wc/store/cart").invalidateResolutionForStore(),n.disabled=!1}))})(e)}),[d.setExtensionData]),b=(0,t.useCallback)((e=>{d(e),v("eu-vat-for-woocommerce-block-example","billing_eu_vat_number",e),wp.data.dispatch("wc/store/cart").invalidateResolutionForStore()}),[d.setExtensionData]),{validationError:p,validationErrorInput:L}=(0,c.useSelect)((e=>{const a=e("wc/store/validation");return{validationError:a.getValidationError("eu-vat-for-woocommerce"),validationErrorInput:a.getValidationError("billing_eu_vat_number")}}));return(0,a.createElement)(a.Fragment,null,(0,a.createElement)("div",{id:"alg_eu_vat_for_woocommerce_field",className:"alg-eu-vat-for-woocommerce-fields"},(0,a.createElement)(e.ValidatedTextInput,{id:"billing_eu_vat_number",type:"text",required:!0,className:"billing-eu-vat-number",label:(0,l.__)("EU VAT Number","eu-vat-for-woocommerce"),value:n,onChange:g,onBlur:b}),(0,a.createElement)("div",{id:"alg_wc_eu_vat_progress"}),(0,a.createElement)("div",{id:"custom-checkout"}),(0,a.createElement)("input",{type:"hidden",id:"store_previous_country",name:"store_previous_country",value:w})))}})})();