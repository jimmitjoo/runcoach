/* global _ */

// @todo Model after pro/assets/js/frontend/payment-forms/stripe-elements

/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * Internal dependencies.
 */
import { convertFormDataToCartData } from '@wpsimplepay/cart';
import { Cart } from '@wpsimplepay/core/frontend/payment-methods/stripe-checkout';
import { create as createCustomer } from '@wpsimplepay/core/frontend/payments/customer.js';
import { create as createSession } from '@wpsimplepay/core/frontend/payments/session.js';

/**
 * Submit payment form.
 *
 * @param {Event} e Form submit Event.
 * @param {Element} cardElementInstance Stripe Elements card.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
async function submitForm( e, spFormElem, formData ) {
	e.preventDefault();

	const {
		showError,
		enableForm,
		disableForm,
		triggerBrowserValidation,
	} = window.simpayApp;

	const {
		debugLog,
	} = window.spShared;

	// Remove existing errors.
	showError( spFormElem, formData, '' );

	// Disable form while processing.
	disableForm( spFormElem, formData, true );

	// HTML5 validation check.
	if ( ! spFormElem[ 0 ].checkValidity() ) {
		triggerBrowserValidation( spFormElem, formData );
		enableForm( spFormElem, formData );

		return;
	}

	// Allow further validation.
	//
	// jQuery( document.body ).on( 'simpayBeforeStripePayment', function( e, spFormElem, formData ) {
	//  formData.isValid = false;
	// } );
	spFormElem.trigger( 'simpayBeforeStripePayment', [ spFormElem, formData ] );

	if ( ! formData.isValid ) {
		enableForm( spFormElem, formData );

		return;
	}

	try {
		let customer_id;

		// Only generate a custom Customer if we need to map on-page form fields.
		if ( formData.hasCustomerFields ) {
			const customer = await createCustomer(
				{},
				spFormElem,
				formData
			);

			customer_id = customer.id;
		} else {
			customer_id = null;
		}

		// Generate a Checkout Session.
		const session = await createSession(
			{
				customer_id,
			},
			spFormElem,
			formData
		);

		spFormElem.stripeInstance.redirectToCheckout( {
			sessionId: session.sessionId,
		} ).then( ( result ) => {
			throw result.error;
		} );
	} catch ( error ) {
		if ( _.isObject( error ) ) {
			const { responseJSON, responseText, message } = error;
			const errorMessage = message ? message : ( responseJSON && responseJSON.message ? responseJSON.message : responseText );

			showError( spFormElem, formData, errorMessage );
		}

		debugLog( 'Payment Form Error:', error );
		enableForm( spFormElem, formData );
	}
}

/**
 * Bind events for Stripe Checkout.
 *
 * @param {Event} e simpayBindCoreFormEventsAndTriggers Event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( e, spFormElem, formData ) {
	const {
		enableForm,
		disableForm,
		showError,
		isStripeCheckoutForm,
	} = window.simpayApp;

	const {
		debugLog,
	} = window.spShared;

	// Don't continue if this form is not using Stripe Checkout.
	if ( ! isStripeCheckoutForm( formData ) ) {
		return;
	}

	disableForm( spFormElem, formData, true );

	const submitBtn = spFormElem.find( '.simpay-payment-btn' );

	if ( 0 === submitBtn.length ) {
		return;
	}

	try {
		// Create a cart.

		// Convert legacay data in to something usable.
		const {
			items,
			currency,
			taxPercent,
			isNonDecimalCurrency,
		} = convertFormDataToCartData( formData );

		const cart = new Cart( {
			currency,
			taxPercent,
			isNonDecimalCurrency,
		} );

		if ( items.length > 0 ) {
			items.forEach( ( item ) => {
				cart.addLineItem( item );
			} );
		}

		// Attach cart.
		spFormElem.cart = cart;

		// Submit form when button is clicked.
		submitBtn[ 0 ].addEventListener( 'click', ( e ) => submitForm( e, spFormElem, formData ) );

		enableForm( spFormElem, formData );
	} catch ( error ) {
		showError( spFormElem, formData, error.message );
		debugLog( error.id, error.message );
	}
}
