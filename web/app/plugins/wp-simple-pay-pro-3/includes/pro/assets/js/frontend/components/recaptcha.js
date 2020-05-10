/* global simpayGoogleRecaptcha, grecaptcha, jQuery */

const { siteKey, i18n } = simpayGoogleRecaptcha;


// Create a reCAPTCHA token when the form is loaded.
jQuery( document ).on( 'simpayCoreFormVarsInitialized', function( e, spFormElem, formData ) {
	const {
		debugLog,
	} = window.spShared;

	const {
		enableForm,
		disableForm,
		showError,
	} = window.simpayApp;

	grecaptcha.ready( () => {
		// Disable form while we generate a token.
		disableForm( spFormElem, formData, true );

		try {
			grecaptcha.execute( siteKey, {
				action: `simple_pay_form_${ formData.formId }`,
			} )
				.then( ( token ) => {
					// Token could not be generated, do not attempt to validate the form.
					if ( ! token ) {
						return;
					}

					wp.ajax.send( 'simpay_validate_recaptcha', {
						data: {
							token,
							form_id: formData.formId,
						},
						/**
						 * Enable form on success.
						 *
						 * @since 3.7.1
						 */
						success() {
							enableForm( spFormElem, formData );
						},
						/**
						 * Show error message on error.
						 *
						 * @since 3.7.1
						 */
						error() {
							showError( spFormElem, formData, i18n.invalid );
							spFormElem.find( ':not(.simpay-errors)' ).remove();
						}
					} );
				} );
		} catch {
			// Enable form.
			enableForm( spFormElem, formData );

			debugLog( 'Your payment form will not be checked for robots:', '' );
			debugLog( 'Unable to generate reCAPTCHA token. Please ensure you are using v3 of the reCAPTCHA and you have entered valid keys in Simple Pay > Settings > General.', '' );
		}
	} );
} );
