<?php


namespace Rocket\Footer\JS\Lazyload;


class BlogHerAds extends LazyloadAbstract {

	private $base64_injected = false;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( 'ads.blogherads.com' === parse_url( $src, PHP_URL_HOST ) || preg_match( '~blogherads\s*.\s*adq\s*.\s*push\s*\(\s*\[[\'"].*[\'"]\s*,\s*[\'"](.*)[\'"]\s*\]\s*\)\s*;~U', $content, $matches ) ) {

			$tag              = $this->tags->current();
			$lazyload_content = '';
			if ( ! empty( $matches ) ) {
				$prev_tag = $tag;
				do {
					$prev_tag = $prev_tag->previousSibling;
				} while ( null !== $prev_tag && XML_ELEMENT_NODE !== $prev_tag->nodeType && 'div' !== strtolower( $tag->tagName ) && $matches[1] !== $tag->getAttribute( 'id' ) );
				$div_tag = $prev_tag;
				if ( ! empty( $div_tag ) ) {
					$lazyload_content = $this->get_script_content( $div_tag ) . $this->get_script_content();
				}
			}
			$this->inject_tag( $this->create_script( 'document.old_write=document.old_write||document.write;document.write=function(data){if(document.currentScript)(function check(){if(typeof jQuery==="undefined")setTimeout(10,check);else jQuery(document.currentScript).before(data)})()};' ) );
			$file = rocket_footer_js()->remote_fetch( $src );
			if ( ! empty( $file ) && false !== strpos( $file, 'static/blogherads.js' ) ) {
				$prev_tag = $tag;
				do {
					$prev_tag = $prev_tag->previousSibling;
				} while ( null !== $prev_tag && XML_ELEMENT_NODE !== $prev_tag->nodeType && 'script' !== strtolower( $tag->tagName ) );
				$js_tag = $prev_tag;
				if ( ! $this->base64_injected ) {
					$this->inject_tag( $this->create_script( '(function(){function f(a){this.message=a}var b="undefined"!=typeof exports?exports:"undefined"!=typeof self?self:$.global;f.prototype=Error();f.prototype.name="InvalidCharacterError";b.btoa||(b.btoa=function(a){a=String(a);for(var g,d,c=0,b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",e="";a.charAt(c|0)||(b="=",c%1);e+=b.charAt(63&g>>8-c%1*8)){d=a.charCodeAt(c+=.75);if(255<d)throw new f("\'btoa\' failed: The string to be encoded contains characters outside of the Latin1 range.");
g=g<<8|d}return e});b.atob||(b.atob=function(a){a=String(a).replace(/[=]+$/,"");if(1==a.length%4)throw new f("\'atob\' failed: The string to be decoded is not correctly encoded.");for(var b=0,d,c,h=0,e="";c=a.charAt(h++);~c&&(d=b%4?64*d+c:c,b++%4)?e+=String.fromCharCode(255&d>>(-2*b&6)):0)c="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=".indexOf(c);return e})})();' ) );
					$this->base64_injected = true;;
				}

				$this->inject_tag( $this->create_script( '(function(a){var f=a(window).height(),c=[];(function g(b){if(b.getBoundingClientRect){var d=parseInt(b.getBoundingClientRect().top+window.scrollY);d-100>f&&!c[d]&&(c[d]=b)}a(b).children().each(function(b,a){g(a)})})(document);var e=c.filter(Boolean).shift();e&&a(e).before(atob("' . base64_encode( $this->content_document->saveHTML( $js_tag ) . $this->content_document->saveHTML( $tag ) ) . '"))})(jQuery);' ) );
				$this->tags->remove();

				return;
			}
			if ( empty( $lazyload_content ) ) {
				$lazyload_content = $this->get_script_content();
			}
			$span = $this->create_tag( 'span' );
			$img  = $this->create_pixel_image();
			$span->setAttribute( 'data-lazy-widget', "blogherads-{$this->instance}" );
			$span->appendChild( $img );
			$tag->parentNode->appendChild( $span );
			$this->lazyload_script( $lazyload_content, "blogherads-{$this->instance}" );
			$this->instance ++;
		}
	}
}