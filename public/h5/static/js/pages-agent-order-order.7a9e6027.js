(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-agent-order-order"],{"0a75":function(t,e,a){"use strict";var i=a("4b5d"),n=a.n(i);n.a},"18ec":function(t,e,a){"use strict";var i;a.d(e,"b",(function(){return n})),a.d(e,"c",(function(){return o})),a.d(e,"a",(function(){return i}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-uni-view",{staticClass:"load-more"},[a("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:1===t.loadingType&&t.showImage,expression:"loadingType === 1 && showImage"}],staticClass:"loading-img"},[a("v-uni-view",{staticClass:"load1"},[a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}})],1),a("v-uni-view",{staticClass:"load2"},[a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}})],1),a("v-uni-view",{staticClass:"load3"},[a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}})],1)],1),a("v-uni-text",{staticClass:"loading-text",style:{color:t.color}},[t._v(t._s(0===t.loadingType?t.contentText.contentdown:1===t.loadingType?t.contentText.contentrefresh:t.contentText.contentnomore))])],1)},o=[]},3923:function(t,e,a){var i=a("24fb");e=i(!1),e.push([t.i,".agent-order-photo[data-v-29411856],\n.agent-order-photo uni-image[data-v-29411856]{width:%?80?%;height:%?80?%;border-radius:50%}",""]),t.exports=e},4889:function(t,e,a){"use strict";a.r(e);var i=a("c048"),n=a.n(i);for(var o in i)"default"!==o&&function(t){a.d(e,t,(function(){return i[t]}))}(o);e["default"]=n.a},"4b5d":function(t,e,a){var i=a("3923");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("4f06").default;n("78d2546e",i,!0,{sourceMap:!1,shadowMode:!1})},"5f6c":function(t,e,a){"use strict";a.r(e);var i=a("7ffa"),n=a("4889");for(var o in n)"default"!==o&&function(t){a.d(e,t,(function(){return n[t]}))}(o);a("0a75");var s,r=a("f0c5"),l=Object(r["a"])(n["default"],i["b"],i["c"],!1,null,"29411856",null,!1,i["a"],s);e["default"]=l.exports},"7ad4":function(t,e,a){"use strict";a("a9e3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i={name:"load-more",props:{loadingType:{type:Number,default:0},showImage:{type:Boolean,default:!0},color:{type:String,default:"#777777"},contentText:{type:Object,default:function(){return{contentdown:"上拉显示更多",contentrefresh:"正在加载...",contentnomore:"没有更多数据了"}}}},data:function(){return{}}};e.default=i},"7ffa":function(t,e,a){"use strict";var i;a.d(e,"b",(function(){return n})),a.d(e,"c",(function(){return o})),a.d(e,"a",(function(){return i}));var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-uni-view",[a("v-uni-view",{staticClass:"top-tabbar"},t._l(t.tableList,(function(e,i){return a("v-uni-view",{key:i,class:t.state_active==e.value?"tab-item active":"tab-item",on:{click:function(a){arguments[0]=a=t.$handleEvent(a),t.stateFunc(e.value)}}},[t._v(t._s(e.text))])})),1),a("v-uni-scroll-view",{staticClass:"scroll-Y",style:"height:"+t.scrollviewHigh+"px;",attrs:{"scroll-y":"true","lower-threshold":"50"},on:{scrolltolower:function(e){arguments[0]=e=t.$handleEvent(e),t.scrolltolowerFunc.apply(void 0,arguments)}}},[a("v-uni-view",{staticClass:"p-0-30 bg-white"},[t._l(t.tableData,(function(e,i){return a("v-uni-view",{key:i,staticClass:"border-b p-20-0"},[a("v-uni-view",{staticClass:"d-b-c f24"},[a("v-uni-text",[t._v("订单号"+t._s(e.order_master.order_no))]),1==e.is_settled?a("v-uni-text",{staticClass:"blue"},[t._v("已结算")]):a("v-uni-text",{staticClass:"gray"},[t._v("未结算")])],1),e.user?a("v-uni-view",{staticClass:"d-b-c mt20"},[a("v-uni-view",{staticClass:"agent-order-photo"},[a("v-uni-image",{attrs:{src:e.user.avatarUrl,mode:"aspectFill"}})],1),a("v-uni-view",{staticClass:"flex-1 ml20 f24"},[a("v-uni-view",{staticClass:"d-b-c"},[a("v-uni-text",{staticClass:"gray3"},[t._v(t._s(e.user.nickName))]),e.first_user_id==e.user.user_id?a("v-uni-text",{staticClass:"red"},[t._v("+￥"+t._s(e.first_money))]):t._e(),e.second_user_id==e.user.user_id?a("v-uni-text",{staticClass:"red"},[t._v("+￥"+t._s(e.second_money))]):t._e(),e.third_user_id==e.user.user_id?a("v-uni-text",{staticClass:"red"},[t._v("+￥"+t._s(e.third_money))]):t._e()],1),a("v-uni-view",{staticClass:"d-b-c"},[a("v-uni-text",{staticClass:"gray9"},[t._v("消费金额：￥"+t._s(e.order_price))]),a("v-uni-text",{staticClass:"gray9"},[t._v(t._s(e.create_time))])],1),e.first_user_id==t.user_id&&e.first_money>=.01?a("v-uni-view",{staticClass:"d-b-c"},[a("v-uni-text",{staticClass:"blue"},[t._v("一级分销佣金：￥"+t._s(e.first_money))])],1):t._e(),e.second_user_id==t.user_id&&e.second_money>=.01?a("v-uni-view",{staticClass:"d-b-c"},[a("v-uni-text",{staticClass:"blue"},[t._v("二级分销佣金：￥"+t._s(e.second_money))])],1):t._e(),e.third_user_id==t.user_id&&e.third_money>=.01?a("v-uni-view",{staticClass:"d-b-c"},[a("v-uni-text",{staticClass:"blue"},[t._v("二级分销佣金：￥"+t._s(e.third_money))])],1):t._e(),t._l(e.different_level,(function(e,i){return e.user.user_id==t.user_id?a("v-uni-view",{key:i,staticClass:"d-b-c"},[a("v-uni-text",{staticClass:"blue"},[t._v("代理佣金：￥"+t._s(e.money))])],1):t._e()})),t._l(e.commission_rules,(function(e,i){return e.user.user_id==t.user_id?a("v-uni-view",{key:i,staticClass:"d-b-c"},[a("v-uni-text",{staticClass:"blue"},[t._v("推荐佣金：￥"+t._s(e.money))])],1):t._e()}))],2)],1):t._e()],1)})),0!=t.tableData.length||t.loading?a("uni-load-more",{attrs:{loadingType:t.loadingType}}):a("v-uni-view",{staticClass:"d-c-c p30"},[a("v-uni-text",{staticClass:"iconfont icon-wushuju"}),a("v-uni-text",{staticClass:"cont"},[t._v("亲，暂无相关记录哦")])],1)],2)],1)],1)},o=[]},"80f5":function(t,e,a){"use strict";var i=a("87c8"),n=a.n(i);n.a},"87c8":function(t,e,a){var i=a("c7dc");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=a("4f06").default;n("6821b5fb",i,!0,{sourceMap:!1,shadowMode:!1})},c048:function(t,e,a){"use strict";var i=a("4ea4");a("99af"),a("ac1f"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=i(a("ef1a")),o={components:{uniLoadMore:n.default},data:function(){return{phoneHeight:0,scrollviewHigh:0,state_active:-1,tableData:[],settled:-1,page:1,no_more:!1,loading:!0,tableList:[],list_rows:15,user_id:0}},computed:{loadingType:function(){return this.loading?1:0!=this.tableData.length&&this.no_more?2:0}},mounted:function(){this.init(),this.getData()},methods:{init:function(){var t=this;uni.getSystemInfo({success:function(e){t.phoneHeight=e.windowHeight;var a=uni.createSelectorQuery().select(".top-tabbar");a.boundingClientRect((function(e){var a=t.phoneHeight-e.height;t.scrollviewHigh=a})).exec()}}),t.user_id=uni.getStorageSync("user_id")},getData:function(){var t=this;t.loading=!0,t._get("plus.agent.order/lists",{settled:t.state_active,page:t.page||1,list_rows:t.list_rows},(function(e){t.loading=!1,t.tableList=[{value:-1,text:e.data.words.order.words.all.value},{value:0,text:e.data.words.order.words.unsettled.value},{value:1,text:e.data.words.order.words.settled.value}],t.tableData=t.tableData.concat(e.data.list.data),t.last_page=e.data.list.last_page,t.last_page<=1&&(t.no_more=!0)}))},stateFunc:function(t){var e=this;t!=e.state_active&&(e.tableData=[],e.page=1,e.state_active=t,e.getData())},scrolltolowerFunc:function(){var t=this;t.no_more||(t.page++,t.page<=t.last_page?t.getData():t.no_more=!0)}}};e.default=o},c7dc:function(t,e,a){var i=a("24fb");e=i(!1),e.push([t.i,".load-more[data-v-3fb1f804]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;height:%?80?%;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.loading-img[data-v-3fb1f804]{height:24px;width:24px;margin-right:10px}.loading-text[data-v-3fb1f804]{font-size:%?28?%;color:#777}.loading-img>uni-view[data-v-3fb1f804]{position:absolute}.load1[data-v-3fb1f804],\n.load2[data-v-3fb1f804],\n.load3[data-v-3fb1f804]{height:24px;width:24px}.load2[data-v-3fb1f804]{-webkit-transform:rotate(30deg);transform:rotate(30deg)}.load3[data-v-3fb1f804]{-webkit-transform:rotate(60deg);transform:rotate(60deg)}.loading-img>uni-view uni-view[data-v-3fb1f804]{width:6px;height:2px;border-top-left-radius:1px;border-bottom-left-radius:1px;background:#777;position:absolute;opacity:.2;-webkit-transform-origin:50%;transform-origin:50%;-webkit-animation:load-data-v-3fb1f804 1.56s ease infinite}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(1){-webkit-transform:rotate(90deg);transform:rotate(90deg);top:2px;left:9px}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(2){-webkit-transform:rotate(180deg);top:11px;right:0}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(3){-webkit-transform:rotate(270deg);transform:rotate(270deg);bottom:2px;left:9px}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(4){top:11px;left:0}.load1 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:0s;animation-delay:0s}.load2 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:.13s;animation-delay:.13s}.load3 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:.26s;animation-delay:.26s}.load1 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.39s;animation-delay:.39s}.load2 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.52s;animation-delay:.52s}.load3 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.65s;animation-delay:.65s}.load1 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:.78s;animation-delay:.78s}.load2 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:.91s;animation-delay:.91s}.load3 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:1.04s;animation-delay:1.04s}.load1 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.17s;animation-delay:1.17s}.load2 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.3s;animation-delay:1.3s}.load3 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.43s;animation-delay:1.43s}@-webkit-keyframes load-data-v-3fb1f804{0%{opacity:1}100%{opacity:.2}}",""]),t.exports=e},ef1a:function(t,e,a){"use strict";a.r(e);var i=a("18ec"),n=a("fbec");for(var o in n)"default"!==o&&function(t){a.d(e,t,(function(){return n[t]}))}(o);a("80f5");var s,r=a("f0c5"),l=Object(r["a"])(n["default"],i["b"],i["c"],!1,null,"3fb1f804",null,!1,i["a"],s);e["default"]=l.exports},fbec:function(t,e,a){"use strict";a.r(e);var i=a("7ad4"),n=a.n(i);for(var o in i)"default"!==o&&function(t){a.d(e,t,(function(){return i[t]}))}(o);e["default"]=n.a}}]);