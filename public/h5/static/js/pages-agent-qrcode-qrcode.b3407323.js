(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-agent-qrcode-qrcode"],{"07d3":function(t,e,a){"use strict";var n=a("6f5e"),i=a.n(n);i.a},4495:function(t,e,a){"use strict";a.r(e);var n=a("f494"),i=a("ebb6");for(var o in i)"default"!==o&&function(t){a.d(e,t,(function(){return i[t]}))}(o);a("07d3");var u,r=a("f0c5"),s=Object(r["a"])(i["default"],n["b"],n["c"],!1,null,"247aa82a",null,!1,n["a"],u);e["default"]=s.exports},"4e3a":function(t,e,a){var n=a("24fb");e=n(!1),e.push([t.i,".qrcode uni-image[data-v-247aa82a]{width:100%}.btns-wrap[data-v-247aa82a]{position:fixed;height:%?88?%;right:0;bottom:0;left:0;display:-webkit-box;display:-webkit-flex;display:flex;z-index:10}.btns-wrap .btn-red[data-v-247aa82a]{width:100%;height:%?88?%;line-height:%?88?%;border-radius:0}",""]),t.exports=e},"6f5e":function(t,e,a){var n=a("4e3a");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var i=a("4f06").default;i("44434eca",n,!0,{sourceMap:!1,shadowMode:!1})},c726:function(t,e,a){"use strict";(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={data:function(){return{qrcode_url:""}},mounted:function(){this.getData()},methods:{getData:function(){var t=this;uni.showLoading({title:"加载中"});var e="wx";e="mp",t._get("plus.agent.qrcode/poster",{source:e},(function(e){uni.hideLoading(),t.qrcode_url=e.data.qrcode}))},savePosterImg:function(){var e=this;uni.showLoading({title:"加载中"}),uni.downloadFile({url:e.qrcode_url,success:function(a){uni.hideLoading(),uni.saveImageToPhotosAlbum({filePath:a.tempFilePath,success:function(t){uni.showToast({title:"保存成功",icon:"success",duration:2e3}),e.isCreatedImg=!1},fail:function(e){t("log",e.errMsg," at pages/agent/qrcode/qrcode.vue:70"),"saveImageToPhotosAlbum:fail auth deny"===e.errMsg&&(uni.showToast({title:"请允许访问相册后重试",icon:"none",duration:1e3}),setTimeout((function(){uni.openSetting()}),1e3))},complete:function(e){t("log","complete"," at pages/agent/qrcode/qrcode.vue:83")}})}})}}};e.default=a}).call(this,a("0de9")["log"])},ebb6:function(t,e,a){"use strict";a.r(e);var n=a("c726"),i=a.n(n);for(var o in n)"default"!==o&&function(t){a.d(e,t,(function(){return n[t]}))}(o);e["default"]=i.a},f494:function(t,e,a){"use strict";var n;a.d(e,"b",(function(){return i})),a.d(e,"c",(function(){return o})),a.d(e,"a",(function(){return n}));var i=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-uni-view",{staticClass:"qrcode"},[a("v-uni-image",{attrs:{src:t.qrcode_url,mode:"widthFix"}}),a("v-uni-view",{staticClass:"btns-wrap"},[a("v-uni-view",{staticClass:"f34 tc ww100",attrs:{type:"default"}},[t._v("长按保存图片")])],1)],1)},o=[]}}]);