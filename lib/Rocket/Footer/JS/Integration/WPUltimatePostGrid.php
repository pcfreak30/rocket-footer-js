<?php


namespace Rocket\Footer\JS\Integration;


class WPUltimatePostGrid extends IntegrationAbstract {
	public function init() {
		if ( class_exists( '\WPUltimatePostGrid' ) && $this->plugin->lazyload_manager->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		}
	}

	public function scripts() {
		wp_add_inline_script( 'wpupg_public', '
		(function(f,g,l){g[f]=g[f]||l();"undefined"!=typeof module&&module.exports?module.exports=g[f]:"function"==typeof define&&define.amd&&define(function(){return g[f]})})("Promise","undefined"!=typeof global?global:this,function(){function f(b,a){p.add(b,a);n||(n=w(p.drain))}function g(b){var a=typeof b;if(null!=b&&("object"==a||"function"==a))var c=b.then;return"function"==typeof c?c:!1}function l(){for(var b=0;b<this.chain.length;b++){var a=void 0,c=void 0,e=1===this.state?this.chain[b].success:this.chain[b].failure,
d=this.chain[b];try{!1===e?d.reject(this.msg):(c=!0===e?this.msg:e.call(void 0,this.msg),c===d.promise?d.reject(TypeError("Promise-chain cycle")):(a=g(c))?a.call(c,d.resolve,d.reject):d.resolve(c))}catch(x){d.reject(x)}}this.chain.length=0}function q(b){var a,c=this;if(!c.triggered){c.triggered=!0;c.def&&(c=c.def);try{(a=g(b))?f(function(){var e=new r(c);try{a.call(b,function(){q.apply(e,arguments)},function(){m.apply(e,arguments)})}catch(d){m.call(e,d)}}):(c.msg=b,c.state=1,0<c.chain.length&&f(l,
c))}catch(e){m.call(new r(c),e)}}}function m(b){var a=this;a.triggered||(a.triggered=!0,a.def&&(a=a.def),a.msg=b,a.state=2,0<a.chain.length&&f(l,a))}function t(b,a,c,e){for(var d=0;d<a.length;d++)(function(d){b.resolve(a[d]).then(function(a){c(d,a)},e)})(d)}function r(b){this.def=b;this.triggered=!1}function y(b){this.promise=b;this.state=0;this.triggered=!1;this.chain=[];this.msg=void 0}function h(b){if("function"!=typeof b)throw TypeError("Not a function");if(0!==this.__NPO__)throw TypeError("Not a promise");
this.__NPO__=1;var a=new y(this);this.then=function(c,b){var d={success:"function"==typeof c?c:!0,failure:"function"==typeof b?b:!1};d.promise=new this.constructor(function(a,c){if("function"!=typeof a||"function"!=typeof c)throw TypeError("Not a function");d.resolve=a;d.reject=c});a.chain.push(d);0!==a.state&&f(l,a);return d.promise};this["catch"]=function(a){return this.then(void 0,a)};try{b.call(void 0,function(c){q.call(a,c)},function(c){m.call(a,c)})}catch(c){m.call(a,c)}}var n,u=Object.prototype.toString,
w="undefined"!=typeof setImmediate?function(b){return setImmediate(b)}:setTimeout;try{Object.defineProperty({},"x",{});var k=function(b,a,c,e){return Object.defineProperty(b,a,{value:c,writable:!0,configurable:!1!==e})}}catch(b){k=function(a,c,b){a[c]=b;return a}}var p=function(){function b(a,b){this.fn=a;this.self=b;this.next=void 0}var a,c,e;return{add:function(d,f){e=new b(d,f);c?c.next=e:a=e;c=e;e=void 0},drain:function(){var b=a;for(a=c=n=void 0;b;)b.fn.call(b.self),b=b.next}}}();var v=k({},
"constructor",h,!1);h.prototype=v;k(v,"__NPO__",0,!1);k(h,"resolve",function(b){return b&&"object"==typeof b&&1===b.__NPO__?b:new this(function(a,c){if("function"!=typeof a||"function"!=typeof c)throw TypeError("Not a function");a(b)})});k(h,"reject",function(b){return new this(function(a,c){if("function"!=typeof a||"function"!=typeof c)throw TypeError("Not a function");c(b)})});k(h,"all",function(b){var a=this;return"[object Array]"!=u.call(b)?a.reject(TypeError("Not an array")):0===b.length?a.resolve([]):
new a(function(c,e){if("function"!=typeof c||"function"!=typeof e)throw TypeError("Not a function");var d=b.length,f=Array(d),g=0;t(a,b,function(b,a){f[b]=a;++g===d&&c(f)},e)})});k(h,"race",function(b){var a=this;return"[object Array]"!=u.call(b)?a.reject(TypeError("Not an array")):new a(function(c,e){if("function"!=typeof c||"function"!=typeof e)throw TypeError("Not a function");t(a,b,function(b,a){c(a)},e)})});return h});
		(function($) {
    $(function() {
        function delay(ms) {
            return new Promise(function(resolve){ return setTimeout(resolve, ms);});
		}
        var promise = Promise.resolve()
        $(document).on("lazyload", ".wpupg-grid img", function() {
            var grid = $(this).closest(".wpupg-grid");
            promise = promise.then(function() {
                var eventPromise = new Promise(function(resolve) {
                    grid.one("layoutComplete",resolve);
                })
                grid.isotopewpupg("layout");
                return eventPromise;
            }).then(function(){
                return delay(100);
            });
        })
    })
    $(window).on("load", function() {
        $(".wpupg-grid").each(function() {
            $(this).data("isotopewpupg") && $(this).isotopewpupg("layout")
        });
    });
})(jQuery);' );
	}
}
