(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-agent-qrcode-qrcode"],{"07d3":function(t,e,n){"use strict";var a=n("6f5e"),i=n.n(a);i.a},4495:function(t,e,n){"use strict";n.r(e);var a=n("f494"),i=n("ebb6");for(var o in i)"default"!==o&&function(t){n.d(e,t,(function(){return i[t]}))}(o);n("07d3");var u,r=n("f0c5"),s=Object(r["a"])(i["default"],a["b"],a["c"],!1,null,"247aa82a",null,!1,a["a"],u);e["default"]=s.exports},"4e3a":function(t,e,n){var a=n("24fb");e=a(!1),e.push([t.i,".qrcode uni-image[data-v-247aa82a]{width:100%}.btns-wrap[data-v-247aa82a]{position:fixed;height:%?88?%;right:0;bottom:0;left:0;display:-webkit-box;display:-webkit-flex;display:flex;z-index:10}.btns-wrap .btn-red[data-v-247aa82a]{width:100%;height:%?88?%;line-height:%?88?%;border-radius:0}",""]),t.exports=e},"6f5e":function(t,e,n){var a=n("4e3a");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var i=n("4f06").default;i("44434eca",a,!0,{sourceMap:!1,shadowMode:!1})},c726:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={data:function(){return{qrcode_url:""}},mounted:function(){this.getData()},methods:{getData:function(){var t=this;uni.showLoading({title:"加载中"});var e="wx";e="mp",t._get("plus.agent.qrcode/poster",{source:e},(function(e){uni.hideLoading(),t.qrcode_url=e.data.qrcode}))},savePosterImg:function(){var t=this;uni.showLoading({title:"加载中"}),uni.downloadFile({url:t.qrcode_url,success:function(e){uni.hideLoading(),uni.saveImageToPhotosAlbum({filePath:e.tempFilePath,success:function(e){uni.showToast({title:"保存成功",icon:"success",duration:2e3}),t.isCreatedImg=!1},fail:function(t){console.log(t.errMsg),"saveImageToPhotosAlbum:fail auth deny"===t.errMsg&&(uni.showToast({title:"请允许访问相册后重试",icon:"none",duration:1e3}),setTimeout((function(){uni.openSetting()}),1e3))},complete:function(t){console.log("complete")}})}})}}};e.default=a},ebb6:function(t,e,n){"use strict";n.r(e);var a=n("c726"),i=n.n(a);for(var o in a)"default"!==o&&function(t){n.d(e,t,(function(){return a[t]}))}(o);e["default"]=i.a},f494:function(t,e,n){"use strict";var a;n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return o})),n.d(e,"a",(function(){return a}));var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{staticClass:"qrcode"},[n("v-uni-image",{attrs:{src:t.qrcode_url,mode:"widthFix"}}),n("v-uni-view",{staticClass:"btns-wrap"},[n("v-uni-view",{staticClass:"f34 tc ww100",attrs:{type:"default"}},[t._v("长按保存图片")])],1)],1)},o=[]}}]);