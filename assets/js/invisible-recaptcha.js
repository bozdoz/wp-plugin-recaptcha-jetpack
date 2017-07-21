(function ($) {
	/*
	
	find recaptcha buttons and alter submit buttons to verify with recaptcha first

	*/
	var captcha_ready = $.Deferred();

	$('.invisible-recaptcha').each(function () {
		var $this = $(this),
			$form = $this.closest('form.contact-form'),
			id,
			submit_id;
		if (!$form.length) return;
		// get id
		id = $form[0]['contact-form-id'].value;
		submit_id = 'bozdoz_jpr_submit_' + id;

		// add id to submit
		$form.find('[type="submit"]')
			.attr('id', submit_id);

		captcha_ready.done(function () {
			grepcaptcha.render(submit_id, {
				'sitekey' : $this.data('sitekey'),
				'callback' : $form.submit,
			})
		});
	});

	window.bozdoz_jpr_onLoad = function () {
		captcha_ready.resolve();
		console.log('onload fired');
	};

})(window.jQuery);