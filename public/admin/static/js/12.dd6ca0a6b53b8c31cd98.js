webpackJsonp([12],{"5vaF":function(t,e){},XwkR:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=n("aFVK"),i=n("vMJZ"),a={data:function(){return{username:"",version:""}},created:function(){this.username=Object(s.d)("userinfo"),this.getTableList()},methods:{getTableList:function(){var t=this;i.a.getVersion({},!0).then(function(e){t.loading=!1,t.version=e.data.version}).catch(function(e){t.loading=!1})}}},r={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"home-container"},[e("h1",{staticClass:"home-title"},[this._v("\n          后台运营管理系统\n    ")]),this._v(" "),e("p",{staticClass:"home-des"},[this._v("\n      尊敬的 "+this._s(this.username)+"  用户，欢迎使用后台管理员系统\n    ")])])},staticRenderFns:[]};var o=n("VU/8")(a,r,!1,function(t){n("5vaF")},null,null);e.default=o.exports}});
//# sourceMappingURL=12.dd6ca0a6b53b8c31cd98.js.map