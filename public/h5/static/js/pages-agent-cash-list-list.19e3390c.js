(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-agent-cash-list-list"],{"18ec":function(t,a,e){"use strict";var i;e.d(a,"b",(function(){return n})),e.d(a,"c",(function(){return o})),e.d(a,"a",(function(){return i}));var n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("v-uni-view",{staticClass:"load-more"},[e("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:1===t.loadingType&&t.showImage,expression:"loadingType === 1 && showImage"}],staticClass:"loading-img"},[e("v-uni-view",{staticClass:"load1"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1),e("v-uni-view",{staticClass:"load2"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1),e("v-uni-view",{staticClass:"load3"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1)],1),e("v-uni-text",{staticClass:"loading-text",style:{color:t.color}},[t._v(t._s(0===t.loadingType?t.contentText.contentdown:1===t.loadingType?t.contentText.contentrefresh:t.contentText.contentnomore))])],1)},o=[]},"1a99":function(t,a,e){"use strict";(function(t){var i=e("4ea4");e("99af"),e("ac1f"),Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var n=i(e("ef1a")),o={components:{uniLoadMore:n.default},data:function(){return{phoneHeight:0,scrollviewHigh:0,state_active:-1,tableData:[],no_more:!1,loading:!0,last_page:0,page:1,list_rows:20,tableList:[]}},computed:{loadingType:function(){return this.loading?1:0!=this.tableData.length&&this.no_more?2:0}},mounted:function(){this.init(),this.getData()},methods:{init:function(){var t=this;uni.getSystemInfo({success:function(a){t.phoneHeight=a.windowHeight;var e=uni.createSelectorQuery().select(".top-tabbar");e.boundingClientRect((function(a){var e=t.phoneHeight-a.height;t.scrollviewHigh=e})).exec()}})},getData:function(){var t=this,a=t.page;t.loading=!0;var e=t.list_rows;t._get("plus.agent.cash/lists",{status:t.state_active,page:a||1,list_rows:e},(function(a){if(t.loading=!1,t.tableList=[{value:-1,text:a.data.words.cash_list.words.all.value},{value:10,text:a.data.words.cash_list.words.apply_10.value},{value:20,text:a.data.words.cash_list.words.apply_20.value},{value:40,text:a.data.words.cash_list.words.apply_40.value},{value:30,text:a.data.words.cash_list.words.apply_30.value}],t.tableData=t.tableData.concat(a.data.list.data),t.last_page=a.data.list.last_page,a.data.list.last_page<=1)return t.no_more=!0,!1}))},stateFunc:function(t){var a=this;t!=a.state_active&&(a.tableData=[],a.page=1,a.state_active=t,a.getData())},scrolltoupperFunc:function(){t("log","滚动视图区域到顶"," at pages/agent/cash/list/list.vue:165")},scrolltolowerFunc:function(){var t=this;t.page<t.last_page&&(t.page++,t.getData()),t.no_more=!0}}};a.default=o}).call(this,e("0de9")["log"])},"7ad4":function(t,a,e){"use strict";e("a9e3"),Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var i={name:"load-more",props:{loadingType:{type:Number,default:0},showImage:{type:Boolean,default:!0},color:{type:String,default:"#777777"},contentText:{type:Object,default:function(){return{contentdown:"上拉显示更多",contentrefresh:"正在加载...",contentnomore:"没有更多数据了"}}}},data:function(){return{}}};a.default=i},"80f5":function(t,a,e){"use strict";var i=e("87c8"),n=e.n(i);n.a},"87c8":function(t,a,e){var i=e("c7dc");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var n=e("4f06").default;n("6821b5fb",i,!0,{sourceMap:!1,shadowMode:!1})},bd54:function(t,a,e){"use strict";e.r(a);var i=e("1a99"),n=e.n(i);for(var o in i)"default"!==o&&function(t){e.d(a,t,(function(){return i[t]}))}(o);a["default"]=n.a},c7dc:function(t,a,e){var i=e("24fb");a=i(!1),a.push([t.i,".load-more[data-v-3fb1f804]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;height:%?80?%;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.loading-img[data-v-3fb1f804]{height:24px;width:24px;margin-right:10px}.loading-text[data-v-3fb1f804]{font-size:%?28?%;color:#777}.loading-img>uni-view[data-v-3fb1f804]{position:absolute}.load1[data-v-3fb1f804],\n.load2[data-v-3fb1f804],\n.load3[data-v-3fb1f804]{height:24px;width:24px}.load2[data-v-3fb1f804]{-webkit-transform:rotate(30deg);transform:rotate(30deg)}.load3[data-v-3fb1f804]{-webkit-transform:rotate(60deg);transform:rotate(60deg)}.loading-img>uni-view uni-view[data-v-3fb1f804]{width:6px;height:2px;border-top-left-radius:1px;border-bottom-left-radius:1px;background:#777;position:absolute;opacity:.2;-webkit-transform-origin:50%;transform-origin:50%;-webkit-animation:load-data-v-3fb1f804 1.56s ease infinite}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(1){-webkit-transform:rotate(90deg);transform:rotate(90deg);top:2px;left:9px}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(2){-webkit-transform:rotate(180deg);top:11px;right:0}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(3){-webkit-transform:rotate(270deg);transform:rotate(270deg);bottom:2px;left:9px}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(4){top:11px;left:0}.load1 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:0s;animation-delay:0s}.load2 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:.13s;animation-delay:.13s}.load3 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:.26s;animation-delay:.26s}.load1 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.39s;animation-delay:.39s}.load2 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.52s;animation-delay:.52s}.load3 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.65s;animation-delay:.65s}.load1 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:.78s;animation-delay:.78s}.load2 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:.91s;animation-delay:.91s}.load3 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:1.04s;animation-delay:1.04s}.load1 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.17s;animation-delay:1.17s}.load2 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.3s;animation-delay:1.3s}.load3 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.43s;animation-delay:1.43s}@-webkit-keyframes load-data-v-3fb1f804{0%{opacity:1}100%{opacity:.2}}",""]),t.exports=a},c858:function(t,a,e){"use strict";var i;e.d(a,"b",(function(){return n})),e.d(a,"c",(function(){return o})),e.d(a,"a",(function(){return i}));var n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("v-uni-view",[e("v-uni-view",{staticClass:"top-tabbar"},t._l(t.tableList,(function(a,i){return e("v-uni-view",{key:i,class:t.state_active==a.value?"tab-item active":"tab-item",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.stateFunc(a.value)}}},[t._v(t._s(a.text))])})),1),e("v-uni-scroll-view",{staticClass:"scroll-Y",style:"height:"+t.scrollviewHigh+"px;",attrs:{"scroll-y":"true","lower-threshold":"50"},on:{scrolltoupper:function(a){arguments[0]=a=t.$handleEvent(a),t.scrolltoupperFunc.apply(void 0,arguments)},scrolltolower:function(a){arguments[0]=a=t.$handleEvent(a),t.scrolltolowerFunc.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"p-0-30 bg-white"},[t._l(t.tableData,(function(a,i){return e("v-uni-view",{key:i,staticClass:"d-b-c border-b p-20-0"},[e("v-uni-view",{staticClass:"d-s-s f-w d-c flex-1"},[e("v-uni-text",{staticClass:"30"},[t._v("提现")]),e("v-uni-text",{staticClass:"gray9 f22"},[t._v(t._s(a.create_time))])],1),e("v-uni-view",[e("v-uni-text",{class:"审核通过"==a.apply_status.text?"green":"gray9"},[t._v(t._s(a.apply_status.text))]),e("v-uni-text",{staticClass:"red ml20"},[t._v(t._s(a.money)+"元")])],1)],1)})),0!=t.tableData.length||t.loading?e("uni-load-more",{attrs:{loadingType:t.loadingType}}):e("v-uni-view",{staticClass:"d-c-c p30"},[e("v-uni-text",{staticClass:"iconfont icon-wushuju"}),e("v-uni-text",{staticClass:"cont"},[t._v("亲，暂无相关记录哦")])],1)],2)],1)],1)},o=[]},ef1a:function(t,a,e){"use strict";e.r(a);var i=e("18ec"),n=e("fbec");for(var o in n)"default"!==o&&function(t){e.d(a,t,(function(){return n[t]}))}(o);e("80f5");var l,s=e("f0c5"),r=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"3fb1f804",null,!1,i["a"],l);a["default"]=r.exports},f67a:function(t,a,e){"use strict";e.r(a);var i=e("c858"),n=e("bd54");for(var o in n)"default"!==o&&function(t){e.d(a,t,(function(){return n[t]}))}(o);var l,s=e("f0c5"),r=Object(s["a"])(n["default"],i["b"],i["c"],!1,null,"466668bf",null,!1,i["a"],l);a["default"]=r.exports},fbec:function(t,a,e){"use strict";e.r(a);var i=e("7ad4"),n=e.n(i);for(var o in i)"default"!==o&&function(t){e.d(a,t,(function(){return i[t]}))}(o);a["default"]=n.a}}]);