<?php


namespace Rocket\Footer\JS\Integration;

class DiviBuilder extends IntegrationAbstract {
	private $current_guid;

	/**
	 *
	 */
	public function init() {
		add_action( 'after_setup_theme', [ $this, 'theme_check' ] );

	}

	public function theme_check() {
		if ( function_exists( 'et_setup_builder' ) ) {
			add_action( 'rocket_footer_js_do_rewrites', [ $this, 'rewrite' ] );
			if ( $this->plugin->lazyload_manager->is_enabled() ) {
				if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
					add_filter( 'a3_lazy_load_run_filter', '__return_false' );
				}
				add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
			}
		}
	}

	public function scripts() {
		wp_add_inline_script( 'jquery-core', '(function($){$(function(){$(".et_pb_slider").on("simple_slider_after_move_to", function(){$(window).trigger("resize")})})})(jQuery);' );
	}

	public function rewrite() {
		$xpath = new \DOMXPath( $this->plugin->document );
		$tags  = $xpath->query( '//div[contains(concat(" ", normalize-space(@class), " "), " et_pb_video_overlay ")]' );
		/** @var \Rocket\Footer\JS\DOMElement $tag */
		foreach ( $tags as $tag ) {
			preg_match_all( '/url\\(\\s*([\'"]?(.*?)[\'"]?|[^\\)\\s]+)\\s*\\)/i', $tag->getAttribute( 'style' ), $matches );
			if ( ! empty( $matches ) && ! empty( $matches[1] ) ) {
				$match = array_shift( $matches[2] );
				if ( empty( $match ) ) {
					$match = array_shift( $matches[1] );
				}
				if ( 0 === strpos( $match, 'data:' ) ) {
					continue;
				}
				$url                = trim( $match, '"' . "'" );
				$this->current_guid = $url;
				add_filter( 'posts_where_paged', [ $this, 'filter_where' ] );
				$attachments = get_posts( [
					'post_type'        => 'attachment',
					'suppress_filters' => false,
					'posts_per_page'   => 1,
					'order_by'         => 'none',
				] );
				remove_filter( 'posts_where_paged', [ $this, 'filter_where' ] );
				$attachment_id = 0;
				if ( ! empty( $attachments ) ) {
					$attachment_id = end( $attachments )->ID;
				}
				if ( empty( $attachment_id ) ) {
					continue;
				}
				$meta  = wp_get_attachment_metadata( $attachment_id );
				$ratio = round( $meta['height'] / $meta['width'], 2 );
				$tag->setAttribute( 'data-aspect-ratio', $ratio );
			}
		}
	}

	public function filter_where( $where ) {
		$url_parts           = parse_url( $this->current_guid );
		$url_parts['host']   = $this->plugin->domain;
		$url_parts['scheme'] = 'http';
		$url                 = http_build_url( $url_parts );

		$url_parts['scheme'] = 'https';
		$url_ssl             = http_build_url( $url_parts );

		$where .= $this->wpdb->prepare( " AND (guid = %s OR guid = %s)", $url, $url_ssl );

		return $where;
	}
}
