/**
 * WordPress dependencies.
 */
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies.
 */
import {
	create as createCustomer,
} from '@wpsimplepay/core/frontend/payments/customer.js';

import {
	create as createPaymentIntent,
	handleServerResponse as handlePaymentIntentServerResponse,
} from '@wpsimplepay/pro/frontend/payments/paymentintent.js';

import {
	create as createSubscription,
	handleServerResponse as handleSubscriptionServerResponse,
} from '@wpsimplepay/pro/frontend/payments/subscription.js';

import {
	getElementsConfig,
} from '@wpsimplepay/pro/frontend/payment-forms/stripe-elements';

export * from './cart';

/**
 * Handle a Stripe Payment method.
 *
 * Depending on the form type, follow one of two flows:
 *
 * 1. https://stripe.com/docs/billing/subscriptions/payment
 * 2. https://stripe.com/docs/payments/payment-intents/quickstart#manual-confirmation-flow
 *
 * @param {Object} result Stripe createSource result.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export async function handle( result, spFormElem, formData ) {
	if ( result.error ) {
		throw result.error;
	}

	// Customer is required for both flows.
	const customer = await createCustomer(
		{
			source_id: result.source.id,
		},
		spFormElem,
		formData
	);

	const successUrl = addQueryArgs( formData.stripeParams.success_url, {
		customer_id: customer.id,
	} );

	let paymentIntentRequiresAction;

	if ( formData.isSubscription || formData.isRecurring ) {
		const subscription = await createSubscription(
			{
				customer_id: customer.id,
			},
			spFormElem,
			formData
		);

		// Handle next actions on Subscription's PaymentIntent.
		paymentIntentRequiresAction = await handleSubscriptionServerResponse( subscription, spFormElem, formData );
	} else {
		const paymentIntent = await createPaymentIntent(
			{
				customer_id: customer.id,
				payment_method_id: result.source.id,
			},
			spFormElem,
			formData
		);

		// No SCA needed, redirect.
		if ( ! paymentIntent.requires_action ) {
			return window.location.href = successUrl;
		}

		// Handle next actions on PaymentIntent.
		paymentIntentRequiresAction = await handlePaymentIntentServerResponse(
			{
				customer_id: customer.id,
				payment_intent: paymentIntent,
			},
			spFormElem,
			formData
		);
	}

	// Nothing else is needed, redirect.
	if ( false === paymentIntentRequiresAction ) {
		return window.location.href = successUrl;
	}
}

/**
 * Sets up a Stripe Elements Card field and binds events.
 *
 * @link https://stripe.com/docs/stripe-js/reference#elements-create
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( spFormElem, formData ) {
	const elements = spFormElem.stripeInstance.elements();
	const cardEl = spFormElem[ 0 ].querySelector( '.simpay-card-wrap' );

	if ( ! cardEl ) {
		return;
	}

	// Create Element Card instance.
	spFormElem.cardElementInstance = elements.create(
		'card',
		getElementsConfig( spFormElem, cardEl )
	);

	// Mount and setup Element card instance.
	spFormElem.cardElementInstance.mount( cardEl );

	// Enable form when Card field is ready.
	spFormElem.cardElementInstance.on( 'ready', () => window.simpayApp.enableForm( spFormElem, formData ) );

	// Live feedback when card updates.
	spFormElem.cardElementInstance.on( 'change', ( result ) => {
		window.simpayApp.enableForm( spFormElem, formData );

		if ( result.error ) {
			window.simpayApp.showError( spFormElem, formData, result.error.message );
		} else {
			window.simpayApp.showError( spFormElem, formData, '' );
		}
	} );
}

/**
 * Creates a Card Source.
 *
 * @todo Switch to a true PaymentMethod.
 *       Stripe currently converts these behind the scenes.
 *       https://stripe.com/docs/stripe-js/reference#stripe-create-payment-method
 *
 * @link https://stripe.com/docs/stripe-js/reference#stripe-create-source
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 * @return {Promise}
 */
export function create( spFormElem, formData ) {
	const {
		currency,
	} = formData;

	const owner = getOwnerData( spFormElem, formData );

	return spFormElem.stripeInstance
		.createSource(
			spFormElem.cardElementInstance,
			{
				type: 'card',
				currency,
				owner,
			}
		);
}

/**
 * Handles a Card Payment Method creation error.
 *
 * @param {Object} error Error object from Promise or general throw.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function onError( error, spFormElem, formData ) {
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
 * Find card owner data in the form.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function getOwnerData( spFormElem, formData ) {
	const billingAddressContainer = spFormElem.find( '.simpay-billing-address-container' );

	const name = spFormElem.find( '.simpay-customer-name' ).val() || null;
	const email = spFormElem.find( '.simpay-email' ).val() || null;
	const phone = spFormElem.find( '.simpay-telephone' ).val() || null;
	const address = 0 !== billingAddressContainer.length ? {
		line1: billingAddressContainer.find( '.simpay-address-street' ).val() || null,
		city: billingAddressContainer.find( '.simpay-address-city' ).val() || null,
		state: billingAddressContainer.find( '.simpay-address-state' ).val() || null,
		postal_code: billingAddressContainer.find( '.simpay-address-zip' ).val() || null,
		country: billingAddressContainer.find( '.simpay-address-country' ).val() || null,
	} : null;

	return {
		name,
		email,
		phone,
		address,
	};
}
