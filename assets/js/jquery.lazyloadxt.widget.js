/*! Lazy Load XT v1.1.0 2016-01-12
 * http://ressio.github.io/lazy-load-xt
 * (C) 2016 RESS.io
 * Licensed under MIT */

(function ($) {
	var options = $.lazyLoadXT,
		widgetAttr = options.widgetAttr || 'data-lazy-widget',
		reComment = /<!--([\s\S]*)-->/;

	options.selector += ',[' + widgetAttr + ']';

	$(document).on('lazyshow', '[' + widgetAttr + ']', function () {
		var $this = $(this),
			$target = $this,
			id = $this.attr(widgetAttr),
			match;

		if (id) {
			$target = $('#' + id);
		}
		var triggered = false;
		if ($target.length) {
			match = reComment.exec($target.html());
			if (match) {
				$target.replaceWith($.trim(match[ 1 ]));
			}
			if (1 === $this.data('lazy-remove')) {
				$this.triggerHandler('load');
				$this.remove();
				triggered = true;
			}
		}
		if (!triggered) {
			$this.triggerHandler('load');
		}
	});

})(window.jQuery || window.Zepto || window.$);