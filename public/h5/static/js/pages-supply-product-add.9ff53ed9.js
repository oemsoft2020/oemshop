(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-supply-product-add","components-upload-upload"],{"0b08":function(t,e,i){"use strict";i("4160"),i("159b"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n={data:function(){return{imageList:[]}},onLoad:function(){},mounted:function(){this.chooseImageFunc()},methods:{chooseImageFunc:function(){var t=this;uni.chooseImage({count:9,sizeType:["original","compressed"],sourceType:["album"],success:function(e){t.uploadFile(e.tempFilePaths)},fail:function(e){t.$emit("getImgs",null)},complete:function(t){}})},uploadFile:function(t){var e=this,i=0,n=t.length,a={token:uni.getStorageSync("token"),app_id:e.getAppId()};uni.showLoading({title:"图片上传中"}),t.forEach((function(t,s){uni.uploadFile({url:e.websiteUrl+"/index.php?s=/api/file.upload/image",filePath:t,name:"iFile",formData:a,success:function(t){var i="object"===typeof t.data?t.data:JSON.parse(t.data);1===i.code&&e.imageList.push(i.data)},complete:function(){i++,n===i&&(uni.hideLoading(),e.$emit("getImgs",e.imageList))}})}))}}};e.default=n},1241:function(t,e,i){"use strict";i.r(e);var n=i("5652"),a=i("f724");for(var s in a)"default"!==s&&function(t){i.d(e,t,(function(){return a[t]}))}(s);var o,u=i("f0c5"),c=Object(u["a"])(a["default"],n["b"],n["c"],!1,null,"132d2670",null,!1,n["a"],o);e["default"]=c.exports},5652:function(t,e,i){"use strict";var n;i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return s})),i.d(e,"a",(function(){return n}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view")},s=[]},6536:function(t,e,i){"use strict";var n;i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return s})),i.d(e,"a",(function(){return n}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:" pb100"},[i("v-uni-form",{on:{submit:function(e){arguments[0]=e=t.$handleEvent(e),t.formSubmit.apply(void 0,arguments)},reset:function(e){arguments[0]=e=t.$handleEvent(e),t.formReset.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"bg-white p-0-30 f30"},[i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("商品名：")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"product_name",type:"text",placeholder:"请输入"},model:{value:t.form.product_name,callback:function(e){t.$set(t.form,"product_name",e)},expression:"form.product_name"}})],1),i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("商品编号：")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"product_no",type:"text",placeholder:"请输入"},model:{value:t.form.product_no,callback:function(e){t.$set(t.form,"product_no",e)},expression:"form.product_no"}})],1),i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("商品分类：")]),i("v-uni-view",{staticClass:"uni-list-cell-db"},[i("v-uni-picker",{attrs:{mode:"selector",value:"",range:t.category,"range-key":"name"},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.bindCatChange.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"uni-input"},[t._v(t._s(t.cat_name))])],1)],1)],1),i("v-uni-view",{staticClass:" bg-white"},[i("v-uni-view",{staticClass:"group-hd"},[i("v-uni-view",{staticClass:"left"},[i("v-uni-text",{staticClass:"min-name"},[t._v("商品图片：")]),i("v-uni-text",{staticClass:"gray9"},[t._v("(最多6张)")])],1)],1),i("v-uni-view",{staticClass:"upload-list d-s-c"},[t._l(t.form.image,(function(e,n){return i("v-uni-view",{key:n,staticClass:"item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.deleteFunc(e)}}},[i("v-uni-image",{attrs:{src:e.file_path,mode:"aspectFit"}})],1)})),t.form.image.length<6?i("v-uni-view",{staticClass:"item d-c-c d-stretch"},[i("v-uni-view",{staticClass:"upload-btn d-c-c d-c flex-1",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.openUpload()}}},[i("v-uni-text",{staticClass:"icon iconfont icon-xiangji f34"}),i("v-uni-text",{staticClass:"gray9"},[t._v("上传图片")])],1)],1):t._e()],2)],1),i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("销售价：")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"product_price",type:"text",placeholder:"请输入"},model:{value:t.form.sku.product_price,callback:function(e){t.$set(t.form.sku,"product_price",e)},expression:"form.sku.product_price"}})],1),i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("成本价：")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"product_supply_price",type:"text",placeholder:"请输入"},model:{value:t.form.sku.product_supply_price,callback:function(e){t.$set(t.form.sku,"product_supply_price",e)},expression:"form.sku.product_supply_price"}})],1),i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("划线价：")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"line_price",type:"text",placeholder:"请输入"},model:{value:t.form.sku.line_price,callback:function(e){t.$set(t.form.sku,"line_price",e)},expression:"form.sku.line_price"}})],1),i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("总库存：")]),i("v-uni-input",{staticClass:"ml20 flex-1 p-30-0",attrs:{name:"stock_num",type:"text",placeholder:"请输入"},model:{value:t.form.sku.stock_num,callback:function(e){t.$set(t.form.sku,"stock_num",e)},expression:"form.sku.stock_num"}})],1),i("v-uni-view",{staticClass:"d-s-c border-b-e"},[i("v-uni-text",{staticClass:"key-name"},[t._v("运费模板：")]),i("v-uni-view",{staticClass:"uni-list-cell-db"},[i("v-uni-picker",{attrs:{mode:"selector",value:"",range:t.delivery,"range-key":"name"},on:{change:function(e){arguments[0]=e=t.$handleEvent(e),t.bindDeliveryChange.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"uni-input"},[t._v(t._s(t.delivery_name))])],1)],1)],1),i("v-uni-view",{staticClass:"d-s-c border-b-e",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.gotoContent.apply(void 0,arguments)}}},[i("v-uni-text",{staticClass:"key-name"},[t._v("商品详情：")]),i("v-uni-view",{staticClass:"m20 flex-1 p-30-0",staticStyle:{width:"80%","text-align":"right"}},[t._v("详情")])],1)],1),i("v-uni-view",{staticClass:"p30"},[i("v-uni-button",{staticClass:"btn-red f30 mt30",attrs:{"form-type":"submit",type:"default"}},[t._v("下一步")])],1)],1),t.isUpload?i("Upload",{on:{getImgs:function(e){arguments[0]=e=t.$handleEvent(e),t.getImgsFunc.apply(void 0,arguments)}}}):t._e()],1)},s=[]},a947:function(t,e,i){"use strict";i.r(e);var n=i("6536"),a=i("e4e8");for(var s in a)"default"!==s&&function(t){i.d(e,t,(function(){return a[t]}))}(s);var o,u=i("f0c5"),c=Object(u["a"])(a["default"],n["b"],n["c"],!1,null,"19ada5b3",null,!1,n["a"],o);e["default"]=c.exports},e4e8:function(t,e,i){"use strict";i.r(e);var n=i("f742"),a=i.n(n);for(var s in n)"default"!==s&&function(t){i.d(e,t,(function(){return n[t]}))}(s);e["default"]=a.a},f724:function(t,e,i){"use strict";i.r(e);var n=i("0b08"),a=i.n(n);for(var s in n)"default"!==s&&function(t){i.d(e,t,(function(){return n[t]}))}(s);e["default"]=a.a},f742:function(t,e,i){"use strict";var n=i("4ea4");i("99af"),i("4160"),i("a434"),i("159b"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a=n(i("1241")),s={components:{Upload:a.default},data:function(){return{gongsijieshao:"",images:[],loadding:!0,isUpload:!1,cat_name:"选择分类",delivery_name:"选择运费模板",category:[],delivery:[],form:{product_name:"",product_days:"",delivery_type:[],product_no:"",product_diy_no:"",category_id:null,brand_id:null,image:[],selling_point:"",spec_type:10,deduct_stock_type:20,sale_time:"",delivery_time:"",sku:{product_price:0,product_supply_price:0,line_price:0,stock_num:0,product_weight:"0"},content:"",delivery_id:"",product_status:10,link_status:0,link_start_at:"",link_end_at:"",sales_initial:0,product_sort:100,setting:{buy_auth:{show_price_id:[],buy_auth_id:[],tips:"",name:"跳转连接",linkUrl:""}},commission_type:[],automatic_shelves:0},from_url:""}},onLoad:function(t){this.from_url=t.from_url?t.from_url:"",uni.setStorageSync("goods_content","")},onShow:function(){console.log(100);var t=uni.getStorageSync("goods_content");console.log(t),t&&(this.form.content=t)},mounted:function(){this.getData()},methods:{bindCatChange:function(t){var e=t.detail.value,i=this;i.form.category_id=i.category[e].category_id,i.cat_name=i.category[e]["name"]},bindDeliveryChange:function(t){var e=t.detail.value,i=this;i.form.delivery_id=i.delivery[e].delivery_id,i.delivery_name=i.delivery[e]["name"]},gotoContent:function(){uni.navigateTo({url:"/pages/supply/product/part/content"})},getData:function(){var t=this;uni.showLoading({title:"加载中"}),t._get("plus.supply.product/add",{},(function(e){var i=e.data.catgory;i.forEach((function(e){t.category.push(e),e.child&&e.child.forEach((function(e){t.category.push(e)}))})),t.delivery=e.data.delivery,uni.hideLoading()}))},alert:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";uni.showToast({title:t,duration:1e3,icon:"none"})},formSubmit:function(t){var e=this,i=e.form,n={};return i.certify_type=e.type,n.product_data=JSON.stringify(i),""==i.product_name?(e.alert("请输入名称"),!1):i.category_id?0==i.sku.product_price?(e.alert("请输入销售价"),!1):0==i.sku.product_supply_price?(e.alert("请输入供货价格"),!1):0==i.sku.stock_num?(e.alert("请输入库存"),!1):0==i.delivery_id?(e.alert("请选择运费模板"),!1):(uni.showLoading({title:"正在提交",mask:!0}),void e._post("plus.supply.product/add",n,(function(t){uni.hideLoading(),uni.showModal({title:"提示",content:t.msg,showCancel:!1,success:function(){uni.redirectTo({url:"/pages/supply/product/list/list"})}})}))):(e.alert("请选择分类"),!1)},openUpload:function(t){this.file_key=t,this.isUpload=!0},getImgsFunc:function(t){var e=this;if(e.isUpload=!1,t&&"undefined"!=typeof t){var i=e.form.image.length,n=t.length;if(i+n<7)e.form.image=e.form.image.concat(t);else for(var a=6-i,s=0;s<a;s++)e.form.image.push(t[s])}},deleteFunc:function(t){this.form.image.splice(t,1)}}};e.default=s}}]);