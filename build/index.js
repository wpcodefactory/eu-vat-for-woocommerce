(()=>{"use strict";var e,o={565:()=>{window.React,window.wp.plugins;const e=window.wc.blocksCheckout,o=window.wc.wcSettings,{optInDefaultText:r}=(window.wc.wcBlocksRegistry,window.wp.i18n,window.wp.element,window.wp.data,(0,o.getSetting)("eu-vat-for-woocommerce_data","")),{registerCheckoutBlock:t}=wc.blocksCheckout,{hasError:n}=!1;(0,o.getSetting)("eu-vat-for-woocommerce_data"),(0,e.registerCheckoutFilters)("eu-vat-for-woocommerce",{itemName:(e,o,r)=>(document.getElementById("billing_eu_vat_number").value,e)})}},r={};function t(e){var n=r[e];if(void 0!==n)return n.exports;var i=r[e]={exports:{}};return o[e](i,i.exports,t),i.exports}t.m=o,e=[],t.O=(o,r,n,i)=>{if(!r){var c=1/0;for(u=0;u<e.length;u++){for(var[r,n,i]=e[u],a=!0,w=0;w<r.length;w++)(!1&i||c>=i)&&Object.keys(t.O).every((e=>t.O[e](r[w])))?r.splice(w--,1):(a=!1,i<c&&(c=i));if(a){e.splice(u--,1);var l=n();void 0!==l&&(o=l)}}return o}i=i||0;for(var u=e.length;u>0&&e[u-1][2]>i;u--)e[u]=e[u-1];e[u]=[r,n,i]},t.o=(e,o)=>Object.prototype.hasOwnProperty.call(e,o),(()=>{var e={57:0,350:0};t.O.j=o=>0===e[o];var o=(o,r)=>{var n,i,[c,a,w]=r,l=0;if(c.some((o=>0!==e[o]))){for(n in a)t.o(a,n)&&(t.m[n]=a[n]);if(w)var u=w(t)}for(o&&o(r);l<c.length;l++)i=c[l],t.o(e,i)&&e[i]&&e[i][0](),e[i]=0;return t.O(u)},r=globalThis.webpackChunkeu_vat_for_woocommerce=globalThis.webpackChunkeu_vat_for_woocommerce||[];r.forEach(o.bind(null,0)),r.push=o.bind(null,r.push.bind(r))})();var n=t.O(void 0,[350],(()=>t(565)));n=t.O(n)})();