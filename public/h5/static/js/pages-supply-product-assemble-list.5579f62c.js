(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-supply-product-assemble-list"],{"18ec":function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return o})),e.d(i,"a",(function(){return a}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"load-more"},[e("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:1===t.loadingType&&t.showImage,expression:"loadingType === 1 && showImage"}],staticClass:"loading-img"},[e("v-uni-view",{staticClass:"load1"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1),e("v-uni-view",{staticClass:"load2"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1),e("v-uni-view",{staticClass:"load3"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1)],1),e("v-uni-text",{staticClass:"loading-text",style:{color:t.color}},[t._v(t._s(0===t.loadingType?t.contentText.contentdown:1===t.loadingType?t.contentText.contentrefresh:t.contentText.contentnomore))])],1)},o=[]},"1e56":function(t,i,e){"use strict";e.r(i);var a=e("5456"),n=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,(function(){return a[t]}))}(o);i["default"]=n.a},5456:function(t,i,e){"use strict";var a=e("4ea4");e("99af"),e("a434"),e("ac1f"),e("841c"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n=a(e("ef1a")),o={components:{uniLoadMore:n.default},data:function(){return{phoneHeight:0,scrollviewHigh:0,topRefresh:!1,loading:!0,no_more:!1,type_active:0,price_top:!1,listData:[],page:1,category_id:0,search:"",sortType:"",sortPrice:0,list_rows:10,last_page:0}},computed:{loadingType:function(){return this.loading?1:0!=this.listData.length&&this.no_more?2:0}},onLoad:function(t){this.category_id=t.category_id,t.search&&(this.search=t.search),t.sortType&&(this.sortType=t.sortType),t.sortPrice&&(this.sortPrice=t.sortPrice),this.supply_id=t.supply_id?t.supply_id:0},mounted:function(){this.init(),this.getData()},onPullDownRefresh:function(){this.restoreData(),this.getData()},methods:{init:function(){var t=this;uni.getSystemInfo({success:function(i){t.phoneHeight=i.windowHeight;var e=uni.createSelectorQuery().select(".top-box");e.boundingClientRect((function(i){var e=t.phoneHeight-i.height;t.scrollviewHigh=e})).exec()}})},restoreData:function(){this.listData=[],this.category_id=0,this.search="",this.sortType="",this.sortPrice=0},addAssemble:function(){uni.navigateTo({url:"/pages/supply/product/assemble/add"})},editActivity:function(t){uni.navigateTo({url:"/pages/supply/product/assemble/edit?assemble_activity_id="+t})},tabTypeFunc:function(t){var i=this;i.listData=[],i.page=1,i.no_more=!1,i.loading=!0,2==t?(i.price_top=!this.price_top,1==i.price_top?i.sortPrice=0:i.sortPrice=1,i.sortType="price"):1==t&&(i.price_top=!this.price_top,i.sortType="sales"),i.type_active=t,i.getData()},getData:function(){var t=this;t.page,t.list_rows,t.category_id,t.supply_id,t.search,t.sortType,t.sortPrice;t.loading=!0,t._get("plus.supply.assemble/active",{},(function(i){t.loading=!1,console.log(i),t.listData=t.listData.concat(i.data.list),t.no_more=!0}),(function(t){uni.redirectTo({url:"/pages/user/index/index"})}))},gotoList:function(t){var i="pages/supply/product/assemble/product?assemble_activity_id="+t;this.gotoPage(i)},scrolltolowerFunc:function(){var t=this;if(t.bottomRefresh=!0,t.page++,t.loading=!0,t.page>t.last_page)return t.loading=!1,void(t.no_more=!0);t.getData()},deleteActivity:function(t,i){var e=this;uni.showModal({title:"提示",content:"确认删除？",success:function(a){a.confirm?(uni.showLoading({title:"处理中"}),e._post("plus.supply.assemble/delete",{assemble_activity_id:i},(function(i){console.log(i),e.listData.splice(t,1),uni.hideLoading()}))):a.cancel&&console.log("用户点击取消")}})}}};i.default=o},7613:function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return o})),e.d(i,"a",(function(){return a}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",[e("v-uni-view",{staticClass:"top-box"},[e("v-uni-view",{staticClass:"index-search-box nav-item"},[e("v-uni-button",{staticClass:"btn-red",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.addAssemble()}}},[t._v("添加拼团活动")])],1)],1),e("v-uni-view",{staticClass:"prodcut-list-wrap"},[e("v-uni-scroll-view",{staticClass:"scroll-Y",style:"height:"+t.scrollviewHigh+"px;",attrs:{"scroll-y":"true","lower-threshold":"50"},on:{scrolltolower:function(i){arguments[0]=i=t.$handleEvent(i),t.scrolltolowerFunc.apply(void 0,arguments)}}},[e("v-uni-view",{class:t.topRefresh?"top-refresh open":"top-refresh"},t._l(3,(function(t,i){return e("v-uni-view",{key:i,staticClass:"circle"})})),1),e("v-uni-view",{staticClass:"list"},t._l(t.listData,(function(i,a){return e("v-uni-view",{key:a},[e("v-uni-view",{staticClass:"item"},[e("v-uni-view",{staticClass:"product-cover"},[e("v-uni-image",{attrs:{src:i.file.file_path,mode:"aspectFill"}})],1),e("v-uni-view",{staticClass:"product-info"},[e("v-uni-view",{staticClass:"product-title"},[t._v("活动名称："+t._s(i.title))]),e("v-uni-view",{staticClass:"already-sale"},[e("v-uni-text",[t._v("开始时间"+t._s(i.start_time_text))])],1),e("v-uni-view",{staticClass:"already-sale"},[e("v-uni-text",[t._v("结束时间"+t._s(i.end_time_text))])],1)],1)],1),e("v-uni-view",{staticClass:"order-bts"},[e("v-uni-button",{staticClass:"btn-blue",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.gotoList(i.assemble_activity_id)}}},[t._v("商品管理")]),e("v-uni-button",{on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.editActivity(i.assemble_activity_id)}}},[t._v("修改")]),e("v-uni-button",{on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.deleteActivity(a,i.assemble_activity_id)}}},[t._v("删除")])],1)],1)})),1),0!=t.listData.length||t.loading?e("uni-load-more",{attrs:{loadingType:t.loadingType}}):e("v-uni-view",{staticClass:"d-c-c p30"},[e("v-uni-text",{staticClass:"iconfont icon-wushuju"}),e("v-uni-text",{staticClass:"cont"},[t._v("亲，暂无相关记录哦")])],1)],1)],1)],1)},o=[]},"7ad4":function(t,i,e){"use strict";e("a9e3"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={name:"load-more",props:{loadingType:{type:Number,default:0},showImage:{type:Boolean,default:!0},color:{type:String,default:"#777777"},contentText:{type:Object,default:function(){return{contentdown:"上拉显示更多",contentrefresh:"正在加载...",contentnomore:"没有更多数据了"}}}},data:function(){return{}}};i.default=a},"80f5":function(t,i,e){"use strict";var a=e("87c8"),n=e.n(a);n.a},"87c8":function(t,i,e){var a=e("c7dc");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("6821b5fb",a,!0,{sourceMap:!1,shadowMode:!1})},aaed:function(t,i,e){var a=e("f2b2");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("5108e726",a,!0,{sourceMap:!1,shadowMode:!1})},c0a8:function(t,i,e){"use strict";var a=e("aaed"),n=e.n(a);n.a},c7dc:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,".load-more[data-v-3fb1f804]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;height:%?80?%;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.loading-img[data-v-3fb1f804]{height:24px;width:24px;margin-right:10px}.loading-text[data-v-3fb1f804]{font-size:%?28?%;color:#777}.loading-img>uni-view[data-v-3fb1f804]{position:absolute}.load1[data-v-3fb1f804],\n.load2[data-v-3fb1f804],\n.load3[data-v-3fb1f804]{height:24px;width:24px}.load2[data-v-3fb1f804]{-webkit-transform:rotate(30deg);transform:rotate(30deg)}.load3[data-v-3fb1f804]{-webkit-transform:rotate(60deg);transform:rotate(60deg)}.loading-img>uni-view uni-view[data-v-3fb1f804]{width:6px;height:2px;border-top-left-radius:1px;border-bottom-left-radius:1px;background:#777;position:absolute;opacity:.2;-webkit-transform-origin:50%;transform-origin:50%;-webkit-animation:load-data-v-3fb1f804 1.56s ease infinite}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(1){-webkit-transform:rotate(90deg);transform:rotate(90deg);top:2px;left:9px}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(2){-webkit-transform:rotate(180deg);top:11px;right:0}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(3){-webkit-transform:rotate(270deg);transform:rotate(270deg);bottom:2px;left:9px}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(4){top:11px;left:0}.load1 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:0s;animation-delay:0s}.load2 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:.13s;animation-delay:.13s}.load3 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:.26s;animation-delay:.26s}.load1 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.39s;animation-delay:.39s}.load2 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.52s;animation-delay:.52s}.load3 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.65s;animation-delay:.65s}.load1 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:.78s;animation-delay:.78s}.load2 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:.91s;animation-delay:.91s}.load3 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:1.04s;animation-delay:1.04s}.load1 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.17s;animation-delay:1.17s}.load2 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.3s;animation-delay:1.3s}.load3 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.43s;animation-delay:1.43s}@-webkit-keyframes load-data-v-3fb1f804{0%{opacity:1}100%{opacity:.2}}",""]),t.exports=i},cdee:function(t,i,e){"use strict";e.r(i);var a=e("7613"),n=e("1e56");for(var o in n)"default"!==o&&function(t){e.d(i,t,(function(){return n[t]}))}(o);e("c0a8");var r,s=e("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"60c44abd",null,!1,a["a"],r);i["default"]=l.exports},ef1a:function(t,i,e){"use strict";e.r(i);var a=e("18ec"),n=e("fbec");for(var o in n)"default"!==o&&function(t){e.d(i,t,(function(){return n[t]}))}(o);e("80f5");var r,s=e("f0c5"),l=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"3fb1f804",null,!1,a["a"],r);i["default"]=l.exports},f2b2:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.nav-item[data-v-60c44abd]{display:-webkit-box;display:-webkit-flex;display:flex}.nav-item .index-search[data-v-60c44abd]{width:80%}.nav-item uni-button[data-v-60c44abd]{margin-left:%?10?%}.inner-tab[data-v-60c44abd]{position:relative;height:%?80?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-justify-content:space-around;justify-content:space-around;-webkit-box-align:center;-webkit-align-items:center;align-items:center;border-bottom:1px solid #ddd;background:#fff;box-shadow:0 %?8?% %?12?% 0 rgba(0,0,0,.1);z-index:9}.inner-tab .item[data-v-60c44abd]{-webkit-box-flex:1;-webkit-flex:1;flex:1;font-size:%?30?%}.inner-tab .item.active[data-v-60c44abd],\r\n.inner-tab .item .arrow.active .iconfont[data-v-60c44abd]{color:#e2231a}.inner-tab .item .box[data-v-60c44abd]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row}.inner-tab .item .arrows[data-v-60c44abd]{margin-left:%?10?%;line-height:0}.inner-tab .item .iconfont[data-v-60c44abd]{line-height:%?24?%;font-size:%?24?%}.inner-tab .item .arrow[data-v-60c44abd],\r\n.inner-tab .item .svg-icon[data-v-60c44abd]{width:%?20?%;height:%?20?%}.prodcut-list-wrap .list[data-v-60c44abd]{background:#fff}.prodcut-list-wrap .list .item[data-v-60c44abd]{padding:%?20?%;display:-webkit-box;display:-webkit-flex;display:flex}.prodcut-list-wrap .product-cover[data-v-60c44abd],\r\n.prodcut-list-wrap .product-cover uni-image[data-v-60c44abd]{width:%?220?%;height:%?220?%}.prodcut-list-wrap .product-info[data-v-60c44abd]{-webkit-box-flex:1;-webkit-flex:1;flex:1;margin-left:%?30?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-justify-content:space-around;justify-content:space-around}.prodcut-list-wrap .product-title[data-v-60c44abd]{display:-webkit-box;line-height:%?40?%;height:%?80?%;overflow:hidden;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:%?32?%}.prodcut-list-wrap .already-sale[data-v-60c44abd]{margin-top:%?20?%;color:#999;font-size:%?24?%}.prodcut-list-wrap .already-sale > uni-text[data-v-60c44abd]{padding:%?6?% %?10?%;background-color:#f2f2f7}.prodcut-list-wrap .price[data-v-60c44abd]{color:#e2231a;font-size:%?24?%}.prodcut-list-wrap .price .num[data-v-60c44abd]{margin-left:%?6?%;padding:0 %?4?%;font-size:%?40?%}.prodcut-list-wrap .list .order-bts[data-v-60c44abd]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end;-webkit-box-align:center;-webkit-align-items:center;align-items:center;border-bottom:%?16?% solid #f6f6f6}.prodcut-list-wrap .list .order-bts uni-button[data-v-60c44abd]{margin:0;padding:0 %?20?%;height:%?60?%;line-height:%?60?%;margin-left:%?20?%;margin-bottom:%?20?%;font-size:%?24?%;border:1px solid #ccc;white-space:nowrap}.prodcut-list-wrap .list .order-bts uni-button[data-v-60c44abd]::after{display:none}.prodcut-list-wrap .list .order-bts uni-button.btn-border-red[data-v-60c44abd]{border:1px solid #e2231a;font-size:%?24?%;color:#e2231a}.prodcut-list-wrap .list .order-bts uni-button.btn-red[data-v-60c44abd]{background:#e2231a;border:1px solid #e2231a;font-size:%?24?%;color:#fff}',""]),t.exports=i},fbec:function(t,i,e){"use strict";e.r(i);var a=e("7ad4"),n=e.n(a);for(var o in a)"default"!==o&&function(t){e.d(i,t,(function(){return a[t]}))}(o);i["default"]=n.a}}]);