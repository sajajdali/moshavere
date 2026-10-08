(function($) {
	"use strict";

	// فقط برای المنت هایی که در صفحه وجود دارند؛ در غیر این صورت PerfectScrollbar خطا میدهد
	function initScroll(selector) {
		const el = document.querySelector(selector);
		if (!el) {
			return null;
		}
		return new PerfectScrollbar(el, {
			useBothWheelAxes: true,
			suppressScrollX: true,
		});
	}

	// For APP-SIDEBAR
	initScroll('.app-sidebar');

	// For Header Message dropdown
	initScroll('.message-menu');

	// For Header Notification dropdown
	initScroll('.notifications-menu');

	// For Header Cart dropdown
	initScroll('.cart-menu');

})(jQuery);
