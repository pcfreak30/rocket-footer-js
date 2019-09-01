<?php


namespace Rocket\Footer\JS\Integration;


use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Core\Responsive\Responsive;
use Elementor\Element_Base;

/**
 * Class Elementor
 *
 * @package Rocket\Footer\JS\Integration
 */
class Elementor extends IntegrationAbstract {
	/**
	 * @var bool
	 */
	private $lazy_load_widget_off = false;

	/**
	 * @var bool
	 */
	private $lazy_load_widget_thumbnail_off = false;

	/**
	 * @var bool
	 */
	private $lazy_load_widget_thumbnail_size;

	/**
	 * @var
	 */
	private $lazy_load_widget_thumbnail_alt;
	/**
	 * @var array
	 */
	private $default_element_lazyload_widgets = [
		'image',
		'image-box',
		'image-carousel',
		'video',
	];
	/**
	 * @var array
	 */
	private $no_lazyload_classes = [ 'no-lazyload' ];

	/**
	 *
	 */
	public function init() {
		add_action( 'wp', [ $this, 'wp_action' ] );
		if ( is_admin() ) {
			add_action( 'wp_loaded', [ $this, 'wp_action' ] );
		}
	}

	/**
	 *
	 */
	public function wp_action() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}
		if ( ! $this->plugin->lazyload_manager->is_enabled() ) {
			return;
		}
		if ( ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) && ! is_admin() ) {
			return;
		}

		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'elementor_scripts' ] );
		add_action( 'elementor/frontend/before_render', [ $this, 'lazyload_attributes' ] );
		add_action( 'elementor/frontend/widget/after_render', [ $this, 'maybe_remove_lazyload_filter' ] );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
		add_action( 'elementor/widget/render_content', [ $this, 'lazyload' ], 10, 2 );
		add_action( 'elementor/element/before_section_end', [ $this, 'add_lazyload_control' ], 10, 2 );
		add_action( 'a3_lazy_load_skip_images_classes', [ $this, 'a3_skip_classes' ], 10, 2 );
		add_action( 'rocket_async_css_lazy_load_responsive_image', [ $this, 'maybe_lazyload_image' ], 10, 2 );
	}

	/**
	 * @param $classes
	 *
	 * @return array|string
	 */
	public function a3_skip_classes( $classes ) {
		if ( is_string( $classes ) ) {
			$classes = trim( $classes );
			if ( ! empty( $classes ) ) {
				$classes = array_map( 'trim', explode( ',', $classes ) );
			}
		}
		if ( is_array( $classes ) ) {
			$classes = array_merge( $classes, $this->no_lazyload_classes );
			$classes = implode( ', ', $classes );
		}

		return $classes;
	}

	/**
	 * @param $value
	 * @param $classes
	 *
	 * @return bool
	 */
	public function maybe_lazyload_image( $value, $classes ) {
		$classes = array_map( 'trim', explode( ' ', $classes ) );
		if ( 0 < count( array_intersect( $this->no_lazyload_classes, $classes ) ) ) {
			$value = false;
		}

		return $value;
	}

	/**
	 *
	 */
	public function elementor_scripts() {
		wp_add_inline_script( 'elementor-frontend', '(function(a){a(window).on("elementor/frontend/init",function(){elementorFrontend.hooks.addAction("frontend/element_ready/tabs.default",function(a,b){a.find(".elementor-tab-content.elementor-active").css("display","block")},11)})})(jQuery);' );
	}

	/**
	 * @param \Elementor\Element_Base $element
	 */
	public function lazyload_attributes( Element_Base $element ) {
		$settings             = array_filter( $element->get_settings() );
		$setting_keys_desktop = [
			'background_image'          => true,
			'background_overlay'        => true,
			'background_video_fallback' => true,
		];
		$setting_keys         = [];
		foreach ( $setting_keys_desktop as $setting_key ) {
			$setting_keys ["{$setting_key}_mobile"] = true;
			$setting_keys ["{$setting_key}_tablet"] = true;
		}

		$setting_keys = array_merge( $setting_keys, $setting_keys_desktop );
		$found        = array_intersect_key( $settings, $setting_keys );
		$lazyload     = false;
		if ( $found ) {
			foreach ( $found as $item ) {
				$item = array_filter( $item );
				if ( ! empty( $item ) ) {
					$lazyload = true;
					break;
				}
			}
		}

		$lazyload_setting           = isset( $settings['lazyload'] ) && 'yes' === $settings['lazyload'];
		$elementor_lazyload_setting = isset( $settings['lazy_load'] ) && 'yes' === $settings['lazy_load'];
		$show_overlay               = isset( $settings['show_image_overlay'] ) && 'yes' === $settings['show_image_overlay'];

		$background_lazyload = $lazyload && isset( $settings['background_lazyload'] ) && 'yes' === $settings['background_lazyload'];

		if ( $background_lazyload ) {
			$element->add_render_attribute( '_wrapper', 'data-lazyload-bg', 1 );
			$element->add_render_attribute( '_wrapper', 'class', 'lazyload' );
		}
		if ( ! $lazyload_setting && $elementor_lazyload_setting && 'video' === $element->get_name() ) {
			$element->add_render_attribute( 'image-overlay', 'data-lazyload-bg', 1 );
			$element->add_render_attribute( 'image-overlay', 'class', 'lazyload' );
		}

		if ( in_array( $element->get_name(), apply_filters( 'rocket_footer_js_elementor_lazyload_widgets', $this->default_element_lazyload_widgets ) ) ) {
			if ( ! $lazyload_setting ) {
				$this->lazy_load_widget_off = true;
				add_filter( 'a3_lazy_load_run_filter', '__return_false' );
				if ( ! ( $show_overlay && $elementor_lazyload_setting ) ) {
					add_filter( 'wp_get_attachment_image_attributes', [ $this, 'no_lazyload_image' ] );
				}
			} else {
				if ( 'video' === $element->get_name() ) {
					if ( ! ( isset( $settings['lazyload_thumbnail'] ) && 'yes' === $settings['lazyload_thumbnail'] ) ) {
						$this->lazy_load_widget_thumbnail_off = true;
					}
					if ( ! empty( $settings['lazyload_thumbnail_alt'] ) ) {
						$this->lazy_load_widget_thumbnail_alt = $settings['lazyload_thumbnail_alt'];
					} else {
						$this->lazy_load_widget_thumbnail_alt = null;
					}
					if ( ! empty( $settings['lazyload_thumbnail_size'] ) ) {
						$this->lazy_load_widget_thumbnail_size = $settings['lazyload_thumbnail_size'];
					} else {
						$this->lazy_load_widget_thumbnail_size = null;
					}
				}
				$element->set_settings( 'show_play_icon', 'no' );
				$element->set_settings( 'show_image_overlay', 'no' );
			}
		}
	}

	/**
	 * @param $attr
	 *
	 * @return mixed
	 */
	public function no_lazyload_image( $attr ) {
		$attr['class'] .= ' no-lazyload';

		return $attr;
	}

	/**
	 *
	 */
	public function maybe_remove_lazyload_filter() {
		if ( $this->lazy_load_widget_off || $this->lazy_load_widget_thumbnail_off ) {
			remove_filter( 'a3_lazy_load_run_filter', '__return_false' );
			remove_filter( 'wp_get_attachment_image_attributes', [ $this, 'no_lazyload_image' ] );
			$this->lazy_load_widget_off           = false;
			$this->lazy_load_widget_thumbnail_off = false;
		}
	}

	/**
	 *
	 */
	public function enqueue_styles() {
		$breakpoints = Responsive::get_breakpoints();
		$style       = <<<CSS
 .elementor-element[data-lazyload-bg], .elementor-element[data-lazyload-bg] > .elementor-background-overlay, .elementor-element[data-lazyload-bg] > .elementor-motion-effects-container > .elementor-motion-effects-layer, .elementor-element[data-lazyload-bg] > .elementor-element-populated {
    background: none !important;
}
CSS;
		$css         = $style;

		foreach ( $breakpoints as $breakpoint ) {
			$css .= "@media(max-width:{$breakpoint}px){$style}";
		}
		wp_add_inline_style( 'elementor-frontend', $css );
	}

	/**
	 * @param $widget_content
	 *
	 * @return mixed|void
	 */
	public function lazyload( $widget_content, Element_Base $element ) {
		if ( 'video' === $element->get_name() ) {
			if ( $this->lazy_load_widget_off ) {
				$widget_content = str_replace( '<iframe ', '<iframe data-no-lazyload="1" ', $widget_content );
			}
			if ( $this->lazy_load_widget_thumbnail_off ) {
				$widget_content = str_replace( '<iframe ', '<iframe data-no-lazyload-thumbnail="1" ', $widget_content );
			}
			if ( null !== $this->lazy_load_widget_thumbnail_size ) {
				$widget_content = str_replace( '<iframe ', sprintf( '<iframe data-thumbnail-size="%s"', $this->lazy_load_widget_thumbnail_size ), $widget_content );
			}
			if ( null !== $this->lazy_load_widget_thumbnail_alt ) {
				$widget_content = str_replace( '<iframe ', sprintf( '<iframe data-thumbnail-alt="%s"', $this->lazy_load_widget_thumbnail_alt ), $widget_content );
			}

		}

		return apply_filters( 'a3_lazy_load_html', $widget_content );
	}

	/**
	 * @param \Elementor\Controls_Stack $controls_stack
	 * @param                           $section_id
	 */
	public function add_lazyload_control( Controls_Stack $controls_stack, $section_id ) {
		if ( in_array( $section_id, [
				'section_background',
				'_section_background',
			] ) || ( 'section_style' === $section_id && 'column' === $controls_stack->get_name() ) ) {
			$controls_stack->add_control(
				'background_lazyload_divider',
				[
					'type'      => Controls_Manager::DIVIDER,
					'condition' => [
						'background_background'  => 'classic',
						'background_image[url]!' => '',
					],
				]
			);
			$controls_stack->add_control( 'background_lazyload', [
				'label'     => __( 'Lazy Load', $this->plugin->safe_slug ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [
					'background_background'  => 'classic',
					'background_image[url]!' => '',
				],
				'default'   => 'yes',
			] );
		}
		if ( in_array( $controls_stack->get_name(), apply_filters( 'rocket_footer_js_elementor_lazyload_widgets', $this->default_element_lazyload_widgets ) ) ) {
			if ( in_array( $section_id, [ 'section_image', 'section_video' ] ) ) {
				$video = 'video' === $controls_stack->get_name();
				$controls_stack->add_control(
					'lazyload_divider',
					[
						'type'      => Controls_Manager::DIVIDER,
						'condition' => [
							'image[url]!' => '',
						],
					]
				);
				$condition = [];
				if ( ! $video ) {
					$condition = [
						'image[url]!' => '',
					];
				}
				$controls_stack->add_control( 'lazyload', [
					'label'       => __( 'Lazy Load', $this->plugin->safe_slug ),
					'type'        => Controls_Manager::SWITCHER,
					'description' => 'Enable the lazyload, provided by Rocket Footer JS, for this element',
					'condition'   => $condition,
					'default'     => 'yes',
				] );

				if ( $video ) {
					$controls_stack->add_control( 'lazyload_thumbnail', [
						'label'       => __( 'Lazy Load Thumbnail', $this->plugin->safe_slug ),
						'type'        => Controls_Manager::SWITCHER,
						'description' => 'Enable lazyloading the generated video thumbnail',
						'condition'   => [
							'lazyload' => 'yes',
						],
						'default'     => 'yes',
					] );
					$controls_stack->add_control( 'lazyload_thumbnail_size', [
						'label'       => __( 'Lazy Load Thumbnail Custom Size', $this->plugin->safe_slug ),
						'type'        => Controls_Manager::TEXT,
						'description' => 'If this is set, it will override the sizes attribute of the generated video thumbnail. Useful in advanced or edge case situations',
						'condition'   => [
							'lazyload' => 'yes',
						],
						'default'     => '',
					] );
					$controls_stack->add_control( 'lazyload_thumbnail_alt', [
						'label'       => __( 'Lazy Load Thumbnail Alt Text', $this->plugin->safe_slug ),
						'type'        => Controls_Manager::TEXT,
						'description' => 'If this is set, will override the alt attribute for SEO to the thumbnail image. Default is what is given via oEmbed',
						'condition'   => [
							'lazyload' => 'yes',
						],
						'default'     => '',
					] );
				}
			}
		}
	}
}
