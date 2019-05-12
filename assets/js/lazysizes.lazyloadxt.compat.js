(function ($) {
	$.fn.lazyLoadXT = function () {
		lazySizes.init();
	}
	document.addEventListener('lazybeforeunveil', function (e) {
		e.target[ 'lazyLoadXT' ] = { srcAttr: 'data-src' };
		$(e.target).trigger('lazyshow');
	});
	document.addEventListener('lazyunveilread', function (e) {
		$(e.target).trigger('lazyload');
	});
	$.lazyLoadXT = { selector: '' };
})(jQuery);
