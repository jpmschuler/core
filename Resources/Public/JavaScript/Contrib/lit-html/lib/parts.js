define(["exports","./directive","./dom","./part","./template","./template-instance","./template-result"],(function(t,e,i,s,n,a,o){"use strict";
/**
     * @license
     * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
     * This code may only be used under the BSD style license found at
     * http://polymer.github.io/LICENSE.txt
     * The complete set of authors may be found at
     * http://polymer.github.io/AUTHORS.txt
     * The complete set of contributors may be found at
     * http://polymer.github.io/CONTRIBUTORS.txt
     * Code distributed by Google as part of the polymer project is also
     * subject to an additional IP rights grant found at
     * http://polymer.github.io/PATENTS.txt
     */const r=t=>null===t||!("object"==typeof t||"function"==typeof t),h=t=>Array.isArray(t)||!(!t||!t[Symbol.iterator]);class l{constructor(t,e,i){this.dirty=!0,this.element=t,this.name=e,this.strings=i,this.parts=[];for(let t=0;t<i.length-1;t++)this.parts[t]=this._createPart()}_createPart(){return new u(this)}_getValue(){const t=this.strings,e=t.length-1,i=this.parts;if(1===e&&""===t[0]&&""===t[1]){const t=i[0].value;if("symbol"==typeof t)return String(t);if("string"==typeof t||!h(t))return t}let s="";for(let n=0;n<e;n++){s+=t[n];const e=i[n];if(void 0!==e){const t=e.value;if(r(t)||!h(t))s+="string"==typeof t?t:String(t);else for(const e of t)s+="string"==typeof e?e:String(e)}}return s+=t[e],s}commit(){this.dirty&&(this.dirty=!1,this.element.setAttribute(this.name,this._getValue()))}}class u{constructor(t){this.value=void 0,this.committer=t}setValue(t){t===s.noChange||r(t)&&t===this.value||(this.value=t,e.isDirective(t)||(this.committer.dirty=!0))}commit(){for(;e.isDirective(this.value);){const t=this.value;this.value=s.noChange,t(this)}this.value!==s.noChange&&this.committer.commit()}}class d{constructor(t){this.value=void 0,this.__pendingValue=void 0,this.options=t}appendInto(t){this.startNode=t.appendChild(n.createMarker()),this.endNode=t.appendChild(n.createMarker())}insertAfterNode(t){this.startNode=t,this.endNode=t.nextSibling}appendIntoPart(t){t.__insert(this.startNode=n.createMarker()),t.__insert(this.endNode=n.createMarker())}insertAfterPart(t){t.__insert(this.startNode=n.createMarker()),this.endNode=t.endNode,t.endNode=this.startNode}setValue(t){this.__pendingValue=t}commit(){if(null===this.startNode.parentNode)return;for(;e.isDirective(this.__pendingValue);){const t=this.__pendingValue;this.__pendingValue=s.noChange,t(this)}const t=this.__pendingValue;t!==s.noChange&&(r(t)?t!==this.value&&this.__commitText(t):t instanceof o.TemplateResult?this.__commitTemplateResult(t):t instanceof Node?this.__commitNode(t):h(t)?this.__commitIterable(t):t===s.nothing?(this.value=s.nothing,this.clear()):this.__commitText(t))}__insert(t){this.endNode.parentNode.insertBefore(t,this.endNode)}__commitNode(t){this.value!==t&&(this.clear(),this.__insert(t),this.value=t)}__commitText(t){const e=this.startNode.nextSibling,i="string"==typeof(t=null==t?"":t)?t:String(t);e===this.endNode.previousSibling&&3===e.nodeType?e.data=i:this.__commitNode(document.createTextNode(i)),this.value=t}__commitTemplateResult(t){const e=this.options.templateFactory(t);if(this.value instanceof a.TemplateInstance&&this.value.template===e)this.value.update(t.values);else{const i=new a.TemplateInstance(e,t.processor,this.options),s=i._clone();i.update(t.values),this.__commitNode(s),this.value=i}}__commitIterable(t){Array.isArray(this.value)||(this.value=[],this.clear());const e=this.value;let i,s=0;for(const n of t)i=e[s],void 0===i&&(i=new d(this.options),e.push(i),0===s?i.appendIntoPart(this):i.insertAfterPart(e[s-1])),i.setValue(n),i.commit(),s++;s<e.length&&(e.length=s,this.clear(i&&i.endNode))}clear(t=this.startNode){i.removeNodes(this.startNode.parentNode,t.nextSibling,this.endNode)}}class c extends u{}let _=!1;(()=>{try{const t={get capture(){return _=!0,!1}};window.addEventListener("test",t,t),window.removeEventListener("test",t,t)}catch(t){}})();const p=t=>t&&(_?{capture:t.capture,passive:t.passive,once:t.once}:t.capture);t.AttributeCommitter=l,t.AttributePart=u,t.BooleanAttributePart=class{constructor(t,e,i){if(this.value=void 0,this.__pendingValue=void 0,2!==i.length||""!==i[0]||""!==i[1])throw new Error("Boolean attributes can only contain a single expression");this.element=t,this.name=e,this.strings=i}setValue(t){this.__pendingValue=t}commit(){for(;e.isDirective(this.__pendingValue);){const t=this.__pendingValue;this.__pendingValue=s.noChange,t(this)}if(this.__pendingValue===s.noChange)return;const t=!!this.__pendingValue;this.value!==t&&(t?this.element.setAttribute(this.name,""):this.element.removeAttribute(this.name),this.value=t),this.__pendingValue=s.noChange}},t.EventPart=class{constructor(t,e,i){this.value=void 0,this.__pendingValue=void 0,this.element=t,this.eventName=e,this.eventContext=i,this.__boundHandleEvent=t=>this.handleEvent(t)}setValue(t){this.__pendingValue=t}commit(){for(;e.isDirective(this.__pendingValue);){const t=this.__pendingValue;this.__pendingValue=s.noChange,t(this)}if(this.__pendingValue===s.noChange)return;const t=this.__pendingValue,i=this.value,n=null==t||null!=i&&(t.capture!==i.capture||t.once!==i.once||t.passive!==i.passive),a=null!=t&&(null==i||n);n&&this.element.removeEventListener(this.eventName,this.__boundHandleEvent,this.__options),a&&(this.__options=p(t),this.element.addEventListener(this.eventName,this.__boundHandleEvent,this.__options)),this.value=t,this.__pendingValue=s.noChange}handleEvent(t){"function"==typeof this.value?this.value.call(this.eventContext||this.element,t):this.value.handleEvent(t)}},t.NodePart=d,t.PropertyCommitter=class extends l{constructor(t,e,i){super(t,e,i),this.single=2===i.length&&""===i[0]&&""===i[1]}_createPart(){return new c(this)}_getValue(){return this.single?this.parts[0].value:super._getValue()}commit(){this.dirty&&(this.dirty=!1,this.element[this.name]=this._getValue())}},t.PropertyPart=c,t.isIterable=h,t.isPrimitive=r,Object.defineProperty(t,"__esModule",{value:!0})}));