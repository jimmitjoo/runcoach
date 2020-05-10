/* global _, $ */

/**
 * Internal dependencies.
 */
import { update as updateCustomAmount } from '@wpsimplepay/pro/frontend/components/custom-amount.js';
import { handle as handleSource } from '@wpsimplepay/pro/frontend/payment-methods/card';

/**
 * Setup and enable Payment Request Button if needed.
 *
 * @param {Object} spFormElem Form element.
 * @param {Object} formData Payment form data.
 */
export function setup( e, spFormElem, formData ) {
	const {
		formId,
		formInstance,
		formDisplayType,
		stripeParams: {
			country,
			currency,
		},
		hasPaymentRequestButton,
	} = formData;

	if ( ! hasPaymentRequestButton ) {
		return;
	}

	const {
		id,
		i18n,
		requestPayerName,
		requestPayerEmail,
		requestShipping,
		shippingOptions,
		type,
	} = hasPaymentRequestButton;

	const {
		triggerBrowserValidation,
		disableForm,
	} = window.simpayApp;

	const {
		paymentRequestButtons,
	} = window.simpayAppPro;

	const {
		convertToCents,
	} = window.spShared;

	const {
		stripeInstance: stripe,
		cart,
	} = spFormElem;

	const stripeElements = stripe.elements();

	const key = `${ formInstance }-${ formId }`;

	// Generate initial state of button. Eventually used to generate a request.
	paymentRequestButtons[ key ] = stripe.paymentRequest( {
		country,
		currency: currency.toLowerCase(),
		total: {
			label: i18n.totalLabel,
			amount: cart.getTotal(),
		},
		displayItems: getDisplayItems( spFormElem, formData ),
		requestPayerName,
		requestPayerEmail,
		requestShipping,
	} );

	// Create the button element to render.
	const prButton = stripeElements.create( 'paymentRequestButton', {
		paymentRequest: paymentRequestButtons[ key ],
		style: {
			paymentRequestButton: {
				type,
			},
		},
	} );

	// Check the availability of the Payment Request API.
	// @todo Remove anonymous function usage.
	paymentRequestButtons[ key ]
		.canMakePayment()
		.then( ( result ) => {
			// Hide container if no payment can be made.
			if ( null === result ) {
				const containers = document.querySelectorAll( `form[data-simpay-form-id="${ formId }"] #${ id }` );

				if ( containers.length > 0 ) {
					_.each( containers, ( container ) => ( container.style.display = 'none' ) );
				}

				return;
			}

			let toMount;

			/**
			 * Due to lack of formInstance context during Overlay toggles we can reference
			 * the last instance of the NodeList for PRB containers (based on formId) instead.
			 *
			 * This ensures the PRB that appears in the overlays (which is always the last
			 * in the NodeList) is always the one being mounted (or remounted).
			 *
			 * @link https://github.com/wpsimplepay/wp-simple-pay-pro/issues/1002
			 * @link https://github.com/wpsimplepay/wp-simple-pay-pro/issues/610
			 * @link https://github.com/wpsimplepay/wp-simple-pay-pro/issues/645
			 *
			 * @see {overlays.js:setup}
			 */
			if ( 'overlay' === formDisplayType ) {
				const buttons = document.querySelectorAll( `form[data-simpay-form-id="${ formData.formId }"] #${ formData.hasPaymentRequestButton.id } .simpay-payment-request-button-container__button` );

				toMount = buttons[ buttons.length - 1 ];
			} else {
				toMount = document.querySelector( `form[data-simpay-form-instance="${ formData.formInstance }"] #${ formData.hasPaymentRequestButton.id } .simpay-payment-request-button-container__button` );
			}

			if ( toMount ) {
				toMount.innerHTML = '';
				prButton.mount( toMount );
			}

			// Ensure form is valid before continuing.
			prButton.on( 'click', function( e ) {
				if ( ! paymentRequestIsValid( spFormElem, formData ) ) {
					e.preventDefault();

					// Show browser validation.
					triggerBrowserValidation( spFormElem, formData );
				}

				// Update custom amount and recalculate, but do not attempt to update PRB.
				const isCustomAmountValid = updateCustomAmount( null, spFormElem, formData, false );

				if ( ! isCustomAmountValid ) {
					e.preventDefault();
				}

				// Update items for a final time.
				update( spFormElem, formData );
			} );
		} );

	/**
	 * Update shipping options for request.
	 * There are no defined shipping methods, so this is merely to satisfy the API requirements.
	 *
	 * @todo Remove anonymous function usage.
	 * @todo Populate hidden fields so the values are sent through?
	 *
	 * @param {Object} e Payment Request Button event.
	 */
	paymentRequestButtons[ key ].on( 'shippingaddresschange', function( e ) {
		e.updateWith( {
			status: 'success',
			shippingOptions: shippingOptions,
		} );
	} );

	/**
	 * Handle token once created.
	 *
	 * @param {Object} e Payment Request Button event.
	 */
	paymentRequestButtons[ key ].on( 'source', function( e ) {
		const {
			payerEmail,
			payerName,
			complete,
		} = e;

		disableForm( spFormElem, formData, true );

		complete( 'success' );

		if ( payerEmail && '' !== payerEmail ) {
			$( '<input>' ).attr( {
				type: 'hidden',
				name: 'simpay_email',
				value: payerEmail,
			} ).appendTo( spFormElem );
		}

		if ( payerName && '' !== payerName ) {
			$( '<input>' ).attr( {
				type: 'hidden',
				name: 'simpay_name',
				value: payerName,
			} ).appendTo( spFormElem );
		}

		return handleSource( e, spFormElem, formData );
	} );
}

/**
 * Update Payment Request Button when data changes.
 *
 * @todo Separate out total and item generators.
 *
 * @param {Object} spFormElem Form element.
 * @param {Object} formData Payment form data.
 */
export function update( spFormElem, formData ) {
	const {
		formId,
		formInstance,
		hasPaymentRequestButton,
	} = formData;

	if ( ! hasPaymentRequestButton ) {
		return;
	}

	const {
		i18n,
	} = hasPaymentRequestButton;

	const {
		paymentRequestButtons,
	} = window.simpayAppPro;

	const {
		convertToCents,
	} = window.spShared;

	const {
		cart,
	} = spFormElem;

	const key = `${ formInstance }-${ formId }`;

	// Enable if not previously setup.
	if ( ! paymentRequestButtons.hasOwnProperty( key ) ) {
		setup( null, spFormElem, formData );
	}

	paymentRequestButtons[ key ].update( {
		total: {
			label: i18n.totalLabel,
			amount: cart.getTotal(),
		},
		displayItems: getDisplayItems( spFormElem, formData ),
	} );
}

/**
 * Custom check to see if relevant custom fields are valid before allowing Payment Button Request.
 *
 * @param {Object} spFormElem Form element.
 * @param {Object} formData Payment form data.
 */
export function paymentRequestIsValid( spFormElem, formData ) {
	/**
	 * Determine if a form control is a "classic" field, meaning it is needed
	 * to submit a standard payment form instead of using the Payment Request API.
	 *
	 * @param {HTMLElement} control Form control.
	 * @return {bool} If the field is classic.
	 */
	function isClassicField( control ) {
		const classicFields = [
			'simpay-customer-name-container',
			'simpay-email-container',
			'simpay-card-container',
			'simpay-address-container',
			'simpay-address-street-container',
			'simpay-address-city-container',
			'simpay-address-state-container',
			'simpay-address-zip-container',
			'simpay-address-country-container',
			'simpay-telephone-container',
		];

		const classList = control.classList;
		let is = false;

		classList.forEach( function( className ) {
			if ( -1 !== classicFields.indexOf( className ) ) {
				is = true;
			}
		} );

		return is;
	}

	let requiredFieldsValid = true;

	_.each( spFormElem[0].querySelectorAll( '.simpay-form-control' ), function( control ) {
		const classicField = isClassicField( control );

		if ( classicField ) {
			return;
		}

		const inputs = control.querySelectorAll( 'input' );

		_.each( inputs, function( input ) {
			if ( ! input.required ) {
				return;
			}

			if ( ! input.validity.valid ) {
				requiredFieldsValid = false;
			}
		} );
	} );

	return requiredFieldsValid;
}

/**
 * Generate Payment Request API displayItems from form data.
 *
 * @param {Object} spFormElem Form element.
 * @param {Object} formData Form data.
 * @return {Array}
 */
export function getDisplayItems( spFormElem, formData ) {
	const displayItems = [];

	const {
		convertToCents,
	} = window.spShared;

	const {
		cart,
	} = spFormElem;

	const {
		hasPaymentRequestButton: {
			i18n,
		}
	} = formData;

	// Add subscription plan to list.
	try {
		const plan = cart.getLineItem( 'base' );

		displayItems.push( {
			label: plan.title ? plan.title : i18n.planLabel,
			amount: plan.getSubtotal(),
		} );
	} catch ( error ) {
		// Item couldn't be found, do not add it.
	}

	// Combine setup fees to a single line.
	let setupFeeAmount = 0;

	try {
		const planSetupFee = cart.getLineItem( 'plan-setup-fee' );
		setupFeeAmount += planSetupFee.getSubtotal();
	} catch ( error ) {
		// Item couldn't be found, do not add it.
	}

	try {
		const setupFee = cart.getLineItem( 'setup-fee' );
		setupFeeAmount += setupFee.getSubtotal();
	} catch ( error ) {
		// Item couldn't be found, do not add it.
	}

	if ( setupFeeAmount > 0 ) {
		displayItems.push( {
			label: i18n.setupFeeLabel,
			amount: setupFeeAmount,
		} );
	}

	// Add tax to list.
	if ( cart.taxPercent > 0 ) {
		displayItems.push( {
			label: i18n.taxLabel.replace( '%s', cart.getTaxPercent() ),
			amount: cart.getTax(),
		} );
	}

	// Add tax to list.
	if ( cart.getDiscount() > 0 ) {
		displayItems.push( {
			label: i18n.couponLabel.replace( '%s', cart.getCoupon().name ),
			amount: cart.getDiscount() * -1,
		} );
	}

	return displayItems;
}
