/*jslint browser:true */
/*jshint browser:true, jquery:true */

(function ($) {
	'use strict';

	var options = $.lazyLoadXT,
		widgetAttr = options.videoEmbedAttr || 'data-lazy-video-embed',
		reComment = /<!--([\s\S]*)-->/;

	options.selector += ',[' + widgetAttr + ']';

	$(document).on('click', '[' + widgetAttr + ']', function () {
		var $this = $(this),
			$target = $this,
			id = $this.attr(widgetAttr),
			match;

		if (id) {
			$target = $('#' + id);
		}

		if ($target.length) {
			match = reComment.exec($target.html());
			if (match) {
				var $video = $($.trim(match[ 1 ])).insertBefore($target);
				$target.remove();
				if ($this !== $target) {
					$this.remove();
				}
				$video.lazyLoadXT();
			}
		}

		$this.triggerHandler('load');
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
						old_et_pb_play_overlayed_video($play_video);
					}
				}
				setTimeout(check, 10);
			})();
		}
	});

})(window.jQuery || window.Zepto || window.$);