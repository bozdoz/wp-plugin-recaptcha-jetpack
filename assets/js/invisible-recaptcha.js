(function ($) {
	/*
	
	find recaptcha buttons and alter submit buttons to verify with recaptcha first

	*/
	var $form;

	$('.invisible-recaptcha').eq(0).each(function () {
		var $this = $(this);

		$form = $this.closest('form.contact-form');

		if (!$form.length) return;
	});

	window.bozdoz_jpr_onSubmit = function (token) {
		// add token to form?
		// submit form for some reason
		alert(token);
		$form.submit();
	};

})(window.jQuery);