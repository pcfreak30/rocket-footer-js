(function ($) {
	$.fn.lazyLoadXT = function () {
		lazySizes.init();
	}
	document.addEventListener('lazybeforeunveil', function (e) {
		var $e = $(e.target);
		$e.lazyLoadXT = $.extend($e.lazyLoadXT, { srcAttr: 'data-src' });
		$(e.target).trigger('lazyshow', [ $e ]);
	});
	document.addEventListener('lazyunveilread', function (e) {
		$(e.target).trigger('lazyload');
	});
	$.lazyLoadXT = { selector: '' };
})(jQuery);
