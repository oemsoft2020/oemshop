(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-address-add-add"],{"173d":function(e,t,i){"use strict";var a=i("3eda"),n=i.n(a);n.a},2414:function(e,t,i){"use strict";i.r(t);var a=i("a970"),n=i("6016");for(var s in n)"default"!==s&&function(e){i.d(t,e,(function(){return n[e]}))}(s);i("173d");var o,d=i("f0c5"),r=Object(d["a"])(n["default"],a["b"],a["c"],!1,null,"578e7060",null,!1,a["a"],o);t["default"]=r.exports},"3eda":function(e,t,i){var a=i("85db");"string"===typeof a&&(a=[[e.i,a,""]]),a.locals&&(e.exports=a.locals);var n=i("4f06").default;n("0cef95de",a,!0,{sourceMap:!1,shadowMode:!1})},6016:function(e,t,i){"use strict";i.r(t);var a=i("d818"),n=i.n(a);for(var s in a)"default"!==s&&function(e){i.d(t,e,(function(){return a[e]}))}(s);t["default"]=n.a},"85db":function(e,t,i){var a=i("24fb");t=a(!1),t.push([e.i,".address-form .key-name[data-v-578e7060]{width:%?200?%}.address-form .btn-red[data-v-578e7060]{height:%?88?%;line-height:%?88?%;border-radius:%?44?%;box-shadow:0 %?8?% %?16?% 0 rgba(226,35,26,.6)}",""]),e.exports=t},a970:function(e,t,i){"use strict";var a;i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return s})),i.d(t,"a",(function(){return a}));var n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{staticClass:"address-form"},[i("v-uni-form",{on:{submit:function(t){arguments[0]=t=e.$handleEvent(t),e.formSubmit.apply(void 0,arguments)},reset:function(t){arguments[0]=t=e.$handleEvent(t),e.formReset.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"bg-white p-0-30 f30"},[i("v-uni-view",{staticClass:"d-s-c"},[i("v-uni-text",{staticClass:"key-name"},[e._v("收货人")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"name",type:"text",placeholder:"请输入收货人姓名"},model:{value:e.address.name,callback:function(t){e.$set(e.address,"name",t)},expression:"address.name"}})],1),i("v-uni-view",{staticClass:"d-s-c"},[i("v-uni-text",{staticClass:"key-name"},[e._v("联系方式")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"phone",type:"text",placeholder:"请输入收货人手机号"},model:{value:e.address.phone,callback:function(t){e.$set(e.address,"phone",t)},expression:"address.phone"}})],1),i("v-uni-view",{staticClass:"d-s-c"},[i("v-uni-text",{staticClass:"key-name"},[e._v("所在地区")]),i("v-uni-view",{staticClass:"input-box flex-1"},[i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{type:"text",value:"","placeholder-class":"grary",placeholder:"",disabled:"true"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.showMulLinkageThreePicker.apply(void 0,arguments)}},model:{value:e.selectCity,callback:function(t){e.selectCity=t},expression:"selectCity"}})],1)],1),i("v-uni-view",{staticClass:"d-s-c"},[i("v-uni-text",{staticClass:"key-name"},[e._v("详细地址")]),i("v-uni-textarea",{staticClass:"ml20 flex-1 p-30-0 lh150",attrs:{name:"detail","auto-height":!0,placeholder:"请输入街道小区楼牌号等"},model:{value:e.address.detail,callback:function(t){e.$set(e.address,"detail",t)},expression:"address.detail"}})],1)],1),i("v-uni-view",{staticClass:"p30"},[i("v-uni-button",{staticClass:"btn-red f30 mt30",attrs:{type:"primary","form-type":"submit"}},[e._v("确认")])],1)],1),i("mpvue-city-picker",{ref:"mpvueCityPicker",attrs:{pickerValueDefault:e.cityPickerValueDefault},on:{onConfirm:function(t){arguments[0]=t=e.$handleEvent(t),e.onConfirm.apply(void 0,arguments)}}})],1)},s=[]},d818:function(e,t,i){"use strict";(function(e){var a=i("4ea4");Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=a(i("7109")),s={components:{mpvueCityPicker:n.default},data:function(){return{cityPickerValueDefault:[0,0,0],selectCity:"选择省,市,区",province_id:0,city_id:0,region_id:0,address:{}}},methods:{formSubmit:function(e){var t=this,i=e.detail.value;if(i.province_id=t.province_id,i.city_id=t.city_id,i.region_id=t.region_id,""==i.name)return uni.showToast({title:"请输入收货人姓名",duration:1e3,icon:"none"}),!1;if(""==i.phone)return uni.showToast({title:"请输入手机号码",duration:1e3,icon:"none"}),!1;var a=/^((0\d{2,3}-\d{7,8})|(1[3456789]\d{9}))$/;return a.test(i.phone)?0!=i.province_id&&0!=i.city_id&&!i.region_id||""!=i.detail?""==i.detail?(uni.showToast({title:"请输入街道小区楼牌号等",duration:1e3,icon:"none"}),!1):void t._post("user.address/add",i,(function(e){t.showSuccess(e.msg,(function(){uni.navigateBack()}))})):(uni.showToast({title:"请选择完整省市区",duration:1e3,icon:"none"}),!1):(uni.showToast({title:"手机号码格式不正确",duration:1e3,icon:"none"}),!1)},formReset:function(t){e("log","清空数据"," at pages/user/address/add/add.vue:119")},showMulLinkageThreePicker:function(){this.$refs.mpvueCityPicker.show()},onConfirm:function(e){this.selectCity=e.label,this.province_id=e.cityCode[0],this.city_id=e.cityCode[1],this.region_id=e.cityCode[2]}}};t.default=s}).call(this,i("0de9")["log"])}}]);