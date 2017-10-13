/*jslint browser:true, plusplus:true, vars:true */
/*jshint browser:true, jquery:true */

(function ($) {
	'use strict';

	$(function () {
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
	});

})(window.jQuery || window.Zepto || window.$);