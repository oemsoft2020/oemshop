(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-supply-product-assemble-edit","components-upload-upload"],{"0b08":function(t,e,i){"use strict";i("4160"),i("159b"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n={data:function(){return{imageList:[]}},onLoad:function(){},mounted:function(){this.chooseImageFunc()},methods:{chooseImageFunc:function(){var t=this;uni.chooseImage({count:9,sizeType:["original","compressed"],sourceType:["album"],success:function(e){t.uploadFile(e.tempFilePaths)},fail:function(e){t.$emit("getImgs",null)},complete:function(t){}})},uploadFile:function(t){var e=this,i=0,n=t.length,a={token:uni.getStorageSync("token"),app_id:e.getAppId()};uni.showLoading({title:"图片上传中"}),t.forEach((function(t,s){uni.uploadFile({url:e.websiteUrl+"/index.php?s=/api/file.upload/image",filePath:t,name:"iFile",formData:a,success:function(t){var i="object"===typeof t.data?t.data:JSON.parse(t.data);1===i.code&&e.imageList.push(i.data)},complete:function(){i++,n===i&&(uni.hideLoading(),e.$emit("getImgs",e.imageList))}})}))}}};e.default=n},1241:function(t,e,i){"use strict";i.r(e);var n=i("5652"),a=i("f724");for(var s in a)"default"!==s&&function(t){i.d(e,t,(function(){return a[t]}))}(s);var o,u=i("f0c5"),l=Object(u["a"])(a["default"],n["b"],n["c"],!1,null,"132d2670",null,!1,n["a"],o);e["default"]=l.exports},5652:function(t,e,i){"use strict";var n;i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return s})),i.d(e,"a",(function(){return n}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view")},s=[]},"6e8c":function(t,e,i){"use strict";i.r(e);var n=i("dce9"),a=i.n(n);for(var s in n)"default"!==s&&function(t){i.d(e,t,(function(){return n[t]}))}(s);e["default"]=a.a},bfdf:function(t,e,i){"use strict";i.r(e);var n=i("eb65"),a=i("6e8c");for(var s in a)"default"!==s&&function(t){i.d(e,t,(function(){return a[t]}))}(s);var o,u=i("f0c5"),l=Object(u["a"])(a["default"],n["b"],n["c"],!1,null,"97b86204",null,!1,n["a"],o);e["default"]=l.exports},dce9:function(t,e,i){"use strict";(function(t){var n=i("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=n(i("1241")),s={components:{Upload:a.default},data:function(){return{loadding:!0,type:"",isUpload:!1,form:{title:"",image_id:"",start_time:"",end_time:"",assemble_type:1,status:1,together_time:24,sort:100,poster:""},from_url:""}},onLoad:function(t){this.assemble_activity_id=t.assemble_activity_id?t.assemble_activity_id:0},mounted:function(){this.getData()},methods:{getData:function(){var e=this,i=e.assemble_activity_id;uni.showLoading({title:"加载中"}),e._get("plus.supply.assemble/editActive",{assemble_activity_id:i},(function(i){t("log",i," at pages/supply/product/assemble/edit.vue:111");var n=i.data.detail;e.form.title=n.title,e.form.image_id=n.image_id,e.form.start_time=n.start_time,e.form.end_time=n.end_time,e.form.together_time=n.together_time,e.form.poster=n.file.file_path,uni.hideLoading()}))},formSubmit:function(t){var e=this,i=e.form;return i.assemble_activity_id=e.assemble_activity_id,i.title?i.together_time?i.start_time&&i.end_time?i.image_id?(uni.showLoading({title:"正在提交",mask:!0}),void e._post("plus.supply.assemble/editActive",i,(function(t){uni.hideLoading(),uni.showToast({title:t.msg,duration:3e3,complete:function(){uni.navigateTo({url:"/pages/supply/product/assemble/list"})}})}))):(uni.showToast({title:"请上传活动海报",duration:1e3,icon:"none"}),!1):(uni.showToast({title:"请设置活动时间",duration:1e3,icon:"none"}),!1):(uni.showToast({title:"请输入凑团时间",duration:1e3,icon:"none"}),!1):(uni.showToast({title:"请设置名称",duration:1e3,icon:"none"}),!1)},bindStartTime:function(t){this.form.start_time=t.target.value},bindEndTime:function(t){this.form.end_time=t.target.value},openUpload:function(t){this.file_key=t,this.isUpload=!0},getImgsFunc:function(e){var i=this;i.isUpload=!1,e&&"undefined"!=typeof e&&(t("log",e,i.form[i.file_key]," at pages/supply/product/assemble/edit.vue:198"),i.form[i.file_key]=e[0].file_path,i.form.image_id=e[0].file_id)},deleteFunc:function(t){this.form[t]=""}}};e.default=s}).call(this,i("0de9")["log"])},eb65:function(t,e,i){"use strict";var n;i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return s})),i.d(e,"a",(function(){return n}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"refund-apply pb100"},[i("v-uni-form",{on:{submit:function(e){arguments[0]=e=t.$handleEvent(e),t.formSubmit.apply(void 0,arguments)},reset:function(e){arguments[0]=e=t.$handleEvent(e),t.formReset.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"bg-white p-0-30 f30"},[i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("活动名：")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"title",type:"text",placeholder:"请输入"},model:{value:t.form.title,callback:function(e){t.$set(t.form,"title",e)},expression:"form.title"}})],1),i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("凑团时间(小时)：")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"together_time",min:"1",type:"number",placeholder:"请输入整数"},model:{value:t.form.together_time,callback:function(e){t.$set(t.form,"together_time",e)},expression:"form.together_time"}})],1),i("v-uni-view",{staticClass:" bg-white border-b-e"},[i("v-uni-view",{staticClass:"group-hd"},[i("v-uni-view",{staticClass:"left"},[i("v-uni-text",{staticClass:"min-name"},[t._v("活动海报：")])],1)],1),i("v-uni-view",{staticClass:"upload-list d-s-c"},[t.form.poster?i("v-uni-view",{staticClass:"item",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.deleteFunc("poster")}}},[i("v-uni-image",{attrs:{src:t.form.poster,mode:"aspectFit"}})],1):t._e(),t.form.poster?t._e():i("v-uni-view",{staticClass:"item d-c-c d-stretch"},[i("v-uni-view",{staticClass:"upload-btn d-c-c d-c flex-1",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.openUpload("poster")}}},[i("v-uni-text",{staticClass:"icon iconfont icon-xiangji f34"}),i("v-uni-text",{staticClass:"gray9"},[t._v("上传活动海报")])],1)],1)],1),i("v-uni-view",{staticClass:"uni-list"},[i("v-uni-view",{staticClass:"uni-list-cell"},[i("v-uni-view",{staticClass:"uni-list-cell-left"},[t._v("开始日期：")]),i("v-uni-view",{staticClass:"uni-list-cell-db"},[i("v-uni-picker",{attrs:{mode:"date"},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.bindStartTime.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"uni-input"},[t._v(t._s(t.form.start_time||"请选择活动开始日期"))])],1)],1)],1)],1),i("v-uni-view",{staticClass:"uni-list"},[i("v-uni-view",{staticClass:"uni-list-cell"},[i("v-uni-view",{staticClass:"uni-list-cell-left"},[t._v("结束日期：")]),i("v-uni-view",{staticClass:"uni-list-cell-db"},[i("v-uni-picker",{attrs:{mode:"date"},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.bindEndTime.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"uni-input"},[t._v(t._s(t.form.end_time||"请选择活动结束日期"))])],1)],1)],1)],1)],1)],1),i("v-uni-view",{staticClass:"p30"},[i("v-uni-button",{staticClass:"btn-red f30 mt30",attrs:{"form-type":"submit",type:"default"}},[t._v("确定")])],1)],1),t.isUpload?i("Upload",{on:{getImgs:function(e){arguments[0]=e=t.$handleEvent(e),t.getImgsFunc.apply(void 0,arguments)}}}):t._e()],1)},s=[]},f724:function(t,e,i){"use strict";i.r(e);var n=i("0b08"),a=i.n(n);for(var s in n)"default"!==s&&function(t){i.d(e,t,(function(){return n[t]}))}(s);e["default"]=a.a}}]);