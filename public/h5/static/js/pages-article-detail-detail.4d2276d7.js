(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-article-detail-detail"],{"22ce":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,".bottom-panel .popup-bg[data-v-ce0b551a]{position:fixed;top:0;right:0;bottom:0;left:0;background:rgba(0,0,0,.6);z-index:98}.bottom-panel .popup-bg .wechat-box[data-v-ce0b551a]{padding-top:var(--window-top)}.bottom-panel .popup-bg .wechat-box uni-image[data-v-ce0b551a]{width:100%}.bottom-panel .content[data-v-ce0b551a]{position:fixed;width:100%;left:0;bottom:0;min-height:%?200?%;max-height:%?900?%;background-color:#fff;-webkit-transform:translate3d(0,%?980?%,0);transform:translate3d(0,%?980?%,0);-webkit-transition:-webkit-transform .2s cubic-bezier(0,0,.25,1);transition:-webkit-transform .2s cubic-bezier(0,0,.25,1);transition:transform .2s cubic-bezier(0,0,.25,1);transition:transform .2s cubic-bezier(0,0,.25,1),-webkit-transform .2s cubic-bezier(0,0,.25,1);bottom:env(safe-area-inset-bottom);z-index:99}.bottom-panel.open .content[data-v-ce0b551a]{-webkit-transform:translateZ(0);transform:translateZ(0)}.bottom-panel.close .popup-bg[data-v-ce0b551a]{display:none}.module-share .hd[data-v-ce0b551a]{height:%?90?%;line-height:%?90?%;font-size:%?36?%}.module-share .item uni-button[data-v-ce0b551a],.module-share .item uni-button[data-v-ce0b551a]::after{background:none;border:none}.module-share .icon-box[data-v-ce0b551a]{width:%?100?%;height:%?100?%;border-radius:50%;background:#f6bd1d}.module-share .icon-box .iconfont[data-v-ce0b551a]{font-size:%?60?%;color:#fff}.module-share .btns[data-v-ce0b551a]{margin-top:%?30?%}.module-share .btns uni-button[data-v-ce0b551a]{height:%?90?%;line-height:%?90?%;border-radius:0;border-top:1px solid #eee}.module-share .btns uni-button[data-v-ce0b551a]::after{border-radius:0}.module-share .share-friend[data-v-ce0b551a]{background:#04be01}",""]),t.exports=e},39921:function(t,e,i){"use strict";i.r(e);var a=i("4cce"),n=i("8103");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("fc91");var r,s=i("f0c5"),c=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"ce0b551a",null,!1,a["a"],r);e["default"]=c.exports},"4cce":function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"bottom-panel",class:t.Visible?"bottom-panel open":"bottom-panel close"},[i("v-uni-view",{staticClass:"popup-bg",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.closePopup.apply(void 0,arguments)}}},[t.wechat_share?i("v-uni-view",{staticClass:"wechat-box"},[i("v-uni-image",{attrs:{src:"/static/share.png",mode:"widthFix"}})],1):t._e()],1),i("v-uni-view",{staticClass:"content",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e)}}},[i("v-uni-view",{staticClass:"module-box module-share"},[i("v-uni-view",{staticClass:"hd d-c-c"},[t._v("分享")]),i("v-uni-view",{staticClass:"p30 box-s-b"},[i("v-uni-view",{staticClass:"d-c-c"},[i("v-uni-view",{staticClass:"item flex-1 d-c-c d-c"},[i("v-uni-button",{attrs:{"open-type":"share"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.share.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"icon-box d-c-c share-friend"},[i("v-uni-text",{staticClass:"iconfont icon-fenxiang"})],1),i("v-uni-text",{staticClass:"pt20"},[t._v("分享好友")])],1)],1)],1)],1),i("v-uni-view",{staticClass:"btns"},[i("v-uni-button",{attrs:{type:"default"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.closePopup(1)}}},[t._v("取消")])],1)],1)],1)],1)},o=[]},"5de2":function(t,e,i){"use strict";var a=i("98f4"),n=i.n(a);n.a},"5e2a":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={data:function(){return{Visible:!1,poster_img:"",wechat_share:!1}},props:["isbottmpanel","article_id"],watch:{isbottmpanel:function(t,e){t!=e&&(this.wechat_share=!1,this.Visible=t)}},methods:{closePopup:function(t){this.$emit("close",{type:t,poster_img:this.poster_img})},share:function(){this.wechat_share=!0},genePoster:function(){var t=this;uni.showLoading({title:"加载中"});var e="wx";e="mp",t._get("product.product/poster",{product_id:t.article_id,source:e},(function(e){t.poster_img=e.data.qrcode,t.closePopup(2)}),null,(function(){uni.hideLoading()}))}}};e.default=a},"7e2e":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,".share-box[data-v-13e11003]{position:fixed;padding-right:%?10?%;width:%?80?%;height:%?80?%;right:0;bottom:%?180?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;border-radius:%?16?% 0 0 %?16?%;background:rgba(0,0,0,.8)}.share-box uni-button[data-v-13e11003]{padding:0;background:0;line-height:%?60?%}.share-box .iconfont[data-v-13e11003]{margin-bottom:%?10?%;font-size:%?50?%;color:#fff}.article-detail[data-v-13e11003]{padding:%?30?%;background:#fff}.article-detail .title[data-v-13e11003]{font-size:%?44?%}.article-detail .info[data-v-13e11003]{padding:%?40?% 0;color:#999}.article-detail .article-content[data-v-13e11003]{width:100%;box-sizing:border-box;line-height:%?60?%;font-size:34 rpx;overflow:hidden}.article-detail .article-content uni-image[data-v-13e11003],\n.article-detail .article-content img[data-v-13e11003]{display:block;max-width:100%}",""]),t.exports=e},8103:function(t,e,i){"use strict";i.r(e);var a=i("5e2a"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},9274:function(t,e,i){"use strict";i.r(e);var a=i("b22b"),n=i("e5ed");for(var o in n)"default"!==o&&function(t){i.d(e,t,(function(){return n[t]}))}(o);i("5de2");var r,s=i("f0c5"),c=Object(s["a"])(n["default"],a["b"],a["c"],!1,null,"13e11003",null,!1,a["a"],r);e["default"]=c.exports},"92c4":function(t,e,i){"use strict";var a=i("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=a(i("39921")),o=a(i("4c72")),r={components:{share:n.default},data:function(){return{isbottmpanel:!1,url:null,loadding:!1,indicatorDots:!0,autoplay:!0,interval:2e3,duration:500,article_id:0,article:{image:{}},from_user_id:""}},onShow:function(){this.joinShare()},onLoad:function(t){this.article_id=t.article_id,this.from_user_id=t.from_user_id,this.url=window.location.href},mounted:function(){this.getData()},methods:{joinShare:function(){if(0==this.from_user_id)return!1;var t=this,e={article_id:t.article_id,from_user_id:t.from_user_id,to_user_id:uni.getStorageSync("user_id")};this._get("plus.sharePolite.SharePolite/getIntegral",e,(function(t){}))},closeBottmpanel:function(t){this.isbottmpanel=!1,2==t.type&&(this.poster_img=t.poster_img,this.isCreatedImg=!0)},showShare:function(){this.isbottmpanel=!0},getData:function(){var t=this;uni.showLoading({title:"加载中"}),this.loading=!0;var e=t.article_id;this._get("plus.article.article/detail",{article_id:e,url:t.url},(function(e){if(e.data.detail.article_content=o.default.format_content(e.data.detail.article_content),t.article=e.data.detail,t.loadding=!0,uni.hideLoading(),""!=t.url){uni.getStorageSync("user_id")||t.doLogin();var i={article_id:t.article_id,from_user_id:uni.getStorageSync("user_id")};t.configWx(e.data.share.signPackage,e.data.share.shareParams,i)}}))}}};e.default=r},"98f4":function(t,e,i){var a=i("7e2e");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("1d974f20",a,!0,{sourceMap:!1,shadowMode:!1})},b22b:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.loadding?i("v-uni-view",{staticClass:"article-detail"},[i("v-uni-view",{staticClass:"title fb"},[t._v(t._s(t.article.article_title))]),i("v-uni-view",{staticClass:"info d-b-c f24"},[i("v-uni-view",[t.article.category?i("v-uni-text",{staticClass:"red"},[t._v(t._s(t.article.category.name))]):t._e(),i("v-uni-text",{staticClass:"ml30"},[t._v(t._s(t.article.create_time))])],1)],1),i("v-uni-view",{staticClass:"article-content",domProps:{innerHTML:t._s(t.article.article_content)}}),i("v-uni-view",{staticClass:"share-box"},[i("v-uni-button",{attrs:{type:"primary"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.showShare.apply(void 0,arguments)}}},[i("v-uni-text",{staticClass:"icon iconfont icon-share"})],1)],1),i("share",{attrs:{isbottmpanel:t.isbottmpanel,product_id:t.article_id},on:{close:function(e){arguments[0]=e=t.$handleEvent(e),t.closeBottmpanel.apply(void 0,arguments)}}})],1):t._e()},o=[]},e5ed:function(t,e,i){"use strict";i.r(e);var a=i("92c4"),n=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(e,t,(function(){return a[t]}))}(o);e["default"]=n.a},fa28:function(t,e,i){var a=i("22ce");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("e4ea7b50",a,!0,{sourceMap:!1,shadowMode:!1})},fc91:function(t,e,i){"use strict";var a=i("fa28"),n=i.n(a);n.a}}]);