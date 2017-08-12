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
				$target.replaceWith($.trim(match[ 1 ]));
				if ($this !== $target) {
					$this.remove();
				}
			}
		}

		$this.triggerHandler('load');
	});

})(window.jQuery || window.Zepto || window.$);