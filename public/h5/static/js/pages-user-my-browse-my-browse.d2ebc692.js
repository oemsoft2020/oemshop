(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-my-browse-my-browse"],{"0a2a":function(t,a,i){"use strict";i.r(a);var e=i("cfbf"),s=i.n(e);for(var n in e)"default"!==n&&function(t){i.d(a,t,(function(){return e[t]}))}(n);a["default"]=s.a},"11cb":function(t,a,i){var e=i("24fb");a=e(!1),a.push([t.i,".product-item-list .product-item-head .state-text[data-v-4446e839]{margin-top:auto;vertical-align:middle}.product-item-list  .item[data-v-4446e839]{margin-top:%?30?%;padding:%?30?%;background:#fff}.product-item-list .product-list[data-v-4446e839],\n.product-item-list .one-product[data-v-4446e839]{padding:%?20?% 0;height:%?160?%}",""]),t.exports=a},5251:function(t,a,i){"use strict";i.r(a);var e=i("e78a"),s=i("0a2a");for(var n in s)"default"!==n&&function(t){i.d(a,t,(function(){return s[t]}))}(n);i("956b");var o,c=i("f0c5"),u=Object(c["a"])(s["default"],e["b"],e["c"],!1,null,"4446e839",null,!1,e["a"],o);a["default"]=u.exports},"84f5":function(t,a,i){var e=i("11cb");"string"===typeof e&&(e=[[t.i,e,""]]),e.locals&&(t.exports=e.locals);var s=i("4f06").default;s("a4f5a14c",e,!0,{sourceMap:!1,shadowMode:!1})},"956b":function(t,a,i){"use strict";var e=i("84f5"),s=i.n(e);s.a},cfbf:function(t,a,i){"use strict";i("99af"),i("ac1f"),Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var e={data:function(){return{phoneHeight:0,scrollviewHigh:0,state_active:0,page:1,DataList:[],supplyDataList:[],no_more:!1,loading:!1,data_type:"product",transfer_setting:!0}},mounted:function(){this.init(),this.getData()},methods:{init:function(){var t=this;uni.getSystemInfo({success:function(a){t.phoneHeight=a.windowHeight;var i=uni.createSelectorQuery().select(".top-tabbar");i.boundingClientRect((function(a){var i=t.phoneHeight-a.height;t.scrollviewHigh=i})).exec()}})},getData:function(){var t=this;uni.showLoading({title:"加载中"});var a=t.data_type,i=t.page;t._get("user.browse/lists",{data_type:a,page:i||1},(function(i){uni.hideLoading(),"product"==a?t.DataList=t.DataList.concat(i.data.list.data):t.supplyDataList=t.supplyDataList.concat(i.data.list.data),console.log(t.supplyDataList),t.last_page=i.data.list.last_page,i.data.list.last_page<=1&&(t.no_more=!0)}))},stateFunc:function(t){var a=this;a.state_active!=t&&(console.log(t),0==t&&(a.data_type="product",a.supplyDataList=[]),1==t&&(a.data_type="supply",a.DataList=[]),a.state_active=t,a.page=1,a.getData())},scrolltolowerFunc:function(){var t=this;if(t.page++,t.loading=!0,t.page>t.last_page)return t.loading=!1,void(t.no_more=!0);t.getData()},gotoProduct:function(t){var a=t.product.product_id,i="pages/product/detail/detail?product_id="+a;this.gotoPage(i)},gotoSupply:function(t){var a=t.supply.supply_id,i="pages/supply/detail/detail?supply_id="+a;this.gotoPage(i)}}};a.default=e},e78a:function(t,a,i){"use strict";var e;i.d(a,"b",(function(){return s})),i.d(a,"c",(function(){return n})),i.d(a,"a",(function(){return e}));var s=function(){var t=this,a=t.$createElement,i=t._self._c||a;return i("v-uni-view",[i("v-uni-view",{staticClass:"top-tabbar"},[i("v-uni-view",{class:0==t.state_active?"tab-item active":"tab-item",on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.stateFunc(0)}}},[t._v("看过的商品")]),i("v-uni-view",{class:1==t.state_active?"tab-item active":"tab-item",on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.stateFunc(1)}}},[t._v("看过的门店")])],1),i("v-uni-scroll-view",{staticClass:"scroll-Y",style:"height:"+t.scrollviewHigh+"px;",attrs:{"scroll-y":"true","lower-threshold":"50"},on:{scrolltolower:function(a){arguments[0]=a=t.$handleEvent(a),t.scrolltolowerFunc.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"product-item-list"},[t._l(t.DataList,(function(a,e){return i("v-uni-view",{key:e,staticClass:"item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.gotoProduct(a)}}},[i("v-uni-view",{staticClass:"product-item-head d-b-c"},[i("v-uni-view",[i("v-uni-text",{staticClass:"state-text"},[i("v-uni-text",{staticClass:"icon iconfont icon-Homehomepagemenu"})],1),i("v-uni-text",{staticClass:"shop-name flex-1"},[t._v(t._s(a.product.supply?a.product.supply.name:"平台"))])],1)],1),i("v-uni-view",{staticClass:"one-product d-s-c"},[t._l(a.product.image,(function(t,a){return i("v-uni-view",{key:a,staticClass:"cover"},[i("v-uni-image",{attrs:{src:t.file_path,mode:"aspectFit"}})],1)})),i("v-uni-view",{staticClass:"pro-info flex-1"},[t._v(t._s(a.product.product_name)),i("v-uni-view",{staticClass:"price f22"},[t._v("¥"),i("v-uni-text",{staticClass:"f40"},[t._v(t._s(a.product.product_price))])],1)],1)],2)],1)})),t._l(t.supplyDataList,(function(a,e){return i("v-uni-view",{key:e,staticClass:"item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.gotoSupply(a)}}},[i("v-uni-view",{staticClass:"one-product d-s-c"},[i("v-uni-view",{staticClass:"cover"},[i("v-uni-image",{attrs:{src:a.supply.image_url,mode:"aspectFit"}})],1),i("v-uni-view",{staticClass:"pro-info flex-1"},[t._v(t._s(a.supply.name)),i("v-uni-view",{staticClass:"price f22"},[i("v-uni-text",{staticClass:"f30"},[t._v(t._s(a.supply.address))])],1)],1)],1)],1)})),i("v-uni-view",{},[i("v-uni-view",{staticClass:"bottom-refresh"},[t.loading?i("v-uni-view",{staticClass:"d-c-c p30"},[i("v-uni-text",{staticClass:"gray3"},[t._v("加载中...")])],1):t._e(),t.no_more?i("v-uni-view",{staticClass:"d-c-c p30"},[i("v-uni-text",{staticClass:"gray3"},[t._v("~~加载完成~~")])],1):t._e()],1)],1)],2)],1)],1)},n=[]}}]);