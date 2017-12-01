<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

class AvadaGoogleMaps extends LazyloadAbstract {

	protected $script_id;
	protected $script_content;
	protected $map_tag;
	protected $regex = '~fusion_run_map_fusion_map_(\w+)~is';
	protected $processed = false;

	public function init() {
		add_filter( 'avada_setting_get_js_compiler', [ $this, 'zero' ] );
		parent::init();
	}

	public function zero() {
		return '0';
	}

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( empty( $this->script_id ) ) {
			$this->script_id = 'avada_fusion_google_maps';
		}
		$dep_scripts = [];
		/** @var DOMElement $sub_tag */
		foreach ( $this->get_script_collection() as $sub_tag ) {
			$src = $sub_tag->getAttribute( 'src' );
			if ( false !== strpos( parse_url( $src, PHP_URL_PATH ), 'assets/js/infobox_packed.js' ) ||
			     false !== strpos( parse_url( $src, PHP_URL_PATH ), 'library/infobox_packed.js' ) ||
			     false !== strpos( parse_url( $src, PHP_URL_PATH ), 'library/jquery.fusion_maps.js' ) ||
			     false !== strpos( parse_url( $src, PHP_URL_PATH ), 'general/fusion-google-map.js' ) ) {
				$dep_scripts [] = $src;
				if ( $sub_tag->parentNode ) {
					$sub_tag->parentNode->removeChild( $sub_tag );
					$this->tags->flag_removed();
				}
			}
		}

		$element     = $this->content_document->getElementById( "fusion_map_{$this->regex_match[1]}" );
		$lazy_widget = $element->getAttribute( 'data-lazy-widget' );
		if ( ! empty( $lazy_widget ) ) {
			$this->tags->remove();

			return;
		}
		$dep_scripts = array_unique( $dep_scripts );
		if ( ! empty( $dep_scripts ) ) {
			$dep_scripts = array_map( function ( $script ) {
				return '$.getScript( ' . wp_json_encode( $script ) . ' )';
			}, $dep_scripts );

			$new_script = $this->create_script( '(function($){(function check(){if(typeof google==="undefined" || typeof $==="undefined")setTimeout(check,10);else{$.when(' . implode( ',', $dep_scripts ) . ',  $.Deferred(function( deferred ){$( deferred.resolve );})).done(function(){' . $content . '; if(document.readyState == "complete"){' . $this->regex_match[0] . '();}})}})()})(jQuery);' );
		} else {
			$new_script = $this->create_script( '(function(){(function check(){if(typeof google=="undefined")setTimeout(check,10);else{' . $content . ';' . $this->regex_match[0] . '();}})()})();' );
		}
		$this->script_content .= $this->get_script_content( $new_script );
		$this->tags->remove();

		if ( ! empty( $element ) ) {
			$this->processed = true;
			$element->setAttribute( 'data-lazy-widget', $this->script_id );
		}
	}

	protected function before_do_lazyload() {
		$collection = $this->get_script_collection();
		/** @var DOMElement $tag */
		$map_instances = [];
		foreach ( $collection as $tag ) {
			$src = $tag->getAttribute( 'src' );
			if ( ! empty( $src ) ) {
				$src = rocket_add_url_protocol( $src );
			}
			if ( 'maps.googleapis.com' === parse_url( $src, PHP_URL_HOST ) ) {
				if ( preg_match( '/key=\w+/i', $src ) && empty( $this->map_tag ) ) {
					$this->map_tag = $tag;
				} else {
					$map_instances[] = $tag;
				}
			}
		}
		if ( empty( $this->map_tag ) && ! empty( $map_instances ) ) {
			$this->map_tag = array_shift( $map_instances );
		}

	}

	protected function is_match( $content, $src ) {
		return class_exists( 'Avada_GoogleMap' ) && Avada()->settings->get( 'status_gmap' ) && parent::is_match( $content, $src );
	}

	protected function after_do_lazyload() {
		if ( ! empty( $this->map_tag ) && $this->processed ) {
			$this->lazyload_script( $this->get_script_content( $this->map_tag ) . $this->script_content, $this->script_id, $this->map_tag );
		}
	}
}