/*jslint browser:true, plusplus:true, vars:true */
/*jshint browser:true, jquery:true */

(function ($) {
	'use strict';


	$(function () {
		// Prevent lazy load glitch with rev slider
		$('.rev_slider').each(function () {
			var $this = $(this);
			$(this).one('revolution.slide.onloaded', function () {
				(function check () {
					$this.find('img.lazy-loaded').each(function () {
						if (1 === $(this).width() && 1 === $(this).height()) {
							$(this).css({
								width: "",
								height: ""
							});
						}
					})
					setTimeout(check, 10);
				})();
			});

		});
		// Force re-render of fusion slider when images inside load
		$('.fusion-carousel-item img').on('lazyload', function () {
				$(this).closest('.fusion-carousel').fusion_recalculate_carousel();
			}
		)
	});

})(window.jQuery || window.Zepto || window.$);