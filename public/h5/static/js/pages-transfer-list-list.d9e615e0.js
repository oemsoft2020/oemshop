(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-transfer-list-list"],{"0951":function(t,i,a){var e=a("24fb");i=e(!1),i.push([t.i,".article-list-wrap .type-list .tab-item[data-v-73208b66]{padding:0 %?30?%;font-size:%?34?%;height:%?86?%;line-height:%?86?%;white-space:nowrap;border-bottom:%?4?% solid #fff}.article-list-wrap .type-list .tab-item.active[data-v-73208b66]{\r\n\t/* border-bottom: 4rpx solid #E2231A; */margin-bottom:0}.article-list[data-v-73208b66]{background:#fff}.article-list .item[data-v-73208b66]{padding:%?30?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;border-bottom:1px solid #e3e3e3}.article-list .item .info[data-v-73208b66]{-webkit-box-flex:1;-webkit-flex:1;flex:1;overflow:hidden}.article-list .item .title[data-v-73208b66]{margin-left:5px;font-size:%?36?%;padding-top:20px}.article-list .item .status[data-v-73208b66]{width:30%;height:20px;\r\n\t/* padding-top: 10px; */float:right;text-align:right;font-size:%?25?%}.article-list .item .summary[data-v-73208b66]{float:left;margin-top:%?20?%;margin-left:5px;font-size:%?28?%;color:#999}.article-list .item .user[data-v-73208b66]{\r\n\t/* float: right; */\r\n\t/* margin-top: 10rpx; */\r\n\t/* padding-top: 20px; */text-align:right;font-size:%?28?%;color:#999}.article-list .item .user1[data-v-73208b66]{float:right;text-align:left;padding-top:10px;font-size:%?28?%;color:#999}.article-list .item .title[data-v-73208b66],\r\n.article-list .item .summary[data-v-73208b66]{display:-webkit-box;overflow:hidden;-webkit-line-clamp:2;-webkit-box-orient:vertical}.article-list .item .pic[data-v-73208b66]{padding-left:%?15?%}.article-list .item .pic[data-v-73208b66],\r\n.article-list .item .pic uni-image[data-v-73208b66]{width:%?160?%;height:%?160?%}.article-list .item .datatime[data-v-73208b66]{float:right;text-align:right;margin-top:%?20?%;\r\n\t/* margin-left: 100px; */font-size:%?25?%;color:#999}",""]),t.exports=i},"18ec":function(t,i,a){"use strict";var e;a.d(i,"b",(function(){return n})),a.d(i,"c",(function(){return o})),a.d(i,"a",(function(){return e}));var n=function(){var t=this,i=t.$createElement,a=t._self._c||i;return a("v-uni-view",{staticClass:"load-more"},[a("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:1===t.loadingType&&t.showImage,expression:"loadingType === 1 && showImage"}],staticClass:"loading-img"},[a("v-uni-view",{staticClass:"load1"},[a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}})],1),a("v-uni-view",{staticClass:"load2"},[a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}})],1),a("v-uni-view",{staticClass:"load3"},[a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}}),a("v-uni-view",{style:{background:t.color}})],1)],1),a("v-uni-text",{staticClass:"loading-text",style:{color:t.color}},[t._v(t._s(0===t.loadingType?t.contentText.contentdown:1===t.loadingType?t.contentText.contentrefresh:t.contentText.contentnomore))])],1)},o=[]},3188:function(t,i,a){"use strict";a.r(i);var e=a("4ca7"),n=a("581a");for(var o in n)"default"!==o&&function(t){a.d(i,t,(function(){return n[t]}))}(o);a("e42c");var l,r=a("f0c5"),s=Object(r["a"])(n["default"],e["b"],e["c"],!1,null,"73208b66",null,!1,e["a"],l);i["default"]=s.exports},"4ca7":function(t,i,a){"use strict";var e;a.d(i,"b",(function(){return n})),a.d(i,"c",(function(){return o})),a.d(i,"a",(function(){return e}));var n=function(){var t=this,i=t.$createElement,a=t._self._c||i;return a("v-uni-view",{staticClass:"article-list-wrap"},[a("v-uni-view",{staticClass:"top-tabbar"},[a("v-uni-scroll-view",{attrs:{"scroll-x":"true","scroll-with-animation":"true"}},[a("v-uni-view",{staticClass:"type-list d-s-c"},[a("v-uni-view",{class:0==t.type_active?"tab-item  active":"tab-item ",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.tabTypeFunc(0)}}},[t._v("全部")]),a("v-uni-view",{class:0==t.type_active?"tab-item  active":"tab-item ",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.tabTypeFunc(1)}}},[t._v("已赠送")]),a("v-uni-view",{class:0==t.type_active?"tab-item  active":"tab-item ",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.tabTypeFunc(2)}}},[t._v("已接收")])],1)],1)],1),[a("v-uni-scroll-view",{staticClass:"scroll-Y",style:"height:"+t.scrollviewHigh+"px;",attrs:{"scroll-y":"true","lower-threshold":"50"},on:{scrolltolower:function(i){arguments[0]=i=t.$handleEvent(i),t.scrolltolowerFunc.apply(void 0,arguments)}}},[a("v-uni-view",{staticClass:"article-list"},t._l(t.listData,(function(i,e){return a("v-uni-view",{key:e,staticClass:"item"},[a("v-uni-view",{staticClass:"pic"},[a("v-uni-image",{attrs:{src:"http://wx-cdn.jiujiuyunhui.com/20191225190430008d44467.png",mode:"aspectFill"}})],1),a("v-uni-view",{staticClass:"info"},[a("v-uni-view",{staticClass:"status"},[a("v-uni-button",{attrs:{type:"warn"}},[t._v(t._s(i.status))])],1),a("v-uni-view",{staticClass:"title"},[t._v(t._s(i.product_name))]),a("v-uni-view",{staticClass:"summary"},[t._v("编号："+t._s(i.number))]),a("v-uni-view",{staticClass:"user"},[t._v("赠送人："+t._s(i.user))]),a("v-uni-view",{staticClass:"user1"},[t._v(t._s(i.time))])],1)],1)})),1),0!=t.listData.length||t.loading?a("uni-load-more",{attrs:{loadingType:t.loadingType}}):a("v-uni-view",{staticClass:"d-c-c p30"},[a("v-uni-text",{staticClass:"iconfont icon-wushuju"}),a("v-uni-text",{staticClass:"cont"},[t._v("亲，暂无相关记录哦")])],1)],1)]],2)},o=[]},"581a":function(t,i,a){"use strict";a.r(i);var e=a("b3d3"),n=a.n(e);for(var o in e)"default"!==o&&function(t){a.d(i,t,(function(){return e[t]}))}(o);i["default"]=n.a},"7ad4":function(t,i,a){"use strict";a("a9e3"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var e={name:"load-more",props:{loadingType:{type:Number,default:0},showImage:{type:Boolean,default:!0},color:{type:String,default:"#777777"},contentText:{type:Object,default:function(){return{contentdown:"上拉显示更多",contentrefresh:"正在加载...",contentnomore:"没有更多数据了"}}}},data:function(){return{}}};i.default=e},"80f5":function(t,i,a){"use strict";var e=a("87c8"),n=a.n(e);n.a},"87c8":function(t,i,a){var e=a("c7dc");"string"===typeof e&&(e=[[t.i,e,""]]),e.locals&&(t.exports=e.locals);var n=a("4f06").default;n("6821b5fb",e,!0,{sourceMap:!1,shadowMode:!1})},b3d3:function(t,i,a){"use strict";(function(t){var e=a("4ea4");a("99af"),a("ac1f"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n=e(a("ef1a")),o={components:{uniLoadMore:n.default},data:function(){return{loading:!0,phoneHeight:0,scrollviewHigh:0,listData:[],no_more:null,list_rows:10,page:1,categorys:[],type_active:0}},computed:{loadingType:function(){return this.loading?1:0!=this.listData.length&&this.no_more?2:0}},mounted:function(){this.init(),this.getData()},methods:{init:function(){var t=this;uni.getSystemInfo({success:function(i){t.phoneHeight=i.windowHeight;var a=uni.createSelectorQuery().select(".top-tabbar");a.boundingClientRect((function(i){var a=t.phoneHeight-i.height;t.scrollviewHigh=a})).exec()}})},tabTypeFunc:function(t){t!=this.type_active&&(this.type_active=t,this.page=1,this.listData=[],this.getData())},getData:function(){var i=this,a=i.page,e=i.list_rows;i.loading=!0,uni.showLoading({title:"加载中"}),i._get("plus.transfer.transfer/index",{page:a||1,list_rows:e,category_id:i.type_active},(function(a){t("log",a.data.list.data," at pages/transfer/list/list.vue:151"),i.listData=i.listData.concat(a.data.list.data),i.last_page=a.data.list.last_page,a.data.list.last_page<=1&&(i.no_more=!0),i.loading=!1,uni.hideLoading()}))},scrolltolowerFunc:function(){var t=this;if(t.bottomRefresh=!0,t.page++,t.loading=!0,t.page>t.last_page)return t.loading=!1,void(t.no_more=!0);t.getData()},gotoDetail:function(t){uni.navigateTo({url:"/pages/article/detail/detail?article_id="+t})}}};i.default=o}).call(this,a("0de9")["log"])},c7dc:function(t,i,a){var e=a("24fb");i=e(!1),i.push([t.i,".load-more[data-v-3fb1f804]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:horizontal;-webkit-box-direction:normal;-webkit-flex-direction:row;flex-direction:row;height:%?80?%;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.loading-img[data-v-3fb1f804]{height:24px;width:24px;margin-right:10px}.loading-text[data-v-3fb1f804]{font-size:%?28?%;color:#777}.loading-img>uni-view[data-v-3fb1f804]{position:absolute}.load1[data-v-3fb1f804],\n.load2[data-v-3fb1f804],\n.load3[data-v-3fb1f804]{height:24px;width:24px}.load2[data-v-3fb1f804]{-webkit-transform:rotate(30deg);transform:rotate(30deg)}.load3[data-v-3fb1f804]{-webkit-transform:rotate(60deg);transform:rotate(60deg)}.loading-img>uni-view uni-view[data-v-3fb1f804]{width:6px;height:2px;border-top-left-radius:1px;border-bottom-left-radius:1px;background:#777;position:absolute;opacity:.2;-webkit-transform-origin:50%;transform-origin:50%;-webkit-animation:load-data-v-3fb1f804 1.56s ease infinite}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(1){-webkit-transform:rotate(90deg);transform:rotate(90deg);top:2px;left:9px}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(2){-webkit-transform:rotate(180deg);top:11px;right:0}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(3){-webkit-transform:rotate(270deg);transform:rotate(270deg);bottom:2px;left:9px}.loading-img>uni-view uni-view[data-v-3fb1f804]:nth-child(4){top:11px;left:0}.load1 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:0s;animation-delay:0s}.load2 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:.13s;animation-delay:.13s}.load3 uni-view[data-v-3fb1f804]:nth-child(1){-webkit-animation-delay:.26s;animation-delay:.26s}.load1 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.39s;animation-delay:.39s}.load2 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.52s;animation-delay:.52s}.load3 uni-view[data-v-3fb1f804]:nth-child(2){-webkit-animation-delay:.65s;animation-delay:.65s}.load1 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:.78s;animation-delay:.78s}.load2 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:.91s;animation-delay:.91s}.load3 uni-view[data-v-3fb1f804]:nth-child(3){-webkit-animation-delay:1.04s;animation-delay:1.04s}.load1 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.17s;animation-delay:1.17s}.load2 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.3s;animation-delay:1.3s}.load3 uni-view[data-v-3fb1f804]:nth-child(4){-webkit-animation-delay:1.43s;animation-delay:1.43s}@-webkit-keyframes load-data-v-3fb1f804{0%{opacity:1}100%{opacity:.2}}",""]),t.exports=i},e3bc:function(t,i,a){var e=a("0951");"string"===typeof e&&(e=[[t.i,e,""]]),e.locals&&(t.exports=e.locals);var n=a("4f06").default;n("e2edfa90",e,!0,{sourceMap:!1,shadowMode:!1})},e42c:function(t,i,a){"use strict";var e=a("e3bc"),n=a.n(e);n.a},ef1a:function(t,i,a){"use strict";a.r(i);var e=a("18ec"),n=a("fbec");for(var o in n)"default"!==o&&function(t){a.d(i,t,(function(){return n[t]}))}(o);a("80f5");var l,r=a("f0c5"),s=Object(r["a"])(n["default"],e["b"],e["c"],!1,null,"3fb1f804",null,!1,e["a"],l);i["default"]=s.exports},fbec:function(t,i,a){"use strict";a.r(i);var e=a("7ad4"),n=a.n(e);for(var o in e)"default"!==o&&function(t){a.d(i,t,(function(){return e[t]}))}(o);i["default"]=n.a}}]);