/* global simpayUpdatePaymentMethod, Stripe, jQuery, _ */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies.
 */
import {
	updatePaymentMethod as updateSubscriptionPaymentMethod,
} from './payments/subscription.js';

import {
	setup as setupCardPaymentMethod,
	create as createCardPaymentMethod,
} from './payment-methods/card';

function handleError( error, spFormElem, formData ) {
	// @todo DRY with other form handling.
	if ( ! _.isObject( error ) ) {
		return;
	}

	const {
		responseJSON,
		responseText,
		message,
	} = error;

	const foundMessage = ( responseJSON && responseJSON.message ? responseJSON.message : responseText );
	const errorMessage = message ? message : foundMessage;

	const {
		showError,
		enableForm,
	} = window.simpayApp;

	const {
		debugLog,
	} = window.spShared;

	showError( spFormElem, formData, errorMessage );
	enableForm( spFormElem, formData );

	debugLog( 'Payment Form Error:', error );
}

/**
 * Update a Customer with a new Source.
 *
 * @param {Object} result PaymentMethod object.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
async function handlePaymentMethodUpdate( result, spFormElem, formData ) {
	if ( result.error ) {
		throw result.error;
	}

	const customer_id = spFormElem.find( 'input[name="customer_id"]' ).val();
	const subscription_id = spFormElem.find( 'input[name="subscription_id"]' ).val();
	const subscription_key = spFormElem.find( 'input[name="subscription_key"]' ).val();
	const _wpnonce = spFormElem.find( 'input[name="_wpnonce"]' ).val();
	const source_id = result.source.id;

	// Update a Subscription's PaymentMethod
	await updateSubscriptionPaymentMethod(
		subscription_id,
		{
			_wpnonce,
			subscription_key,
			source_id,
			customer_id,
		}
	)
		.fail( ( error ) => handleError( error, spFormElem, formData ) );

	window.location.replace( window.location.href );
}

/**
 * Submit an "Update Paymnent Method" form.
 *
 * @param {Event} e Submit event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function submitForm( e, spFormElem, formData ) {
	e.preventDefault();

	// Remove existing errors.
	window.simpayApp.showError( spFormElem, formData, '' );

	// Disable form while processing.
	window.simpayApp.disableForm( spFormElem, formData, true );

	try {
		createCardPaymentMethod( spFormElem, formData )
			.then( ( result ) => handlePaymentMethodUpdate( result, spFormElem, formData ) )
			.catch( ( error ) => handleError( error, spFormElem, formData ) );
	} catch ( error ) {
		handleError( error, spFormElem, formData );
	}
}

/**
 * Setup "Update Paymnent Method" form.
 */
function setupForm() {
	const formEl = document.getElementById( 'simpay-form-update-payment-method' );

	if ( ! formEl ) {
		return;
	}

	const {
		i18n,
		stripe: {
			key,
		},
	} = simpayUpdatePaymentMethod;

	const formData = {
		formDisplayType: 'embedded',
		checkoutButtonLoadingText: i18n.loading,
		checkoutButtonText: i18n.submit,
		stripeParams: {
			key,
		},
	};

	const spFormElem = jQuery( formEl );
	spFormElem.stripeInstance = Stripe( formData.stripeParams.key );

	// Setup Stripe Elements.
	setupCardPaymentMethod( spFormElem, formData );

	formEl.addEventListener( 'submit', ( e ) => submitForm( e, spFormElem, formData ) );
}

// DOM Ready.
domReady( setupForm );
