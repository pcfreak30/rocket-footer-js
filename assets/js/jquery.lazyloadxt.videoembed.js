/*jslint browser:true */
/*jshint browser:true, jquery:true */

(function ($) {
	'use strict';

	var options = $.lazyLoadXT,
		widgetAttr = options.videoEmbedAttr || 'data-lazy-video-embed',
		reComment = /<!--([\s\S]*)-->/;

	options.selector += ',[' + widgetAttr + ']';
	$(document).on('lazyload', '[' + widgetAttr + ']', function () {
		var id = $(this).attr(widgetAttr),
			match, $target = $();

		if (id) {
			$target = $('#' + id);
		}
		if ($(this).data('lazyLoadedVideo')) {
			return false;
		}
		match = reComment.exec($target.html());
		if (match) {
			var html = $('<div>' + $.trim(match[ 1 ]) + '</div>');
			if (html.find('iframe')) {
				$(this).wrap($('<div />', {
					'data-lazy-video-embed-container': $(this).data('lazyVideoEmbedType')
				})).after($('<div />', { class: 'play' }));
				var image = $(this);
				image.parent().attr({
					width: html.find('iframe').attr('width'),
					height: html.find('iframe').attr('height')
				});
				$(this).siblings('.play').click(function () {
					image.click();
				})
			}
			// Force VC Composer Video element to have no padding and back up padding data
			var vc_video_wrapper = $(this).closest('.wpb_video_wrapper');
			if (vc_video_wrapper.length) {
				vc_video_wrapper.data('videoPadding', vc_video_wrapper.css('padding-top'));
				vc_video_wrapper.css('padding-top', '0px');
			}

			var divi_video_wrapper = $(this).closest('.et_pb_video, .et_main_video_container, .et_pb_video_wrap');
			if (divi_video_wrapper.length) {
				$(this).parent().hide();
			}

			var fusion_video_wrapper = $(this).closest('.fusion-video, .wpb_wrapper');
			if (fusion_video_wrapper.length && $.fn.fitVids) {
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
			}

			var embedContainer = $(this).parent();
			var resizeContainer = function () {
				var embedContainerClientHeight = embedContainer.children('img').get(0).clientHeight;
				var embedContainerHeight = parseFloat(embedContainer.css('height').replace('px', ''));
				if (embedContainerHeight > embedContainerClientHeight) {
					embedContainer.css('height', embedContainerClientHeight + 'px');
					embedContainer.attr('height', embedContainerClientHeight + 'px');
				}
			}
			if (embedContainer.children('img').data('lazied')) {
				embedContainer.children('img').on('lazyload', resizeContainer);
			} else {
				resizeContainer();
			}

			$(this).data('lazyLoadedVideo', true);
		}
	});

	// Avada/Fusion privacy box compatibility

	$('.fusion-privacy-placeholder').find('.fusion-privacy-consent').on('click', function () {
		$('.fusion-privacy-consent').each(function () {
			$(this).closest('.fluid-width-video-wrapper').siblings('.fluid-width-video-wrapper').show().end().remove();
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
					vc_video_wrapper.css('padding-top', vc_video_wrapper.data('videoPadding'));
					vc_video_wrapper.removeData('videoPadding');
				}
				var embedContainer = $video.siblings('[data-lazy-video-embed-container]').add($video.prevAll('p').find('[data-lazy-video-embed-container]')).add($video.siblings('.fluid-width-video-wrapper').children('[data-lazy-video-embed-container]'));
				if (embedContainer.length) {
					embedContainer.show();
					embedContainer.append($video).addClass('loading-container');
					var $icon = embedContainer.find('.play');
					$icon.removeClass('play').addClass('loading');
					var div = $('<div />');
					$icon.append(div, div.clone(), div.clone(), div.clone());
					var iframe = embedContainer.children('iframe');
					iframe.attr('src', iframe.data('src')).addClass('lazyload').one('load', function () {
						embedContainer.find('.loading').remove();
						embedContainer.parent().append(embedContainer.children());
						embedContainer.remove();
						var fluid_wrapper = $(this).closest('.fluid-width-video-wrapper');
						if (fluid_wrapper.length) {
							fluid_wrapper.parent().append(this);
							fluid_wrapper.remove();
							if ($.fn.fitVids) {
								$(this).parent().fitVids();
							}
						}/**/
					});
				}

				var fusion_video_wrapper = $video.closest('.fusion-video, .wpb_wrapper');
				if (fusion_video_wrapper.length) {
					if ($video.is('[data-privacy-src-disabled]')) {
						$video.removeClass('fusion-hidden');
					}
				}

				var divi_video_wrapper = $video.closest('.et_pb_video, .et_main_video_container, .et_pb_video_wrap');
				if (divi_video_wrapper.length) {
					divi_video_wrapper.show();
					if ($.fn.fitVids) {
						var embedContainerHeight = parseFloat(embedContainer.css('height').replace('px', ''));
						embedContainer.attr('width', embedContainer.css('width').replace('px', ''));
						embedContainer.attr('height', embedContainerHeight);
					}
					divi_video_wrapper.fitVids({ customSelector: '[data-lazy-video-embed-container]' });
				}
			}
		}

		//$this.triggerHandler('load');
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

	/* Divi Builder Video Overlay Workaround */
	$(function () {
		if ($('.et_pb_video_overlay').length) {
			var old_et_pb_play_overlayed_video = null;
			(function check () {
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
			})();
		}
	});

})(window.jQuery || window.Zepto || window.$);
