(function ($) {
	/*
	
	find recaptcha buttons and alter submit buttons to verify with recaptcha first

	*/
	var $this = $('.invisible-recaptcha').eq(0),
		$form = $this.closest('form.contact-form');

	if (!$form.length) return;

	// add atts to button
	$form.find('[type="submit"]')
		.attr('data-sitekey', $this.data('sitekey'))
		.attr('data-callback', 'bozdoz_rjp_onSubmit')
		.addClass('g-recaptcha');

	addScript('https://www.google.com/recaptcha/api.js');

	function addScript (src) {
		var a = document.createElement('script'),
			b = document.getElementsByTagName('script')[0];
		a.async = 1;
		a.defer = 1;
		a.src = src;
		b.parentNode.insertBefore(a, b);
	}

	// needs to be global to be triggered by Google's callback (above)
	window.bozdoz_rjp_onSubmit = function (token) {
		// submit form for some reason
		$form.submit();
	};

})(window.jQuery);