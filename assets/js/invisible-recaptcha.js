(function ($) {
	/*
	
	find recaptcha buttons and alter submit buttons to verify with recaptcha first

	*/
	$('.invisible-recaptcha').each(function () {
		var $this = $(this),
			$form = $this.closest('form.contact-form');
		if (!$form.length) return;
		// get id
		id = $form[0]['contact-form-id'].value;
		submit_id = 'bozdoz_jpr_submit_' + id;

		// add atts to submit button
		$form.find('[type="submit"]')
			.attr('class', 'g-recaptcha')
			.attr('data-sitekey', $this.data('sitekey'))
			.attr('data-callback', 'bozdoz_jpr_onSubmit');
	});

	addScript('https://www.google.com/recaptcha/api.js');

	function addScript (src) {
		var a = document.createElement('script'),
			b = document.getElementsByTagName('script')[0];
		a.async = 1;
		a.src = src;
		b.parentNode.insertBefore(a, b);
	}

	window.bozdoz_jpr_onSubmit = function (token) {
		// add token to form?
		debugger;
		console.log(this, token);
	};

})(window.jQuery);