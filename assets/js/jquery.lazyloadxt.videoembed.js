/*jslint browser:true */
/*jshint browser:true, jquery:true */

(function ($) {
	'use strict';

	var options = $.lazyLoadXT,
		widgetAttr = options.videoEmbedAttr || 'data-lazy-video-embed',
		reComment = /<!--([\s\S]*)-->/;

	options.selector += ',[' + widgetAttr + ']';
	$(document).on('lazyload', '[' + widgetAttr + ']', function () {

		var el = $(this), id = $(el).attr(widgetAttr),
			match, $target = $();

		if (id) {
			$target = $('#' + id);
		}
		if ($(el).data('lazyLoadedVideo')) {
			return false;
		}
		match = reComment.exec($target.html());
		if (match) {
			var html = $('<div>' + $.trim(match[ 1 ]) + '</div>');
			if (html.find('iframe')) {
				var parent = el.parent();
				$(el).wrap($('<div />', {
					'data-lazy-video-embed-container': $(el).data('lazyVideoEmbedType')
				}))
				$(el).after($('<div />', { class: 'play' }));
				var image = $(el);
				var width = html.find('iframe').attr('width');
				if (width) {
					image.parent().attr('width', width);
				}
				var height = html.find('iframe').attr('height');
				if (height) {
					image.parent().attr('height', height);
				}
			}
			// Force VC Composer Video element to have no padding and back up padding data
			var vc_video_wrapper = $(el).closest('.wpb_video_wrapper');
			if (vc_video_wrapper.length) {
				lazySizes.rAF(function () {
					vc_video_wrapper.data('videoPadding', vc_video_wrapper.css('padding-top'));
					vc_video_wrapper.css('padding-top', '0px');
				});
			}

			var divi_video_wrapper = $(el).closest('.et_pb_video, .et_main_video_container, .et_pb_video_wrap').find('.et_pb_video_overlay');
			if (divi_video_wrapper.length) {
				lazySizes.rAF(function () {
					$(el).siblings('.play').hide();
				});
			}

			var fusion_video_wrapper = $(el).closest('.fusion-video, .wpb_wrapper');
			if (fusion_video_wrapper.length && $.fn.fitVids) {
				lazySizes.rAF(function () {
					fusion_video_wrapper.fitVids({ customSelector: '[data-lazy-video-embed-container]' });
					fusion_video_wrapper.find('.fluid-width-video-wrapper').removeAttr('style');
					var fusion_privacy_placeholder = fusion_video_wrapper.find('.fusion-privacy-placeholder');
					// Avada/Fusion privacy box compatibility
					if (fusion_privacy_placeholder.length) {
						fusion_privacy_placeholder.siblings('.fluid-width-video-wrapper').hide();
						var padding,
							parent = fusion_privacy_placeholder.parent(),
							prev = fusion_privacy_placeholder.prev(),
							width = fusion_privacy_placeholder.outerWidth(),
							height = fusion_privacy_placeholder.outerHeight();

						if (!(parent.hasClass("fusion-background-video-wrapper") || parent.hasClass("fluid-width-video-wrapper"))) {
							padding = (height / width * 100) + "%";
							fusion_privacy_placeholder.wrap('<div class="fluid-width-video-wrapper" style="padding-top:' + padding + '" />');
							fusion_privacy_placeholder.parent().append(prev)
						}
					}
					resize_linked_videos();
				});
			}

			var embedContainer = $(el).parent();
			lazySizes.rAF(function () {
				image.addClass('lazyload');

				if (embedContainer.children('img').is('.lazyload, .lazyloading')) {
					embedContainer.children('img').on('lazyload', resizeContainer);
				} else {
					resizeContainer();
				}

				$(el).data('lazyLoadedVideo', true);
			})
			var resizeContainer = lazySizes.debounce(lazySizes.rAFIt(function () {
				var embedContainerNode = embedContainer.children('img').get(0);
				var embedContainerHeight = parseFloat(embedContainer.css('height').replace('px', ''));
				if (!embedContainerNode && !embedContainerHeight) {
					resizeContainer();
					return;
				}
				if (!embedContainerNode) {
					resizeContainer();
					return;
				}
				var embedContainerClientHeight = embedContainerNode.clientHeight;
				if (!embedContainerClientHeight) {
					resizeContainer();
					return;
				}
				if (embedContainerHeight > embedContainerClientHeight) {
					embedContainer.css('height', embedContainerClientHeight + 'px');
					embedContainer.attr('height', embedContainerClientHeight + 'px');
				}
			}, true));
		}
	});

	$(document).on('click', '[' + widgetAttr + '] + .play', function () {
		$(this).siblings('[' + widgetAttr + ']').click();
	});

	// Avada/Fusion privacy box compatibility
	lazySizes.rAF(function () {
		$('.fusion-privacy-placeholder').find('.fusion-privacy-consent').on('click', function () {
			$('.fusion-privacy-consent').each(function () {
				$(this).closest('.fluid-width-video-wrapper').siblings('.fluid-width-video-wrapper').show().end().remove();
			});
		});
	});

	$(document).on('click', '[' + widgetAttr + ']', function () {
		var $this = $(this),
			$target = $this,
			id = $this.attr(widgetAttr),
			match;

		if (id) {
			$target = $('#' + id);
		}

		var videoType = $this.data('lazyVideoEmbedType');
		var videoWidth = $this.data('lazyVideoWidth');
		var videoHeight = $this.data('lazyVideoHeight');

		if ($target.length) {
			match = reComment.exec($target.html());
			if (match) {
				var $video = $($.trim(match[ 1 ])).removeClass('lazyload').insertBefore($target);
				$target.remove();
				if ($this !== $target) {
					$this.remove();
				}
				//Restore VC Composer Video wrapper element padding
				var vc_video_wrapper = $video.closest('.wpb_video_wrapper');
				if (vc_video_wrapper.length) {
					lazySizes.rAF(function () {
						vc_video_wrapper.css('padding-top', vc_video_wrapper.data('videoPadding'));
						vc_video_wrapper.removeData('videoPadding');
					});
				}
				var embedContainer = $video.siblings('[data-lazy-video-embed-container]').add($video.prevAll('p').find('[data-lazy-video-embed-container]')).add($video.siblings('.fluid-width-video-wrapper').children('[data-lazy-video-embed-container]'));
				if (embedContainer.length) {
					embedContainer.show();
					embedContainer.append($video).addClass('loading-container');
					var $icon = embedContainer.find('.play').show();
					$icon.removeClass('play').addClass('loading');
					var div = $('<div />');
					$icon.append(div, div.clone(), div.clone(), div.clone());
					var iframe = embedContainer.children('iframe');
					if (embedContainer.closest('.elementor-fit-aspect-ratio').length) {
						embedContainer.parent().prepend($icon)
					}
					iframe.attr('src', iframe.data('src')).addClass('lazyload').one('load', lazySizes.rAFIt(function () {
						$icon.remove();
						embedContainer.parent().append(embedContainer.children());
						embedContainer.remove();
						var fluid_wrapper = $(this).closest('.fluid-width-video-wrapper');
						if (fluid_wrapper.length) {
							fluid_wrapper.parent().append(this);
							fluid_wrapper.remove();
							if ($.fn.fitVids) {
								if (videoWidth && videoHeight) {
									iframe.attr({ width: videoWidth, height: videoHeight });
								}
								$(this).parent().fitVids();
							}
						}
					}));
				}

				var fusion_video_wrapper = $video.closest('.fusion-video, .wpb_wrapper');
				if (fusion_video_wrapper.length) {
					if ($video.is('[data-privacy-src-disabled]')) {
						lazySizes.rAF(function () {
							$video.removeClass('fusion-hidden');
						});
					}
				}

				var divi_video_box = $video.closest('.et_pb_video, .et_main_video_container, .et_pb_video_wrap')
				var divi_video_wrapper = divi_video_box.find('.et_pb_video_overlay');
				if (!divi_video_wrapper.length) {
					lazySizes.rAF(function () {
						divi_video_wrapper.show();
						if ($.fn.fitVids) {
							var embedContainerHeight = embedContainer.css('height');
							if (embedContainerHeight) {
								embedContainer.attr('height', parseFloat(embedContainerHeight.replace('px', '')));
							}
							var embedContainerWidth = embedContainer.css('width');
							if (embedContainerWidth) {
								embedContainer.attr('width', parseFloat(embedContainerWidth.replace('px', '')));
							}
							divi_video_box.fitVids({
								customSelector: '[data-lazy-video-embed-container]',
								ignore: ['iframe[src*="player.vimeo.com"]', 'iframe[src*="youtube.com"]', 'iframe[src*="youtube-nocookie.com"]', 'iframe[src*="kickstarter.com"][src*="video.html"]', 'object', 'embed']
							});
						}
					});
				}
			}
		}
	});

	function resize_linked_videos () {
		$('img[data-size-linked-to].lazy-loaded').each(function () {
			var linked_video_id = $(this).data('sizeLinkedTo');
			var id_class = 'img.lazy-loaded.video-id-' + linked_video_id;
			var linked_video = $(id_class);
			if (linked_video.length) {
				$(this).css('height', linked_video.get(0).clientHeight + 'px');
			}
		});
	}

	$(window).on('resize', resize_linked_videos);
	$(document).on('lazyload', 'img', resize_linked_videos);

	// Avada Fusion/Visual Composer Resize Compatibility
	$(function () {
		lazySizes.rAF(function () {
			$('.fusion-video[class*="video-size-linked-to-"]').each(function () {
				var classes = $(this).attr("class").split(' ');
				var id = classes.filter(function (item) {
					return item.startsWith('video-size-linked-to-');
				})
				id = id.pop().replace('video-size-linked-to-', '');
				$(this).find('img').attr('data-size-linked-to', id);
				$(this).removeClass('video-size-linked-to-' + id);
			})
			resize_linked_videos();
		});
	});

	/* Divi Builder Video Overlay Workaround */
	$(function () {
		if ($('.et_pb_video_overlay').length) {
			var old_et_pb_play_overlayed_video = null;
			var check = lazySizes.rAFIt(function () {
				var func = window.et_pb_play_overlayed_video;
				var is_overridden = func !== old_et_pb_play_overlayed_video;
				var original_exists = old_et_pb_play_overlayed_video !== null;
				if (func && (!is_overridden !== !original_exists)) {
					old_et_pb_play_overlayed_video = window.et_pb_play_overlayed_video;
					window.et_pb_play_overlayed_video = function ($play_video) {
						var $this = $play_video,
							$wrapper = $this.closest('.et_pb_video, .et_main_video_container, .et_pb_video_wrap'),
							$image = $wrapper.find('.et_pb_video_box img');
						$wrapper.find('.et_pb_video_overlay').parent().css('paddingTop', '').end().hide();
						$image.click();
						var $video_iframe = $wrapper.find("iframe");
						if ($video_iframe.length) {
							$video_iframe.on('lazyload', function () {
								if ($.fn.fitVids)
									$wrapper.fitVids();
								old_et_pb_play_overlayed_video($play_video)
							});
							return;
						}
						$wrapper.find('video').load(function () {
							old_et_pb_play_overlayed_video($play_video);
						}).show().lazyLoadXT();
					}
				}
				setTimeout(check, 10);
			});
			check();
		}
	});

})(window.jQuery || window.Zepto || window.$);
