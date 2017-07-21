(function ($) {
	/*
	
	find recaptcha buttons and alter submit buttons to verify with recaptcha first

	*/
	var $form;

	$('.invisible-recaptcha').eq(0).each(function () {
		var $this = $(this);

		$form = $this.closest('form.contact-form');

		if (!$form.length) return;

		// disable submit (for some reason)
		$form.on('submit', function (e) {
			if (e.isDefaultPrevented()) {
				console.error('form validation failed');
			} else {
				e.preventDefault();
				console.log('form success');
				grecaptcha.execute();
			}
		})

	});

	window.bozdoz_jpr_onSubmit = function (token) {
		// add token to form?
		// submit form for some reason
		$form.submit();
	};

})(window.jQuery);