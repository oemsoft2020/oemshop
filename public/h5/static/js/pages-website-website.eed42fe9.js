(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-website-website"],{5194:function(t,e,a){"use strict";var i=a("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=i(a("9f57")),u=i(a("f271")),r={components:{diy:n.default,Tabbar:u.default},data:function(){return{page_id:0,items:{},page_info:{}}},onLoad:function(t){this.page_id=t.page_id?t.page_id:0,this.supply_id=t.supply_id?t.supply_id:0;var e=uni.getStorageSync("currentSupplyId");!this.supply_id&&e&&(this.supply_id=e),this.getData()},methods:{getData:function(){var t=this;t._get("index/website",{page_id:t.page_id,supply_id:t.supply_id},(function(e){t.page_info=e.data.page,t.items=e.data.items,t.setPage(t.page_info)}))},setPage:function(t){uni.setNavigationBarTitle({title:t.params.title});var e="#000000";"white"==t.style.titleTextColor&&(e="#ffffff"),uni.setNavigationBarColor({frontColor:e,backgroundColor:t.style.titleBackgroundColor})}}};e.default=r},ac31:function(t,e,a){"use strict";a.d(e,"b",(function(){return n})),a.d(e,"c",(function(){return u})),a.d(e,"a",(function(){return i}));var i={diy:a("9f57").default},n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-uni-view",{staticClass:"diy-page"},[a("diy",{attrs:{diyItems:t.items,diyPage:t.page_info}}),a("Tabbar")],1)},u=[]},c62e:function(t,e,a){"use strict";a.r(e);var i=a("ac31"),n=a("eab7");for(var u in n)"default"!==u&&function(t){a.d(e,t,(function(){return n[t]}))}(u);var r,o=a("f0c5"),s=Object(o["a"])(n["default"],i["b"],i["c"],!1,null,"44a18e32",null,!1,i["a"],r);e["default"]=s.exports},eab7:function(t,e,a){"use strict";a.r(e);var i=a("5194"),n=a.n(i);for(var u in i)"default"!==u&&function(t){a.d(e,t,(function(){return i[t]}))}(u);e["default"]=n.a}}]);