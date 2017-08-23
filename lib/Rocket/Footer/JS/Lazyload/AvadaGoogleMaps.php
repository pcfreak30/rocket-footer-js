<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMElement;

class AvadaGoogleMaps extends LazyloadAbstract {

	protected $script_id;
	protected $script_comtent;
	protected $map_tag;
	protected $regex = '~fusion_run_map_fusion_map_(\w+)~is';
	protected $processed = false;
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
		$sub_url = '';
		/** @var DOMElement $sub_tag */
		foreach ( $this->get_script_collection() as $sub_tag ) {
			$src = $sub_tag->getAttribute( 'src' );
			if ( false !== strpos( parse_url( $src, PHP_URL_PATH ), 'assets/js/infobox_packed.js' ) ) {
				$sub_url = $src;
				if ( $sub_tag->parentNode ) {
					$sub_tag->parentNode->removeChild( $sub_tag );
					$this->tags->flag_removed();
				}
			}
		}
		if ( ! empty( $sub_url ) ) {
			$new_script = $this->create_script( '(function(){(function check(){if(typeof google=="undefined")setTimeout(check,10);else{jQuery.getScript("' . $sub_url . '", function(){' . $content . '; if(document.readyState == "complete"){' . $this->regex_match[0] . '();}})}})()})();' );
		} else {
			$new_script = $this->create_script( '(function(){(function check(){if(typeof google=="undefined")setTimeout(check,10);else{' . $content . ';' . $this->regex_match[0] . '();}})()})();' );
		}
		$this->script_comtent .= $this->get_script_content( $new_script );
		$this->tags->remove();

		$element = $this->content_document->getElementById( "fusion_map_{$this->regex_match[1]}" );
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
			$this->lazyload_script( $this->script_comtent, $this->script_id, $this->map_tag );
		}
	}
}