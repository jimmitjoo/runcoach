/* global wpApiSettings */

/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * Internal dependencies.
 */
import { apiRequest } from '@wpsimplepay/utils';

/**
 * Create a Subscription object based on current formData.
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 * @return {Promise} AJAX promise.
 */
export function create( data = {}, spFormElem, formData ) {
	return apiRequest( 'v2/subscription', {
		form_values: serialize( spFormElem[ 0 ], { hash: true } ),
		form_data: formData,
		form_id: formData.formId,
		...data,
	} );
}

/**
 * Update a Subscription's payment method.
 *
 * Requires a Subscription's key and linked Customer for verification.
 * Takes direct arguments vs. spFormElem and full form data.
 *
 * @param {string} subscription_id ID of the Subscription.
 * @param {Object} form_values {
 *
 *   @type {string} nonce Form nonce.
 *   @type {string} subscription_key Subscription secret key.
 *   @type {string} source_id ID of the Source.
 *   @type {string} customer_id ID of the Customer.
 * }
 * @return {Promise} AJAX promise.
 */
export function updatePaymentMethod( subscription_id, args ) {
	const {
		customer_id,
	} = args;

	return apiRequest( `v2/subscription/payment_method/${ subscription_id }/${ customer_id }`, {
		form_values: args,
	} );
}

/**
 * Handle server response/next actions for a PaymentIntent.
 *
 * Use the Subscription's invoice to determine if action needs ot be taken.
 *
 * @param {Object} subscription \Stripe\Subscription.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function handleServerResponse( subscription, spFormElem, formData ) {
	const {
		status,
		latest_invoice: {
			payment_intent: paymentIntent,
		},
		pending_setup_intent: setupIntent,
	} = subscription;

	const {
		stripeParams: {
			success_url: successUrl,
			error_url: errorUrl,
		},
	} = formData;

	if ( ! ( paymentIntent || setupIntent ) ) {
		return false;
	}

	// Initial charge is required.
	if ( paymentIntent ) {
		const {
			status: paymentIntentStatus,
			client_secret: paymentIntentClientSecret,
			last_payment_error: paymentIntentError,
		} = paymentIntent;

		// Subscription is active, and intent needs no further action.
		// Subscription is trialing, and intent needs no further action.
		if (
			( 'active' === status && 'succeeded' === paymentIntentStatus ) ||
			( 'trialing' === status && 'succeeded' === paymentIntentStatus )
		) {
			return false;
		}

		// Could not complete with current payment method, allow client to try again.
		if ( 'incomplete' === status && 'requires_payment_method' === paymentIntentStatus ) {
			if ( paymentIntentError.message ) {
				throw {
					message: paymentIntentError.message,
				};
			}

			return false;
		}

		// Handle 3D Secure.
		if ( 'incomplete' === status && 'requires_action' === paymentIntentStatus ) {
			return spFormElem.stripeInstance.handleCardPayment(
				paymentIntentClientSecret
			)
				.then( ( result ) => {
					if ( result.error ) {
						throw result.error;
					}

					return false;
				} );
		}
	}

	// No initial charge needed, setup for future.
	if ( setupIntent ) {
		const {
			status: setupIntentStatus,
			client_secret: setupIntentClientSecret,
		} = setupIntent;

		// Handle 3D Secure.
		if ( 'requires_action' === setupIntentStatus ) {
			return spFormElem.stripeInstance.handleCardSetup(
				setupIntentClientSecret
			)
				.then( ( result ) => {
					if ( result.error ) {
						throw result.error;
					}

					return false;
				} );
		// Could not complete with current payment method, allow client to try again.
		} else if ( 'requires_payment_method' === setupIntentStatus ) {
			return false;
		}
	}

	// Unhandled status. Direct to error page.
	return window.location.href = errorUrl;
}
