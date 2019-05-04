/*jslint browser:true, plusplus:true, vars:true */
/*jshint browser:true, jquery:true */

(function ($) {
	'use strict';

	var options = $.lazyLoadXT,
		bgAttr = options.bgAttr || 'data-lazyload-bg';

	options.selector += ',[' + bgAttr + ']';

	$(document).on('lazyshow', function (e) {
		var $this = $(e.target),
			url = $this.attr(bgAttr);
		if (!!url && '1' !== url) {
			$this
				.css('background-image', "url('" + url + "')")
				.removeAttr(bgAttr)
				.triggerHandler('load');
		}
		if ('1' === url) {
			$this.removeAttr(bgAttr).triggerHandler('load');
		}
	});
	/* Workaround to auto resize divi video posters */
	$(document).on('lazyload', '.et_pb_video_overlay', function () {
		var ratio = $(this).data('aspectRatio');
		if ($.fn.fitVids && ratio) {
			$(this).closest('.et_pb_video').find('.et_pb_video_box img').hide();
			$(this).parent().fitVids({ customSelector: '.et_pb_video_overlay' }).end().parent().css('paddingTop', (ratio * 100) + '%');
		}
	});
	/* Workaround to force divi parallax's to re-render on lazyload  */
	$(document).on('lazyload', '.et_parallax_bg', function () {
		var parent = $(this).parent();
		window.et_parallax_set_height && window.et_parallax_set_height(parent);
		window.et_apply_parallax && window.et_apply_parallax((parent));
		window.et_parallax_set_height && $(window).on('scroll', $.proxy(window.et_parallax_set_height, parent));
	});

})(window.jQuery || window.Zepto || window.$);
