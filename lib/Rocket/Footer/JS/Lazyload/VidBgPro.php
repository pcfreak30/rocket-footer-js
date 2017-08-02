<?php


namespace Rocket\Footer\JS\Lazyload;


class VidBgPro extends LazyloadAbstract {

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {

	}

	protected function after_do_lazyload() {
		if ( $this->is_enabled() && function_exists( 'vidbgpro_init_footer' ) ) {
			$page_tag = $this->content_document->getElementById( 'vidbgpro-page' );
			if ( null !== $page_tag ) {
				if ( is_page() || is_single() ) {
					$the_id = get_the_ID();
				} elseif ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
					$the_id = get_option( 'page_for_posts' );
				}
				if ( ! empty( $the_id ) ) {
					$container_field = get_post_meta( $the_id, 'vidbg_metabox_field_container', true );
					if ( ! empty( $container_field ) ) {
						$tag = false;
						switch ( $container_field[0] ) {
							case '#':
								$tag = $this->content_document->getElementById( $container_field );
								break;
							case '.':
								$result = $this->xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " ' . substr( $container_field, 1 ) . ' ")]' );
								if ( $result->length ) {
									$tag = $result->item( 0 );
								}
								break;
							default:
								$result = $this->get_tag_collection( $container_field );
								if ( $result->valid() ) {
									$tag = $result->current();
								}
								break;
						}
						if ( null !== $tag ) {
							$tag->setAttribute( 'data-lazy-widget', 'vidbgpro-page' );
							$this->lazyload_script( $this->get_tag_content( $page_tag ), 'vidbgpro-page' );
						}
					}
				}
			}
		}
	}

	protected function is_enabled() {
		return false;
	}
}