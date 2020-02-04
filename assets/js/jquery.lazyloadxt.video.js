/*jslint browser:true */
/*jshint browser:true, jquery:true */

(function ($) {
	'use strict';

	var options = $.lazyLoadXT;

	options.selector += ',video,iframe[data-src]';
	options.videoPoster = 'data-poster';

	function load () {
		this.dispatchEvent(new CustomEvent('load'));
	}

	$(document).on('lazyshow', 'video', function (e, $el) {
		var srcAttr = $el.lazyLoadXT.srcAttr,
			isFuncSrcAttr = $.isFunction(srcAttr),
			changed = false;

		$el.attr('poster', $el.attr(options.videoPoster));
		$el.children('source,track')
			.each(function (index, el) {
				var $child = $(el),
					src = isFuncSrcAttr ? srcAttr($child) : $child.attr(srcAttr);
				if (src) {
					$child.attr('src', src);
					changed = true;
				}
			}).on('loadeddata', load);
		$el.on('loadeddata', load);
		// reload video
		if (changed) {
			this.load();
			// Video.JS compatibility
			var vjs, vsrc;
			if (typeof videojs !== 'undefined' && (vjs = $el.closest('.video-js'))) {
				if (((vsrc = $el.attr('src')) && vsrc) || (((vsrc = $el.children('source').attr('src')) && vsrc))) {
					videojs(vjs.get(0)).src(vsrc)
				}
			}
		}
		$el.triggerHandler('load')
		if ($el.hasClass('wp-video-shortcode-lazyload') || $el.hasClass('wp-audio-shortcode-lazyload')) {
			if ($el.hasClass('wp-video-shortcode-lazyload')) {
				$el.removeClass('wp-video-shortcode-lazyload').addClass('wp-video-shortcode');
			}
			if ($el.hasClass('wp-audio-shortcode-lazyload')) {
				$el.removeClass('wp-audio-shortcode-lazyload').addClass('wp-audio-shortcode');
			}
			if (window.wp && window.wp.mediaelement) {
				window.wp.mediaelement.initialize();
			}
		}
		$el.children('source,track').triggerHandler('load');
	});

	/*
		Always hide the divi module video element
	 */
	$(function () {
		$('.et_pb_video_box video').hide();
	})

})(window.jQuery || window.Zepto || window.$);
