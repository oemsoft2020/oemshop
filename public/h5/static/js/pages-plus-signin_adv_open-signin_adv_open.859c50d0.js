(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-plus-signin_adv_open-signin_adv_open"],{"7ace":function(t,e,n){"use strict";n.r(e);var i=n("b1f9"),a=n("d674");for(var o in a)"default"!==o&&function(t){n.d(e,t,(function(){return a[t]}))}(o);n("f0ac");var c,r=n("f0c5"),u=Object(r["a"])(a["default"],i["b"],i["c"],!1,null,"a5383432",null,!1,i["a"],c);e["default"]=u.exports},b1f9:function(t,e,n){"use strict";var i;n.d(e,"b",(function(){return a})),n.d(e,"c",(function(){return o})),n.d(e,"a",(function(){return i}));var a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{staticStyle:{"margin-bottom":"3rem"}},[t._l(t.orderList,(function(e,i){return n("v-uni-view",{key:i},[n("v-uni-view",{staticClass:"zo",style:{height:6*(e.product_count-1)+8+"rem"}},[n("v-uni-label",{staticStyle:{width:"10%",float:"left","line-height":"8rem"}},[n("v-uni-checkbox",{attrs:{checked:e.checked},on:{click:function(n){arguments[0]=n=t.$handleEvent(n),t.xuan(e.checked,i)}}})],1),n("v-uni-view",{staticStyle:{width:"80%",float:"left"}},[n("v-uni-view",[n("v-uni-view",{staticStyle:{float:"left"}},[t._v("订单号："+t._s(e.order_no))]),n("v-uni-view",{staticStyle:{color:"red",float:"right"}},[t._v(t._s(e.state_text))]),n("v-uni-view",{staticStyle:{clear:"both"}})],1),t._l(e.product_list,(function(e,i){return n("v-uni-view",{key:i,staticStyle:{"margin-top":"1rem"}},[n("v-uni-view",{staticStyle:{float:"left"}},[n("v-uni-image",{staticStyle:{width:"5rem",height:"5rem"},attrs:{src:e.product_img}})],1),n("v-uni-view",{staticStyle:{float:"left","margin-left":"10px"}},[t._v(t._s(e.product_name)+"x"+t._s(e.product_num))]),n("v-uni-view",{staticStyle:{clear:"both"}})],1)}))],2),n("v-uni-view",{staticStyle:{clear:"both"}}),n("v-uni-view",{staticStyle:{position:"relative",bottom:"1.5rem",float:"right",right:"2rem"}},[t._v("有效期："+t._s(e.days_count)+"天")])],1)],1)})),n("v-uni-view",{staticStyle:{position:"fixed",width:"100%","background-color":"#FFFFFF",bottom:"0",height:"2.5rem",padding:"0rem 0.5rem"}},[n("v-uni-view",{staticStyle:{"line-height":"2.5rem",float:"left"}},[n("v-uni-label",[n("v-uni-checkbox",{attrs:{checked:t.quan},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.xuan_quan()}}})],1),n("v-uni-text",[t._v("全选")])],1),n("v-uni-view",{staticStyle:{float:"right","line-height":"2.5rem","margin-right":".5rem"}},[n("v-uni-view",{staticStyle:{float:"left","margin-right":"2rem"}},[t._v("合计："+t._s(t.tian)+"天")]),n("v-uni-view",{staticStyle:{float:"left",width:"4rem","text-align":"center","background-color":"#e2231a",color:"#FFFFFF"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.go_open()}}},[t._v("开通")]),n("v-uni-view",{staticStyle:{clear:"both"}})],1),n("v-uni-view",{staticStyle:{clear:"both"}})],1)],2)},o=[]},b23a:function(t,e,n){"use strict";(function(t){n("4160"),n("159b"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i={data:function(){return{orderList:[],quan:!1,dan:!1,tian:0}},onLoad:function(){this.getOrderList()},onShow:function(){},methods:{getOrderList:function(){var e=this;e._get("plus.signadv.Signadv/getOrderList",{},(function(n){t("log",n," at pages/plus/signin_adv_open/signin_adv_open.vue:62"),e.orderList=n.data.is_order_list}))},xuan_quan:function(){var t=this;t.quan?(t.quan=!1,t.orderList.forEach((function(t){t.checked=!1}))):(t.quan=!0,t.orderList.forEach((function(t){t.checked=!0}))),t.calculateTian()},calculateTian:function(){var e=this,n=0;e.orderList.forEach((function(t){t.checked&&(n+=t.days_count)})),e.tian=n,t("log",n," at pages/plus/signin_adv_open/signin_adv_open.vue:90")},xuan:function(t,e){var n=this;n.orderList[e]["checked"]=!t,n.calculateTian()},go_open:function(){var e=this,n="";if(e.orderList.forEach((function(t){t.checked&&(n=n+","+t.order_id)})),t("log",n," at pages/plus/signin_adv_open/signin_adv_open.vue:109"),0==n.length)return uni.showModal({title:"提示",content:"请选择订单",showCancel:!1}),!1;e._get("plus.signadv.signadv/changeOrderType",{order_ids:n,days:e.tian},(function(t){uni.showModal({title:"提示",content:t.msg,showCancel:!1,success:function(t){if(t.confirm){var e="/pages/plus/signin_adv/signin_adv";uni.navigateTo({url:"/pages/common/success?go_url="+e+"&go_font=前去打卡"})}}})}),(function(){}))}}};e.default=i}).call(this,n("0de9")["log"])},b404:function(t,e,n){var i=n("e7c2");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var a=n("4f06").default;a("4ad3d76f",i,!0,{sourceMap:!1,shadowMode:!1})},d674:function(t,e,n){"use strict";n.r(e);var i=n("b23a"),a=n.n(i);for(var o in i)"default"!==o&&function(t){n.d(e,t,(function(){return i[t]}))}(o);e["default"]=a.a},e7c2:function(t,e,n){var i=n("24fb");e=i(!1),e.push([t.i,".zo[data-v-a5383432]{width:100%;border:1px solid #d7d7d7;padding:.5rem 1rem;background-color:#fff;margin-top:.5rem}",""]),t.exports=e},f0ac:function(t,e,n){"use strict";var i=n("b404"),a=n.n(i);a.a}}]);