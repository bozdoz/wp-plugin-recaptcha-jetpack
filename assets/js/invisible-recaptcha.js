(function ($) {
	/*
	
	find recaptcha buttons and alter submit buttons to verify with recaptcha first

	*/
	var $form;

	$('.invisible-recaptcha').eq(0).each(function () {
		var $this = $(this);

		$form = $this.closest('form.contact-form');

		if (!$form.length) return;

		// add atts to button
		$form.find('[type="submit"]')
			.attr('data-sitekey', $this.data('sitekey'))
			.attr('data-callback', $this.data('callback'))
			.addClass('g-recaptcha');
	});

	addScript('https://www.google.com/recaptcha/api.js');

	function addScript (src) {
		var a = document.createElement('script'),
			b = document.getElementsByTagName('script')[0];
		a.async = 1;
		a.defer = 1;
		a.src = src;
		b.parentNode.insertBefore(a, b);
	}

	window.bozdoz_jpr_onSubmit = function (token) {
		// add token to form?
		// submit form for some reason
		alert(token);
		$form.submit();
	};

})(window.jQuery);