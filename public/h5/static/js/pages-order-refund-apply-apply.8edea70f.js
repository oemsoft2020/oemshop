(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-order-refund-apply-apply","components-upload-upload"],{"0b08":function(t,e,i){"use strict";i("4160"),i("159b"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n={data:function(){return{imageList:[]}},onLoad:function(){},mounted:function(){this.chooseImageFunc()},methods:{chooseImageFunc:function(){var t=this;uni.chooseImage({count:9,sizeType:["original","compressed"],sourceType:["album"],success:function(e){t.uploadFile(e.tempFilePaths)},fail:function(e){t.$emit("getImgs",null)},complete:function(t){}})},uploadFile:function(t){var e=this,i=0,n=t.length,a={token:uni.getStorageSync("token"),app_id:e.getAppId()};uni.showLoading({title:"图片上传中"}),t.forEach((function(t,s){uni.uploadFile({url:e.websiteUrl+"/index.php?s=/api/file.upload/image",filePath:t,name:"iFile",formData:a,success:function(t){var i="object"===typeof t.data?t.data:JSON.parse(t.data);1===i.code&&e.imageList.push(i.data)},complete:function(){i++,n===i&&(uni.hideLoading(),e.$emit("getImgs",e.imageList))}})}))}}};e.default=n},1241:function(t,e,i){"use strict";i.r(e);var n=i("5652"),a=i("f724");for(var s in a)"default"!==s&&function(t){i.d(e,t,(function(){return a[t]}))}(s);var u,o=i("f0c5"),r=Object(o["a"])(a["default"],n["b"],n["c"],!1,null,"132d2670",null,!1,n["a"],u);e["default"]=r.exports},1601:function(t,e,i){"use strict";i.r(e);var n=i("b343"),a=i.n(n);for(var s in n)"default"!==s&&function(t){i.d(e,t,(function(){return n[t]}))}(s);e["default"]=a.a},5652:function(t,e,i){"use strict";var n;i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return s})),i.d(e,"a",(function(){return n}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view")},s=[]},"5a8f":function(t,e,i){"use strict";i.r(e);var n=i("641c"),a=i("1601");for(var s in a)"default"!==s&&function(t){i.d(e,t,(function(){return a[t]}))}(s);var u,o=i("f0c5"),r=Object(o["a"])(a["default"],n["b"],n["c"],!1,null,"34b6e94a",null,!1,n["a"],u);e["default"]=r.exports},"641c":function(t,e,i){"use strict";var n;i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return s})),i.d(e,"a",(function(){return n}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"refund-apply pb100"},[i("v-uni-form",{on:{submit:function(e){arguments[0]=e=t.$handleEvent(e),t.formSubmit.apply(void 0,arguments)},reset:function(e){arguments[0]=e=t.$handleEvent(e),t.formReset.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"one-product d-s-c p30 bg-white "},[i("v-uni-view",{staticClass:"cover"},[i("v-uni-image",{attrs:{src:t.product.image.file_path,mode:"aspectFit"}})],1),i("v-uni-view",{staticClass:"flex-1"},[i("v-uni-view",{staticClass:"pro-info"},[t._v(t._s(t.product.product_name))]),i("v-uni-view",{staticClass:"pt10 p-0-30 f24 gray9"},[i("v-uni-text",{},[t._v("单价：¥"+t._s(t.product.line_price))]),i("v-uni-text",{staticClass:"ml20"},[t._v("数量："+t._s(t.product.total_num))])],1)],1)],1),i("v-uni-view",{staticClass:"group bg-white"},[i("v-uni-view",{staticClass:"group-hd border-b-e"},[i("v-uni-view",{staticClass:"left"},[i("v-uni-text",{staticClass:"min-name"},[t._v("服务类型")])],1)],1),i("v-uni-view",{staticClass:"d-s-c p-20-0"},[30!=t.product.orderM.delivery_type.value?i("v-uni-button",{class:10==t.type?"btn-red-border":"",attrs:{type:"default"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.tabType(10)}}},[t._v("退货/退款")]):t._e(),30==t.product.orderM.delivery_type.value?i("v-uni-button",{class:30==t.type?"ml20 btn-red-border":"ml20",attrs:{type:"default"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.tabType(30)}}},[t._v("仅退款")]):t._e()],1)],1),i("v-uni-view",{staticClass:"group bg-white"},[i("v-uni-view",{staticClass:"group-hd"},[i("v-uni-view",{staticClass:"left"},[i("v-uni-text",{staticClass:"min-name"},[t._v("申请原因")])],1)],1),i("v-uni-view",{staticClass:"d-s-c"},[i("v-uni-textarea",{staticClass:"p10 box-s-b border flex-1 f28 lh150",attrs:{value:"",name:"content",placeholder:"请详细填写申请原因，注意保持商品的完好，建议您先与卖家沟通"}})],1)],1),10==t.type||30==t.type?i("v-uni-view",{staticClass:"group bg-white"},[i("v-uni-view",{staticClass:"group-hd"},[i("v-uni-view",{staticClass:"left"},[i("v-uni-text",{staticClass:"min-name"},[t._v("退款金额：")]),i("v-uni-text",{staticClass:"red f30"},[t._v("¥"+t._s(t.product.total_price))])],1)],1)],1):t._e(),i("v-uni-view",{staticClass:"group bg-white"},[i("v-uni-view",{staticClass:"group-hd"},[i("v-uni-view",{staticClass:"left"},[i("v-uni-text",{staticClass:"min-name"},[t._v("上传凭证")]),i("v-uni-text",{staticClass:"gray9"},[t._v("(最多6张)")])],1)],1),i("v-uni-view",{staticClass:"upload-list d-s-c"},[t._l(t.images,(function(e,n){return i("v-uni-view",{key:n,staticClass:"item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.deleteFunc(e)}}},[i("v-uni-image",{attrs:{src:e.file_path,mode:"aspectFit"}})],1)})),t.images.length<6?i("v-uni-view",{staticClass:"item d-c-c d-stretch"},[i("v-uni-view",{staticClass:"upload-btn d-c-c d-c flex-1",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.openUpload()}}},[i("v-uni-text",{staticClass:"icon iconfont icon-xiangji f34"}),i("v-uni-text",{staticClass:"gray9"},[t._v("上传图片")])],1)],1):t._e()],2)],1),i("v-uni-view",{staticClass:"foot-btns"},[i("v-uni-button",{staticClass:"btn-red",attrs:{"form-type":"submit"}},[t._v("确认提交")])],1)],1),t.isUpload?i("Upload",{on:{getImgs:function(e){arguments[0]=e=t.$handleEvent(e),t.getImgsFunc.apply(void 0,arguments)}}}):t._e()],1)},s=[]},b343:function(t,e,i){"use strict";var n=i("4ea4");i("99af"),i("a434"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=n(i("1241")),s={components:{Upload:a.default},data:function(){return{loadding:!0,indicatorDots:!0,autoplay:!0,interval:2e3,duration:500,type:10,isUpload:!1,order_product_id:0,product:{},images:[],temlIds:[]}},onLoad:function(t){this.order_product_id=t.order_product_id},onShow:function(){this.getData()},mounted:function(){this.getData()},methods:{getData:function(){var t=this;uni.showLoading({title:"加载中"});var e=t.order_product_id;t._get("user.refund/apply",{order_product_id:e,platform:t.getPlatform()},(function(e){t.product=e.data.detail,t.temlIds=e.data.template_arr,30==t.product.orderM.delivery_type.value&&(t.type=30),uni.hideLoading()}))},tabType:function(t){this.type=t},formSubmit:function(t){var e=this,i=t.detail.value;i.type=e.type,i.order_product_id=e.order_product_id,i.images=JSON.stringify(e.images);var n=function(){uni.showLoading({title:"正在提交",mask:!0}),e._post("user.refund/apply",i,(function(t){uni.hideLoading(),uni.showToast({title:t.msg,duration:3e3,complete:function(){uni.navigateTo({url:"/pages/order/refund/index/index"})}})}))};e.subMessage(e.temlIds,n)},openUpload:function(){this.isUpload=!0},getImgsFunc:function(t){var e=this;if(e.isUpload=!1,t&&"undefined"!=typeof t){var i=e.images.length,n=t.length;if(i+n<7)e.images=e.images.concat(t);else for(var a=6-i,s=0;s<a;s++)e.images.push(t[s])}},deleteFunc:function(t){this.images.splice(t,1)}}};e.default=s},f724:function(t,e,i){"use strict";i.r(e);var n=i("0b08"),a=i.n(n);for(var s in n)"default"!==s&&function(t){i.d(e,t,(function(){return n[t]}))}(s);e["default"]=a.a}}]);